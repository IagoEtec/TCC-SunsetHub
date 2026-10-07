<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/upload_helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../front-end/login.php');
    exit;
}

// CSRF: impede que outro site force o upload/alteração da foto de
// perfil da vítima usando a sessão autenticada dela.
csrf_exigir();

function falharFoto(string $mensagem): never
{
    echo '<script>alert(' . json_encode($mensagem, JSON_UNESCAPED_UNICODE) . '); window.location.href="../front-end/perfil.php";</script>';
    exit;
}

$diretorio = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'perfis';
if (!is_dir($diretorio) && !@mkdir($diretorio, 0775, true)) {
    falharFoto('Não foi possível criar a pasta uploads/perfis. Verifique a permissão de escrita do XAMPP.');
}
if (!is_writable($diretorio)) {
    falharFoto('A pasta uploads/perfis não tem permissão de escrita.');
}

$novoNome = 'perfil_' . (int) $_SESSION['id'] . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.jpg';
$caminhoFisico = $diretorio . DIRECTORY_SEPARATOR . $novoNome;
$caminhoWeb = '../uploads/perfis/' . $novoNome;

try {
    $recorte = (string) ($_POST['foto_cortada'] ?? '');
    $imagemSalva = $recorte !== ''
        ? salvar_base64_jpeg($recorte, $caminhoFisico, 5 * 1024 * 1024, [0.99, 1.01])
        : salvar_upload_como_jpeg($_FILES['foto'] ?? [], $caminhoFisico, 8 * 1024 * 1024, [0.1, 10.0]);
    if (!$imagemSalva || !is_file($caminhoFisico)) falharFoto('Selecione uma imagem e confirme o recorte.');

    $stmt = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['id']]);
    $antiga = $stmt->fetchColumn();
    $stmt = $pdo->prepare('UPDATE usuarios SET foto = ? WHERE id = ?');
    $stmt->execute([$caminhoWeb, $_SESSION['id']]);
    $_SESSION['foto'] = $caminhoWeb;

    if ($antiga && !preg_match('/^https?:\/\//i', $antiga)) {
        $arquivoAntigo = dirname(__DIR__) . '/' . ltrim(str_replace('../', '', $antiga), '/');
        if (is_file($arquivoAntigo) && realpath($arquivoAntigo) !== realpath($caminhoFisico)) @unlink($arquivoAntigo);
    }
    header('Location: ../front-end/perfil.php?status=sucesso');
    exit;
} catch (Throwable $e) {
    if (is_file($caminhoFisico)) @unlink($caminhoFisico);
    error_log('Falha no upload de foto: ' . $e->getMessage());
    falharFoto('Erro ao atualizar a foto. Confira a permissão da pasta uploads/perfis e tente novamente.');
}
