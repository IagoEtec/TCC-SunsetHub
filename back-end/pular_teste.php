<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once __DIR__ . '/conexao.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Faça login para continuar.']);
    exit;
}

// CSRF (via header, chamada feita por fetch()): impede que outro site
// dispare esta ação em nome do usuário autenticado.
csrf_exigir_header();

try {
    // Compatibilidade com bancos criados antes do sistema de patentes.
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN patente VARCHAR(30) NULL AFTER banner");
    } catch (PDOException $ignored) {
        // A coluna já existe.
    }
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN xp INT NOT NULL DEFAULT 0 AFTER patente");
    } catch (PDOException $ignored) {
        // A coluna já existe.
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET patente = ?, xp = COALESCE(xp, 0) WHERE id = ?');
    $stmt->execute(['Ferro 1', (int) $_SESSION['id']]);

    echo json_encode([
        'ok' => true,
        'patente' => 'Ferro 1',
        'message' => 'Parabéns! Você conquistou a patente Ferro 1.'
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Não foi possível salvar sua patente agora.']);
}
