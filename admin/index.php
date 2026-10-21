<?php
require_once 'admin_auth.php';
$paginaAtual = 'dashboard';

// ===================== DADOS VINDOS DO BANCO =====================
$totalUsuarios      = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalMovimentacoes = (int) $pdo->query("SELECT COUNT(*) FROM movimentacoes")->fetchColumn();

$totalEntradas = (float) $pdo->query(
    "SELECT COALESCE(SUM(valor), 0) FROM movimentacoes WHERE tipo = 'entrada'"
)->fetchColumn();

$totalSaidas = (float) $pdo->query(
    "SELECT COALESCE(SUM(valor), 0) FROM movimentacoes WHERE tipo = 'saida'"
)->fetchColumn();

$saldo = $totalEntradas - $totalSaidas;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin AFDE</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="admin-content">
    <div class="admin-topo">
        <div>
            <h1>Dashboard</h1>
            <p>Olá, <?= htmlspecialchars($adminLogadoNome) ?> — visão geral da plataforma.</p>
        </div>
    </div>

    <div class="cards-grid">
        <div class="card-stat">
            <span class="rotulo">Usuários cadastrados</span>
            <span class="valor"><?= $totalUsuarios ?></span>
        </div>

        <div class="card-stat">
            <span class="rotulo">Movimentações registradas</span>
            <span class="valor"><?= $totalMovimentacoes ?></span>
        </div>

        <div class="card-stat positivo">
            <span class="rotulo">Total de entradas</span>
            <span class="valor">R$ <?= number_format($totalEntradas, 2, ',', '.') ?></span>
        </div>

        <div class="card-stat negativo">
            <span class="rotulo">Total de saídas</span>
            <span class="valor">R$ <?= number_format($totalSaidas, 2, ',', '.') ?></span>
        </div>

        <div class="card-stat <?= $saldo >= 0 ? 'positivo' : 'negativo' ?>">
            <span class="rotulo">Saldo (entradas - saídas)</span>
            <span class="valor">R$ <?= number_format($saldo, 2, ',', '.') ?></span>
        </div>
    </div>
</main>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=99" defer></script>
</body>
</html>

