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

// CSRF: sem isso, outro site poderia associar um itemId arbitrário
// (potencialmente controlado pelo atacante) à conta bancária conectada
// da vítima, usando a sessão dela.
csrf_exigir_header();

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$itemId = $body['itemId'] ?? null;
$usuarioId = $_SESSION['id'] ?? null;

if (!$usuarioId || !is_string($itemId) || trim($itemId) === '' || strlen($itemId) > 255) {
    pluggyJsonResponse(['success' => false, 'error' => 'Item ou usuário inválido.'], 400);
}

$stmt = $pdo->prepare('UPDATE usuarios SET itemid = :itemid WHERE id = :id');
$stmt->execute([':itemid' => trim($itemId), ':id' => $usuarioId]);

pluggyJsonResponse(['success' => true]);

