<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();

require_once("../back-end/conexao.php");


/* ============================================================
   PROTEÇÃO DA PÁGINA
============================================================ */

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


/* ============================================================
   ATUALIZAR SESSÃO COM O TEMA ATUAL
============================================================ */

$_SESSION['tema'] = $tema;


/* ============================================================
   ID DO USUÁRIO
============================================================ */

$usuarioId = (int) $usuario['id'];

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

    <title>Diversificação de Investimentos</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <link
        rel="stylesheet"
        href="../css/investimentos.css"
    >

    <link
        rel="icon"
        type="image/png"
        href="../img/favicon.png"
    >

    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>


<body class="pagina-diversificacao">

<?php include_once 'navbar.php'; ?>


<main>


    <!-- ========================================================
         HERO
    ========================================================= -->

    <section class="hero_fixa">

        <div class="hero_fixa_container">

            <div class="hero_fixa_content">

                <span class="tag_fixa">
                    Estratégia Inteligente
                </span>

                <h1>

                    Diversificação

                    <br>

                    <span class="destaque">
                        Não Coloque Todos os Ovos na Mesma Cesta
                    </span>

                </h1>

                <p>
                    Aprenda a distribuir seus investimentos de forma
                    inteligente para reduzir riscos e maximizar
                    oportunidades de ganho no longo prazo.
                </p>


                <div class="hero_fixa_buttons">

                    <button
                        type="button"
                        class="btn_fixa_primary"
                        onclick="scrollToConteudo()"
                    >
                        Aprender Estratégias
                    </button>


                    <button
                        type="button"
                        class="btn_fixa_secondary"
                        onclick="window.location.href='home.php#investimentos'"
                    >
                        Voltar
                    </button>

                </div>

            </div>


            <div class="hero_fixa_card">

                <div class="card_fixa_info">

                    <div class="taxa_info">

                        <span>
                            Redução de Risco
                        </span>

                        <h3>
                            Até 70%
                        </h3>

                        <small>
                            com diversificação adequada
                        </small>

                    </div>


                    <div class="taxa_info">

                        <span>
                            Carteiras Diversificadas
                        </span>

                        <h3>
                            +40%
                        </h3>

                        <small>
                            retorno no longo prazo
                        </small>

                    </div>


                    <div class="taxa_info">

                        <span>
                            Investidores que Diversificam
                        </span>

                        <h3>
                            85%
                        </h3>

                        <small>
                            têm melhores resultados
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </section>



    <!-- ========================================================
         O QUE É DIVERSIFICAÇÃO
    ========================================================= -->

    <section class="o_que_e">

        <div class="section_title">

            <h2>
                O que é
                <span class="destaque">
                    Diversificação?
                </span>
            </h2>

            <p>
                Entenda o conceito fundamental dos investimentos
            </p>

        </div>


        <div class="explicacao_container">

            <div class="explicacao_texto">

                <p>

                    <strong>Diversificação</strong>
                    é a estratégia de distribuir seus investimentos
                    entre diferentes tipos de ativos, setores e mercados
                    para reduzir os riscos da sua carteira.

                </p>


                <p>

                    É como o ditado popular:

                    <strong>
                        "não coloque todos os ovos na mesma cesta"
                    </strong>.

                    Se um investimento cair, outros podem subir,
                    equilibrando seus resultados e protegendo seu
                    patrimônio.

                </p>


                <div class="destaque_box">

                    <span>
                        Objetivo Principal
                    </span>

                    <p>

                        Maximizar retornos para um determinado nível
                        de risco ou minimizar riscos para um determinado
                        nível de retorno esperado.

                    </p>

                </div>

            </div>


            <div class="explicacao_icon">

                <div class="icon_circle">

                    <span>
                        📊
                    </span>

                </div>

            </div>

        </div>

    </section>



    <!-- ========================================================
         COMO FUNCIONA
    ========================================================= -->

    <section class="como_funciona_fixa">

        <div class="section_title">

            <h2>

                Como
                <span class="destaque">
                    Funciona
                </span>
                na Prática

            </h2>

            <p>
                Os pilares da diversificação inteligente
            </p>

        </div>


        <div class="steps_fixa">


            <div class="step_fixa">

                <div class="step_num">
                    1
                </div>

                <h3>
                    Classes de Ativos
                </h3>

                <p>
                    Distribua entre Renda Fixa, Renda Variável,
                    Imóveis e Internacional.
                </p>

            </div>


            <div class="step_fixa">

                <div class="step_num">
                    2
                </div>

                <h3>
                    Setores da Economia
                </h3>

                <p>
                    Invista em diferentes setores: tecnologia,
                    saúde, energia e financeiro.
                </p>

            </div>


            <div class="step_fixa">

                <div class="step_num">
                    3
                </div>

                <h3>
                    Prazos Diferentes
                </h3>

                <p>
                    Combine investimentos de curto, médio
                    e longo prazo.
                </p>

            </div>


            <div class="step_fixa">

                <div class="step_num">
                    4
                </div>

                <h3>
                    Rebalanceamento
                </h3>

                <p>
                    Ajuste periodicamente para manter
                    a proporção ideal.
                </p>

            </div>


        </div>

    </section>



    <!-- ========================================================
         TIPOS DE DIVERSIFICAÇÃO
    ========================================================= -->

    <section class="tipos_fixa">

        <div class="section_title">

            <h2>

                Tipos de
                <span class="destaque">
                    Diversificação
                </span>

            </h2>

            <p>
                Conheça as principais formas de distribuir
                seus investimentos
            </p>

        </div>


        <div class="tipos_grid_fixa">


            <!-- CLASSES -->

            <div class="tipo_card_fixa">

                <div class="tipo_icon_fixa">

                    <span>
                        📊
                    </span>

                </div>

                <h3>
                    Por Classes de Ativos
                </h3>

                <p class="tipo_desc_fixa">

                    Distribuição entre diferentes tipos de
                    investimentos com comportamentos distintos.

                </p>


                <div class="tipo_vantagens_fixa">

                    <span>
                        Ações
                    </span>

                    <span>
                        Renda Fixa
                    </span>

                    <span>
                        Imóveis
                    </span>

                    <span>
                        Internacional
                    </span>

                </div>


                <p class="tipo_rentabilidade_fixa">

                    <strong>
                        Benefício:
                    </strong>

                    Redução da volatilidade total da carteira

                </p>

            </div>



            <!-- SETORES -->

            <div class="tipo_card_fixa">

                <div class="tipo_icon_fixa">

                    <span>
                        🏢
                    </span>

                </div>

                <h3>
                    Por Setores
                </h3>

                <p class="tipo_desc_fixa">

                    Invista em empresas de diferentes
                    segmentos da economia.

                </p>


                <div class="tipo_vantagens_fixa">

                    <span>
                        Tecnologia
                    </span>

                    <span>
                        Saúde
                    </span>

                    <span>
                        Energia
                    </span>

                    <span>
                        Financeiro
                    </span>

                </div>


                <p class="tipo_rentabilidade_fixa">

                    <strong>
                        Benefício:
                    </strong>

                    Proteção contra crises setoriais específicas

                </p>

            </div>



            <!-- GEOGRÁFICA -->

            <div class="tipo_card_fixa">

                <div class="tipo_icon_fixa">

                    <span>
                        🌎
                    </span>

                </div>

                <h3>
                    Geográfica
                </h3>

                <p class="tipo_desc_fixa">

                    Invista em diferentes países e regiões
                    do mundo.

                </p>


                <div class="tipo_vantagens_fixa">

                    <span>
                        🇧🇷 Brasil
                    </span>

                    <span>
                        🇺🇸 EUA
                    </span>

                    <span>
                        🇪🇺 Europa
                    </span>

                    <span>
                        🇨🇳 Ásia
                    </span>

                </div>


                <p class="tipo_rentabilidade_fixa">

                    <strong>
                        Benefício:
                    </strong>

                    Proteção contra riscos políticos
                    e econômicos locais

                </p>

            </div>



            <!-- PRAZOS -->

            <div class="tipo_card_fixa">

                <div class="tipo_icon_fixa">

                    <span>
                        ⏳
                    </span>

                </div>

                <h3>
                    Por Prazos
                </h3>

                <p class="tipo_desc_fixa">

                    Combine investimentos com diferentes
                    vencimentos.

                </p>


                <div class="tipo_vantagens_fixa">

                    <span>
                        Curto Prazo
                    </span>

                    <span>
                        Médio Prazo
                    </span>

                    <span>
                        Longo Prazo
                    </span>

                </div>


                <p class="tipo_rentabilidade_fixa">

                    <strong>
                        Benefício:
                    </strong>

                    Liquidez imediata + rentabilidade
                    de longo prazo

                </p>

            </div>


        </div>

    </section>



    <!-- ========================================================
         VANTAGENS E DESVANTAGENS
    ========================================================= -->

    <section class="pros_contras">

        <div class="section_title">

            <h2>

                Vantagens vs
                <span class="destaque">
                    Desvantagens
                </span>

            </h2>

            <p>
                Entenda os prós e contras da diversificação
            </p>

        </div>


        <div class="comparativo_grid">


            <div class="vantagens_box">

                <h3>
                    ✅ Vantagens
                </h3>

                <ul>

                    <li>
                        Redução significativa do risco total
                    </li>

                    <li>
                        Proteção contra perdas catastróficas
                    </li>

                    <li>
                        Maior estabilidade nos retornos
                    </li>

                    <li>
                        Aproveita oportunidades em diferentes mercados
                    </li>

                    <li>
                        Melhor relação risco-retorno no longo prazo
                    </li>

                    <li>
                        Menos estresse com oscilações do mercado
                    </li>

                </ul>

            </div>


            <div class="desvantagens_box">

                <h3>
                    ❌ Desvantagens
                </h3>

                <ul>

                    <li>
                        Pode limitar ganhos extraordinários
                    </li>

                    <li>
                        Exige mais conhecimento e pesquisa
                    </li>

                    <li>
                        Custos de transação podem ser maiores
                    </li>

                    <li>
                        Complexidade na gestão da carteira
                    </li>

                    <li>
                        Risco de diversificação excessiva
                    </li>

                </ul>

            </div>


        </div>

    </section>



    <!-- ========================================================
         DICAS PRÁTICAS
    ========================================================= -->

    <section class="dicas_praticas">

        <div class="section_title">

            <h2>

                Estratégias
                <span class="destaque">
                    Práticas
                </span>

            </h2>

            <p>
                Como aplicar a diversificação no seu dia a dia
            </p>

        </div>


        <div class="dicas_grid">


            <div class="dica_item">

                <div class="dica_num">
                    01
                </div>

                <div class="dica_content">

                    <h3>
                        Regra 60/40
                    </h3>

                    <p>

                        Clássica: 60% em Renda Variável e
                        40% em Renda Fixa. Ajuste conforme
                        seu perfil de risco.

                    </p>

                </div>

            </div>



            <div class="dica_item">

                <div class="dica_num">
                    02
                </div>

                <div class="dica_content">

                    <h3>
                        ETFs e Fundos de Índice
                    </h3>

                    <p>

                        Invista em ETFs que replicam índices
                        como IBOVESPA e S&P 500 para obter
                        diversificação instantânea.

                    </p>

                </div>

            </div>



            <div class="dica_item">

                <div class="dica_num">
                    03
                </div>

                <div class="dica_content">

                    <h3>
                        Rebalanceamento Anual
                    </h3>

                    <p>

                        A cada ano, ajuste sua carteira para
                        manter as proporções originais.

                    </p>

                </div>

            </div>



            <div class="dica_item">

                <div class="dica_num">
                    04
                </div>

                <div class="dica_content">

                    <h3>
                        Invista no Exterior
                    </h3>

                    <p>

                        Aloque parte da carteira em ativos
                        internacionais para diversificação
                        geográfica e cambial.

                    </p>

                </div>

            </div>


        </div>

    </section>



    <!-- ========================================================
         EXEMPLO PRÁTICO
    ========================================================= -->

    <section class="exemplo_container">

        <div class="exemplo_box">

            <div class="exemplo_header">

                <span>
                    💡 Exemplo Prático
                </span>

                <h2>
                    Carteira Concentrada vs Diversificada
                </h2>

            </div>


            <div class="exemplo_calc">


                <div class="exemplo_valor concentrada">

                    <h4>
                        ❌ Carteira Concentrada
                    </h4>

                    <p class="valor_medio">
                        90% Ações BR
                    </p>

                    <p class="valor_medio">
                        10% Renda Fixa
                    </p>

                    <p class="risco_alto">
                        <strong>
                            Risco: Alto
                        </strong>
                    </p>

                    <p class="risco_alto">
                        Volatilidade: +25%
                    </p>

                    <p class="risco_alto">
                        Perda potencial: -40%
                    </p>

                </div>


                <div class="exemplo_seta">
                    VS
                </div>


                <div class="exemplo_valor diversificada">

                    <h4>
                        ✅ Carteira Diversificada
                    </h4>

                    <p class="valor_medio">
                        30% Ações BR
                    </p>

                    <p class="valor_medio">
                        25% Internacional
                    </p>

                    <p class="valor_medio">
                        25% Renda Fixa
                    </p>

                    <p class="valor_medio">
                        20% FIIs
                    </p>

                    <p class="risco_moderado">

                        <strong>
                            Risco: Moderado
                        </strong>

                    </p>

                    <p class="risco_moderado">
                        Volatilidade: +12%
                    </p>

                    <p class="risco_moderado">
                        Perda potencial: -15%
                    </p>

                </div>


            </div>


            <p class="exemplo_nota">

                *Exemplo simplificado para ilustrar o efeito
                da diversificação na redução de risco.

            </p>

        </div>

    </section>



    <!-- ========================================================
         CTA FINAL
    ========================================================= -->

    <section class="cta_fixa">

        <div class="cta_fixa_content">

            <h2>
                Comece a diversificar seus investimentos hoje
            </h2>

            <p>
                Analise sua carteira atual e distribua seus
                recursos de forma inteligente.
            </p>


            <div class="cta_fixa_buttons">


                <?php if (isset($_SESSION['id'])): ?>

                    <button
                        type="button"
                        class="btn_cta"
                        onclick="window.location.href='../front-end/carteira.php'"
                    >
                        Ir para minha Carteira
                    </button>


                    <button
                        type="button"
                        class="btn_cta_sec"
                        onclick="window.location.href='../front-end/analisador.php'"
                    >
                        Analisar Diversificação
                    </button>


                <?php else: ?>

                    <button
                        type="button"
                        class="btn_cta"
                        onclick="window.location.href='cadastro.php'"
                    >
                        Criar Conta Grátis
                    </button>


                    <button
                        type="button"
                        class="btn_cta_sec"
                        onclick="window.location.href='login.php'"
                    >
                        Já tenho conta
                    </button>

                <?php endif; ?>


            </div>

        </div>

    </section>


</main>



<script>

function scrollToConteudo() {

    const conteudo =
        document.querySelector('.o_que_e');

    if (conteudo) {

        conteudo.scrollIntoView({
            behavior: 'smooth'
        });

    }

}

</script>



    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>

