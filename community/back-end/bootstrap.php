<?php
declare(strict_types=1);

require_once __DIR__ . '/../../back-end/seguranca.php';
iniciar_sessao_segura();
require_once __DIR__ . '/../../back-end/conexao.php';

function comunidade_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
}

require_once __DIR__ . '/schema.php';

function comunidade_usuario(PDO $pdo, bool $redirectToLogin = false): array
{
    $id = filter_var($_SESSION['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
        if ($redirectToLogin) {
            header('Location: ../../front-end/login.php?next=community', true, 302);
            exit;
        }
        comunidade_json(['ok' => false, 'error' => 'Entre na sua conta do portal para acessar a comunidade.'], 401);
    }
    $stmt = $pdo->prepare('SELECT id, nome, email, foto, COALESCE(xp, 0) AS xp, COALESCE(patente, \'Novato\') AS patente FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        if ($redirectToLogin) {
            encerrar_sessao_segura();
            header('Location: ../../front-end/login.php?next=community', true, 302);
            exit;
        }
        comunidade_json(['ok' => false, 'error' => 'Sua sessão não está mais válida. Entre novamente no portal.'], 401);
    }
    $handle = strtolower((string)strstr((string)$user['email'], '@', true));
    $handle = preg_replace('/[^a-z0-9_]/', '', $handle) ?: ('membro' . (int)$user['id']);
    return [
        'id' => (int)$user['id'], 'name' => (string)$user['nome'], 'username' => $handle,
        'email' => (string)$user['email'], 'bio' => 'Membro da comunidade de educação financeira.',
        'level' => max(1, (int)floor((int)$user['xp'] / 250) + 1), 'xp' => (int)$user['xp'],
        'rank_name' => (string)($user['patente'] ?: 'Novato'),
        'avatar_color' => '#ffad52', 'avatar' => $user['foto'] ?? null,
    ];
}

function comunidade_exigir_csrf(): void
{
    $provided = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $expected = (string)($_SESSION['csrf_token'] ?? '');
    if ($provided === '' || $expected === '' || !hash_equals($expected, $provided)) {
        comunidade_json(['ok' => false, 'error' => 'Sessão expirada ou token de segurança inválido. Recarregue a página e tente novamente.'], 403);
    }
}
