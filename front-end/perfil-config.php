<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}
header('Location: perfil.php');
exit;
