<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once("../back-end/conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: ../front-end/login.php");
    exit();
}

$usuario_id = $_SESSION['id'];

// valores padrão (evita erro quando abre a página)
$valor_inicial = 0;
$aporte_mensal = 0;
$taxa_juros = 0;
$tempo = 0;

$valor_final = 0;
$total_investido = 0;
$lucro = 0; 

// só calcula se veio POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $valor_inicial = floatval($_POST['valor_inicial']);
    $aporte_mensal = floatval($_POST['aporte_mensal']);
    $taxa_juros = floatval($_POST['taxa_juros']) / 100;
    $tempo = intval($_POST['tempo']);

    $valor_final = $valor_inicial;

    for ($i = 1; $i <= $tempo; $i++) {
        $valor_final = ($valor_final + $aporte_mensal) * (1 + $taxa_juros);
    }

    $total_investido = $valor_inicial + ($aporte_mensal * $tempo);

    $lucro = $valor_final - $total_investido;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Financeiro</title>
  <link rel="stylesheet" href="../css/calculadora.css?v=2">
  <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>
<body class="calculadora-page">

<?php

include_once 'navbar.php';

?>

<div class="container">
<main class="main">

<!-- HERO -->
<section class="hero-calculadora">
    <div class="hero-text">
        <span class="badge">Planejamento Financeiro</span>

        <h1>Simule seus investimentos e descubra quanto seu dinheiro pode render.</h1>

        <p>
            Utilize nossa calculadora de juros compostos para planejar seus objetivos financeiros.
        </p>
    </div>
</section>

<!-- FORM -->
<section class="content" id="content">

<div class="calculadora-box">

<div class="titulo-calculadora">
    <h2>Calculadora de Juros Compostos</h2>
</div>

<form method="post" action="#content">
<div class="form-grid">

    <div class="input-group">
        <label>Valor Inicial (R$)</label>
        <input name="valor_inicial" type="number" required>
    </div>

    <div class="input-group">
        <label>Aporte Mensal (R$)</label>
        <input name="aporte_mensal" type="number" required>
    </div>

    <div class="input-group">
        <label>Taxa de Juros (% ao mês)</label>
        <input name="taxa_juros" type="number" step="0.01" required>
    </div>

    <div class="input-group">
        <label>Tempo (meses)</label>
        <input name="tempo" type="number" required>
    </div>

</div>

    <button type="submit" class="btn-calcular">
        Calcular
    </button>
</form>

<div class="info-aporte">
    <span>Como a simulação funciona</span>

    <p>
        Nesta calculadora, os aportes mensais são considerados no
        <strong>início de cada mês</strong>. Isso significa que cada novo
        aporte começa a render juros imediatamente no mês em que é realizado,
        simulando um cenário em que o investimento é feito logo no início do período.
    </p>
</div>

<!-- RESULTADOS -->
<div class="resultado">

    <div class="resultado-card">
        <h4>Total Investido</h4>
        <p>
            R$ <?= number_format($total_investido, 2, ',', '.'); ?>
        </p>
    </div>

    <div class="resultado-card">
        <h4>Juros Ganhos</h4>
        <p>
            R$ <?= number_format($lucro, 2, ',', '.'); ?>
        </p>
    </div>

    <div class="resultado-card destaque">
        <h4>Patrimônio Final</h4>
        <p>
            R$ <?= number_format($valor_final, 2, ',', '.'); ?>
        </p>
    </div>

</div>

</div>
</section>

</main>
</div>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>
