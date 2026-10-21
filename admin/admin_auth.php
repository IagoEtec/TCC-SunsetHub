<?php
/**
 * admin_auth.php
 * Inclua no TOPO de toda página dentro de /admin/.
 * Não cria um novo sistema de login: reaproveita a sessão criada por
 * back-end/processa_login.php e back-end/google-callback.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../back-end/seguranca.php';
    iniciar_sessao_segura();
}

require_once __DIR__ . '/../back-end/conexao.php';

// 1) Existe usuário logado?
if (!isset($_SESSION['id'])) {
    header('Location: ../front-end/login.php');
    exit;
}

// 2) É admin? Confirma sempre no banco (não confia apenas na sessão),
//    assim uma revogação de permissão já vale no próximo clique.
$stmtAdmin = $pdo->prepare("SELECT id, nome, tipo FROM usuarios WHERE id = ?");
$stmtAdmin->execute([$_SESSION['id']]);
$dadosAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

if (!$dadosAdmin || $dadosAdmin['tipo'] !== 'admin') {
    header('Location: ../front-end/home.php?erro=acesso_negado');
    exit;
}

// Disponível para todas as páginas que incluírem este arquivo
$adminLogadoId   = (int) $dadosAdmin['id'];
$adminLogadoNome = $dadosAdmin['nome'];

