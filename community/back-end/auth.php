<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
try {
    $user = comunidade_usuario($pdo);
    comunidade_instalar_schema($pdo);
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        comunidade_json(['ok' => false, 'error' => 'Método não permitido.'], 405);
    }
    comunidade_json(['ok' => true, 'user' => $user, 'csrf' => csrf_token()]);
} catch (PDOException $e) {
    tratar_erro_bd($e, 'sunsethub_auth');
    comunidade_json(['ok' => false, 'error' => 'Não foi possível validar sua sessão.'], 500);
}
