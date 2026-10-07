<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once '../back-end/conexao.php';
require_once 'pluggy-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pluggyJsonResponse(['success' => false, 'error' => 'Método não permitido.'], 405);
}

if (!isset($_SESSION['id'])) {
    pluggyJsonResponse(['success' => false, 'error' => 'Usuário não autenticado.'], 401);
}

csrf_exigir_header();

try {
    $resultado = openFinanceSincronizarUsuario($pdo, (int) $_SESSION['id']);
    pluggyJsonResponse([
        'success' => true,
        'connected' => $resultado['connected'] ?? false,
        'sincronizado' => $resultado['sincronizado'] ?? 0,
        'avisos' => $resultado['avisos'] ?? [],
    ]);
} catch (Throwable $exception) {
    tratar_erro_bd($exception, 'open-finance/sincronizar');
    pluggyJsonResponse([
        'success' => false,
        'error' => 'Não foi possível sincronizar a conta.',
    ], 500);
}
