<?php

require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';
require_once '../open-finance/pluggy-helper.php';

function historicoJson(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['id'])) {
    historicoJson(['sucesso' => false, 'mensagem' => 'Usuário não autenticado.'], 401);
}

try {
    openFinanceEnsureSchema($pdo);
    $usuarioId = (int) $_SESSION['id'];
    $stmtUsuario = $pdo->prepare('SELECT data_criacao FROM usuarios WHERE id = ?');
    $stmtUsuario->execute([$usuarioId]);
    $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        historicoJson(['sucesso' => false, 'mensagem' => 'Usuário não encontrado.'], 404);
    }

    $timezone = new DateTimeZone('America/Sao_Paulo');
    $dataCriacao = new DateTimeImmutable((string) $usuario['data_criacao'], $timezone);
    $inicio = $dataCriacao->modify('first day of this month')->setTime(0, 0);
    $agora = new DateTimeImmutable('now', $timezone);

    $stmt = $pdo->prepare(<<<'SQL'
        SELECT
            ano,
            mes,
            categoria,
            SUM(receitas) AS receitas,
            SUM(despesas) AS despesas
        FROM (
            SELECT
                YEAR(data_criacao) AS ano,
                MONTH(data_criacao) AS mes,
                COALESCE(NULLIF(TRIM(categoria), ''), 'Outros') AS categoria,
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) AS receitas,
                SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) AS despesas
            FROM movimentacoes
            WHERE usuario_id = :usuario_id_manual
              AND data_criacao >= :inicio_manual
              AND data_criacao < :fim
            GROUP BY YEAR(data_criacao), MONTH(data_criacao), COALESCE(NULLIF(TRIM(categoria), ''), 'Outros')

            UNION ALL

            SELECT
                YEAR(data_transacao) AS ano,
                MONTH(data_transacao) AS mes,
                COALESCE(NULLIF(TRIM(categoria), ''), 'Outros') AS categoria,
                SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) AS receitas,
                SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END) AS despesas
            FROM open_finance_transacoes
            WHERE usuario_id = :usuario_id_open
              AND data_transacao >= :inicio_open
              AND data_transacao < :fim_open
            GROUP BY YEAR(data_transacao), MONTH(data_transacao), COALESCE(NULLIF(TRIM(categoria), ''), 'Outros')
        ) AS totais
        GROUP BY ano, mes, categoria
        ORDER BY ano ASC, mes ASC, categoria ASC
    SQL);
    $stmt->execute([
        ':usuario_id_manual' => $usuarioId,
        ':inicio_manual' => $inicio->format('Y-m-d H:i:s'),
        ':fim' => $agora->format('Y-m-d H:i:s'),
        ':usuario_id_open' => $usuarioId,
        ':inicio_open' => $inicio->format('Y-m-d H:i:s'),
        ':fim_open' => $agora->format('Y-m-d H:i:s'),
    ]);

    $porMes = [];
    $categorias = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $chave = sprintf('%04d-%02d', (int) $item['ano'], (int) $item['mes']);
        $categoria = trim((string) ($item['categoria'] ?? '')) ?: 'Outros';
        $despesas = round((float) $item['despesas'], 2);
        $receitas = round((float) $item['receitas'], 2);

        if (!isset($porMes[$chave])) {
            $porMes[$chave] = [
                'receitas' => 0.0,
                'despesas' => 0.0,
                'categorias' => [],
            ];
        }

        $porMes[$chave]['receitas'] += $receitas;
        $porMes[$chave]['despesas'] += $despesas;
        if ($despesas > 0) {
            $porMes[$chave]['categorias'][$categoria] =
                ($porMes[$chave]['categorias'][$categoria] ?? 0.0) + $despesas;
            $categorias[$categoria] = true;
        }
    }

    $nomesMeses = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
        5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
    ];
    $meses = [];
    for ($cursor = $inicio; $cursor <= $agora; $cursor = $cursor->modify('+1 month')) {
        $chave = $cursor->format('Y-m');
        $totais = $porMes[$chave] ?? [
            'receitas' => 0.0,
            'despesas' => 0.0,
            'categorias' => [],
        ];
        $categoriasDoMes = [];
        foreach (array_keys($categorias) as $categoria) {
            $categoriasDoMes[$categoria] = round((float) ($totais['categorias'][$categoria] ?? 0), 2);
        }
        $meses[] = [
            'chave' => $chave,
            'rotulo' => $nomesMeses[(int) $cursor->format('n')] . '/' . $cursor->format('Y'),
            'receitas' => round((float) $totais['receitas'], 2),
            'despesas' => round((float) $totais['despesas'], 2),
            'categorias' => $categoriasDoMes,
            'resultado' => round((float) $totais['receitas'] - (float) $totais['despesas'], 2),
        ];
    }

    historicoJson([
        'sucesso' => true,
        'periodo' => [
            'inicio' => $inicio->format('Y-m-d'),
            'fim' => $agora->format('Y-m-d'),
            'descricao' => 'Despesas por categoria de ' . $inicio->format('m/Y') . ' até ' . $agora->format('m/Y') . '.',
        ],
        'categorias' => array_keys($categorias),
        'meses' => $meses,
    ]);
} catch (Throwable $exception) {
    tratar_erro_bd($exception, 'dados_historico');
    historicoJson([
        'sucesso' => false,
        'mensagem' => 'Não foi possível carregar o histórico mensal.',
    ], 500);
}
