<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();

require_once '../back-end/conexao.php';


if (!isset($_SESSION['id'])) {

    header('Location: login.php');

    exit;

}


$stmt = $pdo->prepare("
    SELECT
        id,
        nome,
        tema,
        data_criacao
    FROM usuarios
    WHERE id = ?
");


$stmt->execute([
    $_SESSION['id']
]);


$usuario = $stmt->fetch(
    PDO::FETCH_ASSOC
);


if (!$usuario) {

    session_destroy();

    header('Location: login.php');

    exit;

}


$tema = $usuario['tema'] ?? 'sistema';

?>

<!DOCTYPE html>

<html
    lang="pt-BR"
    class="<?= htmlspecialchars($tema) ?>"
>

<head>
<meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Calendário Financeiro | AFDE
    </title>

    <link rel="icon" type="image/png" href="../img/favicon.png">

    <link
        rel="stylesheet"
        href="../css/calendario.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/icon?family=Material+Icons"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>


<body>
<?php include_once 'navbar.php'; ?>


<div class="background_shapes">

    <div class="shape shape1"></div>

    <div class="shape shape2"></div>

    <div class="shape shape3"></div>

</div>



<a
    href="carteira.php"
    class="btn_voltar"
>

    <span class="material-icons">
        arrow_back
    </span>

    Voltar para carteira

</a>



<main class="calendario_container">


    <!-- CABEÇALHO -->

    <section class="calendario_header glass">


        <div>


            <span class="calendario_eyebrow">

                Finanças

            </span>


            <h1>

                Calendário financeiro

            </h1>


            <p>

                Visualize lançamentos manuais e transações da sua conta conectada,
                acompanhe as categorias e veja sua evolução desde a criação da conta.

            </p>


        </div>


        <div class="calendar_icon">

            <span class="material-icons">

                calendar_month

            </span>

        </div>


    </section>



    <!-- NAVEGAÇÃO DOS MESES -->

    <section class="month_navigation glass">


        <button
            id="btnAnterior"
            class="month_button"
        >

            <span class="material-icons">

                chevron_left

            </span>

        </button>


        <div class="month_title_container">


            <span>

                Período selecionado

            </span>


            <h2 id="mesTitulo">

                Carregando...

            </h2>

            <small id="fonteDados" class="data_source">
                Carregando lançamentos...
            </small>


        </div>


        <button
            id="btnProximo"
            class="month_button"
        >

            <span class="material-icons">

                chevron_right

            </span>

        </button>


    </section>



    <!-- CONTEÚDO -->

    <section
        id="dashboard"
        class="dashboard_grid"
    >


        <!-- GRÁFICO -->

        <div class="chart_card glass">


            <div class="chart_header">


                <div>


                    <h2>

                        Despesas por categoria

                    </h2>


                    <p>

                        Distribuição das suas despesas.

                    </p>


                </div>


                <div class="chart_badge">

                    <span class="material-icons">

                        pie_chart

                    </span>

                </div>


            </div>



            <div
                id="chartArea"
                class="chart_area"
            >


                <canvas
                    id="graficoPizza"
                ></canvas>


                <div
                    id="emptyChart"
                    class="empty_chart hidden"
                >


                    <span class="material-icons">

                        pie_chart_outline

                    </span>


                    <h3>

                        Nenhuma despesa

                    </h3>


                    <p>

                        Não existem movimentações
                        neste período.

                    </p>


                </div>


            </div>


        </div>



        <!-- RESUMO -->

        <div class="summary_column">


            <div class="summary_card glass-card">


                <div class="summary_top">


                    <div class="summary_icon receita">

                        <span class="material-icons">

                            trending_up

                        </span>

                    </div>


                    <span>

                        Receitas

                    </span>


                </div>


                <strong
                    id="receitas"
                    class="positive"
                >

                    R$ 0,00

                </strong>


            </div>



            <div class="summary_card glass-card">


                <div class="summary_top">


                    <div class="summary_icon despesa">

                        <span class="material-icons">

                            trending_down

                        </span>

                    </div>


                    <span>

                        Despesas

                    </span>


                </div>


                <strong
                    id="despesas"
                    class="negative"
                >

                    R$ 0,00

                </strong>


            </div>



            <div class="summary_card glass-card">


                <div class="summary_top">


                    <div class="summary_icon resultado">

                        <span class="material-icons">

                            analytics

                        </span>

                    </div>


                    <span>

                        Resultado

                    </span>


                </div>


                <strong
                    id="resultado"
                >

                    R$ 0,00

                </strong>


            </div>


        </div>


    </section>



    <!-- HISTÓRICO DESDE A CRIAÇÃO DA CONTA -->

    <section class="history_card glass">

        <div class="history_header">

            <div>

                <h2>Histórico mensal</h2>

                <p id="historicoDescricao">
                    Receitas e despesas de todos os meses desde a criação da sua conta.
                </p>

            </div>

            <div class="chart_badge">
                <span class="material-icons">timeline</span>
            </div>

        </div>

        <div class="history_chart_area">
            <canvas id="graficoHistorico"></canvas>
            <div id="emptyHistorico" class="empty_chart hidden">
                <span class="material-icons">timeline</span>
                <h3>Histórico vazio</h3>
                <p>As transações aparecerão aqui depois da sincronização.</p>
            </div>
        </div>

    </section>


    <!-- LEGENDA -->

    <section
        id="legenda"
        class="legend_card glass"
    >


        <div class="legend_header">


            <h2>

                Categorias

            </h2>


            <span
                id="quantidadeMovimentacoes"
            >

                0 movimentações

            </span>


        </div>


        <div
            id="listaCategorias"
            class="category_list"
        >

        </div>


    </section>


</main>



<!-- CHART.JS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>


/* ==========================================
   ELEMENTOS
========================================== */

const mesTitulo =
    document.getElementById(
        'mesTitulo'
    );


const btnAnterior =
    document.getElementById(
        'btnAnterior'
    );


const btnProximo =
    document.getElementById(
        'btnProximo'
    );


const receitasEl =
    document.getElementById(
        'receitas'
    );


const despesasEl =
    document.getElementById(
        'despesas'
    );


const resultadoEl =
    document.getElementById(
        'resultado'
    );


const listaCategorias =
    document.getElementById(
        'listaCategorias'
    );


const emptyChart =
    document.getElementById(
        'emptyChart'
    );


const quantidadeMovimentacoes =
    document.getElementById(
        'quantidadeMovimentacoes'
    );


const fonteDados =
    document.getElementById(
        'fonteDados'
    );


const historicoDescricao =
    document.getElementById(
        'historicoDescricao'
    );


const emptyHistorico =
    document.getElementById(
        'emptyHistorico'
    );


const canvasHistorico =
    document.getElementById(
        'graficoHistorico'
    );


/* ==========================================
   DATA ATUAL
========================================== */

const hoje = new Date();


let mesAtual =
    hoje.getMonth() + 1;


let anoAtual =
    hoje.getFullYear();


let grafico = null;
let graficoHistorico = null;
let sincronizacaoInicial = null;


const nomesMeses = [

    'Janeiro',
    'Fevereiro',
    'Março',
    'Abril',
    'Maio',
    'Junho',
    'Julho',
    'Agosto',
    'Setembro',
    'Outubro',
    'Novembro',
    'Dezembro'

];



/* ==========================================
   FORMATAR MOEDA
========================================== */

function formatarMoeda(valor) {

    return new Intl.NumberFormat(

        'pt-BR',

        {

            style: 'currency',

            currency: 'BRL'

        }

    ).format(valor);

}



/* ==========================================
   CORES DO GRÁFICO
========================================== */

const cores = [

    '#54e39a',

    '#4dabf7',

    '#9775fa',

    '#ff922b',

    '#f06595',

    '#20c997',

    '#ffd43b',

    '#748ffc'

];



async function sincronizarSeNecessario() {

    if (sincronizacaoInicial) {
        return sincronizacaoInicial;
    }

    sincronizacaoInicial = fetch(
        '../open-finance/sincronizar.php',
        {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        }
    )
        .then(async resposta => {
            const dados = await resposta.json();
            if (!resposta.ok || dados.success === false) {
                throw new Error(dados.error || 'Sincronização indisponível.');
            }
            return dados;
        })
        .catch(erro => {
            console.warn('Sincronização Open Finance:', erro.message);
            return null;
        });

    return sincronizacaoInicial;
}


/* ==========================================
   CARREGAR DADOS
========================================== */

async function carregarDados() {

    await sincronizarSeNecessario();

    const url =
        `../back-end/dados_grafico.php?mes=${mesAtual}&ano=${anoAtual}`;


    try {


        const resposta =
            await fetch(url);


        const dados =
            await resposta.json();


        if (!dados.sucesso) {

            return;

        }


        atualizarTela(
            dados
        );


    } catch (erro) {

        console.error(
            'Erro ao carregar gráfico:',
            erro
        );

    }

}



/* ==========================================
   CARREGAR HISTÓRICO MENSAL
========================================== */

async function carregarHistorico() {

    await sincronizarSeNecessario();

    try {

        const resposta = await fetch(
            '../back-end/dados_historico.php',
            { cache: 'no-store' }
        );

        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || 'Não foi possível carregar o histórico.');
        }

        historicoDescricao.textContent = dados.periodo?.descricao ||
            'Receitas e despesas de todos os meses desde a criação da sua conta.';

        if (!dados.meses?.length) {
            emptyHistorico.classList.remove('hidden');
            canvasHistorico.style.display = 'none';
            return;
        }

        emptyHistorico.classList.add('hidden');
        canvasHistorico.style.display = 'block';

        if (graficoHistorico) {
            graficoHistorico.destroy();
        }

        const coresHistorico = [
            '#ff6b6b', '#ff922b', '#ffd43b', '#54e39a',
            '#4dabf7', '#9775fa', '#f06595', '#20c997'
        ];
        const categoriasHistorico = dados.categorias || [];
        const datasetsHistorico = categoriasHistorico.map((categoria, index) => ({
            type: 'bar',
            label: categoria,
            data: dados.meses.map(item => item.categorias?.[categoria] || 0),
            backgroundColor: coresHistorico[index % coresHistorico.length],
            borderRadius: 6,
            maxBarThickness: 34,
            stack: 'despesas'
        }));
        datasetsHistorico.push({
            type: 'line',
            label: 'Receitas',
            data: dados.meses.map(item => item.receitas),
            borderColor: '#54e39a',
            backgroundColor: '#54e39a',
            pointBackgroundColor: '#54e39a',
            pointRadius: 4,
            borderWidth: 3,
            tension: .3,
            fill: false,
            order: 0
        });

        graficoHistorico = new Chart(canvasHistorico, {
            type: 'bar',
            data: {
                labels: dados.meses.map(item => item.rotulo),
                datasets: datasetsHistorico
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        ticks: { color: 'rgba(255,255,255,.65)' },
                        grid: { display: false }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            color: 'rgba(255,255,255,.65)',
                            callback: value => formatarMoeda(value)
                        },
                        grid: { color: 'rgba(255,255,255,.08)' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: 'rgba(255,255,255,.78)' }
                    },
                    tooltip: {
                        callbacks: {
                            label: context => ' ' + context.dataset.label + ': ' + formatarMoeda(context.raw)
                        }
                    }
                }
            }
        });

    } catch (erro) {
        console.error('Erro ao carregar histórico:', erro);
        historicoDescricao.textContent = erro.message;
        emptyHistorico.classList.remove('hidden');
        canvasHistorico.style.display = 'none';
    }

}


