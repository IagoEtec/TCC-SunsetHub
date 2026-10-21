<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

try {
    $user = comunidade_usuario($pdo);
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        http_response_code(405);
        exit;
    }

    $relative = (string)($_GET['file'] ?? '');
    if (!preg_match('~^uploads/chat/[a-f0-9]{36}\.(jpg|png|webp)$~D', $relative, $match)) {
        http_response_code(404);
        exit;
    }

    $uid = (int)$user['id'];
    $authorized = false;

    $direct = $pdo->prepare("SELECT 1
        FROM comunidade_mensagens_privadas m
        JOIN comunidade_amizades a
          ON a.usuario_a = LEAST(m.remetente_id, m.destinatario_id)
         AND a.usuario_b = GREATEST(m.remetente_id, m.destinatario_id)
         AND a.status = 'accepted'
        WHERE m.anexo_path = ? AND (m.remetente_id = ? OR m.destinatario_id = ?)
        LIMIT 1");
    $direct->execute([$relative, $uid, $uid]);
    $authorized = (bool)$direct->fetchColumn();

    if (!$authorized) {
        $group = $pdo->prepare('SELECT 1 FROM comunidade_mensagens_grupo m JOIN comunidade_membros cm ON cm.grupo_id=m.grupo_id AND cm.usuario_id=? WHERE m.anexo_path=? LIMIT 1');
        $group->execute([$uid, $relative]);
        $authorized = (bool)$group->fetchColumn();
    }

    if (!$authorized) {
        // Use the same response for unknown and unauthorized files.
        http_response_code(404);
        exit;
    }

    $base = realpath(dirname(__DIR__) . '/uploads/chat');
    $file = realpath(dirname(__DIR__) . '/' . $relative);
    if ($base === false || $file === false || !is_file($file) || is_link(dirname(__DIR__) . '/' . $relative)) {
        http_response_code(404);
        exit;
    }
    $prefix = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strncmp($file, $prefix, strlen($prefix)) !== 0) {
        http_response_code(404);
        exit;
    }

    $mime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$match[1]];
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string)filesize($file));
    header('Content-Disposition: inline; filename="chat-image.' . $match[1] . '"');
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    header('Cross-Origin-Resource-Policy: same-origin');
    session_write_close();
    readfile($file);
    exit;
} catch (Throwable $e) {
    error_log('[community media] ' . $e->getMessage());
    http_response_code(404);
    exit;
}
