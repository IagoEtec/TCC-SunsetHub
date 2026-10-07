<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once("../back-end/conexao.php");
if (!isset($_SESSION['id'])) {
    header("Location: ../front-end/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Financeiro</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/investimentos.css?v=2">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>
<body class="renda-fixa-page">
<?php include_once 'navbar.php'; ?>

<main>

    <!-- HERO RENDA FIXA -->
    <section class="hero_fixa">
        <div class="hero_fixa_container">
            <div class="hero_fixa_content">
                <span class="tag_fixa"> Investimento Seguro</span>
                <h1>Renda Fixa<br><span class="destaque">Segurança e Rentabilidade</span></h1>
                <p>Invista com previsibilidade e proteja seu patrimônio. Conheça as melhores opções do mercado para começar hoje mesmo.</p>
                <div class="hero_fixa_buttons">
                    <button class="btn_fixa_primary" onclick="scrollToConteudo()">Começar a Investir</button>
                    <button class="btn_fixa_secondary" onclick="window.location.href='home.php#investimentos'">Voltar</button>
                </div>
            </div>
            <div class="hero_fixa_card">
                <div class="card_fixa_info">
                    <div class="taxa_info">
                        <span>CDI Atual</span>
                        <h3>13,65%</h3>
                        <small>a.a.</small>
                    </div>
                    <div class="taxa_info">
                        <span>Selic</span>
                        <h3>13,75%</h3>
                        <small>a.a.</small>
                    </div>
                    <div class="taxa_info">
                        <span>IPCA</span>
                        <h3>4,50%</h3>
                        <small>a.a.</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- O QUE É RENDA FIXA -->
    <section class="o_que_e">
        <div class="section_title">
            <h2>O que é <span class="destaque">Renda Fixa?</span></h2>
            <p>Entenda de forma simples esse tipo de investimento</p>
        </div>
        <div class="explicacao_container">
            <div class="explicacao_texto">
                <p>A <strong>Renda Fixa</strong> é uma categoria de investimento onde as regras de remuneração são definidas no momento da aplicação. Você sabe exatamente quanto vai receber ou como será calculado seu retorno.</p>
                <p>É como <strong>emprestar seu dinheiro</strong> para instituições (governo, bancos, empresas) em troca de uma remuneração combinada. Por isso é considerado mais previsível que a Renda Variável.</p>
                <div class="destaque_box">
                    <span> Perfil Recomendado</span>
                    <p>Iniciantes, conservadores ou quem busca reserva de emergência e objetivos de curto/médio prazo.</p>
                </div>
            </div>
            <div class="explicacao_icon">
                <div class="icon_circle">
                    <span>🔒</span>
                </div>
            </div>
        </div>
    </section>

    <!-- COMO FUNCIONA -->
    <section class="como_funciona_fixa">
        <div class="section_title">
            <h2>Como <span class="destaque">Funciona</span> na Prática</h2>
            <p>O passo a passo para começar a investir</p>
        </div>
        <div class="steps_fixa">
            <div class="step_fixa">
                <div class="step_num">1</div>
                <h3>Escolha o título</h3>
                <p>CDB, Tesouro Direto, LCI, LCA ou Debêntures</p>
            </div>
            <div class="step_fixa">
                <div class="step_num">2</div>
                <h3>Defina o prazo</h3>
                <p>Curto, médio ou longo prazo conforme seu objetivo</p>
            </div>
            <div class="step_fixa">
                <div class="step_num">3</div>
                <h3>Aplique o valor</h3>
                <p>Invista a partir de valores acessíveis (R$30+)</p>
            </div>
            <div class="step_fixa">
                <div class="step_num">4</div>
                <h3>Acompanhe os rendimentos</h3>
                <p>Veja seu dinheiro crescendo dia após dia</p>
            </div>
        </div>
    </section>

    <!-- PRINCIPAIS TIPOS -->
    <section class="tipos_fixa">
        <div class="section_title">
            <h2>Principais <span class="destaque">Tipos</span> de Renda Fixa</h2>
            <p>Conheça as opções e escolha a melhor para você</p>
        </div>
        <div class="tipos_grid_fixa">
            <div class="tipo_card_fixa">
                <div class="tipo_icon_fixa">
                    <span class="tipo_icon_glyph" aria-hidden="true">⌂</span>
                </div>
                <h3>Tesouro Direto</h3>
                <p class="tipo_desc_fixa">Títulos públicos federais. O investimento mais seguro do Brasil.</p>
                <div class="tipo_vantagens_fixa">
                    <span>✓ Seguro</span>
                    <span>✓ Acessível</span>
                    <span>✓ Baixo risco</span>
                </div>
                <p class="tipo_rentabilidade_fixa"><strong>Rentabilidade:</strong> Prefixado + IPCA + Selic</p>
            </div>

            <div class="tipo_card_fixa">
                <div class="tipo_icon_fixa">
                    <span class="tipo_icon_glyph" aria-hidden="true">▣</span>
                </div>
                <h3>CDB</h3>
                <p class="tipo_desc_fixa">Certificado de Depósito Bancário. Empreste para bancos.</p>
                <div class="tipo_vantagens_fixa">
                    <span>✓ Proteção FGC</span>
                    <span>✓ Vários prazos</span>
                    <span>✓ Até 120% CDI</span>
                </div>
                <p class="tipo_rentabilidade_fixa"><strong>Rentabilidade:</strong> % do CDI</p>
            </div>

            <div class="tipo_card_fixa">
                <div class="tipo_icon_fixa">
                    <span class="tipo_icon_glyph" aria-hidden="true">⌘</span>
                </div>
                <h3>LCI / LCA</h3>
                <p class="tipo_desc_fixa">Letras de Crédito. Isentas de Imposto de Renda.</p>
                <div class="tipo_vantagens_fixa">
                    <span>✓ Isento IR</span>
                    <span>✓ Proteção FGC</span>
                    <span>✓ Setor imobiliário/agro</span>
                </div>
                <p class="tipo_rentabilidade_fixa"><strong>Rentabilidade:</strong> % do CDI</p>
            </div>

            <div class="tipo_card_fixa">
                <div class="tipo_icon_fixa">
                    <span class="tipo_icon_glyph" aria-hidden="true">▤</span>
                </div>
                <h3>Debêntures</h3>
                <p class="tipo_desc_fixa">Títulos de dívida de empresas. Maior rentabilidade.</p>
                <div class="tipo_vantagens_fixa">
                    <span>✓ Rendimentos altos</span>
                    <span>✓ Isenção para incentivos</span>
                    <span>⚠️ Risco maior</span>
                </div>
                <p class="tipo_rentabilidade_fixa"><strong>Rentabilidade:</strong> Prefixado + IPCA</p>
            </div>
        </div>
    </section>

    <!-- VANTAGENS E DESVANTAGENS -->
    <section class="prós_contrass">
        <div class="section_title">
            <h2>Vantagens vs <span class="destaque">Desvantagens</span></h2>
            <p>Entenda os pontos fortes e fracos da Renda Fixa</p>
        </div>
        <div class="comparativo_grid">
            <div class="vantagens_box">
                <h3>✅ Vantagens</h3>
                <ul>
                    <li>Previsibilidade de rendimentos</li>
                    <li>Baixa volatilidade (menos oscilações)</li>
                    <li>Proteção do FGC (até R$250 mil por instituição)</li>
                    <li>Ideal para reserva de emergência</li>
                    <li>Opções isentas de Imposto de Renda</li>
                    <li>Investimento a partir de valores baixos</li>
                </ul>
            </div>
            <div class="desvantagens_box">
                <h3>❌ Desvantagens</h3>
                <ul>
                    <li>Rentabilidade potencialmente menor que variável</li>
                    <li>Imposto de Renda regressivo (alguns tipos)</li>
                    <li>Liquidez pode ser baixa em alguns títulos</li>
                    <li>Risco de crédito (emissor pode quebrar)</li>
                    <li>Rentabilidade pode perder para inflação</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- DICAS PRÁTICAS -->
    <section class="dicas_praticas">
        <div class="section_title">
            <h2>Dicas <span class="destaque">Práticas</span></h2>
            <p>Como aplicar no seu dia a dia</p>
        </div>
        <div class="dicas_grid">
            <div class="dica_item">
                <div class="dica_num">01</div>
                <div class="dica_content">
                    <h3>Comece pelo Tesouro Selic</h3>
                    <p>Ideal para iniciantes e reserva de emergência. Rendimento diário e liquidez imediata.</p>
                </div>
            </div>
            <div class="dica_item">
                <div class="dica_num">02</div>
                <div class="dica_content">
                    <h3>Diversifique os prazos</h3>
                    <p>Tenha investimentos de curto, médio e longo prazo para diferentes objetivos.</p>
                </div>
            </div>
            <div class="dica_item">
                <div class="dica_num">03</div>
                <div class="dica_content">
                    <h3>Compare rentabilidades</h3>
                    <p>Para curto prazo: CDI. Para longo prazo: IPCA+. Prefixado para cenários estáveis.</p>
                </div>
            </div>
            <div class="dica_item">
                <div class="dica_num">04</div>
                <div class="dica_content">
                    <h3>Fique atento aos impostos</h3>
                    <p>Quanto mais tempo investido, menor o IR. 22,5% no curto prazo → 15% no longo prazo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- EXEMPLO PRÁTICO -->
    <section class="exemplo_container">
        <div class="exemplo_box">
            <div class="exemplo_header">
                <span> Exemplo Prático</span>
                <h2>Quanto rende na <span class="destaque">Renda Fixa?</span></h2>
            </div>
            <div class="exemplo_calc">
                <div class="exemplo_valor">
                    <h4>Investimento</h4>
                    <p class="valor_grande">R$ 10.000,00</p>
                </div>
                <div class="exemplo_seta">→</div>
                <div class="exemplo_valor">
                    <h4>Prazo</h4>
                    <p class="valor_medio">12 meses</p>
                </div>
                <div class="exemplo_seta">→</div>
                <div class="exemplo_valor">
                    <h4>CDI (13,65%)</h4>
                    <p class="valor_grande destaque">R$ 11.365,00</p>
                    <small>+13,65% a.a.</small>
                </div>
            </div>
            <p class="exemplo_nota">*Exemplo considerando 100% do CDI, sem impostos para simplificar</p>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="cta_fixa">
        <div class="cta_fixa_content">
            <h2>Comece sua jornada nos investimentos</h2>
            <p>Abra sua conta e comece a investir em Renda Fixa hoje mesmo</p>
            <div class="cta_fixa_buttons">
                <?php if (isset($_SESSION['id'])): ?>
                    <button class="btn_cta" onclick="window.location.href='carteira.php'">Ir para minha Carteira</button>
                <?php else: ?>
                    <button class="btn_cta" onclick="window.location.href='cadastro.php'">Criar Conta Grátis</button>
                    <button class="btn_cta_sec" onclick="window.location.href='login.php'">Já tenho conta</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<script>
    function scrollToConteudo() {
        document.querySelector('.o_que_e').scrollIntoView({ behavior: 'smooth' });
    }
</script>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>
