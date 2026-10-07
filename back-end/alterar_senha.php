<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once 'conexao.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// CSRF: alterar a senha é uma operação sensível; sem o token, um site
// externo poderia forçar essa troca usando a sessão já autenticada da
// vítima (ex.: trocando a senha dela sem que ela perceba).
csrf_exigir();

$senhaAtual = (string) ($_POST['senha_atual'] ?? '');
$novaSenha = (string) ($_POST['nova_senha'] ?? '');
$confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

// Busca a senha atual do usuário
$stmt = $pdo->prepare("
    SELECT senha
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$_SESSION['id']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a conta não existir mais (ex.: excluída por um admin em outra aba),
// evita chamar password_verify() com um valor nulo, o que geraria um
// erro de tipo e poderia expor detalhes internos.
if (!$usuario || empty($usuario['senha'])) {
    echo "
    <script>
        alert('Não foi possível validar sua conta. Faça login novamente.');
        window.location='../front-end/login.php';
    </script>
    ";
    exit;
}

// Verifica se a senha atual está correta
if (!password_verify($senhaAtual, $usuario['senha'])) {

    echo "
    <script>
        alert('A senha atual está incorreta!');
        window.location='../front-end/perfil.php';
    </script>
    ";

    exit;
}

// Verifica confirmação da nova senha (comparação estrita)
if (!hash_equals($novaSenha, $confirmarSenha)) {

    echo "
    <script>
        alert('As novas senhas não coincidem!');
        window.location='../front-end/perfil.php';
    </script>
    ";

    exit;
}

// Exige um tamanho mínimo para a nova senha, mesma regra do cadastro.
if (strlen($novaSenha) < 8 || strlen($novaSenha) > 128) {
    echo "
    <script>
        alert('A nova senha deve ter entre 8 e 128 caracteres.');
        window.location='../front-end/perfil.php';
    </script>
    ";
    exit;
}

// Gera novo hash
$novaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

// Atualiza a senha
$stmt = $pdo->prepare("
    UPDATE usuarios
    SET senha = ?
    WHERE id = ?
");

$stmt->execute([
    $novaHash,
    $_SESSION['id']
]);

echo "
<script>
    alert('Senha alterada com sucesso!');
    window.location='../front-end/perfil.php?status=sucesso';
</script>
";
?>
