<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/google-config.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URL);
$client->addScope('email');
$client->addScope('profile');

if (!isset($_GET['code'])) {
    $_SESSION['google_oauth_state'] = bin2hex(random_bytes(32));
    $client->setState($_SESSION['google_oauth_state']);
    header('Location: ' . $client->createAuthUrl());
    exit;
}

$stateRecebido = (string)($_GET['state'] ?? '');
$stateEsperado = $_SESSION['google_oauth_state'] ?? null;
unset($_SESSION['google_oauth_state']);
if (!$stateEsperado || !hash_equals($stateEsperado, $stateRecebido)) {
    http_response_code(403);
    exit('Não foi possível validar esta tentativa de login com o Google.');
}

try {
    $client->authenticate((string)$_GET['code']);
    $oauth = new Google_Service_Oauth2($client);
    $userInfo = $oauth->userinfo->get();
    $email = strtolower(trim((string)$userInfo->email));
    $googleId = trim((string)$userInfo->id);
    if ($email === '' || $googleId === '' || empty($userInfo->verified_email)) {
        throw new RuntimeException('Conta Google sem e-mail verificado.');
    }

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existente) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (oauth_uid, nome, email, foto, provedor) VALUES (?, ?, ?, ?, 'google')");
        $stmt->execute([$googleId, (string)$userInfo->name, $email, (string)$userInfo->picture]);
        $idUsuario = (int)$pdo->lastInsertId();
    } else {
        // Só o mesmo Google subject pode reentrar. Nunca vincular Google a
        // conta local preexistente por e-mail sem confirmação explícita.
        if (!empty($existente['oauth_uid']) && !hash_equals((string)$existente['oauth_uid'], $googleId)) {
            throw new RuntimeException('Identidade Google divergente.');
        }
        if (empty($existente['oauth_uid'])) {
            $_SESSION['mensagem'] = 'Esta conta já existe. Entre com e-mail e senha ou use a recuperação de conta.';
            header('Location: ../front-end/login.php');
            exit;
        }
        $idUsuario = (int)$existente['id'];
    }

    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['id'] = $idUsuario;
    $_SESSION['usuario'] = [
        'id' => $idUsuario,
        'nome' => (string)$userInfo->name,
        'email' => $email,
        'foto' => (string)$userInfo->picture,
    ];
    $_SESSION['nome'] = (string)$userInfo->name;
    $_SESSION['email'] = $email;
    $_SESSION['foto'] = (string)$userInfo->picture;
    header('Location: ../front-end/home.php');
    exit;
} catch (Throwable $e) {
    tratar_erro_bd($e, 'google-callback');
    http_response_code(400);
    exit('Não foi possível concluir o login com o Google.');
}