/* ==========================================
   ATUALIZAR TELA
========================================== */

function atualizarTela(dados) {


    mesTitulo.textContent =
        `${nomesMeses[mesAtual - 1]} ${anoAtual}`;


    /* RESUMO */

    receitasEl.textContent =
        formatarMoeda(
            dados.resumo.receitas
        );


    despesasEl.textContent =
        formatarMoeda(
            dados.resumo.despesas
        );


    resultadoEl.textContent =
        formatarMoeda(
            dados.resumo.resultado
        );


    resultadoEl.className =
        dados.resumo.resultado >= 0

            ? 'positive'

            : 'negative';


    quantidadeMovimentacoes.textContent =
        `${dados.quantidade} movimentação${dados.quantidade === 1 ? '' : 'ões'}`;

    const fontes = dados.fontes || {};
    fonteDados.textContent =
        `${fontes.open_finance || 0} da conta conectada · ${fontes.manual || 0} manual${fontes.manual === 1 ? '' : 'is'}`;


    /* GRÁFICO */

    if (
        dados.grafico.labels.length === 0
    ) {


        emptyChart.classList.remove(
            'hidden'
        );


        document
            .getElementById(
                'graficoPizza'
            )
            .style.display = 'none';


        if (grafico) {

            grafico.destroy();

            grafico = null;

        }


    } else {


        emptyChart.classList.add(
            'hidden'
        );


        const canvas =
            document.getElementById(
                'graficoPizza'
            );


        canvas.style.display =
            'block';


        if (grafico) {

            grafico.destroy();

        }


        grafico =
            new Chart(

                canvas,

                {

                    type: 'doughnut',


                    data: {

                        labels:
                            dados.grafico.labels,


                        datasets: [

                            {

                                data:
                                    dados.grafico.valores,


                                backgroundColor:

                                    dados
                                        .grafico
                                        .valores
                                        .map(
                                            (
                                                _,
                                                index
                                            ) =>
                                                cores[
                                                    index %
                                                    cores.length
                                                ]
                                        ),


                                borderWidth:
                                    0,

                                borderRadius:
                                    8

                            }

                        ]

                    },


                    options: {

                        responsive:
                            true,


                        maintainAspectRatio:
                            false,


                        cutout:
                            '65%',


                        animation: {

                            animateRotate:
                                true,

                            duration:
                                1000

                        },


                        plugins: {

                            legend: {

                                display:
                                    false

                            },


                            tooltip: {

                                callbacks: {

                                    label:

                                        function(
                                            context
                                        ) {

                                            return (
                                                ' ' +
                                                context.label +
                                                ': ' +
                                                formatarMoeda(
                                                    context.raw
                                                )
                                            );

                                        }

                                }

                            }

                        }

                    }

                }

            );


    }


    /* LEGENDA */

    listaCategorias.innerHTML = '';


    dados.grafico.labels.forEach(

        (
            categoria,
            index
        ) => {


            const item =
                document.createElement(
                    'div'
                );


            item.className =
                'category_item';


            const cor =
                cores[
                    index %
                    cores.length
                ];


            item.innerHTML = `

                <div class="category_left">

                    <span
                        class="category_color"
                        style="background:${cor}"
                    ></span>

                    <span>

                        ${escaparCategoria(categoria)}

                    </span>

                </div>


                <strong>

                    ${formatarMoeda(
                        dados.grafico.valores[index]
                    )}

                </strong>

            `;


            listaCategorias.appendChild(
                item
            );


        }

    );


}



/* ==========================================
   MÊS ANTERIOR
========================================== */

btnAnterior.addEventListener(

    'click',

    () => {


        mesAtual--;


        if (mesAtual < 1) {

            mesAtual = 12;

            anoAtual--;

        }


        carregarDados();

    }

);



/* ==========================================
   PRÓXIMO MÊS
========================================== */

btnProximo.addEventListener(

    'click',

    () => {


        const hoje =
            new Date();


        const mesHoje =
            hoje.getMonth() + 1;


        const anoHoje =
            hoje.getFullYear();


        if (

            anoAtual > anoHoje ||

            (

                anoAtual === anoHoje &&

                mesAtual >= mesHoje

            )

        ) {

            return;

        }


        mesAtual++;


        if (mesAtual > 12) {

            mesAtual = 1;

            anoAtual++;

        }


        carregarDados();

    }

);



/* ==========================================
   INICIAR
========================================== */

carregarDados();
carregarHistorico();


</script>



    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>
