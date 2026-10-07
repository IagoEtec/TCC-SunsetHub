<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
header('Content-Type: application/json; charset=utf-8');

require_once '../back-end/conexao.php';
require_once 'pluggy-helper.php';

if (!isset($_SESSION['id'])) {
    pluggyJsonResponse(['error' => 'Usuário não autenticado.'], 401);
}

try {
    $usuarioId = (int) $_SESSION['id'];
    openFinanceEnsureSchema($pdo);
    $itemId = getItemIdDoUsuario($pdo, $usuarioId);

    if (!$itemId) {
        pluggyJsonResponse([
            'connected' => false,
            'error' => 'Nenhuma conta bancária conectada.',
            'transacoes' => [],
        ]);
    }

    $apiKey = pluggyAuth();
    if (!$apiKey) {
        pluggyJsonResponse([
            'connected' => true,
            'error' => 'Falha ao autenticar na Pluggy.',
            'transacoes' => [],
        ], 503);
    }

    $dados = pluggyDadosDoItem($itemId, $apiKey);
    $sincronizado = openFinancePersistirTransacoes($pdo, $usuarioId, $dados['transacoes'] ?? []);

    pluggyJsonResponse([
        'connected' => true,
        'transacoes' => $dados['transacoes'] ?? [],
        'sincronizado' => $sincronizado,
        'avisos' => $dados['avisos'] ?? [],
    ]);
} catch (Throwable $exception) {
    tratar_erro_bd($exception, 'open-finance/transacoes');
    pluggyJsonResponse([
        'error' => 'Não foi possível consultar as transações.',
    ], 500);
}
