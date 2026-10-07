<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once 'conexao.php';

// Validação de método: este endpoint só deve aceitar POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front-end/login.php');
    exit;
}

// CSRF: garante que a requisição partiu do formulário de login legítimo,
// e não de um site externo tentando logar a vítima em outra conta ou
// abusar deste endpoint (ex.: para automatizar tentativas de força bruta).
csrf_exigir();

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
if (strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('E-mail ou senha incorretos!'); window.history.back();</script>";
    exit;
}
$voltarParaComunidade = (($_POST['next'] ?? '') === 'community');
$senha = (string) ($_POST['senha'] ?? '');
$ip = obter_ip_cliente();

try {
    // ===================== PROTEÇÃO CONTRA FORÇA BRUTA =====================
    // Problema: sem isso, um atacante pode tentar milhares de senhas por
    // minuto contra o mesmo e-mail. A verificação é feita ANTES de tocar
    // no banco de usuários para evitar até esse custo desnecessário.
    $segundosRestantes = bruteforce_esta_bloqueado($pdo, $email, $ip);
    if ($segundosRestantes !== null) {
        $minutos = (int) ceil($segundosRestantes / 60);
        echo "<script>alert('Muitas tentativas de login. Tente novamente em aproximadamente {$minutos} minuto(s).'); window.history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se existe usuário E se ele tem senha definida antes de validar
    if ($usuario && !empty($usuario['senha']) && password_verify($senha, $usuario['senha'])) {

        bruteforce_registrar_sucesso($pdo, $email, $ip);

        // Regenera o identificador da sessão após autenticar com sucesso.
        // Problema: se um atacante conseguir fixar/conhecer o ID de sessão
        // de uma vítima ANTES do login (Session Fixation) — por exemplo,
        // enviando um link com um session id escolhido por ele — esse ID
        // continuaria válido e autenticado depois do login, permitindo
        // que o atacante assuma a sessão já logada.
        // Solução: gera um novo ID de sessão (mantendo os dados) logo após
        // a autenticação, invalidando qualquer ID anterior ao login.
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $_SESSION['id'] = $usuario['id'];

        $_SESSION['usuario'] = [
            'id'       => $usuario['id'],
            'nome'     => $usuario['nome'],
            'email'    => $usuario['email'],
            'telefone' => $usuario['telefone'],
            'foto'     => !empty($usuario['foto']) ? $usuario['foto'] : null,
        ];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['telefone'] = $usuario['telefone'];
        $_SESSION['foto'] = $usuario['foto'];

        header('Location: ' . ($voltarParaComunidade ? '../community/front-end/index.php' : '../front-end/home.php'));
        exit;

    } elseif ($usuario && empty($usuario['senha'])) {
        // Conta existe, mas foi criada só via Google — não tem senha ainda.
        // Não contamos como falha de força bruta (não é uma tentativa de
        // adivinhar senha), mas também não confirmamos nada sensível além
        // do que o próprio fluxo de cadastro já expõe.
        echo "<script>alert('Esta conta foi criada com Google. Faça login pelo Google ou defina uma senha na tela de cadastro.'); window.history.back();</script>";
        exit;

    } else {
        bruteforce_registrar_falha($pdo, $email, $ip);
        echo "<script>alert('E-mail ou senha incorretos!'); window.history.back();</script>";
        exit;
    }

} catch (PDOException $e) {
    // Problema: expor $e->getMessage() ao usuário pode vazar nomes de
    // tabelas/colunas e detalhes de configuração do banco.
    // Solução: registra o detalhe técnico só no log do servidor e mostra
    // uma mensagem genérica na tela.
    tratar_erro_bd($e, 'processa_login');
    echo "<script>alert('Não foi possível concluir o login agora. Tente novamente em instantes.'); window.history.back();</script>";
    exit;
}
