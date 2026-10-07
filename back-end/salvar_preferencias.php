<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once("conexao.php");

// ===================== AUTENTICAÇÃO =====================
if (!isset($_SESSION['id'])) {
    header("Location: ../front-end/login.php");
    exit;
}

// ===================== MÉTODO =====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../front-end/configuracoes.php");
    exit;
}

// CSRF: impede que outro site altere as preferências da vítima usando
// a sessão autenticada dela.
csrf_exigir();

// ===================== VALIDAÇÃO (WHITELIST) =====================
$moedasValidas   = ['BRL', 'USD', 'EUR'];
$temasValidos    = ['claro', 'escuro', 'sistema'];
$idiomasValidos  = ['pt-BR', 'en-US', 'es-ES'];

$moeda  = in_array($_POST['moeda'] ?? '', $moedasValidas, true) ? $_POST['moeda'] : 'BRL';
$tema   = in_array($_POST['tema'] ?? '', $temasValidos, true) ? $_POST['tema'] : 'sistema';
$idioma = in_array($_POST['idioma'] ?? '', $idiomasValidos, true) ? $_POST['idioma'] : 'pt-BR';

// ===================== SALVAR NO BANCO =====================
try {
    $update = $pdo->prepare("
        UPDATE usuarios
        SET moeda = ?, tema = ?, idioma = ?
        WHERE id = ?
    ");
    $update->execute([$moeda, $tema, $idioma, $_SESSION['id']]);

    header("Location: ../front-end/configuracoes.php?status=sucesso");
    exit;
} catch (PDOException $e) {
    header("Location: ../front-end/configuracoes.php?status=erro");
    exit;
}

