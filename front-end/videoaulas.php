<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once("../back-end/conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Videoaulas</title>

    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="icon" type="image/png" href="../img/favicon.png">

    <style>

        /* ============================================================
           VARIÁVEIS
        ============================================================ */

        :root {

            --azul-principal: #0A2540;
            --azul-escuro: #06192B;
            --azul-card: #0D2F50;
            --azul-card-hover: #123B61;

            --verde: #16E28A;
            --verde-escuro: #0DB873;

            --branco: #FFFFFF;
            --texto: #F4F7FA;
            --texto-secundario: #C6D0DA;
            --texto-terciario: #91A2B2;

            --borda: rgba(255,255,255,0.12);

        }


        /* ============================================================
           RESET
        ============================================================ */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            font-family:
                'Inter',
                -apple-system,
                BlinkMacSystemFont,
                'Segoe UI',
                Roboto,
                Arial,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #06192B 0%,
                    #0A2540 50%,
                    #071D32 100%
                );

            color: var(--texto);

            min-height: 100vh;

            overflow-x: hidden;

        }


        button,
        input,
        textarea,
        select {

            font-family: inherit;

        }


        /* ============================================================
           HERO
        ============================================================ */

        .hero-videoaulas {

            position: relative;

            padding: 125px 25px 55px;

            background:
                linear-gradient(
                    135deg,
                    #0A2540,
                    #071E34
                );

            border-bottom:
                1px solid rgba(255,255,255,0.10);

            overflow: hidden;

        }


        .hero-videoaulas::before {

            content: "";

            position: absolute;

            width: 500px;
            height: 500px;

            right: -200px;
            top: -250px;

            border-radius: 50%;

            background:
                rgba(22,226,138,0.08);

            filter: blur(90px);

        }


        .hero-videoaulas::after {

            content: "";

            position: absolute;

            width: 400px;
            height: 400px;

            left: -250px;
            bottom: -250px;

            border-radius: 50%;

            background:
                rgba(22,226,138,0.04);

            filter: blur(90px);

        }


        .hero-container {

            width: min(1300px, 100%);

            margin: auto;

            display: flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap: 30px;

            flex-wrap: wrap;

            position: relative;

            z-index: 2;

        }


        .hero-left h1 {

            font-size:
                clamp(2.3rem, 5vw, 4rem);

            font-weight: 800;

            line-height: 1.1;

            color: var(--branco);

        }


        .hero-left h1 .destaque {

            color: var(--verde);

        }


        .hero-left p {

            margin-top: 12px;

            max-width: 520px;

            color: var(--texto-secundario);

            font-size: 1.05rem;

            line-height: 1.6;

        }


        /* ============================================================
           ESTATÍSTICAS
        ============================================================ */

        .hero-right {

            display: flex;

            align-items: center;

            gap: 15px;

            flex-wrap: wrap;

        }


        .hero-stats {

            display: flex;

            align-items: center;

            gap: 20px;

            padding: 14px 20px;

            background:
                rgba(255,255,255,0.06);

            border:
                1px solid rgba(255,255,255,0.12);

            border-radius: 16px;

        }


        .hero-stats span {

            display: flex;

            align-items: center;

            gap: 7px;

            color:
                var(--texto-secundario);

            font-size: 0.9rem;

            white-space: nowrap;

        }


        .hero-stats i {

            color: var(--verde);

        }


        /* ============================================================
           BOTÕES
        ============================================================ */

        .btn-voltar,
        .btn-voltar-modulos {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 12px 20px;

            color: var(--branco);

            background:
                rgba(255,255,255,0.06);

            border:
                1px solid rgba(255,255,255,0.12);

            border-radius: 12px;

            text-decoration: none;

            font-weight: 600;

            cursor: pointer;

            transition: 0.25s ease;

        }


        .btn-voltar:hover,
        .btn-voltar-modulos:hover {

            color: var(--verde);

            border-color:
                rgba(22,226,138,0.35);

            background:
                rgba(22,226,138,0.08);

            transform:
                translateX(-3px);

        }


        /* ============================================================
           MÓDULOS
        ============================================================ */

        .modulos-section {

            width:
                min(1300px, calc(100% - 40px));

            margin:
                45px auto 70px;

        }


        .modulos-header {

            display: flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap: 20px;

            margin-bottom: 25px;

            flex-wrap: wrap;

        }


        .modulos-header h2 {

            color:
                var(--branco);

            font-size: 1.9rem;

        }


        .modulos-header h2 i {

            color:
                var(--verde);

            margin-right: 8px;

        }


        .modulos-header .sub {

            color:
                var(--texto-terciario);

            font-size: 0.85rem;

        }


        /* ============================================================
           CARROSSEL
        ============================================================ */

        .carousel-wrapper {

            position: relative;

            overflow: hidden;

            padding:
                15px 10px 30px;

        }


        .carousel-track {

            display: flex;

            gap: 22px;

            transition:
                transform 0.45s ease;

        }


        /* ============================================================
           CARD MÓDULO
        ============================================================ */

        .modulo-card {

            flex:
                0 0 280px;

            min-height:
                420px;

            display:
                flex;

            flex-direction:
                column;

            align-items:
                center;

            text-align:
                center;

            position:
                relative;

            padding:
                30px 25px 25px;

            background:
                linear-gradient(
                    145deg,
                    #0D2F50,
                    #092640
                );

            border:
                1px solid rgba(255,255,255,0.10);

            border-radius:
                25px;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.30);

            transition:
                0.3s ease;

        }


        .modulo-card:hover {

            transform:
                translateY(-8px);

            border-color:
                rgba(22,226,138,0.30);

            background:
                linear-gradient(
                    145deg,
                    #123B61,
                    #0B2945
                );

            box-shadow:
                0 20px 55px
                rgba(0,0,0,0.40);

        }


        /* ============================================================
           BADGE
        ============================================================ */

        .modulo-badge {

            position:
                absolute;

            top:
                15px;

            right:
                15px;

            padding:
                5px 11px;

            color:
                var(--verde);

            background:
                rgba(22,226,138,0.10);

            border:
                1px solid
                rgba(22,226,138,0.20);

            border-radius:
                999px;

            font-size:
                0.68rem;

            font-weight:
                700;

            text-transform:
                uppercase;

        }


        /* ============================================================
           ÍCONE
        ============================================================ */

        .modulo-icon {

            width:
                90px;

            height:
                90px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            margin:
                20px 0 18px;

            color:
                var(--verde);

            font-size:
                2.8rem;

            background:
                rgba(22,226,138,0.09);

            border:
                1px solid
                rgba(22,226,138,0.16);

            border-radius:
                50%;

            transition:
                0.3s ease;

        }


        .modulo-card:hover .modulo-icon {

            transform:
                scale(1.06);

            background:
                rgba(22,226,138,0.15);

        }


        /* ============================================================
           TEXTOS DO MÓDULO
        ============================================================ */

        .modulo-card h3 {

            color:
                var(--branco);

            font-size:
                1.5rem;

            margin-bottom:
                8px;

        }


        .modulo-desc {

            color:
                var(--verde);

            font-size:
                0.9rem;

            font-weight:
                700;

            margin-bottom:
                10px;

        }


        .modulo-lorem {

            color:
                var(--texto-secundario);

            font-size:
                0.82rem;

            line-height:
                1.55;

            min-height:
                55px;

            margin-bottom:
                15px;

        }


        /* ============================================================
           CONTADOR
        ============================================================ */

        .modulo-aulas-count {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            padding:
                7px 14px;

            color:
                var(--texto-secundario);

            background:
                rgba(255,255,255,0.06);

            border:
                1px solid
                rgba(255,255,255,0.08);

            border-radius:
                999px;

            font-size:
                0.78rem;

        }


        .modulo-aulas-count i {

            color:
                var(--verde);

        }


        /* ============================================================
           BOTÃO ENTRAR
        ============================================================ */

        .btn-entrar {

            width:
                100%;

            margin-top:
                auto;

            padding:
                14px 20px;

            border:
                none;

            border-radius:
                12px;

            background:
                linear-gradient(
                    135deg,
                    var(--verde),
                    var(--verde-escuro)
                );

            color:
                #062238;

            font-weight:
                800;

            font-size:
                0.95rem;

            cursor:
                pointer;

            transition:
                0.25s ease;

        }


        .btn-entrar:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(22,226,138,0.25);

        }


        /* ============================================================
           SETAS
        ============================================================ */

        .carousel-btn {

            position:
                absolute;

            top:
                50%;

            transform:
                translateY(-50%);

            width:
                48px;

            height:
                48px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            color:
                var(--branco);

            background:
                #071D32;

            border:
                1px solid
                rgba(255,255,255,0.15);

            border-radius:
                50%;

            cursor:
                pointer;

            z-index:
                10;

            transition:
                0.25s;

        }


        .carousel-btn:hover {

            color:
                var(--verde);

            border-color:
                rgba(22,226,138,0.35);

            background:
                #0D2F50;

            transform:
                translateY(-50%)
                scale(1.08);

        }


        .carousel-btn.prev {
            left: 5px;
        }


        .carousel-btn.next {
            right: 5px;
        }


        /* ============================================================
           TIMELINE
        ============================================================ */

        .timeline-container {

            display:
                none;

            width:
                min(1200px, calc(100% - 40px));

            margin:
                45px auto 80px;

        }


        .timeline-container.active {

            display:
                block;

            animation:
                aparecer 0.4s ease;

        }


        @keyframes aparecer {

            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }


        .timeline-header {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap:
                20px;

            margin-bottom:
                28px;

            flex-wrap:
                wrap;

        }


        .timeline-header h2 {

            color:
                var(--branco);

            font-size:
                2rem;

        }


        .timeline-header h2 i {

            color:
                var(--verde);

            margin-right:
                8px;

        }


        /* ============================================================
           AULAS
        ============================================================ */

        .aulas-lista {

            display:
                flex;

            flex-direction:
                column;

            gap:
                14px;

        }


        .aula-item {

            position:
                relative;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;

            padding:
                20px 22px;

            background:
                linear-gradient(
                    135deg,
                    #0D2F50,
                    #092640
                );

            border:
                1px solid
                rgba(255,255,255,0.10);

            border-radius:
                17px;

            transition:
                0.25s ease;

        }


        .aula-item:hover {

            transform:
                translateX(5px);

            border-color:
                rgba(22,226,138,0.25);

            background:
                linear-gradient(
                    135deg,
                    #123B61,
                    #0B2945
                );

        }


        .aula-info {

            flex:
                1;

            min-width:
                0;

        }


        .aula-titulo {

            display:
                block;

            color:
                var(--branco);

            font-size:
                1rem;

            font-weight:
                700;

            margin-bottom:
                8px;

        }


        .aula-meta {

            display:
                flex;

            gap:
                18px;

            flex-wrap:
                wrap;

            color:
                var(--texto-secundario);

            font-size:
                0.8rem;

        }


        .aula-meta i {

            color:
                var(--verde);

            margin-right:
                5px;

        }


        /* ============================================================
           DURAÇÃO
        ============================================================ */

        .aula-duracao {

            display:
                flex;

            align-items:
                center;

            gap:
                7px;

            padding:
                7px 13px;

            color:
                var(--verde);

            background:
                rgba(22,226,138,0.09);

            border:
                1px solid
                rgba(22,226,138,0.18);

            border-radius:
                999px;

            font-size:
                0.8rem;

            white-space:
                nowrap;

        }


        /* ============================================================
           BOTÃO ASSISTIR
        ============================================================ */

        .btn-assistir {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                7px;

            padding:
                10px 17px;

            color:
                #062238;

            background:
                var(--verde);

            border:
                none;

            border-radius:
                10px;

            font-weight:
                750;

            font-size:
                0.82rem;

            cursor:
                pointer;

            transition:
                0.25s ease;

            white-space:
                nowrap;

        }


        .btn-assistir:hover {

            background:
                #27F19A;

            transform:
                translateY(-2px);

            box-shadow:
                0 8px 20px
                rgba(22,226,138,0.22);

        }


        /* ============================================================
           TOOLTIP
        ============================================================ */

        .aula-item .tooltip {

            position:
                absolute;

            left:
                25px;

            bottom:
                calc(100% + 12px);

            width:
                320px;

            padding:
                18px;

            background:
                #06192B;

            border:
                1px solid
                rgba(22,226,138,0.25);

            border-radius:
                15px;

            box-shadow:
                0 20px 50px
                rgba(0,0,0,0.55);

            opacity:
                0;

            visibility:
                hidden;

            transform:
                translateY(8px);

            transition:
                0.22s ease;

            z-index:
                50;

            pointer-events:
                none;

        }


        .aula-item:hover .tooltip {

            opacity:
                1;

            visibility:
                visible;

            transform:
                translateY(0);

        }


        .tooltip::after {

            content:
                "";

            position:
                absolute;

            left:
                25px;

            top:
                100%;

            border:
                7px solid transparent;

            border-top-color:
                #06192B;

        }


        .tt-title {

            color:
                var(--branco);

            font-weight:
                750;

            margin-bottom:
                10px;

        }


        .tt-meta {

            display:
                flex;

            flex-direction:
                column;

            gap:
                6px;

            color:
                var(--texto-secundario);

            font-size:
                0.78rem;

        }


        .tt-meta i {

            width:
                17px;

            color:
                var(--verde);

        }


        .tt-resumo {

            margin-top:
                12px;

            padding-top:
                10px;

            border-top:
                1px solid
                rgba(255,255,255,0.10);

            color:
                var(--texto-secundario);

            font-size:
                0.78rem;

            line-height:
                1.5;

        }


        /* ============================================================
           MODAL DO VÍDEO
        ============================================================ */

        .video-modal {

            position:
                fixed;

            inset:
                0;

            display:
                none;

            align-items:
                center;

            justify-content:
                center;

            padding:
                20px;

            background:
                rgba(0,0,0,0.82);

            backdrop-filter:
                blur(8px);

            z-index:
                9999;

        }


        .video-modal.active {

            display:
                flex;

        }


        .video-box {

            width:
                min(950px, 100%);

            background:
                #071D32;

            border:
                1px solid
                rgba(255,255,255,0.12);

            border-radius:
                20px;

            overflow:
                hidden;

            box-shadow:
                0 30px 100px
                rgba(0,0,0,0.60);

        }


        .video-header {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            padding:
                16px 20px;

            background:
                #0A2540;

            border-bottom:
                1px solid
                rgba(255,255,255,0.10);

        }


        .video-header h3 {

            color:
                var(--branco);

            font-size:
                1rem;

        }


        .fechar-video {

            width:
                35px;

            height:
                35px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            color:
                var(--texto);

            background:
                rgba(255,255,255,0.06);

            border:
                1px solid
                rgba(255,255,255,0.10);

            border-radius:
                50%;

            cursor:
                pointer;

            transition:
                0.2s;

        }


        .fechar-video:hover {

            color:
                #ff6b6b;

            background:
                rgba(255,100,100,0.10);

        }


        .video-area {

            aspect-ratio:
                16 / 9;

            background:
                #020B13;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

        }


        .video-area video {

            width:
                100%;

            height:
                100%;

            display:
                block;

            object-fit:
                contain;

        }


        .video-placeholder {

            text-align:
                center;

            padding:
                30px;

            color:
                var(--texto-secundario);

        }


        .video-placeholder i {

            color:
                var(--verde);

            font-size:
                3rem;

            margin-bottom:
                15px;

        }


        .video-placeholder h3 {

            color:
                var(--branco);

            margin-bottom:
                8px;

        }


        .video-placeholder p {

            color:
                var(--texto-terciario);

            font-size:
                0.9rem;

        }


        /* ============================================================
           RESPONSIVO
        ============================================================ */

        @media(max-width: 850px) {

            .hero-container {

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .hero-right {

                width:
                    100%;

            }


            .hero-stats {

                flex-wrap:
                    wrap;

            }

        }


        @media(max-width: 650px) {

            .modulos-section,
            .timeline-container {

                width:
                    calc(100% - 24px);

            }


            .modulo-card {

                flex-basis:
                    240px;

            }


            .aula-item {

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .aula-duracao,
            .btn-assistir {

                align-self:
                    flex-start;

            }


            .aula-item .tooltip {

                left:
                    10px;

                width:
                    min(300px, calc(100vw - 40px));

            }

        }


        @media(max-width: 450px) {

            .hero-videoaulas {

                padding-top:
                    105px;

            }


            .hero-left h1 {

                font-size:
                    2rem;

            }


            .hero-stats {

                width:
                    100%;

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .modulo-card {

                flex-basis:
                    220px;

            }

        }

    </style>

    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>


<body>


<?php include_once 'navbar.php'; ?>


<main>


    <!-- ============================================================
         HERO
    ============================================================ -->

    <section class="hero-videoaulas">

        <div class="hero-container">

            <div class="hero-left">

                <h1>
                    📚
                    <span class="destaque">
                        Videoaulas
                    </span>
                </h1>

                <p>
                    Aprenda no seu ritmo com conteúdos exclusivos,
                    organizados para facilitar sua jornada de aprendizado.
                </p>

            </div>


            <div class="hero-right">

                <div class="hero-stats">

                    <span>
                        <i class="fas fa-film"></i>
                        19 aulas
                    </span>

                    <span>
                        <i class="fas fa-layer-group"></i>
                        5 módulos
                    </span>

                    <span>
                        <i class="fas fa-clock"></i>
                        5h20 total
                    </span>

                </div>


                <a
                    href="aprendizado.php"
                    class="btn-voltar">

                    <i class="fas fa-arrow-left"></i>

                    Voltar

                </a>

            </div>

        </div>

    </section>


    <!-- ============================================================
         MÓDULOS
    ============================================================ -->

    <section
        class="modulos-section"
        id="modulosSection">


        <div class="modulos-header">

            <h2>

                <i class="fas fa-th-large"></i>

                Escolha seu módulo

            </h2>


            <span class="sub">

                <i class="fas fa-arrows-left-right"></i>

                Navegue pelos módulos

            </span>

        </div>


        <div class="carousel-wrapper">


            <div
                class="carousel-track"
                id="modulosTrack">


                <!-- ==================================================
                     MÓDULO 1
                ================================================== -->

                <div
                    class="modulo-card"
                    data-modulo="1">

                    <span class="modulo-badge">
                        Iniciante
                    </span>


                    <div class="modulo-icon">

                        <i class="fas fa-seedling"></i>

                    </div>


                    <h3>
                        Módulo 1
                    </h3>


                    <p class="modulo-desc">
                        Fundamentos do Investimento
                    </p>


                    <p class="modulo-lorem">
                        Lorem ipsum dolor sit amet,
                        consectetur adipiscing elit.
                        Integer vitae turpis.
                    </p>


                    <div class="modulo-aulas-count">

                        <i class="fas fa-play-circle"></i>

                        4 aulas

                    </div>


                    <button
                        type="button"
                        class="btn-entrar"
                        data-modulo="1">

                        Entrar

                        <i class="fas fa-arrow-right"></i>

                    </button>

                </div>


                <!-- ==================================================
                     MÓDULO 2
                ================================================== -->

                <div
                    class="modulo-card"
                    data-modulo="2">

                    <span class="modulo-badge">
                        Intermediário
                    </span>


                    <div class="modulo-icon">

                        <i class="fas fa-chart-line"></i>

                    </div>


                    <h3>
                        Módulo 2
                    </h3>


                    <p class="modulo-desc">
                        Análise Técnica
                    </p>


                    <p class="modulo-lorem">
                        Lorem ipsum dolor sit amet,
                        consectetur adipiscing elit.
                        Sed do eiusmod tempor.
                    </p>


                    <div class="modulo-aulas-count">

                        <i class="fas fa-play-circle"></i>

                        4 aulas

                    </div>


                    <button
                        type="button"
                        class="btn-entrar"
                        data-modulo="2">

                        Entrar

                        <i class="fas fa-arrow-right"></i>

                    </button>

                </div>


                <!-- ==================================================
                     MÓDULO 3
                ================================================== -->

                <div
                    class="modulo-card"
                    data-modulo="3">

                    <span class="modulo-badge">
                        Avançado
                    </span>


                    <div class="modulo-icon">

                        <i class="fas fa-balance-scale"></i>

                    </div>


                    <h3>
                        Módulo 3
                    </h3>


                    <p class="modulo-desc">
                        Fundamentos de Valuation
                    </p>


                    <p class="modulo-lorem">
                        Lorem ipsum dolor sit amet,
                        consectetur adipiscing elit.
                        Duis aute irure dolor.
                    </p>


                    <div class="modulo-aulas-count">

                        <i class="fas fa-play-circle"></i>

                        4 aulas

                    </div>


                    <button
                        type="button"
                        class="btn-entrar"
                        data-modulo="3">

                        Entrar

                        <i class="fas fa-arrow-right"></i>

                    </button>

                </div>


                <!-- ==================================================
                     MÓDULO 4
                ================================================== -->

                <div
                    class="modulo-card"
                    data-modulo="4">

                    <span class="modulo-badge">
                        Expert
                    </span>


                    <div class="modulo-icon">

                        <i class="fas fa-rocket"></i>

                    </div>


                    <h3>
                        Módulo 4
                    </h3>


                    <p class="modulo-desc">
                        Estratégias Avançadas
                    </p>


                    <p class="modulo-lorem">
                        Lorem ipsum dolor sit amet,
                        consectetur adipiscing elit.
                        Excepteur sint occaecat.
                    </p>


                    <div class="modulo-aulas-count">

                        <i class="fas fa-play-circle"></i>

                        4 aulas

                    </div>


                    <button
                        type="button"
                        class="btn-entrar"
                        data-modulo="4">

                        Entrar

                        <i class="fas fa-arrow-right"></i>

                    </button>

                </div>


                <!-- ==================================================
                     MÓDULO 5
                ================================================== -->

                <div
                    class="modulo-card"
                    data-modulo="5">

                    <span class="modulo-badge">
                        Bônus
                    </span>


                    <div class="modulo-icon">

                        <i class="fas fa-gem"></i>

                    </div>


                    <h3>
                        Módulo 5
                    </h3>


                    <p class="modulo-desc">
                        Cases e Simulações
                    </p>


                    <p class="modulo-lorem">
                        Lorem ipsum dolor sit amet,
                        consectetur adipiscing elit.
                        Vivamus lacinia odio.
                    </p>


                    <div class="modulo-aulas-count">

                        <i class="fas fa-play-circle"></i>

                        3 aulas

                    </div>


                    <button
                        type="button"
                        class="btn-entrar"
                        data-modulo="5">

                        Entrar

                        <i class="fas fa-arrow-right"></i>

                    </button>

                </div>


            </div>


            <button
                type="button"
                class="carousel-btn prev"
                id="prevBtn">

                <i class="fas fa-chevron-left"></i>

            </button>


            <button
                type="button"
                class="carousel-btn next"
                id="nextBtn">

                <i class="fas fa-chevron-right"></i>

            </button>


        </div>

    </section>


    <!-- ============================================================
         AULAS
    ============================================================ -->

    <section
        class="timeline-container"
        id="timelineContainer">


        <div class="timeline-header">


            <h2>

                <i class="fas fa-list-ul"></i>

                <span id="moduloTitulo">
                    Módulo 1
                </span>

            </h2>


            <button
                type="button"
                class="btn-voltar-modulos"
                id="btnVoltarModulos">

                <i class="fas fa-arrow-left"></i>

                Voltar

            </button>


        </div>


        <div
            class="aulas-lista"
            id="aulasLista">
        </div>


    </section>


</main>


<!-- ================================================================
     MODAL DO VÍDEO
================================================================ -->

<div
    class="video-modal"
    id="videoModal">


    <div class="video-box">


        <div class="video-header">


            <h3 id="videoTitulo">
                Videoaula
            </h3>


            <button
                type="button"
                class="fechar-video"
                id="fecharVideo">

                <i class="fas fa-times"></i>

            </button>


        </div>


        <div
            class="video-area"
            id="videoArea">

            <div class="video-placeholder">

                <i class="fas fa-circle-play"></i>

                <h3>
                    Vídeo da aula
                </h3>

                <p>
                    O vídeo desta aula ainda não foi cadastrado.
                </p>

            </div>

        </div>


    </div>


</div>


<script>


/* ================================================================
   DADOS DAS AULAS
================================================================ */

const aulasPorModulo = {


    1: [

        {
            titulo:
                "Introdução ao Mundo dos Investimentos",

            professor:
                "Amanda Xagas",

            data:
                "10/03/2025",

            duracao:
                "12:30",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Tipos de Ativos Financeiros",

            professor:
                "Amanda Xagas",

            data:
                "12/03/2025",

            duracao:
                "18:45",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Risco e Retorno",

            professor:
                "Amanda Xagas",

            data:
                "14/03/2025",

            duracao:
                "22:10",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Montando sua Carteira Inicial",

            professor:
                "Amanda Xagas",

            data:
                "17/03/2025",

            duracao:
                "15:20",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        }

    ],


    2: [

        {
            titulo:
                "Gráficos e Tendências",

            professor:
                "Amanda Xagas",

            data:
                "20/03/2025",

            duracao:
                "14:50",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Indicadores Técnicos",

            professor:
                "Amanda Xagas",

            data:
                "22/03/2025",

            duracao:
                "20:10",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Suporte e Resistência",

            professor:
                "Amanda Xagas",

            data:
                "25/03/2025",

            duracao:
                "17:30",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Padrões de Candlestick",

            professor:
                "Amanda Xagas",

            data:
                "28/03/2025",

            duracao:
                "25:00",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        }

    ],


    3: [

        {
            titulo:
                "O que é Valuation?",

            professor:
                "Amanda Xagas",

            data:
                "01/04/2025",

            duracao:
                "16:40",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Fluxo de Caixa Descontado",

            professor:
                "Amanda Xagas",

            data:
                "03/04/2025",

            duracao:
                "22:30",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Múltiplos de Mercado",

            professor:
                "Amanda Xagas",

            data:
                "06/04/2025",

            duracao:
                "19:15",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Análise de Empresas",

            professor:
                "Amanda Xagas",

            data:
                "09/04/2025",

            duracao:
                "21:00",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        }

    ],


    4: [

        {
            titulo:
                "Derivativos e Opções",

            professor:
                "Amanda Xagas",

            data:
                "12/04/2025",

            duracao:
                "18:20",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Estratégias de Hedge",

            professor:
                "Amanda Xagas",

            data:
                "14/04/2025",

            duracao:
                "23:10",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Análise Macro e Micro",

            professor:
                "Amanda Xagas",

            data:
                "17/04/2025",

            duracao:
                "20:45",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Planejamento de Longo Prazo",

            professor:
                "Amanda Xagas",

            data:
                "20/04/2025",

            duracao:
                "16:30",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        }

    ],


    5: [

        {
            titulo:
                "Estudo de Caso: Small Caps",

            professor:
                "Amanda Xagas",

            data:
                "22/04/2025",

            duracao:
                "14:20",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Simulação de Carteira",

            professor:
                "Amanda Xagas",

            data:
                "24/04/2025",

            duracao:
                "19:40",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },


        {
            titulo:
                "Análise de Risco Avançada",

            professor:
                "Amanda Xagas",

            data:
                "26/04/2025",

            duracao:
                "22:10",

            resumo:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        }

    ]

};


/* ================================================================
   CARROSSEL
================================================================ */

const track =
    document.getElementById("modulosTrack");

const prevBtn =
    document.getElementById("prevBtn");

const nextBtn =
    document.getElementById("nextBtn");

let currentIndex = 0;


function getCardWidth() {

    const card =
        track.querySelector(".modulo-card");

    if (!card)
        return 0;

    return card.offsetWidth + 22;

}


function updateCarousel() {

    const cards =
        track.querySelectorAll(".modulo-card");

    if (!cards.length)
        return;

    const cardWidth =
        getCardWidth();

    const wrapper =
        track.parentElement.offsetWidth;

    const totalWidth =
        cards.length * cardWidth - 22;

    const maxOffset =
        Math.max(
            0,
            totalWidth - wrapper
        );

    let offset =
        currentIndex * cardWidth;

    offset =
        Math.min(
            offset,
            maxOffset
        );

    track.style.transform =
        `translateX(-${offset}px)`;

}


prevBtn.addEventListener(
    "click",
    function() {

        if (currentIndex > 0) {

            currentIndex--;

            updateCarousel();

        }

    }
);


nextBtn.addEventListener(
    "click",
    function() {

        const cards =
            track.querySelectorAll(
                ".modulo-card"
            );

        if (
            currentIndex <
            cards.length - 1
        ) {

            currentIndex++;

            updateCarousel();

        }

    }
);


window.addEventListener(
    "resize",
    updateCarousel
);


/* ================================================================
   ELEMENTOS DOS MÓDULOS
================================================================ */

const modulosSection =
    document.getElementById(
        "modulosSection"
    );

const timelineContainer =
    document.getElementById(
        "timelineContainer"
    );

const aulasLista =
    document.getElementById(
        "aulasLista"
    );

const moduloTitulo =
    document.getElementById(
        "moduloTitulo"
    );


/* ================================================================
   ABRIR MÓDULO
================================================================ */

function abrirModulo(moduloId) {


    const card =
        document.querySelector(
            `.modulo-card[data-modulo="${moduloId}"]`
        );


    if (!card)
        return;


    const nome =
        card.querySelector("h3").textContent;


    moduloTitulo.textContent =
        nome;


    const aulas =
        aulasPorModulo[moduloId] || [];


    aulasLista.innerHTML =
        "";


    aulas.forEach(
        function(aula, index) {


            const item =
                document.createElement("div");


            item.className =
                "aula-item";


            item.innerHTML = `

                <div class="aula-info">

                    <span class="aula-titulo">

                        ${aula.titulo}

                    </span>


                    <div class="aula-meta">

                        <span>

                            <i class="fas fa-user"></i>

                            ${aula.professor}

                        </span>


                        <span>

                            <i class="fas fa-calendar"></i>

                            ${aula.data}

                        </span>

                    </div>

                </div>


                <div class="aula-duracao">

                    <i class="fas fa-clock"></i>

                    ${aula.duracao}

                </div>


                <button
                    type="button"
                    class="btn-assistir"
                    data-aula="${index}"
                    data-modulo="${moduloId}">

                    <i class="fas fa-play"></i>

                    Assistir

                </button>


                <div class="tooltip">

                    <div class="tt-title">

                        ${aula.titulo}

                    </div>


                    <div class="tt-meta">

                        <span>

                            <i class="fas fa-user"></i>

                            Professor:
                            <strong>
                                ${aula.professor}
                            </strong>

                        </span>


                        <span>

                            <i class="fas fa-calendar"></i>

                            ${aula.data}

                        </span>


                        <span>

                            <i class="fas fa-clock"></i>

                            ${aula.duracao}

                        </span>

                    </div>


                    <div class="tt-resumo">

                        ${aula.resumo}

                    </div>

                </div>

            `;


            aulasLista.appendChild(item);

        }
    );


    modulosSection.style.display =
        "none";


    timelineContainer.classList.add(
        "active"
    );


    window.scrollTo({

        top:
            timelineContainer.offsetTop - 30,

        behavior:
            "smooth"

    });


}


/* ================================================================
   BOTÕES ENTRAR
================================================================ */

document
    .querySelectorAll(".btn-entrar")
    .forEach(
        function(button) {

            button.addEventListener(
                "click",
                function() {

                    const modulo =
                        Number(
                            this.dataset.modulo
                        );

                    abrirModulo(modulo);

                }
            );

        }
    );


/* ================================================================
   BOTÃO VOLTAR
================================================================ */

document
    .getElementById("btnVoltarModulos")
    .addEventListener(
        "click",
        function() {

            window.location.href = "aprendizado.php";

        }
    );


/* ================================================================
   MODAL DO VÍDEO
================================================================ */

const videoModal =
    document.getElementById(
        "videoModal"
    );

const videoTitulo =
    document.getElementById(
        "videoTitulo"
    );

const videoArea =
    document.getElementById(
        "videoArea"
    );

const fecharVideo =
    document.getElementById(
        "fecharVideo"
    );


/*
    COLOQUE AQUI OS CAMINHOS DOS SEUS VÍDEOS.

    Exemplo:

    1: [
        "videos/modulo1/aula1.mp4",
        "videos/modulo1/aula2.mp4",
        "videos/modulo1/aula3.mp4",
        "videos/modulo1/aula4.mp4"
    ]

    Se deixar vazio, aparecerá a mensagem
    informando que o vídeo ainda não foi cadastrado.
*/

const videos = {

    1: [
        "",
        "",
        "",
        ""
    ],

    2: [
        "",
        "",
        "",
        ""
    ],

    3: [
        "",
        "",
        "",
        ""
    ],

    4: [
        "",
        "",
        "",
        ""
    ],

    5: [
        "",
        "",
        ""
    ]

};


/* ================================================================
   ABRIR VÍDEO
================================================================ */

document.addEventListener(
    "click",
    function(event) {


        const botao =
            event.target.closest(
                ".btn-assistir"
            );


        if (!botao)
            return;


        const modulo =
            Number(
                botao.dataset.modulo
            );


        const aula =
            Number(
                botao.dataset.aula
            );


        const dados =
            aulasPorModulo[modulo][aula];


        const caminho =
            videos[modulo]?.[aula] || "";


        videoTitulo.textContent =
            dados.titulo;


        if (caminho !== "") {

            videoArea.innerHTML = `

                <video
                    controls
                    autoplay>

                    <source
                        src="${caminho}"
                        type="video/mp4">

                    Seu navegador não suporta
                    reprodução de vídeo.

                </video>

            `;

        } else {

            videoArea.innerHTML = `

                <div class="video-placeholder">

                    <i class="fas fa-circle-play"></i>

                    <h3>
                        ${dados.titulo}
                    </h3>

                    <p>
                        O vídeo desta aula ainda
                        não foi cadastrado.
                    </p>

                </div>

            `;

        }


        videoModal.classList.add(
            "active"
        );


    }
);


/* ================================================================
   FECHAR VÍDEO
================================================================ */

function fecharModalVideo() {

    videoModal.classList.remove(
        "active"
    );


    const video =
        videoArea.querySelector(
            "video"
        );


    if (video) {

        video.pause();

        video.currentTime = 0;

    }

}


fecharVideo.addEventListener(
    "click",
    fecharModalVideo
);


videoModal.addEventListener(
    "click",
    function(event) {

        if (
            event.target ===
            videoModal
        ) {

            fecharModalVideo();

        }

    }
);


document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Escape"
        ) {

            fecharModalVideo();

        }

    }
);


/* ================================================================
   INICIALIZAÇÃO
================================================================ */

window.addEventListener(
    "load",
    function() {

        updateCarousel();

    }
);

</script>



    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>

</html>
