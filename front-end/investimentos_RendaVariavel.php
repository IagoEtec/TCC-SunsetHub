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
    <link rel="stylesheet" href="../css/investimentos.css">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>
<body>

<?php include_once 'navbar.php'; ?>
<main>

    <!-- HERO RENDA VARIÁVEL -->
    <section class="hero_variavel">
        <div class="hero_variavel_container">
            <div class="hero_variavel_content">
                <span class="tag_variavel">Potencial de Retorno</span>
                <h1>Renda Variável<br><span class="destaque">Potencial de Crescimento</span></h1>
                <p>Invista em ações e ativos que podem multiplicar seu patrimônio no longo prazo. Entenda os riscos e oportunidades.</p>
                <div class="hero_variavel_buttons">
                    <button class="btn_variavel_primary" onclick="scrollToConteudo()">Começar a Investir</button>
                    <button class="btn_variavel_secondary" onclick="window.location.href='home.php#investimentos'">Voltar</button>
                </div>
            </div>
            <div class="hero_variavel_card">
                <div class="card_variavel_info">
                    <div class="indice_info">
                        <span>Ibovespa</span>
                        <h3>132.500</h3>
                        <small>pontos</small>
                    </div>
                    <div class="indice_info">
                        <span>Variação 2024</span>
                        <h3 class="positivo">+18,5%</h3>
                        <small>ano</small>
                    </div>
                    <div class="indice_info">
                        <span>Dividendos</span>
                        <h3>5,2%</h3>
                        <small>a.a. médio</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- O QUE É RENDA VARIÁVEL -->
    <section class="o_que_e_variavel">
        <div class="section_title">
            <h2>O que é <span class="destaque">Renda Variável?</span></h2>
            <p>Entenda como funciona esse tipo de investimento</p>
        </div>
        <div class="explicacao_container_variavel">
            <div class="explicacao_texto_variavel">
                <p>A <strong>Renda Variável</strong> é uma categoria de investimento onde os retornos não são previamente definidos. Você se torna <strong>sócio</strong> de empresas ou adquire ativos cujo valor oscila conforme o mercado.</p>
                <p>Diferente da Renda Fixa, aqui <strong>não há garantia</strong> de retorno. Porém, o potencial de ganho no longo prazo é historicamente superior, superando a inflação e outras aplicações.</p>
                <div class="destaque_box_variavel">
                    <span>Perfil Recomendado</span>
                    <p>Investidores moderados a agressivos, com horizonte de longo prazo (mínimo 5 anos) e tolerância a oscilações.</p>
                </div>
            </div>
            <div class="explicacao_icon_variavel">
                <div class="icon_circle_variavel">
                    <span>📈</span>
                </div>
            </div>
        </div>
    </section>

    <!-- COMO FUNCIONA -->
    <section class="como_funciona_variavel">
        <div class="section_title">
            <h2>Como <span class="destaque">Funciona</span> na Prática</h2>
            <p>O passo a passo para começar na Bolsa</p>
        </div>
        <div class="steps_variavel">
            <div class="step_variavel">
                <div class="step_num_variavel">1</div>
                <h3>Abra uma conta</h3>
                <p>Em uma corretora de valores ou banco de investimento</p>
            </div>
            <div class="step_variavel">
                <div class="step_num_variavel">2</div>
                <h3>Estude as empresas</h3>
                <p>Análise fundamentalista e indicadores financeiros</p>
            </div>
            <div class="step_variavel">
                <div class="step_num_variavel">3</div>
                <h3>Compre ações</h3>
                <p>Invista em empresas que você acredita</p>
            </div>
            <div class="step_variavel">
                <div class="step_num_variavel">4</div>
                <h3>Acompanhe e rebalanceie</h3>
                <p>Monitore seus investimentos e ajuste a carteira</p>
            </div>
        </div>
    </section>

    <!-- PRINCIPAIS TIPOS -->
    <section class="tipos_variavel">
        <div class="section_title">
            <h2>Principais <span class="destaque">Tipos</span> de Renda Variável</h2>
            <p>Conheça as opções disponíveis no mercado</p>
        </div>
        <div class="tipos_grid_variavel">
            <div class="tipo_card_variavel">
                <div class="tipo_icon_variavel">
                    <img src="img/acoes.png" alt="Ações">
                </div>
                <h3>Ações</h3>
                <p class="tipo_desc_variavel">Frações de empresas negociadas na Bolsa. Você vira sócio.</p>
                <div class="tipo_vantagens_variavel">
                    <span>✓ Dividendos</span>
                    <span>✓ Ganho de capital</span>
                    <span>✓ Liquidez diária</span>
                </div>
                <p class="tipo_rentabilidade_variavel"><strong>Potencial:</strong> Alto no longo prazo</p>
            </div>

            <div class="tipo_card_variavel">
                <div class="tipo_icon_variavel">
                    <img src="img/fiis.png" alt="FIIs">
                </div>
                <h3>Fundos Imobiliários</h3>
                <p class="tipo_desc_variavel">Invista em imóveis sem comprar propriedades físicas.</p>
                <div class="tipo_vantagens_variavel">
                    <span>✓ Isento IR PF</span>
                    <span>✓ Rendimentos mensais</span>
                    <span>✓ Acessível</span>
                </div>
                <p class="tipo_rentabilidade_variavel"><strong>Rentabilidade:</strong> Aluguéis + valorização</p>
            </div>

            <div class="tipo_card_variavel">
                <div class="tipo_icon_variavel">
                    <img src="img/etfs.png" alt="ETFs">
                </div>
                <h3>ETFs</h3>
                <p class="tipo_desc_variavel">Fundos que replicam índices como Ibovespa e S&P500.</p>
                <div class="tipo_vantagens_variavel">
                    <span>✓ Diversificação</span>
                    <span>✓ Baixa taxa</span>
                    <span>✓ Simples</span>
                </div>
                <p class="tipo_rentabilidade_variavel"><strong>Rentabilidade:</strong> Acompanha o índice</p>
            </div>

            <div class="tipo_card_variavel">
                <div class="tipo_icon_variavel">
                    <img src="img/bdr.png" alt="BDRs">
                </div>
                <h3>BDRs</h3>
                <p class="tipo_desc_variavel">Invista em empresas internacionais sem sair do Brasil.</p>
                <div class="tipo_vantagens_variavel">
                    <span>✓ Exposição global</span>
                    <span>✓ Grandes empresas</span>
                    <span>✓ Dólar</span>
                </div>
                <p class="tipo_rentabilidade_variavel"><strong>Rentabilidade:</strong> Variação do ativo + câmbio</p>
            </div>
        </div>
    </section>

    <!-- VANTAGENS E DESVANTAGENS -->
    <section class="prós_contrass_variavel">
        <div class="section_title">
            <h2>Vantagens vs <span class="destaque">Desvantagens</span></h2>
            <p>Entenda os pontos fortes e fracos da Renda Variável</p>
        </div>
        <div class="comparativo_grid_variavel">
            <div class="vantagens_box_variavel">
                <h3>✅ Vantagens</h3>
                <ul>
                    <li>Potencial de retorno superior no longo prazo</li>
                    <li>Recebimento de dividendos e JCP</li>
                    <li>Liquidez diária (ações e FIIs)</li>
                    <li>Proteção contra inflação (empresas reais)</li>
                    <li>Diversificação geográfica (BDRs/ETFs globais)</li>
                    <li>Isenção de IR para PF em vendas até R$20 mil/mês</li>
                </ul>
            </div>
            <div class="desvantagens_box_variavel">
                <h3>❌ Desvantagens</h3>
                <ul>
                    <li>Alta volatilidade (oscilações fortes)</li>
                    <li>Risco de perder parte ou todo o capital</li>
                    <li>Requer estudo e acompanhamento constante</li>
                    <li>Sem garantia de rentabilidade</li>
                    <li>Emoções podem atrapalhar decisões</li>
                    <li>Gatilhos emocionais (medo/greed)</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- DICAS PRÁTICAS -->
    <section class="dicas_praticas_variavel">
        <div class="section_title">
            <h2>Dicas <span class="destaque">Práticas</span></h2>
            <p>Como ter sucesso na Renda Variável</p>
        </div>
        <div class="dicas_grid_variavel">
            <div class="dica_item_variavel">
                <div class="dica_num_variavel">01</div>
                <div class="dica_content_variavel">
                    <h3>Comece com ETFs</h3>
                    <p>Invista no Ibovespa (BOVA11) ou S&P500 (IVVB11) enquanto aprende.</p>
                </div>
            </div>
            <div class="dica_item_variavel">
                <div class="dica_num_variavel">02</div>
                <div class="dica_content_variavel">
                    <h3>Invista no longo prazo</h3>
                    <p>Renda variável é para anos, não meses. Paciência é a chave.</p>
                </div>
            </div>
            <div class="dica_item_variavel">
                <div class="dica_num_variavel">03</div>
                <div class="dica_content_variavel">
                    <h3>Diversifique sua carteira</h3>
                    <p>Diferentes setores, empresas e até países reduzem riscos.</p>
                </div>
            </div>
            <div class="dica_item_variavel">
                <div class="dica_num_variavel">04</div>
                <div class="dica_content_variavel">
                    <h3>Controle as emoções</h3>
                    <p>Não venda no pânico nem compre na euforia. Tenha estratégia.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- EXEMPLO PRÁTICO -->
    <section class="exemplo_container_variavel">
        <div class="exemplo_box_variavel">
            <div class="exemplo_header_variavel">
                <span>📊 Exemplo Prático</span>
                <h2>Quanto pode render na <span class="destaque">Renda Variável?</span></h2>
            </div>
            <div class="exemplo_calc_variavel">
                <div class="exemplo_valor_variavel">
                    <h4>Investimento</h4>
                    <p class="valor_grande">R$ 10.000,00</p>
                </div>
                <div class="exemplo_seta_variavel">→</div>
                <div class="exemplo_valor_variavel">
                    <h4>Prazo</h4>
                    <p class="valor_medio">10 anos</p>
                </div>
                <div class="exemplo_seta_variavel">→</div>
                <div class="exemplo_valor_variavel">
                    <h4>Ibovespa (média 10% a.a.)</h4>
                    <p class="valor_grande destaque">R$ 25.937,00</p>
                    <small>+159% em 10 anos</small>
                </div>
            </div>
            <p class="exemplo_nota_variavel">*Exemplo histórico do Ibovespa. Rentabilidade passada não garante futura.</p>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="cta_variavel">
        <div class="cta_variavel_content">
            <h2>Comece sua jornada nos investimentos</h2>
            <p>Abra sua conta em uma corretora e comece a investir em Renda Variável</p>
            <div class="cta_variavel_buttons">
                <?php if (isset($_SESSION['id'])): ?>
                    <button class="btn_cta_variavel" onclick="window.location.href='carteira.php'">Ir para minha Carteira</button>
                <?php else: ?>
                    <button class="btn_cta_variavel" onclick="window.location.href='cadastro.php'">Criar Conta Grátis</button>
                    <button class="btn_cta_sec_variavel" onclick="window.location.href='login.php'">Já tenho conta</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<script>
    function scrollToConteudo() {
        document.querySelector('.o_que_e_variavel').scrollIntoView({ behavior: 'smooth' });
    }
</script>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>

