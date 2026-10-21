<?php
// Espera que a página que incluir este arquivo defina $paginaAtual
// Valores possíveis: dashboard | usuarios | produtos | movimentacoes
$paginaAtual = $paginaAtual ?? '';
?>
<aside class="admin-sidebar">
    <div class="admin-brand">
        <img src="../img/logo.png" alt="AFDE">
        <span>Admin</span>
    </div>

    <nav class="admin-nav">
        <a href="index.php" class="<?= $paginaAtual === 'dashboard' ? 'ativo' : '' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="usuarios.php" class="<?= $paginaAtual === 'usuarios' ? 'ativo' : '' ?>">
            <i class="fas fa-users"></i> Usuários
        </a>
        <a href="produtos.php" class="<?= $paginaAtual === 'produtos' ? 'ativo' : '' ?>">
            <i class="fas fa-store"></i> Produtos / Loja
        </a>
        <a href="movimentacoes.php" class="<?= $paginaAtual === 'movimentacoes' ? 'ativo' : '' ?>">
            <i class="fas fa-exchange-alt"></i> Movimentações
        </a>
        <a href="moderacao.php" class="<?= $paginaAtual === 'moderacao' ? 'ativo' : '' ?>">
            <i class="fas fa-flag"></i> Moderação
        </a>
        <a href="../front-end/home.php" class="sair">
            <i class="fas fa-sign-out-alt"></i> Sair
        </a>
    </nav>
</aside>
