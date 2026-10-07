<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once 'conexao.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../front-end/login.php");
    exit;
}

// CSRF: impede que outro site altere nome/e-mail/telefone da vítima
// usando a sessão autenticada dela.
csrf_exigir();

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

if (empty($nome) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "
    <script>
        alert('Preencha todos os campos obrigatórios.');
        window.location='../front-end/perfil.php';
    </script>
    ";
    exit;
}

try {

    // Verifica se já existe outro usuário com o mesmo email
    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE email = ?
        AND id != ?
    ");

    $stmt->execute([
        $email,
        $_SESSION['id']
    ]);

    if ($stmt->rowCount() > 0) {

        echo "
        <script>
            alert('Este e-mail já está sendo utilizado por outra conta.');
            window.location='../front-end/perfil.php';
        </script>
        ";

        exit;
    }

    // Atualiza dados
    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET nome = ?, email = ?, telefone = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $nome,
        $email,
        $telefone,
        $_SESSION['id']
    ]);

    // Atualiza sessão
    $_SESSION['nome'] = $nome;
    $_SESSION['email'] = $email;
    $_SESSION['telefone'] = $telefone;

    echo "
    <script>
        alert('Perfil atualizado com sucesso!');
        window.location='../front-end/perfil.php?status=sucesso';
    </script>
    ";

} catch (PDOException $e) {

    echo "
    <script>
        alert('Erro ao atualizar perfil.');
        window.location='../front-end/perfil.php';
    </script>
    ";
}
?>
