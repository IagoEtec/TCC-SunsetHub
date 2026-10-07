<?php

require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';
require_once '../open-finance/pluggy-helper.php';

function calendarioJson(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['id'])) {
    calendarioJson([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], 401);
}

$usuarioId = (int) $_SESSION['id'];
$mes = filter_input(INPUT_GET, 'mes', FILTER_VALIDATE_INT) ?: (int) date('m');
$ano = filter_input(INPUT_GET, 'ano', FILTER_VALIDATE_INT) ?: (int) date('Y');

if ($mes < 1 || $mes > 12 || $ano < 2000 || $ano > 2100) {
    calendarioJson([
        'sucesso' => false,
        'mensagem' => 'Mês ou ano inválido.'
    ], 400);
}

try {
    openFinanceEnsureSchema($pdo);

    $stmtUsuario = $pdo->prepare('SELECT data_criacao FROM usuarios WHERE id = ?');
    $stmtUsuario->execute([$usuarioId]);
    $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        calendarioJson([
            'sucesso' => false,
            'mensagem' => 'Usuário não encontrado.'
        ], 404);
    }

    $timezone = new DateTimeZone('America/Sao_Paulo');
    $dataCriacao = new DateTimeImmutable((string) $usuario['data_criacao'], $timezone);
    $inicioMes = new DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $ano, $mes), $timezone);
    $proximoMes = $inicioMes->modify('+1 month');
    $agora = new DateTimeImmutable('now', $timezone);
    $limiteAtual = $agora->format('Y-m-d H:i:s');

    if ($inicioMes < $dataCriacao->modify('first day of this month')->setTime(0, 0)) {
        calendarioJson([
            'sucesso' => false,
            'mensagem' => 'Esse mês é anterior à criação da conta.'
        ], 400);
    }

    $inicio = $inicioMes->format('Y-m-d H:i:s');
    $fim = $proximoMes > $agora ? $limiteAtual : $proximoMes->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare(<<<'SQL'
        SELECT
            id,
            descricao,
            tipo,
            valor,
            categoria,
            data_criacao,
            'manual' AS fonte
        FROM movimentacoes
        WHERE usuario_id = :usuario_id_manual
          AND data_criacao >= :inicio_manual
          AND data_criacao < :fim_manual

        UNION ALL

        SELECT
            id,
            descricao,
            tipo,
            valor,
            categoria,
            data_transacao AS data_criacao,
            'open_finance' AS fonte
        FROM open_finance_transacoes
        WHERE usuario_id = :usuario_id_open
          AND data_transacao >= :inicio_open
          AND data_transacao < :fim_open

        ORDER BY data_criacao ASC
    SQL);
    $stmt->execute([
        ':usuario_id_manual' => $usuarioId,
        ':inicio_manual' => $inicio,
        ':fim_manual' => $fim,
        ':usuario_id_open' => $usuarioId,
        ':inicio_open' => $inicio,
        ':fim_open' => $fim,
    ]);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categorias = [];
    $receitas = 0.0;
    $despesas = 0.0;
    $manuais = 0;
    $openFinance = 0;

    foreach ($movimentacoes as $movimentacao) {
        $valor = abs((float) $movimentacao['valor']);
        $tipo = strtolower((string) $movimentacao['tipo']);

        if ($tipo === 'entrada') {
            $receitas += $valor;
        } else {
            $despesas += $valor;
            $categoria = trim((string) ($movimentacao['categoria'] ?? '')) ?: 'Outros';
            $categorias[$categoria] = ($categorias[$categoria] ?? 0.0) + $valor;
        }

        if (($movimentacao['fonte'] ?? '') === 'open_finance') {
            $openFinance++;
        } else {
            $manuais++;
        }
    }

    arsort($categorias);
    $labels = array_keys($categorias);
    $valores = array_map(static fn(float $valor): float => round($valor, 2), array_values($categorias));

    calendarioJson([
        'sucesso' => true,
        'periodo' => [
            'mes' => $mes,
            'ano' => $ano,
            'inicio' => $inicio,
            'fim' => $fim,
            'ate_hoje' => $proximoMes > $agora,
        ],
        'conta' => [
            'mes_criacao' => (int) $dataCriacao->format('m'),
            'ano_criacao' => (int) $dataCriacao->format('Y'),
        ],
        'fontes' => [
            'manual' => $manuais,
            'open_finance' => $openFinance,
        ],
        'grafico' => [
            'labels' => $labels,
            'valores' => $valores,
        ],
        'resumo' => [
            'receitas' => round($receitas, 2),
            'despesas' => round($despesas, 2),
            'resultado' => round($receitas - $despesas, 2),
        ],
        'quantidade' => count($movimentacoes),
    ]);
} catch (Throwable $exception) {
    tratar_erro_bd($exception, 'dados_grafico');
    calendarioJson([
        'sucesso' => false,
        'mensagem' => 'Não foi possível carregar os dados do calendário.',
    ], 500);
}
