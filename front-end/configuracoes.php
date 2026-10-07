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

try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN banner VARCHAR(255) NULL AFTER foto");
} catch (PDOException $ignored) {
    // A coluna já existe ou será criada pelo migration_perfil.sql.
}

$stmt = $pdo->prepare("
    SELECT id, nome, email, telefone, foto, banner, tema
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

$fotoConfiguracao = $usuario['foto'] ?? '';
if ($fotoConfiguracao && !preg_match('/^https?:\\/\\//i', $fotoConfiguracao)) {
    $fotoConfiguracao = str_starts_with($fotoConfiguracao, '../') ? $fotoConfiguracao : '../' . ltrim($fotoConfiguracao, '/');
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
   INICIAIS DO USUÁRIO
============================================================ */

$partesNome = explode(" ", trim($usuario['nome']));

$iniciais = strtoupper(substr($partesNome[0], 0, 1));

if (count($partesNome) > 1) {

    $iniciais .= strtoupper(substr(end($partesNome), 0, 1));

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

    <title>Configurações | AFDE</title>

    <link
        rel="stylesheet"
        href="../css/configuracoes.css"
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


<div class="background_shapes">

    <div class="shape shape1"></div>

    <div class="shape shape2"></div>

    <div class="shape shape3"></div>

</div>


<main class="perfil_container">


    <!-- ===================== CABEÇALHO DO PERFIL ===================== -->

    <section class="perfil_header glass">

        <div class="perfil_header_info">


            <?php if (!empty($fotoConfiguracao)): ?>

                <img
                    src="<?= htmlspecialchars($fotoConfiguracao, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Foto de perfil"
                    class="perfil_avatar"
                >

            <?php else: ?>

                <div class="perfil_avatar perfil_avatar_placeholder">

                    <?= htmlspecialchars($iniciais) ?>

                </div>

            <?php endif; ?>


            <div class="perfil_dados">

                <h1>
                    <?= htmlspecialchars($usuario['nome']) ?>
                </h1>


                <p class="perfil_email">

                    <span class="material-icons">
                        mail_outline
                    </span>

                    <?= htmlspecialchars($usuario['email']) ?>

                </p>


                <?php if (!empty($usuario['telefone'])): ?>

                    <p class="perfil_telefone">

                        <span class="material-icons">
                            call
                        </span>

                        <?= htmlspecialchars($usuario['telefone']) ?>

                    </p>

                <?php endif; ?>

            </div>

        </div>


        <a
            href="perfil.php"
            class="btn_editar_perfil"
        >

            <span class="material-icons">
                edit
            </span>

            Editar perfil

        </a>

    </section>


    <!-- ===================== SEÇÃO: CONTA ===================== -->

    <h2 class="settings_section_title">
        Conta
    </h2>


    <div class="settings_grid">


        <a
            href="perfil.php"
            class="settings_card glass-card"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    person
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Dados pessoais
                </h3>

                <p>
                    Nome, e-mail, telefone e foto de perfil
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </a>


        <!-- Preferências -->

        <button
            type="button"
            class="settings_card glass-card settings_card_btn"
            data-modal-target="modal-preferencias"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    tune
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Preferências
                </h3>

                <p>
                    Personalize sua experiência: moeda, tema, idioma e outras preferências
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </button>


        <!-- Notificações -->

        <button
            type="button"
            class="settings_card glass-card settings_card_btn"
            data-modal-target="modal-notificacoes"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    notifications_none
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Notificações
                </h3>

                <p>
                    Escolha como e quando você quer ser avisado
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </button>

    </div>


    <!-- ===================== SEGURANÇA E PRIVACIDADE ===================== -->

    <h2 class="settings_section_title">
        Segurança e privacidade
    </h2>


    <div class="settings_grid">


        <a
            href="perfil.php"
            class="settings_card glass-card"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    lock_outline
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Senha e login
                </h3>

                <p>
                    Altere sua senha e gerencie o acesso à sua conta
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </a>


        <a
            href="privacidade.php"
            class="settings_card glass-card"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    shield
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Privacidade e dados
                </h3>

                <p>
                    Controle o uso dos seus dados e exporte suas informações
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </a>

    </div>


    <!-- ===================== SUPORTE ===================== -->

    <h2 class="settings_section_title">
        Suporte
    </h2>


    <div class="settings_grid">


        <a
            href="ajuda.php"
            class="settings_card glass-card"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    help_outline
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Central de ajuda
                </h3>

                <p>
                    Tire dúvidas e entre em contato com o suporte
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </a>


        <a
            href="sobre.php"
            class="settings_card glass-card"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    info_outline
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Sobre o AFDE
                </h3>

                <p>
                    Versão do aplicativo, termos de uso e política de privacidade
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </a>

    </div>


    <!-- ===================== SAIR ===================== -->

    <div class="settings_grid">

        <button
            type="button"
            class="settings_card glass-card settings_card_danger settings_card_btn"
            data-modal-target="modal-sair"
        >

            <div class="settings_card_icon">

                <span class="material-icons">
                    logout
                </span>

            </div>


            <div class="settings_card_text">

                <h3>
                    Sair da conta
                </h3>

                <p>
                    Encerrar sua sessão neste dispositivo
                </p>

            </div>


            <span class="material-icons settings_card_arrow">
                arrow_forward_ios
            </span>

        </button>

    </div>

</main>


<!-- ===================== MODAL: SAIR DA CONTA ===================== -->

<div
    class="modal_overlay"
    id="modal-sair"
    data-modal
>

    <div
        class="modal_box glass"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-sair-title"
    >

        <button
            type="button"
            class="modal_close"
            data-modal-close
            aria-label="Fechar"
        >

            <span class="material-icons">
                close
            </span>

        </button>


        <div class="modal_icon modal_icon_danger">

            <span class="material-icons">
                logout
            </span>

        </div>


        <h2
            id="modal-sair-title"
            class="modal_title"
        >
            Sair da conta
        </h2>


        <p class="modal_text">

            Tem certeza que deseja encerrar sua sessão neste dispositivo?
            Você precisará fazer login novamente para acessar sua conta.

        </p>


        <div class="modal_actions">

            <button
                type="button"
                class="btn_modal_secundario"
                data-modal-close
            >
                Cancelar
            </button>


            <a
                href="../back-end/logout.php"
                class="btn_modal_perigo"
            >
                Sair da conta
            </a>

        </div>

    </div>

</div>


<!-- ===================== MODAL: PREFERÊNCIAS ===================== -->

<div
    class="modal_overlay"
    id="modal-preferencias"
    data-modal
>

    <div
        class="modal_box glass"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-preferencias-title"
    >

        <button
            type="button"
            class="modal_close"
            data-modal-close
            aria-label="Fechar"
        >

            <span class="material-icons">
                close
            </span>

        </button>


        <div class="modal_icon">

            <span class="material-icons">
                tune
            </span>

        </div>


        <h2
            id="modal-preferencias-title"
            class="modal_title"
        >
            Preferências
        </h2>


        <p class="modal_text">

            Personalize a moeda, o tema e o idioma da sua experiência no AFDE.

        </p>


        <form
            action="../back-end/salvar_preferencias.php"
            method="POST"
            class="modal_form"
        >
            <?= csrf_campo() ?>



            <div class="modal_field">

                <label for="moeda">
                    Moeda
                </label>


                <select
                    name="moeda"
                    id="moeda"
                >

                    <option value="BRL">
                        Real (R$)
                    </option>

                    <option value="USD">
                        Dólar (US$)
                    </option>

                    <option value="EUR">
                        Euro (€)
                    </option>

                </select>

            </div>


            <div class="modal_field">

                <label for="tema">
                    Tema
                </label>


                <select
                    name="tema"
                    id="tema"
                >

                    <option value="claro">
                        Claro
                    </option>

                    <option value="escuro">
                        Escuro
                    </option>

                    <option value="sistema">
                        Automático (sistema)
                    </option>

                </select>

            </div>


            <div class="modal_field">

                <label for="idioma">
                    Idioma
                </label>


                <select
                    name="idioma"
                    id="idioma"
                >

                    <option value="pt-BR">
                        Português (Brasil)
                    </option>

                    <option value="en-US">
                        English (US)
                    </option>

                    <option value="es-ES">
                        Español
                    </option>

                </select>

            </div>


            <div class="modal_actions">

                <button
                    type="button"
                    class="btn_modal_secundario"
                    data-modal-close
                >
                    Cancelar
                </button>


                <button
                    type="submit"
                    class="btn_modal_primario"
                >
                    Salvar alterações
                </button>

            </div>

        </form>

    </div>

</div>


<!-- ===================== MODAL: NOTIFICAÇÕES ===================== -->

<div
    class="modal_overlay"
    id="modal-notificacoes"
    data-modal
>

    <div
        class="modal_box glass"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-notificacoes-title"
    >

        <button
            type="button"
            class="modal_close"
            data-modal-close
            aria-label="Fechar"
        >

            <span class="material-icons">
                close
            </span>

        </button>


        <div class="modal_icon">

            <span class="material-icons">
                notifications_none
            </span>

        </div>


        <h2
            id="modal-notificacoes-title"
            class="modal_title"
        >
            Notificações
        </h2>


        <p class="modal_text">

            Escolha como e quando você quer ser avisado sobre a sua conta.

        </p>


        <form
            action="../back-end/atualizar-notificacoes.php"
            method="POST"
            class="modal_form"
        >
            <?= csrf_campo() ?>



            <label class="modal_toggle">

                <div class="modal_toggle_text">

                    <span>
                        Notificações por e-mail
                    </span>

                    <small>
                        Receba resumos e alertas importantes no seu e-mail
                    </small>

                </div>


                <input
                    type="checkbox"
                    name="notif_email"
                    value="1"
                    checked
                >


                <span class="modal_toggle_slider"></span>

            </label>


            <label class="modal_toggle">

                <div class="modal_toggle_text">

                    <span>
                        Notificações push
                    </span>

                    <small>
                        Alertas em tempo real no navegador ou app
                    </small>

                </div>


                <input
                    type="checkbox"
                    name="notif_push"
                    value="1"
                    checked
                >


                <span class="modal_toggle_slider"></span>

            </label>


            <label class="modal_toggle">

                <div class="modal_toggle_text">

                    <span>
                        Resumo semanal
                    </span>

                    <small>
                        Um resumo das suas finanças toda semana
                    </small>

                </div>


                <input
                    type="checkbox"
                    name="notif_resumo"
                    value="1"
                >


                <span class="modal_toggle_slider"></span>

            </label>


            <label class="modal_toggle">

                <div class="modal_toggle_text">

                    <span>
                        Alertas de gastos
                    </span>

                    <small>
                        Avisos quando você se aproximar do limite definido
                    </small>

                </div>


                <input
                    type="checkbox"
                    name="notif_alertas"
                    value="1"
                    checked
                >


                <span class="modal_toggle_slider"></span>

            </label>


            <div class="modal_actions">

                <button
                    type="button"
                    class="btn_modal_secundario"
                    data-modal-close
                >
                    Cancelar
                </button>


                <button
                    type="submit"
                    class="btn_modal_primario"
                >
                    Salvar alterações
                </button>

            </div>

        </form>

    </div>

</div>


<script>

(function () {

    const openButtons =
        document.querySelectorAll('[data-modal-target]');

    const closeButtons =
        document.querySelectorAll('[data-modal-close]');

    const overlays =
        document.querySelectorAll('[data-modal]');


    function openModal(id) {

        const modal =
            document.getElementById(id);

        if (!modal) return;

        modal.classList.add('modal_open');

        document.body.classList.add('modal_no_scroll');

    }


    function closeModal(overlay) {

        overlay.classList.remove('modal_open');

        document.body.classList.remove('modal_no_scroll');

    }


    openButtons.forEach(function (btn) {

        btn.addEventListener('click', function () {

            openModal(
                btn.getAttribute('data-modal-target')
            );

        });

    });


    closeButtons.forEach(function (btn) {

        btn.addEventListener('click', function () {

            closeModal(
                btn.closest('[data-modal]')
            );

        });

    });


    overlays.forEach(function (overlay) {

        overlay.addEventListener('click', function (e) {

            if (e.target === overlay) {

                closeModal(overlay);

            }

        });

    });


    document.addEventListener('keydown', function (e) {

        if (e.key === 'Escape') {

            document
                .querySelectorAll('.modal_open')
                .forEach(function (overlay) {

                    closeModal(overlay);

                });

        }

    });

})();

</script>



    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>
