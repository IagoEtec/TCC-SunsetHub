<?php
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
// Encerramento completo: limpa variáveis, expira o cookie no navegador
// e destrói a sessão no servidor (evita reuso do cookie antigo).
encerrar_sessao_segura();
header('Location: ../front-end/home.php');
?>

