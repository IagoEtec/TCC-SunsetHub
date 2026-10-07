<?php

require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();

require_once '../back-end/conexao.php';


/* ============================================================
   PROTEÇÃO DA PÁGINA
============================================================ */

if (!isset($_SESSION['id'])) {

    header('Location: ../front-end/login.php');

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
   ID DO USUÁRIO
============================================================ */

$usuarioId = (int) $usuario['id'];


/* ============================================================
   RESUMO FINANCEIRO
============================================================ */

$resumo = $pdo->prepare("
    SELECT

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'entrada'
                    THEN valor
                    ELSE -valor
                END
            ),
            0
        ) AS saldo,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'entrada'
                    AND MONTH(data_criacao) = MONTH(CURDATE())
                    AND YEAR(data_criacao) = YEAR(CURDATE())
                    THEN valor
                    ELSE 0
                END
            ),
            0
        ) AS receitas_mes,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'saida'
                    AND MONTH(data_criacao) = MONTH(CURDATE())
                    AND YEAR(data_criacao) = YEAR(CURDATE())
                    THEN valor
                    ELSE 0
                END
            ),
            0
        ) AS despesas_mes

    FROM movimentacoes

    WHERE usuario_id = ?
");

$resumo->execute([$usuarioId]);

$dadosResumo =
    $resumo->fetch(PDO::FETCH_ASSOC) ?: [];

$saldo =
    (float) ($dadosResumo['saldo'] ?? 0);

$receitasMes =
    (float) ($dadosResumo['receitas_mes'] ?? 0);

$despesasMes =
    (float) ($dadosResumo['despesas_mes'] ?? 0);

$resultadoMes =
    $receitasMes - $despesasMes;

?>
<!DOCTYPE html>

<html
    lang="pt-BR"
    class="<?= htmlspecialchars($tema, ENT_QUOTES, 'UTF-8') ?>"
>



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Analisador de Gastos · AFDE</title>
    <link rel="stylesheet" href="../css/analisador.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap"
        rel="stylesheet"
    >

    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>

