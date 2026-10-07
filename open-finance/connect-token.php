<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once 'pluggy-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pluggyJsonResponse(['error' => 'Método não permitido.'], 405);
}

if (!isset($_SESSION['id'])) {
    pluggyJsonResponse(['error' => 'Usuário não autenticado.'], 401);
}

csrf_exigir_header();

$apiKey = pluggyAuth();
if (!$apiKey) {
    pluggyJsonResponse(['error' => 'A integração Pluggy não está configurada.'], 503);
}

$response = pluggyRequest('POST', '/connect_token', $apiKey, [
    'options' => ['clientUserId' => 'usuario-' . (int) $_SESSION['id']],
]);

if ($response['code'] !== 200 || empty($response['data']['accessToken'])) {
    pluggyJsonResponse([
        'error' => 'Não foi possível gerar o token de conexão.',
        'details' => $response['data']['message'] ?? $response['error'] ?? null,
    ], $response['code'] ?: 502);
}

pluggyJsonResponse(['accessToken' => $response['data']['accessToken']]);

