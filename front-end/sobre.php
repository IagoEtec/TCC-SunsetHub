<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();

require_once("../back-end/conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: ../front-end/login.php");
    exit;
}


/* ============================================================
   BUSCAR USUÁRIO E TEMA
============================================================ */

$stmt = $pdo->prepare("
    SELECT id, nome, email, tema
    FROM usuarios
    WHERE id = ?
");

$stmt->execute([$_SESSION['id']]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$usuario) {
    session_destroy();

    header("Location: ../front-end/login.php");
    exit;
}


/* ============================================================
   TEMA
============================================================ */

$tema = $usuario['tema'] ?? ($_SESSION['tema'] ?? 'sistema');

$temas_permitidos = [
    'claro',
    'escuro',
    'sistema'
];

if (!in_array($tema, $temas_permitidos, true)) {
    $tema = 'sistema';
}

?>

<!DOCTYPE html>

<html
    lang="pt-BR"
    class="<?= htmlspecialchars($tema, ENT_QUOTES, 'UTF-8') ?>"
>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sobre o AFDE</title>

    <link
        rel="stylesheet"
        href="../css/sobre.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/icon?family=Material+Icons"
        rel="stylesheet"
    >

    <link
        rel="icon"
        type="image/png"
        href="../img/favicon.png"
    >

    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>


<body>
<?php include_once 'navbar.php'; ?>


    <!-- ========================================================
         BACKGROUND
    ========================================================= -->

    <div class="background_shapes">

        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>

    </div>


    <!-- ========================================================
         BOTÃO VOLTAR
    ========================================================= -->

    <a
        href="configuracoes.php"
        class="btn_voltar"
    >

        <span class="material-icons">
            arrow_back
        </span>

        Voltar

    </a>


    <!-- ========================================================
         CONTEÚDO
    ========================================================= -->

    <main class="sobre_container">


        <!-- ====================================================
             HERO
        ===================================================== -->

        <section class="sobre_hero">

            <div class="sobre_logo">

                <span class="material-icons">
                    account_balance_wallet
                </span>

            </div>


            <div class="sobre_hero_text">

                <span class="sobre_tag">
                    SOBRE O FINCONTROL
                </span>

                <h1>
                    Controle suas finanças
                    <span>de forma simples.</span>
                </h1>

                <p>
                    O AFDE foi criado para ajudar você a
                    organizar sua vida financeira, acompanhar seus
                    gastos e tomar decisões melhores sobre seu dinheiro.
                </p>

            </div>

        </section>


        <!-- ====================================================
             SOBRE O PROJETO
        ===================================================== -->

        <section class="sobre_card glass-card">

            <div class="sobre_card_icon">

                <span class="material-icons">
                    info_outline
                </span>

            </div>


            <div class="sobre_card_content">

                <h2>
                    Sobre o AFDE
                </h2>

                <p>
                    O AFDE é uma plataforma de gerenciamento
                    financeiro desenvolvida para tornar o controle
                    das finanças pessoais mais fácil, organizado e
                    acessível.
                </p>

                <p>
                    Com ele, você pode registrar receitas e despesas,
                    acompanhar suas movimentações financeiras e
                    visualizar melhor para onde seu dinheiro está indo.
                </p>

                <p>
                    Nossa proposta é oferecer uma experiência simples
                    e intuitiva, evitando complicações desnecessárias
                    para que você possa se concentrar no que realmente
                    importa: cuidar da sua vida financeira.
                </p>

            </div>

        </section>


        <!-- ====================================================
             RECURSOS
        ===================================================== -->

        <h2 class="section_title">
            O que você pode fazer
        </h2>


        <section class="recursos_grid">


            <div class="recurso_card glass-card">

                <div class="recurso_icon">

                    <span class="material-icons">
                        swap_vert
                    </span>

                </div>

                <h3>
                    Controle de transações
                </h3>

                <p>
                    Registre suas receitas e despesas para manter
                    seu histórico financeiro sempre organizado.
                </p>

            </div>


            <div class="recurso_card glass-card">

                <div class="recurso_icon">

                    <span class="material-icons">
                        analytics
                    </span>

                </div>

                <h3>
                    Acompanhe suas finanças
                </h3>

                <p>
                    Tenha uma visão mais clara da sua movimentação
                    financeira e acompanhe seus resultados.
                </p>

            </div>


            <div class="recurso_card glass-card">

                <div class="recurso_icon">

                    <span class="material-icons">
                        category
                    </span>

                </div>

                <h3>
                    Organização por categorias
                </h3>

                <p>
                    Organize seus lançamentos por categorias para
                    entender melhor seus hábitos financeiros.
                </p>

            </div>


            <div class="recurso_card glass-card">

                <div class="recurso_icon">

                    <span class="material-icons">
                        download
                    </span>

                </div>

                <h3>
                    Exportação de dados
                </h3>

                <p>
                    Tenha acesso aos seus dados financeiros e,
                    quando disponível, exporte suas informações.
                </p>

            </div>


        </section>


        <!-- ====================================================
             MISSÃO
        ===================================================== -->

        <section class="missao_card glass-card">

            <div class="missao_visual">

                <div class="missao_circle">

                    <span class="material-icons">
                        flag
                    </span>

                </div>

            </div>


            <div class="missao_content">

                <span class="sobre_tag">
                    NOSSA MISSÃO
                </span>

                <h2>
                    Tornar o controle financeiro mais simples.
                </h2>

                <p>
                    Acreditamos que organizar o dinheiro não precisa
                    ser complicado. O AFDE busca oferecer
                    ferramentas simples para que qualquer pessoa
                    consiga acompanhar suas finanças com mais clareza.
                </p>

            </div>

        </section>


        <!-- ====================================================
             PRINCÍPIOS
        ===================================================== -->

        <h2 class="section_title">
            Nossos princípios
        </h2>


        <section class="principios_list">


            <div class="principio_item glass-card">

                <div class="principio_icon">

                    <span class="material-icons">
                        touch_app
                    </span>

                </div>

                <div>

                    <h3>
                        Simplicidade
                    </h3>

                    <p>
                        Recursos fáceis de entender e utilizar no
                        dia a dia.
                    </p>

                </div>

            </div>


            <div class="principio_item glass-card">

                <div class="principio_icon">

                    <span class="material-icons">
                        security
                    </span>

                </div>

                <div>

                    <h3>
                        Segurança
                    </h3>

                    <p>
                        Priorizamos a proteção das informações e
                        o acesso seguro à sua conta.
                    </p>

                </div>

            </div>


            <div class="principio_item glass-card">

                <div class="principio_icon">

                    <span class="material-icons">
                        visibility
                    </span>

                </div>

                <div>

                    <h3>
                        Transparência
                    </h3>

                    <p>
                        Queremos que você tenha clareza sobre seus
                        dados e sobre o funcionamento da plataforma.
                    </p>

                </div>

            </div>


        </section>


        <!-- ====================================================
             VERSÃO
        ===================================================== -->

        <section class="versao_card glass-card">

            <div class="versao_icon">

                <span class="material-icons">
                    verified
                </span>

            </div>


            <div>

                <h3>
                    AFDE
                </h3>

                <p>
                    Sua ferramenta para uma vida financeira
                    mais organizada.
                </p>

                <span class="versao_texto">
                    Versão 1.0
                </span>

            </div>

        </section>


        <!-- ====================================================
             FOOTER
        ===================================================== -->

        <footer class="sobre_footer">

            <div class="footer_logo">

                <span class="material-icons">
                    account_balance_wallet
                </span>

                AFDE

            </div>

            <p>
                Feito para ajudar você a cuidar melhor das suas finanças.
            </p>

            <span class="copyright">
                © <?= date("Y") ?> AFDE. Todos os direitos reservados.
            </span>

        </footer>


    </main>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>