<body>

    <?php
        include '../front-end/navbar.php';
    ?>

    <main>

        <!-- =====================================================
             HERO DO ANALISADOR
        ====================================================== -->

        <section id="analisador-hero" class="hero">

            <div class="hero_container">

                <div class="hero_content">

                    <span class="tag">
                        Análise Inteligente com IA
                    </span>

                    <h1>
                        Envie seu extrato e descubra para onde seu dinheiro está indo
                    </h1>

                    <p>
                        Envie um arquivo PDF ou CSV do seu extrato bancário.
                        Nossa IA organiza os lançamentos por categoria e sugere
                        formas práticas de economizar.
                    </p>

                </div>


                    <div class="card_dashboard">

                        <div class="saldo">

                            <span>Exemplo de Resultado</span>

                            <h2>R$ 2.903</h2>

                        </div>

                        <div class="dashboard_info">

                            <div>

                                <span>Maior Categoria</span>

                                <h3>Alimentação</h3>

                            </div>

                            <div>

                                <span>Economia Sugerida</span>

                                <h3>R$ 310</h3>

                            </div>

                        </div>

                    </div>



            </div>

        </section>


        <!-- =====================================================
             ÁREA DE UPLOAD
        ====================================================== -->

        <section id="upload" class="section_dark">

            <div class="section_title">

                <h2>Envie seu extrato</h2>

                <p>
                    Aceita arquivos em PDF ou CSV, de até 10&nbsp;MB.
                </p>

            </div>


            <div class="analisador_box">

                <form id="upload-form">

                    <!-- ÁREA DE UPLOAD -->

                    <label
                        for="file-input"
                        class="upload_slot"
                        id="slot"
                    >

                        <svg
                            class="upload_slot_icon"
                            width="40"
                            height="40"
                            viewBox="0 0 40 40"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >

                            <path
                                d="M8 26V32C8 33.1046 8.89543 34 10 34H30C31.1046 34 32 33.1046 32 32V26"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />

                            <path
                                d="M20 6V25"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />

                            <path
                                d="M13 15L20 6L27 15"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                        </svg>


                        <span
                            class="upload_slot_label"
                            id="slot-label"
                        >
                            Solte o extrato aqui ou toque para escolher
                        </span>


                        <span class="upload_slot_hint">
                            PDF ou CSV · até 10&nbsp;MB
                        </span>


                        <input
                            type="file"
                            id="file-input"
                            name="extrato"
                            accept=".pdf,.csv"
                            hidden
                        >

                    </label>


                    <!-- ARQUIVO SELECIONADO -->

                    <div
                        class="file_chip"
                        id="file-chip"
                        hidden
                    >

                        <span
                            class="file_chip_name"
                            id="file-chip-name"
                        ></span>


                        <button
                            type="button"
                            class="file_chip_remove"
                            id="file-chip-remove"
                            aria-label="Remover arquivo"
                        >
                            ×
                        </button>

                    </div>


                    <!-- BOTÃO -->

                    <button
                        type="submit"
                        class="btn_primary btn_full"
                        id="submit-btn"
                        disabled
                    >
                        Analisar Gastos
                    </button>


                    <!-- STATUS -->

                    <p
                        class="upload_status"
                        id="status"
                        role="status"
                    ></p>

                </form>


                <!-- =================================================
                     RESULTADO DA IA
                ================================================== -->

                <div
                    class="resultado_box"
                    id="resultado"
                    hidden
                >

                    <div class="resultado_header">

                        <div class="resultado_icon">
                            ✨
                        </div>

                        <div>

                            <h3>
                                Resumo dos seus gastos
                            </h3>

                            <span class="resultado_subtitle">
                                Análise gerada pela inteligência artificial
                            </span>

                        </div>

                    </div>


                    <!--
                        O Markdown convertido pelo JavaScript
                        será inserido aqui.
                    -->

                    <div
                        class="resultado_conteudo"
                        id="resultado-texto"
                    ></div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             COMO FUNCIONA
        ====================================================== -->

        <section class="section_light">

            <div class="section_title">

                <h2>
                    Como funciona a análise
                </h2>

                <p>
                    Três passos simples até você ver o resumo dos seus gastos.
                </p>

            </div>


            <div class="steps">

                <div class="step_card">

                    <div class="step_number">
                        01
                    </div>

                    <h3>
                        Envie
                    </h3>

                    <p>
                        Faça upload do seu extrato em PDF ou CSV.
                    </p>

                </div>


                <div class="step_card">

                    <div class="step_number">
                        02
                    </div>

                    <h3>
                        Processamos
                    </h3>

                    <p>
                        A IA lê os lançamentos e identifica categorias de gastos.
                    </p>

                </div>


                <div class="step_card">

                    <div class="step_number">
                        03
                    </div>

                    <h3>
                        Receba o resumo
                    </h3>

                    <p>
                        Veja onde seu dinheiro foi e dicas para economizar.
                    </p>

                </div>

            </div>

        </section>

    </main>


    <!-- =========================================================
         FOOTER
    ========================================================== -->

    <footer>

        <div class="footer_container">

            <img
                class="footer_logo"
                src="../img/logo.png"
                alt="Logo"
            >

            <p>
                © 2026 AFDE. Todos os direitos reservados.
            </p>

        </div>

    </footer>


    <!-- =========================================================
         JAVASCRIPT
    ========================================================== -->

    <script>

        const form = document.getElementById('upload-form');

        const slot = document.getElementById('slot');

        const slotLabel = document.getElementById('slot-label');

        const fileInput = document.getElementById('file-input');

        const fileChip = document.getElementById('file-chip');

        const fileChipName = document.getElementById('file-chip-name');

        const removeBtn = document.getElementById('file-chip-remove');

        const submitBtn = document.getElementById('submit-btn');

        const statusEl = document.getElementById('status');

        const resultadoBox = document.getElementById('resultado');

        const resultadoTexto = document.getElementById('resultado-texto');


        // =========================================================
        // ENDEREÇO DA API
        // =========================================================

        const API_URL = 'http://127.0.0.1:5000/api/analisar';


        // =========================================================
        // SELEÇÃO DO ARQUIVO
        // =========================================================

        function setFile(file) {

            if (!file) {
                return;
            }

            fileInput.files = createFileList(file);

            fileChipName.textContent = file.name;

            fileChip.hidden = false;

            slotLabel.textContent = 'Arquivo pronto para envio';

            submitBtn.disabled = false;

        }


        function createFileList(file) {

            const dt = new DataTransfer();

            dt.items.add(file);

            return dt.files;

        }


        function resetFile() {

            fileInput.value = '';

            fileChip.hidden = true;

            slotLabel.textContent =
                'Solte o extrato aqui ou toque para escolher';

            submitBtn.disabled = true;

            resultadoBox.hidden = true;

            resultadoTexto.innerHTML = '';

            statusEl.textContent = '';

        }


        fileInput.addEventListener('change', () => {

            if (fileInput.files[0]) {

                setFile(fileInput.files[0]);

            }

        });


        removeBtn.addEventListener('click', resetFile);


        // =========================================================
        // DRAG AND DROP
        // =========================================================

        ['dragenter', 'dragover'].forEach(evt => {

            slot.addEventListener(evt, e => {

                e.preventDefault();

                slot.classList.add('upload_slot--drag');

            });

        });


        ['dragleave', 'drop'].forEach(evt => {

            slot.addEventListener(evt, e => {

                e.preventDefault();

                slot.classList.remove('upload_slot--drag');

            });

        });


        slot.addEventListener('drop', e => {

            const file = e.dataTransfer.files[0];

            if (file) {

                setFile(file);

            }

        });


        // =========================================================
        // CONVERSOR MARKDOWN → HTML
        //
        // Converte somente os formatos Markdown esperados
        // pela resposta do Gemini.
        //
        // Não usamos innerHTML diretamente com a resposta
        // original da IA.
        // =========================================================

        function escapeHTML(text) {

            return text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        }


        function markdownToHTML(markdown) {

            if (!markdown) {

                return '<p>Nenhum resumo foi retornado.</p>';

            }


            let text = escapeHTML(markdown);


            // =====================================================
            // CÓDIGO INLINE
            // =====================================================

            text = text.replace(
                /`([^`]+)`/g,
                '<code>$1</code>'
            );


            // =====================================================
            // NEGRITO
            // =====================================================

            text = text.replace(
                /\*\*(.+?)\*\*/g,
                '<strong>$1</strong>'
            );


            // =====================================================
            // ITÁLICO
            // =====================================================

            text = text.replace(
                /(?<!\*)\*([^*\n]+)\*(?!\*)/g,
                '<em>$1</em>'
            );


            // =====================================================
            // TÍTULOS MARKDOWN
            // =====================================================

            text = text.replace(
                /^### (.+)$/gm,
                '<h5>$1</h5>'
            );

            text = text.replace(
                /^## (.+)$/gm,
                '<h4>$1</h4>'
            );

            text = text.replace(
                /^# (.+)$/gm,
                '<h3>$1</h3>'
            );


            // =====================================================
            // SEPARADORES
            // =====================================================

            text = text.replace(
                /^\s*(---|\*\*\*)\s*$/gm,
                '<hr>'
            );


            // =====================================================
            // LISTAS NÃO ORDENADAS
            // =====================================================

            text = text.replace(
                /^(?:-|\*) (.+)$/gm,
                '<li>$1</li>'
            );


            text = text.replace(
                /(<li>.*<\/li>(?:\n|$))+/g,
                function(match) {

                    const itens = match
                        .trim()
                        .replace(/\n/g, '');

                    return '<ul>' + itens + '</ul>';

                }
            );


            // =====================================================
            // LISTAS NUMERADAS
            // =====================================================

            text = text.replace(
                /^\d+\.\s+(.+)$/gm,
                '<li>$1</li>'
            );


            // Tenta agrupar itens numerados
            // que ainda estejam fora de uma lista.

            text = text.replace(
                /(<li>.*<\/li>)(?=\n|$)/g,
                '$1'
            );


            // =====================================================
            // QUEBRAS DE LINHA
            // =====================================================

            text = text.replace(
                /\n{2,}/g,
                '</p><p>'
            );


            text = text.replace(
                /\n/g,
                '<br>'
            );


            // =====================================================
            // EVITA PARÁGRAFOS VAZIOS
            // =====================================================

            text = text.replace(
                /<p><br><\/p>/g,
                ''
            );


            return '<p>' + text + '</p>';

        }


        // =========================================================
        // ENVIO DO FORMULÁRIO
        // =========================================================

        form.addEventListener('submit', async (e) => {

            e.preventDefault();


            const file = fileInput.files[0];


            if (!file) {

                console.log('Nenhum arquivo selecionado');

                return;

            }


            // =====================================================
            // VALIDAÇÃO DO TAMANHO
            // =====================================================

            const tamanhoMaximo = 10 * 1024 * 1024;

            if (file.size > tamanhoMaximo) {

                statusEl.textContent =
                    'O arquivo deve ter no máximo 10 MB.';

                statusEl.className =
                    'upload_status upload_status--error';

                return;

            }


            // =====================================================
            // VALIDAÇÃO DA EXTENSÃO
            // =====================================================

            const extensao =
                file.name
                    .split('.')
                    .pop()
                    .toLowerCase();


            if (!['pdf', 'csv'].includes(extensao)) {

                statusEl.textContent =
                    'Envie apenas arquivos PDF ou CSV.';

                statusEl.className =
                    'upload_status upload_status--error';

                return;

            }


            submitBtn.disabled = true;


            statusEl.textContent =
                'Lendo o extrato…';


            statusEl.className =
                'upload_status upload_status--pending';


            resultadoBox.hidden = true;


            const formData = new FormData();

            formData.append(
                'extrato',
                file
            );


            console.log(
                'Enviando arquivo:',
                file.name
            );


            console.log(
                'URL da API:',
                API_URL
            );


            try {

                const res = await fetch(
                    API_URL,
                    {
                        method: 'POST',
                        body: formData
                    }
                );


                console.log(
                    'Status HTTP:',
                    res.status
                );


                const textoResposta =
                    await res.text();


                console.log(
                    'Resposta do Flask:',
                    textoResposta
                );


                if (!res.ok) {

                    throw new Error(
                        `Servidor respondeu ${res.status}: ${textoResposta}`
                    );

                }


                const data =
                    JSON.parse(textoResposta);


                console.log(
                    'JSON recebido:',
                    data
                );


                // =================================================
                // VERIFICA SE O BACKEND RETORNOU ERRO
                // =================================================

                if (data.erro) {

                    throw new Error(
                        data.erro
                    );

                }


                // =================================================
                // CONVERTE MARKDOWN PARA HTML
                // =================================================

                const markdown =
                    data.resumo || '';


                resultadoTexto.innerHTML =
                    markdownToHTML(markdown);


                // =================================================
                // MOSTRA RESULTADO
                // =================================================

                statusEl.textContent =
                    'Resumo pronto — veja abaixo.';


                statusEl.className =
                    'upload_status upload_status--ok';


                resultadoBox.hidden = false;


                resultadoBox.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });


                submitBtn.disabled = false;

            }


            catch (err) {

                console.error(
                    'ERRO COMPLETO:',
                    err
                );


                statusEl.textContent =
                    'Não consegui analisar o arquivo. Tente novamente.';


                statusEl.className =
                    'upload_status upload_status--error';


                submitBtn.disabled = false;

            }

        });

    </script>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>

