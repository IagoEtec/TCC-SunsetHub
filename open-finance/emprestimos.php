<?php
    require_once __DIR__ . '/../back-end/seguranca.php';
    iniciar_sessao_segura();
    header("Content-Type: application/json; charset=utf-8");

    require_once "../back-end/conexao.php";
    require_once "pluggy-helper.php";

    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Usuário não autenticado']);
        exit;
    }

    $usuarioId = $_SESSION['id'];
    $itemId = getItemIdDoUsuario($pdo, $usuarioId);

    if (!$itemId) {
        echo json_encode(['error' => 'Nenhuma conta bancária conectada', 'emprestimos' => []]);
        exit;
    }

    $apiKey = pluggyAuth();

    if (!$apiKey) {
        http_response_code(500);
        echo json_encode(['error' => 'Falha ao autenticar na Pluggy']);
        exit;
    }

    $resposta = pluggyGet(
        "https://api.pluggy.ai/loans?itemId=" . urlencode($itemId),
        $apiKey
    );

    if ($resposta['code'] !== 200) {
        http_response_code($resposta['code']);
        echo json_encode(['error' => 'Falha ao buscar empréstimos', 'details' => null]);
        exit;
    }

    echo json_encode([
        'emprestimos' => $resposta['data']['results'] ?? []
    ]);
    exit;

