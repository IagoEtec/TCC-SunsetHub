<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once("../back-end/conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: ../front-end/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacidade e dados | AFDE</title>

    <link rel="stylesheet" href="../css/paginas-suporte.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>

<body>
<?php include_once 'navbar.php'; ?>

<div class="background_shapes">
    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>
</div>

<a href="configuracoes.php" class="btn_voltar">
    <span class="material-icons">arrow_back</span>
    Voltar
</a>

<main class="pagina_container">

    <!-- ===================== CABEÇALHO ===================== -->
    <section class="pagina_header">
        <div class="pagina_header_icon">
            <span class="material-icons">shield</span>
        </div>
        <div>
            <h1>Privacidade e dados</h1>
            <p>Controle o uso das suas informações dentro do AFDE</p>
        </div>
    </section>

    <!-- ===================== SEÇÃO: SEUS DADOS ===================== -->
    <h2 class="pagina_section_title">Seus dados</h2>

    <div class="pagina_card glass-card">
        <div class="pagina_card_icon">
            <span class="material-icons">download</span>
        </div>
        <div class="pagina_card_text">
            <h3>Exportar meus dados</h3>
            <p>Baixe uma cópia de todas as suas informações cadastradas e transações registradas no AFDE, em formato CSV.</p>
        </div>
        <button type="button" class="btn_pagina_secundario" disabled title="Exportação indisponível nesta versão">
            <span class="material-icons">file_download</span>
            Exportar
        </button>
    </div>

    <div class="pagina_card glass-card">
        <div class="pagina_card_icon">
            <span class="material-icons">visibility_off</span>
        </div>
        <div class="pagina_card_text">
            <h3>Uso dos seus dados</h3>
            <p>Utilizamos suas informações exclusivamente para o funcionamento do AFDE: cálculo de saldos, gráficos e relatórios financeiros. Seus dados não são vendidos ou compartilhados com terceiros para fins de publicidade.</p>
        </div>
    </div>

    <!-- ===================== SEÇÃO: PERMISSÕES ===================== -->
    <h2 class="pagina_section_title">Permissões</h2>

    <div class="pagina_card glass-card">
        <div class="pagina_toggle_row">
            <div class="pagina_card_text">
                <h3>Compartilhar dados de uso anônimos</h3>
                <p>Ajude a melhorar o AFDE enviando estatísticas de uso sem identificação pessoal.</p>
            </div>
            <label class="toggle_switch">
                <input type="checkbox" disabled aria-label="Preferência não persistida; controle desativado">
                <span class="toggle_slider"></span>
            </label>
        </div>
    </div>

    <div class="pagina_card glass-card">
        <div class="pagina_toggle_row">
            <div class="pagina_card_text">
                <h3>Personalização de recomendações</h3>
                <p>Permita que o AFDE use seu histórico financeiro para sugerir metas e categorias.</p>
            </div>
            <label class="toggle_switch">
                <input type="checkbox" disabled aria-label="Preferência não persistida; controle desativado">
                <span class="toggle_slider"></span>
            </label>
        </div>
    </div>

    <!-- ===================== SEÇÃO: ZONA DE PERIGO ===================== -->
    <h2 class="pagina_section_title">Zona de risco</h2>

    <div class="pagina_card glass-card pagina_card_danger">
        <div class="pagina_card_icon pagina_card_icon_danger">
            <span class="material-icons">delete_forever</span>
        </div>
        <div class="pagina_card_text">
            <h3>Excluir minha conta</h3>
            <p>Essa ação é permanente. Todos os seus dados, transações e configurações serão apagados e não poderão ser recuperados.</p>
        </div>
        <button type="button" class="btn_pagina_perigo" disabled aria-disabled="true" title="Exclusão indisponível nesta versão">Excluir conta (indisponível)</button>
    </div>

</main>

<p class="pagina_notice" role="status">Exportação e exclusão de conta estão temporariamente indisponíveis até a implementação segura dos serviços correspondentes. Nenhum dado será apagado.</p>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>

