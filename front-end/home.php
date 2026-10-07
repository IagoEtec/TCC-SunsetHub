<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();

require_once("../back-end/conexao.php");

$tema = 'sistema';

if (isset($_SESSION['id'])) {

    try {

        $stmt = $pdo->prepare("
            SELECT tema
            FROM usuarios
            WHERE id = ?
        ");

        $stmt->execute([$_SESSION['id']]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $usuario &&
            in_array(
                $usuario['tema'],
                ['claro', 'escuro', 'sistema'],
                true
            )
        ) {
            $tema = $usuario['tema'];
        }

    } catch (PDOException $e) {

        $tema = 'sistema';

    }

}

?>


<!DOCTYPE html>
<html
    lang="pt-BR"
    class="<?= htmlspecialchars($tema, ENT_QUOTES, 'UTF-8') ?>"
>


<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AFDE</title>

    <!-- Fonte -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet"
    >

    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../img/favicon.png">

    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>

<body>

    <?php include 'navbar.php'; ?>


    <main>

        <!-- =====================================================
             HERO
        ====================================================== -->

        <section id="inicio" class="hero">

            <div class="hero_container">

                <div class="hero_content">

                    <span class="tag">
                        Plataforma de Educação Financeira
                    </span>

                    <h1>
                        Assuma o controle das suas finanças
                        de forma simples e inteligente
                    </h1>

                    <p>
                        Aprenda educação financeira, organize seus gastos,
                        acompanhe suas metas e descubra como fazer seu
                        dinheiro trabalhar a seu favor.
                    </p>

                    <div class="hero_buttons">

                        <?php if (isset($_SESSION['id'])): ?>

                            <a href="#educacao" class="btn_primary">
                                Começar Agora
                            </a>

                        <?php else: ?>

                            <a href="cadastro.php" class="btn_primary">
                                Criar Conta
                            </a>

                            <a href="login.php" class="btn_secondary">
                                Entrar
                            </a>

                        <?php endif; ?>

                    </div>

                </div>


                <div class="hero_card">

                    <div class="card_dashboard">

                        <div class="saldo">

                            <span>
                                Saldo Atual
                            </span>

                            <h2>
                                R$ 12.580
                            </h2>

                        </div>


                        <div class="dashboard_info">

                            <div>

                                <span>
                                    Receitas
                                </span>

                                <h3>
                                    + R$ 4.300
                                </h3>

                            </div>


                            <div>

                                <span>
                                    Despesas
                                </span>

                                <h3>
                                    - R$ 1.240
                                </h3>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             BENEFÍCIOS
        ====================================================== -->

        <section class="beneficios">

            <div class="section_title">

                <h2>
                    Por que utilizar nossa plataforma?
                </h2>

                <p>
                    Tudo que você precisa para desenvolver
                    uma vida financeira mais saudável.
                </p>

            </div>


            <div class="beneficios_grid">

                <div class="beneficio_card">

                    <h3>
                        Educação Financeira
                    </h3>

                    <p>
                        Conteúdos simples para aprender
                        a cuidar melhor do seu dinheiro.
                    </p>

                </div>


                <div class="beneficio_card">

                    <h3>
                        Controle de Gastos
                    </h3>

                    <p>
                        Entenda para onde seu dinheiro está indo
                        e tome melhores decisões.
                    </p>

                </div>


                <div class="beneficio_card">

                    <h3>
                        Planejamento
                    </h3>

                    <p>
                        Crie metas financeiras e acompanhe
                        sua evolução.
                    </p>

                </div>


                <div class="beneficio_card">

                    <h3>
                        Investimentos
                    </h3>

                    <p>
                        Conheça alternativas para construir
                        patrimônio ao longo do tempo.
                    </p>

                </div>

            </div>

        </section>


        <!-- =====================================================
             COMO FUNCIONA
        ====================================================== -->

        <section class="section_dark como_funciona">

            <div class="section_title">

                <h2>
                    Como funciona
                </h2>

                <p>
                    Três etapas simples para melhorar
                    sua vida financeira.
                </p>

            </div>


            <div class="steps">

                <div class="step_card">

                    <div class="step_number">
                        01
                    </div>

                    <h3>
                        Aprenda
                    </h3>

                    <p>
                        Entenda conceitos financeiros essenciais.
                    </p>

                </div>


                <div class="step_card">

                    <div class="step_number">
                        02
                    </div>

                    <h3>
                        Controle
                    </h3>

                    <p>
                        Organize gastos e acompanhe sua evolução.
                    </p>

                </div>


                <div class="step_card">

                    <div class="step_number">
                        03
                    </div>

                    <h3>
                        Cresça
                    </h3>

                    <p>
                        Planeje metas e desenvolva patrimônio.
                    </p>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ESTATÍSTICAS
        ====================================================== -->

        <section class="estatisticas">

            <div class="estatistica">

                <h2>
                    100%
                </h2>

                <p>
                    Gratuito para começar
                </p>

            </div>


            <div class="estatistica">

                <h2>
                    24h
                </h2>

                <p>
                    Acesso aos conteúdos financeiros
                </p>

            </div>


            <div class="estatistica">

                <h2>
                    Simples
                </h2>

                <p>
                    Interface fácil para iniciantes
                </p>

            </div>


            <div class="estatistica">

                <h2>
                    Seguro
                </h2>

                <p>
                    Área exclusiva para usuários cadastrados
                </p>

            </div>

        </section>


        <!-- =====================================================
             EDUCAÇÃO FINANCEIRA
        ====================================================== -->

        <section id="educacao" class="section_light">

            <div class="section_title">

                <h2>
                    Educação Financeira
                </h2>

                <p>
                    Aprenda a administrar seu dinheiro de forma prática.
                </p>

            </div>


            <div class="cards_grid">

                <div class="info_card">

                    <h3>
                        Orçamento
                    </h3>

                    <p>
                        Organize receitas e despesas
                        de maneira eficiente.
                    </p>

                </div>


                <div class="info_card">

                    <h3>
                        Economia
                    </h3>

                    <p>
                        Descubra formas de economizar
                        sem perder qualidade.
                    </p>

                </div>


                <div class="info_card">

                    <h3>
                        Planejamento
                    </h3>

                    <p>
                        Crie metas realistas para alcançar
                        seus objetivos.
                    </p>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CALCULADORA
        ====================================================== -->

        <section id="calculadora" class="section_dark">

            <div class="section_title">

                <h2>
                    Calculadora Financeira
                </h2>

                <p>
                    Simule cenários e tome decisões
                    mais inteligentes.
                </p>

            </div>


            <div class="calc_box">

                <h3>
                    Ferramenta Financeira
                </h3>

                <p>
                    Analise gastos, metas e projeções financeiras.
                </p>


                <?php if (isset($_SESSION['id'])): ?>

                    <a href="calculadora.php" class="btn_primary">
                        Acessar Calculadora
                    </a>

                <?php else: ?>

                    <a href="login.php" class="btn_primary">
                        Acessar Calculadora
                    </a>

                <?php endif; ?>

            </div>

        </section>


        <!-- =====================================================
             INVESTIMENTOS
        ====================================================== -->

        <section id="investimentos" class="section_light">

            <div class="section_title">

                <h2>
                    Investimentos
                </h2>

                <p>
                    Conheça as principais alternativas
                    para multiplicar seu patrimônio.
                </p>

            </div>


            <div class="cards_grid">

                <!-- RENDA FIXA -->

                <div class="info_card">

                    <h3>
                        Renda Fixa
                    </h3>

                    <p>
                        Segurança e previsibilidade
                        para seu dinheiro.
                    </p>


                    <?php if (isset($_SESSION['id'])): ?>

                        <a
                            href="investimentos_RendaFixa.php"
                            class="btn_primary"
                        >
                            Acessar Renda Fixa
                        </a>

                    <?php else: ?>

                        <a
                            href="login.php"
                            class="btn_primary"
                        >
                            Acessar Renda Fixa
                        </a>

                    <?php endif; ?>

                </div>


                <!-- RENDA VARIÁVEL -->

                <div class="info_card">

                    <h3>
                        Renda Variável
                    </h3>

                    <p>
                        Potencial de retorno superior
                        no longo prazo.
                    </p>


                    <?php if (isset($_SESSION['id'])): ?>

                        <a
                            href="investimentos_RendaVariavel.php"
                            class="btn_primary"
                        >
                            Acessar Renda Variável
                        </a>

                    <?php else: ?>

                        <a
                            href="login.php"
                            class="btn_primary"
                        >
                            Acessar Renda Variável
                        </a>

                    <?php endif; ?>

                </div>


                <!-- DIVERSIFICAÇÃO -->

                <div class="info_card">

                    <h3>
                        Diversificação
                    </h3>

                    <p>
                        Distribua riscos e fortaleça
                        sua carteira.
                    </p>


                    <?php if (isset($_SESSION['id'])): ?>

                        <a
                            href="investimentos_Diversificacao.php"
                            class="btn_primary"
                        >
                            Acessar Diversificação
                        </a>

                    <?php else: ?>

                        <a
                            href="login.php"
                            class="btn_primary"
                        >
                            Acessar Diversificação
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CTA FINAL
        ====================================================== -->

        <section class="cta">

            <div class="cta_content">

                <h2>
                    Comece hoje a transformar
                    sua vida financeira
                </h2>

                <p>
                    Crie sua conta gratuitamente e tenha acesso
                    às ferramentas da plataforma.
                </p>


                <?php if (!isset($_SESSION['id'])): ?>

                    <a
                        href="cadastro.php"
                        class="btn_primary"
                    >
                        Criar Conta Gratuitamente
                    </a>

                <?php endif; ?>

            </div>

        </section>

    </main>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer>

        <div class="footer_container">

            <img
                class="footer_logo"
                src="../img/logo.png"
                alt="Logo AFDE"
            >

            <p>
                © 2026 AFDE. Todos os direitos reservados.
            </p>

        </div>

    </footer>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>

