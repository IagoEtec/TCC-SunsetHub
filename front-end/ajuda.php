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

    <title>Central de ajuda | AFDE</title>

    <link rel="stylesheet" href="../css/paginas-suporte.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/icon?family=Material+Icons"
        rel="stylesheet"
    >

    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>

<body>
<?php include_once 'navbar.php'; ?>

    <!-- ===================== BACKGROUND ===================== -->

    <div class="background_shapes">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
        <div class="shape shape3"></div>
    </div>


    <!-- ===================== BOTÃO VOLTAR ===================== -->

    <a href="configuracoes.php" class="btn_voltar">
        <span class="material-icons">arrow_back</span>
        Voltar
    </a>


    <!-- ===================== CONTEÚDO ===================== -->

    <main class="pagina_container">


        <!-- ===================== CABEÇALHO ===================== -->

        <section class="pagina_header">

            <div class="pagina_header_icon">
                <span class="material-icons">help_outline</span>
            </div>

            <div>
                <h1>Central de ajuda</h1>

                <p>
                    Tire dúvidas frequentes ou fale com o nosso suporte
                </p>
            </div>

        </section>


        <!-- ===================== BUSCA ===================== -->

        <div class="pagina_search glass-card">

            <span class="material-icons">search</span>

            <input
                type="text"
                id="busca-ajuda"
                placeholder="Busque por um assunto (ex: transações, senha, exportar dados...)"
                autocomplete="off"
            >

        </div>


        <!-- ===================== FAQ ===================== -->

        <h2 class="pagina_section_title">
            Perguntas frequentes
        </h2>


        <div class="faq_list" id="faq-list">



            <!-- PERGUNTA 2 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >

                    <span>
                        Como altero minha senha?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        Acesse a página de Configurações e procure pela
                        opção relacionada à senha e login. Informe sua
                        senha atual e depois defina a nova senha.
                    </p>

                </div>

            </div>


            <!-- PERGUNTA 3 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >

                    <span>
                        Consigo exportar meus dados financeiros?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        Sim. Em Configurações, acesse a área de
                        Privacidade e dados. Lá você poderá encontrar
                        a opção para exportar suas informações financeiras
                        e baixar seus dados.
                    </p>

                </div>

            </div>


            <!-- PERGUNTA 4 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >

                    <span>
                        O AFDE é gratuito?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        Sim. O AFDE possui suas principais
                        funcionalidades disponíveis gratuitamente
                        para uso pessoal.
                    </p>

                </div>

            </div>


            <!-- PERGUNTA 5 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >

                    <span>
                        Como excluo minha conta permanentemente?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        Acesse Configurações e procure pela seção
                        de Privacidade e dados. Na área de segurança
                        ou zona de risco, selecione a opção para
                        excluir sua conta. Essa ação é permanente
                        e pode remover seus dados do sistema.
                    </p>

                </div>

            </div>


            <!-- PERGUNTA 7 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >
                    <span>
                        Como cadastro uma transação?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        Acesse a área de transações e selecione
                        "Adicionar". Escolha o tipo "Despesa", informe
                        o valor, a categoria e a data e finalize
                        o cadastro.
                    </p>

                </div>

            </div>


            <!-- PERGUNTA 8 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >

                    <span>
                        Posso editar uma transação depois de cadastrá-la?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        Sim. Localize a transação que deseja alterar
                        e utilize a opção de edição disponível. Depois
                        de modificar as informações, salve as alterações.
                    </p>

                </div>

            </div>


            <!-- PERGUNTA 9 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >

                    <span>
                        Meus dados financeiros ficam seguros?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        O AFDE utiliza mecanismos de segurança
                        para proteger as informações armazenadas.
                        Recomendamos também que você utilize uma senha
                        forte e não compartilhe suas credenciais de acesso.
                    </p>

                </div>

            </div>


            <!-- PERGUNTA 10 -->

            <div class="faq_item glass-card">

                <button
                    type="button"
                    class="faq_question"
                    data-faq-toggle
                    aria-expanded="false"
                >

                    <span>
                        Esqueci minha senha. O que devo fazer?
                    </span>

                    <span class="material-icons faq_arrow">
                        expand_more
                    </span>

                </button>


                <div class="faq_answer">

                    <p>
                        Na tela de login, procure pela opção de
                        recuperação de senha e siga as instruções
                        apresentadas para recuperar o acesso à sua conta.
                    </p>

                </div>

            </div>


        </div>


        <!-- ===================== SEM RESULTADOS ===================== -->

        <div
            id="faq-sem-resultados"
            style="
                display: none;
                color: rgba(255,255,255,0.70);
                text-align: center;
                padding: 30px 20px;
                font-size: 14px;
            "
        >

            <span
                class="material-icons"
                style="
                    display: block;
                    font-size: 35px;
                    margin-bottom: 10px;
                    color: var(--accent);
                "
            >
                search_off
            </span>

            Nenhuma pergunta encontrada para sua busca.

        </div>


        <!-- ===================== CONTATO ===================== -->

        <h2 class="pagina_section_title">
            Ainda precisa de ajuda?
        </h2>


        <div class="pagina_card glass-card">

            <div class="pagina_card_icon">

                <span class="material-icons">
                    mail_outline
                </span>

            </div>


            <div class="pagina_card_text">

                <h3>
                    Fale com o suporte
                </h3>

                <p>
                    Nossa equipe responde em até 24h úteis pelo
                    e-mail suporte@fincontrol.com
                </p>

            </div>


            <a
                href="mailto:suporte@fincontrol.com"
                class="btn_pagina_secundario"
            >

                <span class="material-icons">
                    send
                </span>

                Enviar e-mail

            </a>

        </div>


    </main>


    <!-- ===================== JAVASCRIPT ===================== -->

    <script>

        document.addEventListener("DOMContentLoaded", function () {

            const perguntas = document.querySelectorAll("[data-faq-toggle]");
            const campoBusca = document.getElementById("busca-ajuda");
            const listaFaq = document.getElementById("faq-list");
            const semResultados = document.getElementById("faq-sem-resultados");


            /*
             * =====================================================
             * ABRIR / FECHAR PERGUNTAS
             * =====================================================
             */

            perguntas.forEach(function (pergunta) {

                pergunta.addEventListener("click", function () {

                    const item = pergunta.closest(".faq_item");

                    /*
                     * Se quiser permitir apenas uma pergunta aberta
                     * por vez, fechamos as outras.
                     */

                    document.querySelectorAll(".faq_item.active").forEach(function (outroItem) {

                        if (outroItem !== item) {

                            outroItem.classList.remove("active");

                            const outroBotao = outroItem.querySelector("[data-faq-toggle]");

                            if (outroBotao) {
                                outroBotao.setAttribute("aria-expanded", "false");
                            }

                        }

                    });


                    /*
                     * Alterna a pergunta clicada
                     */

                    const abriu = item.classList.toggle("active");

                    pergunta.setAttribute(
                        "aria-expanded",
                        abriu ? "true" : "false"
                    );

                });

            });


            /*
             * =====================================================
             * BUSCA DAS PERGUNTAS
             * =====================================================
             */

            campoBusca.addEventListener("input", function () {

                const termo = campoBusca.value
                    .toLowerCase()
                    .trim();

                const itens = document.querySelectorAll(".faq_item");

                let encontrados = 0;


                itens.forEach(function (item) {

                    const texto = item.textContent
                        .toLowerCase();

                    if (texto.includes(termo)) {

                        item.style.display = "";

                        encontrados++;

                    } else {

                        item.style.display = "none";

                        item.classList.remove("active");

                        const botao = item.querySelector("[data-faq-toggle]");

                        if (botao) {
                            botao.setAttribute("aria-expanded", "false");
                        }

                    }

                });


                /*
                 * Mostra mensagem quando não encontra
                 * nenhuma pergunta.
                 */

                if (encontrados === 0) {

                    semResultados.style.display = "block";
                    listaFaq.style.display = "none";

                } else {

                    semResultados.style.display = "none";
                    listaFaq.style.display = "flex";

                }

            });

        });

    </script>



    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>

