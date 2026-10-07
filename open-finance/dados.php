<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once '../back-end/conexao.php';
require_once 'pluggy-helper.php';

if (!isset($_SESSION['id'])) {
    pluggyJsonResponse(['error' => 'Usuário não autenticado.'], 401);
}

$usuarioId = (int) $_SESSION['id'];
$sincronizar = !isset($_GET['sync']) || $_GET['sync'] !== '0';

try {
    openFinanceEnsureSchema($pdo);
    $itemId = getItemIdDoUsuario($pdo, $usuarioId);
    $connected = (bool) $itemId;
    $sincronizado = 0;
    $avisos = [];
    $emprestimos = [];
    $investimentos = [];

    if ($sincronizar && $itemId) {
        try {
            $apiKey = pluggyAuth();
            if (!$apiKey) {
                $avisos[] = 'A integração Pluggy não está configurada. Defina PLUGGY_CLIENT_ID e PLUGGY_CLIENT_SECRET.';
            } else {
                $dadosApi = pluggyDadosDoItem($itemId, $apiKey);
                $sincronizado = openFinancePersistirTransacoes($pdo, $usuarioId, $dadosApi['transacoes'] ?? []);
                $emprestimos = $dadosApi['emprestimos'] ?? [];
                $investimentos = $dadosApi['investimentos'] ?? [];
                $avisos = $dadosApi['avisos'] ?? [];
            }
        } catch (Throwable $exception) {
            $avisos[] = 'A sincronização não terminou agora; os dados salvos anteriormente continuam disponíveis.';
        }
    }

    if (!$itemId) {
        $avisos[] = 'Nenhuma conta bancária conectada.';
    }

    $stmt = $pdo->prepare(<<<'SQL'
        SELECT
            transacao_id AS id,
            account_id AS accountId,
            conta_nome AS contaNome,
            tipo,
            status,
            valor AS amount,
            descricao AS description,
            categoria,
            categoria_sugerida AS categoriaSugerida,
            moeda AS currencyCode,
            data_transacao AS date
        FROM open_finance_transacoes
        WHERE usuario_id = :usuario_id
        ORDER BY data_transacao DESC, id DESC
    SQL);
    $stmt->execute([':usuario_id' => $usuarioId]);
    $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($transacoes as &$transacao) {
        $transacao['amount'] = (float) $transacao['amount'];
        $transacao['categoria'] = $transacao['categoria'] ?: 'Outros';
    }
    unset($transacao);

    pluggyJsonResponse([
        'connected' => $connected,
        'transacoes' => $transacoes,
        'emprestimos' => $emprestimos,
        'investimentos' => $investimentos,
        'sincronizado' => $sincronizado,
        'ultima_sincronizacao' => $sincronizar && $itemId ? date('c') : null,
        'avisos' => array_values(array_unique($avisos)),
    ]);
} catch (Throwable $exception) {
    tratar_erro_bd($exception, 'open-finance/dados');
    pluggyJsonResponse([
        'error' => 'Não foi possível carregar os dados da conta conectada.',
    ], 500);
}
