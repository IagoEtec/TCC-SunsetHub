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

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$transacaoId = trim((string) ($body['transacaoId'] ?? ''));
$categoria = trim((string) ($body['categoria'] ?? ''));

$categorias = openFinanceCategoriasPermitidas();
$permitidas = array_values(array_unique(array_merge(...array_values($categorias))));

if ($transacaoId === '' || strlen($transacaoId) > 255 || !in_array($categoria, $permitidas, true)) {
    pluggyJsonResponse(['success' => false, 'error' => 'Transação ou categoria inválida.'], 400);
}

try {
    openFinanceEnsureSchema($pdo);
    $stmt = $pdo->prepare(<<<'SQL'
        UPDATE open_finance_transacoes
        SET categoria = :categoria,
            categoria_origem = 'manual'
        WHERE usuario_id = :usuario_id
          AND transacao_id = :transacao_id
    SQL);
    $stmt->execute([
        ':categoria' => $categoria,
        ':usuario_id' => (int) $_SESSION['id'],
        ':transacao_id' => $transacaoId,
    ]);

    if ($stmt->rowCount() < 1) {
        pluggyJsonResponse(['success' => false, 'error' => 'Transação não encontrada.'], 404);
    }

    pluggyJsonResponse([
        'success' => true,
        'categoria' => $categoria,
    ]);
} catch (Throwable $exception) {
    // Não expõe $exception->getMessage() na resposta (pode revelar nomes
    // de tabelas/colunas); loga o detalhe técnico só no servidor.
    tratar_erro_bd($exception, 'open-finance/classificar');
    pluggyJsonResponse([
        'success' => false,
        'error' => 'Não foi possível salvar a categoria.',
    ], 500);
}
