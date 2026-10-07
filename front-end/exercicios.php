<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once("../back-end/conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Configuração simulada da ofensiva do usuário
$ofensiva_dias = isset($_SESSION['ofensiva_dias']) ? $_SESSION['ofensiva_dias'] : 3;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercícios & Treinos | Plataforma de Aprendizado</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">
    <link rel="stylesheet" href="../css/exercicios-arena.css?v=1">
    <link rel="icon" type="image/png" href="../img/favicon.png">

    <style>
        /* ============================================================
           VARIÁVEIS DE CORES E GLASSMORPHISM (Modo Claro Padrão)
        ============================================================ */
        :root {
            --bg-body-gradient: linear-gradient(135deg, #06192B 0%, #0A2540 50%, #071D32 100%);
            --hero-bg: linear-gradient(135deg, rgba(10, 37, 64, 0.8), rgba(7, 30, 52, 0.9));
            
            --azul-principal: #0A2540;
            --azul-escuro: #06192B;
            --azul-card: rgba(13, 47, 80, 0.65);
            --azul-card-hover: rgba(18, 59, 97, 0.75);

            --verde: #16E28A;
            --verde-escuro: #0DB873;
            --laranja-fogo: #FF7A00;
            --fogo-glow: #FF4500;

            --branco: #FFFFFF;
            --texto: #F4F7FA;
            --texto-secundario: #C6D0DA;
            --texto-terciario: #91A2B2;

            /* Liquid Glass Styling */
            --glass-bg: rgba(255, 255, 255, 0.07);
            --glass-bg-hover: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.15);
            --glass-border-glow: rgba(22, 226, 138, 0.4);
            --glass-shadow: 0 16px 40px rgba(0, 0, 0, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.15);
            --glass-blur: blur(25px);

            /* Popups & Modais */
            --modal-bg: rgba(10, 37, 64, 0.85);
            --modal-overlay: rgba(3, 13, 23, 0.85);
            --option-bg: rgba(255, 255, 255, 0.05);
            --option-border: rgba(255, 255, 255, 0.1);
        }

        /* ============================================================
           VARIÁVEIS - MODO ESCURO
        ============================================================ */
        body.dark-mode {
            --bg-body-gradient: linear-gradient(135deg, #030a12 0%, #05111d 50%, #02080f 100%);
            --hero-bg: linear-gradient(135deg, rgba(4, 15, 26, 0.95), rgba(2, 10, 18, 0.95));

            --azul-principal: #051322;
            --azul-escuro: #020912;

            --branco: #FFFFFF;
            --texto: #E1E8ED;
            --texto-secundario: #A0B0C0;
            --texto-terciario: #6C7D8E;

            /* Glassmorphism Escuro Ajustado */
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-bg-hover: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), inset 0 1px 1px rgba(255, 255, 255, 0.05);

            /* Popups & Modais Escuros */
            --modal-bg: rgba(5, 18, 31, 0.95);
            --modal-overlay: rgba(1, 5, 10, 0.92);
            --option-bg: rgba(255, 255, 255, 0.03);
            --option-border: rgba(255, 255, 255, 0.08);
        }

        /* ============================================================
           RESET & GLOBALS
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-body-gradient);
            color: var(--texto);
            min-height: 100vh;
            overflow-x: hidden;
            transition: background 0.4s ease, color 0.4s ease;
        }

        button, input, select {
            font-family: inherit;
        }

        /* Botão Toggle Modo Escuro */
        .btn-theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 999px;
            color: var(--texto);
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .btn-theme-toggle:hover {
            border-color: var(--verde);
            color: var(--verde);
            transform: translateY(-2px);
        }

        /* ============================================================
           HERO SECTION
        ============================================================ */
        .hero-exercicios {
            position: relative;
            padding: 125px 25px 55px;
            background: var(--hero-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-bottom: 1px solid var(--glass-border);
            overflow: hidden;
            transition: background 0.4s ease;
        }

        .hero-exercicios::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            right: -200px;
            top: -250px;
            border-radius: 50%;
            background: rgba(22, 226, 138, 0.08);
            filter: blur(100px);
            pointer-events: none;
        }

        .hero-exercicios::after {
            content: "";
            position: absolute;
            width: 450px;
            height: 450px;
            left: -200px;
            bottom: -200px;
            border-radius: 50%;
            background: rgba(255, 122, 0, 0.06);
            filter: blur(100px);
            pointer-events: none;
        }

        .hero-container {
            width: min(1300px, 100%);
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(22, 226, 138, 0.12);
            color: var(--verde);
            border: 1px solid rgba(22, 226, 138, 0.25);
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
        }

        .hero-left h1 {
            font-size: clamp(2.3rem, 5vw, 3.8rem);
            font-weight: 800;
            line-height: 1.15;
            color: var(--branco);
        }

        .hero-left h1 .destaque {
            color: var(--verde);
            background: linear-gradient(135deg, #16E28A, #0db873);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-left p {
            margin-top: 12px;
            max-width: 550px;
            color: var(--texto-secundario);
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .hero-right {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Streak / Ofensiva Header Badge */
        .ofensiva-counter {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 22px;
            background: rgba(255, 122, 0, 0.12);
            border: 1px solid rgba(255, 122, 0, 0.3);
            border-radius: 999px;
            color: var(--laranja-fogo);
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 0 20px rgba(255, 122, 0, 0.15);
            backdrop-filter: blur(12px);
            transition: all 0.3s ease;
        }

        .ofensiva-counter i {
            font-size: 1.3rem;
            animation: pulseFogo 1.5s infinite alternate ease-in-out;
        }

        @keyframes pulseFogo {
            0% { transform: scale(1); filter: drop-shadow(0 0 2px rgba(255, 122, 0, 0.5)); }
            100% { transform: scale(1.2); filter: drop-shadow(0 0 10px rgba(255, 69, 0, 0.9)); }
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            color: var(--branco);
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: 0.3s ease;
        }

        .btn-voltar:hover {
            color: var(--verde);
            border-color: rgba(22, 226, 138, 0.4);
            background: rgba(22, 226, 138, 0.1);
            transform: translateX(-3px);
        }

        /* ============================================================
           MÓDULOS DE EXERCÍCIOS
        ============================================================ */
        .modulos-section {
            width: min(1300px, calc(100% - 40px));
            margin: 45px auto 70px;
        }

        .modulos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .modulos-header h2 {
            color: var(--branco);
            font-size: 1.9rem;
            font-weight: 800;
        }

        .modulos-header h2 i {
            color: var(--verde);
            margin-right: 10px;
        }

        .modulos-header .sub {
            color: var(--texto-terciario);
            font-size: 0.9rem;
        }

        /* Carousel Layout */
        .carousel-wrapper {
            position: relative;
            overflow: hidden;
            padding: 15px 5px 30px;
        }

        .carousel-track {
            display: flex;
            gap: 24px;
            transition: transform 0.45s cubic-bezier(0.25, 1, 0.5, 1);
        }

        /* Card Liquid Glass */
        .modulo-card {
            flex: 0 0 290px;
            min-height: 430px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            padding: 32px 24px 26px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            box-shadow: var(--glass-shadow);
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .modulo-card:hover {
            transform: translateY(-10px);
            border-color: rgba(22, 226, 138, 0.35);
            background: var(--glass-bg-hover);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5), 0 0 20px rgba(22, 226, 138, 0.15);
        }

        .modulo-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            padding: 5px 12px;
            color: var(--verde);
            background: rgba(22, 226, 138, 0.12);
            border: 1px solid rgba(22, 226, 138, 0.25);
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modulo-icon {
            width: 88px;
            height: 88px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 18px 0 18px;
            color: var(--verde);
            font-size: 2.6rem;
            background: rgba(22, 226, 138, 0.08);
            border: 1px solid rgba(22, 226, 138, 0.2);
            border-radius: 50%;
            transition: 0.3s ease;
            box-shadow: inset 0 0 15px rgba(22, 226, 138, 0.1);
        }

        .modulo-card:hover .modulo-icon {
            transform: scale(1.08) rotate(5deg);
            background: rgba(22, 226, 138, 0.18);
            border-color: var(--verde);
        }

        .modulo-card h3 {
            color: var(--branco);
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .modulo-desc {
            color: var(--verde);
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .modulo-lorem {
            color: var(--texto-secundario);
            font-size: 0.83rem;
            line-height: 1.55;
            min-height: 52px;
            margin-bottom: 18px;
        }

        .modulo-aulas-count {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 15px;
            color: var(--texto-secundario);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            font-size: 0.78rem;
            margin-bottom: 20px;
        }

        .btn-entrar {
            width: 100%;
            margin-top: auto;
            padding: 14px 20px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--verde), var(--verde-escuro));
            color: #062238;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.3s ease;
            box-shadow: 0 8px 20px rgba(22, 226, 138, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-entrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(22, 226, 138, 0.4);
            filter: brightness(1.05);
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--branco);
            background: var(--modal-bg);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            cursor: pointer;
            z-index: 10;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }

        .carousel-btn:hover {
            color: var(--verde);
            border-color: rgba(22, 226, 138, 0.4);
            background: rgba(13, 47, 80, 0.95);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-btn.prev { left: 5px; }
        .carousel-btn.next { right: 5px; }

        /* ============================================================
           LISTA DE EXERCÍCIOS DO MÓDULO (TIMELINE)
        ============================================================ */
        .timeline-container {
            display: none;
            width: min(1200px, calc(100% - 40px));
            margin: 45px auto 80px;
        }

        .timeline-container.active {
            display: block;
            animation: aparecer 0.4s ease;
        }

        @keyframes aparecer {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .timeline-header h2 {
            color: var(--branco);
            font-size: 2rem;
            font-weight: 800;
        }

        .timeline-header h2 i {
            color: var(--verde);
            margin-right: 10px;
        }

        .aulas-lista {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Exercício Item Card */
        .aula-item {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 22px 28px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            transition: all 0.3s ease;
        }

        .aula-item:hover {
            transform: translateX(6px);
            border-color: rgba(22, 226, 138, 0.35);
            background: var(--glass-bg-hover);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }

        .aula-info {
            flex: 1;
            min-width: 0;
        }

        .aula-titulo {
            display: block;
            color: var(--branco);
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .aula-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            color: var(--texto-secundario);
            font-size: 0.85rem;
        }

        .aula-meta i {
            color: var(--verde);
            margin-right: 6px;
        }

        .aula-meta .vinculo-aula {
            color: var(--verde);
            font-weight: 600;
            background: rgba(22, 226, 138, 0.1);
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid rgba(22, 226, 138, 0.2);
        }

        .aula-duracao {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            color: var(--verde);
            background: rgba(22, 226, 138, 0.1);
            border: 1px solid rgba(22, 226, 138, 0.25);
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .btn-praticar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            color: #062238;
            background: linear-gradient(135deg, var(--verde), #0db873);
            border: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.88rem;
            cursor: pointer;
            transition: 0.3s ease;
            white-space: nowrap;
            box-shadow: 0 6px 18px rgba(22, 226, 138, 0.25);
        }

        .btn-praticar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(22, 226, 138, 0.4);
            filter: brightness(1.1);
        }

        /* Tooltip Liquid Glass em Hover */
        .aula-item .tooltip {
            position: absolute;
            left: 28px;
            bottom: calc(100% + 14px);
            width: 360px;
            padding: 20px;
            background: var(--modal-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(22, 226, 138, 0.35);
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 50;
            pointer-events: none;
        }

        .aula-item:hover .tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .tooltip::after {
            content: "";
            position: absolute;
            left: 30px;
            top: 100%;
            border: 8px solid transparent;
            border-top-color: var(--modal-bg);
        }

        .tt-title {
            color: var(--branco);
            font-weight: 800;
            font-size: 0.98rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tt-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: var(--texto-secundario);
            font-size: 0.82rem;
        }

        .tt-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tt-meta i {
            color: var(--verde);
            width: 16px;
        }

        .tt-resumo {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--texto-secundario);
            font-size: 0.8rem;
            line-height: 1.5;
        }

        /* ============================================================
           MODAL INTERATIVO ESTILO DUOLINGO (10 EXERCÍCIOS)
        ============================================================ */
        .quiz-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--modal-overlay);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            z-index: 9999;
        }

        .quiz-modal.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .quiz-card {
            width: min(720px, 100%);
            background: var(--modal-bg);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.15);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Top Progress Bar */
        .quiz-header {
            padding: 22px 30px 15px;
            display: flex;
            align-items: center;
            gap: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .fechar-quiz {
            background: none;
            border: none;
            color: var(--texto-terciario);
            font-size: 1.3rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .fechar-quiz:hover {
            color: #FF4757;
            transform: scale(1.1);
        }

        .progress-bar-bg {
            flex: 1;
            height: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            width: 10%;
            background: linear-gradient(90deg, var(--verde), #27F19A);
            border-radius: 999px;
            transition: width 0.4s ease;
            box-shadow: 0 0 12px rgba(22, 226, 138, 0.6);
        }

        .quiz-step-count {
            color: var(--texto-secundario);
            font-size: 0.88rem;
            font-weight: 700;
        }

        /* Quiz Body */
        .quiz-body {
            padding: 35px 30px 30px;
        }

        .quiz-pergunta-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--verde);
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quiz-pergunta-meta i {
            font-size: 1rem;
        }

        .quiz-pergunta {
            color: var(--branco);
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.4;
            margin-bottom: 25px;
        }

        /* Alternativas */
        .options-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 25px;
        }

        .option-btn {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: var(--option-bg);
            border: 2px solid var(--option-border);
            border-radius: 16px;
            color: var(--texto);
            font-size: 0.98rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }

        .option-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .option-btn.selected {
            border-color: var(--verde);
            background: rgba(22, 226, 138, 0.12);
            color: var(--branco);
            box-shadow: 0 0 15px rgba(22, 226, 138, 0.2);
        }

        .option-btn.correct {
            border-color: #16E28A;
            background: rgba(22, 226, 138, 0.2);
            color: #FFFFFF;
        }

        .option-btn.wrong {
            border-color: #FF4757;
            background: rgba(255, 71, 87, 0.2);
            color: #FFFFFF;
        }

        .option-letter {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: var(--texto-secundario);
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .option-btn.selected .option-letter {
            background: var(--verde);
            color: #062238;
        }

        /* Quiz Footer Controls */
        .quiz-footer {
            padding: 20px 30px;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-responder {
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--verde), var(--verde-escuro));
            color: #062238;
            border: none;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s ease;
            box-shadow: 0 8px 20px rgba(22, 226, 138, 0.3);
            margin-left: auto;
        }

        .btn-responder:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-responder:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(22, 226, 138, 0.45);
        }

        /* ============================================================
           POPUP / MODAL DE OFENSIVA (STREAK FIRE POPUP)
        ============================================================ */
        .streak-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 100000;
            padding: 20px;
        }

        .streak-modal-overlay.active {
            display: flex;
            animation: modalPopIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes modalPopIn {
            from { opacity: 0; transform: scale(0.8) translateY(30px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .streak-modal-card {
            max-width: 460px;
            width: 100%;
            background: var(--modal-bg);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 122, 0, 0.4);
            border-radius: 32px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 25px 70px rgba(255, 122, 0, 0.25), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .fire-anim-box {
            position: relative;
            width: 110px;
            height: 110px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 122, 0, 0.15);
            border-radius: 50%;
            border: 2px solid rgba(255, 122, 0, 0.4);
            box-shadow: 0 0 30px rgba(255, 122, 0, 0.4);
        }

        .fire-anim-box i {
            font-size: 4.2rem;
            color: var(--laranja-fogo);
            animation: flameGlow 1s infinite alternate ease-in-out;
        }

        @keyframes flameGlow {
            0% { transform: scale(0.95); filter: drop-shadow(0 0 8px #FF7A00); }
            100% { transform: scale(1.1); filter: drop-shadow(0 0 22px #FF4500); }
        }

        .streak-modal-card h2 {
            font-size: 2.1rem;
            color: var(--branco);
            font-weight: 800;
            margin-bottom: 8px;
        }

        .streak-modal-card h2 .highlight-fogo {
            color: var(--laranja-fogo);
            background: linear-gradient(135deg, #FF7A00, #FF4500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .streak-days-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(255, 122, 0, 0.2);
            color: var(--laranja-fogo);
            border: 1px solid rgba(255, 122, 0, 0.4);
            border-radius: 999px;
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .streak-modal-card p {
            color: var(--texto-secundario);
            font-size: 0.98rem;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .btn-streak-continuar {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #FF7A00, #FF4500);
            color: var(--branco);
            border: none;
            border-radius: 999px;
            font-weight: 800;
            font-size: 1.05rem;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 30px rgba(255, 122, 0, 0.4);
        }

        .btn-streak-continuar:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 122, 0, 0.6);
            filter: brightness(1.1);
        }

        /* ============================================================
           RESPONSIVIDADE
        ============================================================ */
        @media (max-width: 850px) {
            .hero-container {
                flex-direction: column;
                align-items: flex-start;
            }
            .hero-right {
                width: 100%;
                justify-content: space-between;
            }
        }

        @media (max-width: 650px) {
            .modulos-section, .timeline-container {
                width: calc(100% - 24px);
            }
            .modulo-card {
                flex-basis: 250px;
            }
            .aula-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .aula-duracao, .btn-praticar {
                align-self: flex-start;
            }
            .aula-item .tooltip {
                left: 10px;
                width: min(320px, calc(100vw - 40px));
            }
        }
    </style>
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>

<body>

<?php include_once 'navbar.php'; ?>

<main>

    <!-- ============================================================
         HERO SECTION
    ============================================================ -->
    <section class="hero-exercicios">
        <div class="hero-container">
            <div class="hero-left">
                <span class="badge-tag">
                    <i class="fas fa-brain"></i> Treinamento Prático
                </span>
                <h1>
                    🎯 Lista de <span class="destaque">Exercícios</span>
                </h1>
                <p>
                    Fixe o conhecimento das videoaulas com listas de 10 exercícios rápidos e dinâmicos no estilo Duolingo.
                </p>
            </div>

            <div class="hero-right">
                <!-- Botão Toggle Modo Escuro -->
                <button type="button" class="btn-theme-toggle" id="btnThemeToggle">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Modo Escuro</span>
                </button>

                <!-- Streak/Ofensiva Counter -->
                <div class="ofensiva-counter" id="headerOfensiva">
                    <i class="fas fa-fire"></i>
                    <span><strong id="diasOfensivaCount"><?php echo $ofensiva_dias; ?></strong> Dias de Ofensiva</span>
                </div>

                <a href="aprendizado.php" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================
         SEÇÃO DE MÓDULOS (CARROSSEL)
    ============================================================ -->
    <section class="modulos-section" id="modulosSection">
        <div class="modulos-header">
            <h2>
                <i class="fas fa-layer-group"></i>
                Escolha o Módulo de Exercícios
            </h2>
            <span class="sub">
                <i class="fas fa-arrows-left-right"></i>
                Navegue e escolha uma trilha
            </span>
        </div>

        <div class="carousel-wrapper">
            <div class="carousel-track" id="modulosTrack">

                <!-- MÓDULO 1 -->
                <div class="modulo-card" data-modulo="1">
                    <span class="modulo-badge">Iniciante</span>
                    <div class="modulo-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h3>Módulo 1</h3>
                    <p class="modulo-desc">Fundamentos do Investimento</p>
                    <p class="modulo-lorem">
                        Aprenda conceitos básicos e exercite o primeiro passo no mundo financeiro.
                    </p>
                    <div class="modulo-aulas-count">
                        <i class="fas fa-list-check"></i>
                        4 Listas Práticas
                    </div>
                    <button type="button" class="btn-entrar" data-modulo="1">
                        Ver Exercícios
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- MÓDULO 2 -->
                <div class="modulo-card" data-modulo="2">
                    <span class="modulo-badge">Intermediário</span>
                    <div class="modulo-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Módulo 2</h3>
                    <p class="modulo-desc">Análise Técnica</p>
                    <p class="modulo-lorem">
                        Teste seus conhecimentos na leitura de gráficos, padrões e tendências.
                    </p>
                    <div class="modulo-aulas-count">
                        <i class="fas fa-list-check"></i>
                        4 Listas Práticas
                    </div>
                    <button type="button" class="btn-entrar" data-modulo="2">
                        Ver Exercícios
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- MÓDULO 3 -->
                <div class="modulo-card" data-modulo="3">
                    <span class="modulo-badge">Avançado</span>
                    <div class="modulo-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3>Módulo 3</h3>
                    <p class="modulo-desc">Fundamentos de Valuation</p>
                    <p class="modulo-lorem">
                        Avalie empresas com balanços reais e simulações de múltiplos.
                    </p>
                    <div class="modulo-aulas-count">
                        <i class="fas fa-list-check"></i>
                        4 Listas Práticas
                    </div>
                    <button type="button" class="btn-entrar" data-modulo="3">
                        Ver Exercícios
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- MÓDULO 4 -->
                <div class="modulo-card" data-modulo="4">
                    <span class="modulo-badge">Expert</span>
                    <div class="modulo-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Módulo 4</h3>
                    <p class="modulo-desc">Estratégias Avançadas</p>
                    <p class="modulo-lorem">
                        Derivativos, opções e hedge colocados à prova de forma simples.
                    </p>
                    <div class="modulo-aulas-count">
                        <i class="fas fa-list-check"></i>
                        4 Listas Práticas
                    </div>
                    <button type="button" class="btn-entrar" data-modulo="4">
                        Ver Exercícios
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- MÓDULO 5 -->
                <div class="modulo-card" data-modulo="5">
                    <span class="modulo-badge">Bônus</span>
                    <div class="modulo-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h3>Módulo 5</h3>
                    <p class="modulo-desc">Cases e Simulações</p>
                    <p class="modulo-lorem">
                        Desafios práticos baseados em cenários reais do mercado global.
                    </p>
                    <div class="modulo-aulas-count">
                        <i class="fas fa-list-check"></i>
                        3 Listas Práticas
                    </div>
                    <button type="button" class="btn-entrar" data-modulo="5">
                        Ver Exercícios
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

            </div>

            <button type="button" class="carousel-btn prev" id="prevBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="carousel-btn next" id="nextBtn">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </section>

    <!-- ============================================================
         LISTA DE EXERCÍCIOS VINCULADOS ÀS VIDEOAULAS
    ============================================================ -->
    <section class="timeline-container" id="timelineContainer">
        <div class="timeline-header">
            <h2>
                <i class="fas fa-tasks"></i>
                <span id="moduloTitulo">Módulo 1 - Exercícios</span>
            </h2>

            <button type="button" class="btn-voltar" id="btnVoltarModulos">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </button>
        </div>

        <div class="aulas-lista" id="exerciciosLista">
            <!-- Injetado via JS -->
        </div>
    </section>

</main>

<!-- ============================================================
     MODAL / POPUP ESTILO DUOLINGO DE EXERCÍCIOS
============================================================ -->
<div class="quiz-modal" id="quizModal">
    <div class="quiz-card">
        <div class="quiz-header">
            <button class="fechar-quiz" id="fecharQuiz">
                <i class="fas fa-times"></i>
            </button>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" id="quizProgressFill"></div>
            </div>
            <span class="quiz-step-count" id="quizStepCount">1/10</span>
        </div>

        <div class="quiz-body">
            <div class="quiz-pergunta-meta">
                <i class="fas fa-bolt"></i>
                <span id="quizMetaAula">Atribuído à Videoaula: Introdução ao Mundo dos Investimentos</span>
            </div>

            <h3 class="quiz-pergunta" id="quizPerguntaText">
                Qual é a principal função de um ativo de Renda Fixa no seu portfólio inicial?
            </h3>

            <div class="options-grid" id="quizOptionsGrid">
                <!-- Opções injetadas via JavaScript -->
            </div>
        </div>

        <div class="quiz-footer">
            <span style="color: var(--texto-terciario); font-size: 0.85rem;">
                <i class="fas fa-shield-halved" style="color: var(--verde);"></i> Lista de 10 Questões Rápidas
            </span>
            <button class="btn-responder" id="btnResponderQuiz" disabled>
                Verificar
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     POPUP DE LIGAÇÃO DE OFENSIVA (STREAK POPUP)
============================================================ -->
<div class="streak-modal-overlay" id="streakModal">
    <div class="streak-modal-card">
        <div class="fire-anim-box">
            <i class="fas fa-fire"></i>
        </div>
        <h2><span class="highlight-fogo">Ofensiva</span> Ligada!</h2>
        <div class="streak-days-badge" id="modalStreakDays">
            🔥 4 Dias Seguidos!
        </div>
        <p>
            Parabéns! Você concluiu a lista de exercícios de hoje e manteve sua chama acesa. Volte amanhã para aumentar ainda mais sua ofensiva!
        </p>
        <button class="btn-streak-continuar" onclick="fecharStreakModal()">
            Incrível! Continuar
        </button>
    </div>
</div>

<script>
/* ================================================================
   TOGGLE DO MODO ESCURO COM PERSISTÊNCIA (LOCALSTORAGE)
================================================================ */
const btnThemeToggle = document.getElementById("btnThemeToggle");
const themeIcon = document.getElementById("themeIcon");
const themeText = document.getElementById("themeText");

function applyTheme(isDark) {
    if (isDark) {
        document.body.classList.add("dark-mode");
        themeIcon.className = "fas fa-sun";
        themeText.textContent = "Modo Claro";
    } else {
        document.body.classList.remove("dark-mode");
        themeIcon.className = "fas fa-moon";
        themeText.textContent = "Modo Escuro";
    }
}

// Verifica tema salvo ou preferência do sistema
const savedTheme = localStorage.getItem("theme");
if (savedTheme) {
    applyTheme(savedTheme === "dark");
}

btnThemeToggle.addEventListener("click", () => {
    const isDark = document.body.classList.toggle("dark-mode");
    localStorage.setItem("theme", isDark ? "dark" : "light");
    applyTheme(isDark);
});

/* ================================================================
   DADOS DOS EXERCÍCIOS VINCULADOS ÀS VIDEOAULAS & ESTIMATIVA DE TEMPO
================================================================ */
const exerciciosPorModulo = {
    1: [
        {
            titulo: "Exercício 01: Conceitos de Renda Fixa vs Variável",
            videoAula: "Introdução ao Mundo dos Investimentos",
            duracaoVideo: "12:30",
            tempoMedioLista: "4 min",
            resumo: "Lista de 10 questões rápidas sobre definições básicas de liquidez, rentabilidade e segurança de ativos iniciais."
        },
        {
            titulo: "Exercício 02: Classificação de Ativos e Títulos",
            videoAula: "Tipos de Ativos Financeiros",
            duracaoVideo: "18:45",
            tempoMedioLista: "5 min",
            resumo: "Identifique a diferença entre Tesouro Direto, CDBs, Ações e Fundos Imobiliários em perguntas interativas."
        },
        {
            titulo: "Exercício 03: Cálculo de Risco e Volatilidade",
            videoAula: "Risco e Retorno",
            duracaoVideo: "22:10",
            tempoMedioLista: "6 min",
            resumo: "Questões práticas avaliando a relação de proporcionalidade entre risco assumido e retorno esperado."
        },
        {
            titulo: "Exercício 04: Diversificação Prática",
            videoAula: "Montando sua Carteira Inicial",
            duracaoVideo: "15:20",
            tempoMedioLista: "5 min",
            resumo: "Aprenda a simular a distribuição percentual de recursos para mitigar riscos desnecessários."
        }
    ],
    2: [
        {
            titulo: "Exercício 05: Leitura de Candlestick",
            videoAula: "Gráficos e Tendências",
            duracaoVideo: "14:50",
            tempoMedioLista: "5 min",
            resumo: "Interprete padrões de abertura, fechamento, máximas e mínimas em candles operacionais."
        },
        {
            titulo: "Exercício 06: Osciladores e Médias Móveis",
            videoAula: "Indicadores Técnicos",
            duracaoVideo: "20:10",
            tempoMedioLista: "6 min",
            resumo: "Identifique sinais de compra e venda usando IFR, MACD e cruzamento de médias."
        },
        {
            titulo: "Exercício 07: Marcação de Suportes e Resistências",
            videoAula: "Suporte e Resistência",
            duracaoVideo: "17:30",
            tempoMedioLista: "4 min",
            resumo: "Exercícios visuais para identificar zonas psicologicamente relevantes de preço."
        },
        {
            titulo: "Exercício 08: Padrões de Reversão de Tendência",
            videoAula: "Padrões de Candlestick",
            duracaoVideo: "25:00",
            tempoMedioLista: "6 min",
            resumo: "Reconheça figuras como OCO, Engolfo e Martelo em 10 testes rápidos."
        }
    ],
    3: [
        {
            titulo: "Exercício 09: Múltiplos P/L e P/VP",
            videoAula: "O que é Valuation?",
            duracaoVideo: "16:40",
            tempoMedioLista: "5 min",
            resumo: "Compare empresas do mesmo setor analisando valuation simplificado."
        },
        {
            titulo: "Exercício 10: Taxa de Desconto e WACC",
            videoAula: "Fluxo de Caixa Descontado",
            duracaoVideo: "22:30",
            tempoMedioLista: "7 min",
            resumo: "Cálculos interativos de valor presente líquido aplicados a exercícios ágeis."
        },
        {
            titulo: "Exercício 11: Benchmark e EV/EBITDA",
            videoAula: "Múltiplos de Mercado",
            duracaoVideo: "19:15",
            tempoMedioLista: "5 min",
            resumo: "Avaliação da eficiência operacional e endividamento corporativo."
        },
        {
            titulo: "Exercício 12: Análise de Balanços e DRE",
            videoAula: "Análise de Empresas",
            duracaoVideo: "21:00",
            tempoMedioLista: "6 min",
            resumo: "Aprenda a encontrar margem bruta, líquida e Ebitda em demonstrativos simulados."
        }
    ],
    4: [
        {
            titulo: "Exercício 13: Calls e Puts na Prática",
            videoAula: "Derivativos e Opções",
            duracaoVideo: "18:20",
            tempoMedioLista: "6 min",
            resumo: "Entenda direitos e obrigações no mercado de opções com alternativas diretas."
        },
        {
            titulo: "Exercício 14: Estruturas de Proteção",
            videoAula: "Estratégias de Hedge",
            duracaoVideo: "23:10",
            tempoMedioLista: "6 min",
            resumo: "Monte estratégias de trava de alta e proteção de carteira contra queda."
        },
        {
            titulo: "Exercício 15: Impactos de Juros e Inflação",
            videoAula: "Análise Macro e Micro",
            duracaoVideo: "20:45",
            tempoMedioLista: "5 min",
            resumo: "Avalie a influência da Taxa Selic e IPCA sobre a rentabilidade dos investimentos."
        },
        {
            titulo: "Exercício 16: Rebalanceamento de Carteira",
            videoAula: "Planejamento de Longo Prazo",
            duracaoVideo: "16:30",
            tempoMedioLista: "4 min",
            resumo: "Simule a manutenção das proporções ideais ao longo dos anos."
        }
    ],
    5: [
        {
            titulo: "Exercício 17: Análise de Cases Real: Small Caps",
            videoAula: "Estudo de Caso: Small Caps",
            duracaoVideo: "14:20",
            tempoMedioLista: "5 min",
            resumo: "Exercícios de tomada de decisão com empresas de alto potencial de crescimento."
        },
        {
            titulo: "Exercício 18: Simulação de Portfólio Resiliente",
            videoAula: "Simulação de Carteira",
            duracaoVideo: "19:40",
            tempoMedioLista: "5 min",
            resumo: "Defenda seu patrimônio em cenários de estresse econômico simulado."
        },
        {
            titulo: "Exercício 19: Teste de Stress e Value at Risk",
            videoAula: "Análise de Risco Avançada",
            duracaoVideo: "22:10",
            tempoMedioLista: "6 min",
            resumo: "Calcule a perda máxima potencial estimada para um portfólio."
        }
    ]
};

/* BANCO DE QUESTÕES ESTILO DUOLINGO PARA O MODAL */
const questoesDuolingo = [
    { pergunta: 'Qual é a primeira etapa antes de escolher um investimento?', opcoes: ['Definir objetivo, prazo e tolerância a risco', 'Seguir a indicação mais comentada', 'Escolher sempre o ativo que mais subiu', 'Investir sem montar orçamento'], correta: 0 },
    { pergunta: 'Qual atitude ajuda a montar uma reserva de emergência?', opcoes: ['Usar ativos de alta liquidez e baixo risco', 'Concentrar tudo em criptomoedas', 'Escolher apenas ativos sem resgate', 'Investir somente em ações'], correta: 0 },
    { pergunta: 'O que é diversificação de carteira?', opcoes: ['Distribuir recursos entre ativos e classes diferentes', 'Comprar o mesmo ativo em várias corretoras', 'Colocar tudo no investimento mais rentável', 'Manter todo o dinheiro parado'], correta: 0 },
    { pergunta: 'Se a inflação sobe e o dinheiro fica parado, o que pode ocorrer?', opcoes: ['O poder de compra pode diminuir', 'O saldo nominal aumenta sozinho', 'O dinheiro passa a render automaticamente', 'O risco desaparece'], correta: 0 },
    { pergunta: 'O que representa a liquidez de um investimento?', opcoes: ['A facilidade e a rapidez para transformar o ativo em dinheiro', 'A garantia de lucro diário', 'O tamanho da empresa emissora', 'A quantidade de dividendos'], correta: 0 },
    { pergunta: 'Qual é uma diferença importante entre rentabilidade nominal e real?', opcoes: ['A real considera o efeito da inflação', 'A nominal sempre é menor', 'A real ignora custos e inflação', 'Não existe diferença'], correta: 0 },
    { pergunta: 'O que é volatilidade?', opcoes: ['A intensidade das oscilações de preço de um ativo', 'A certeza de receber juros', 'O prazo de vencimento de uma conta', 'A taxa de câmbio fixa'], correta: 0 },
    { pergunta: 'Sobre criptomoedas, qual afirmação é mais responsável?', opcoes: ['Podem ter alta volatilidade e exigem gestão de risco', 'São sempre protegidas pelo FGC', 'Não sofrem oscilações', 'Garantem retorno positivo'], correta: 0 },
    { pergunta: 'O que é uma stablecoin?', opcoes: ['Um criptoativo projetado para acompanhar o valor de uma referência', 'Uma ação de empresa estatal', 'Um título público brasileiro', 'Uma moeda sem qualquer risco'], correta: 0 },
    { pergunta: 'Por que não se deve compartilhar a chave privada de uma carteira cripto?', opcoes: ['Quem a possui pode controlar os ativos', 'Ela serve apenas para receber promoções', 'Ela reduz a inflação', 'Ela garante lucro'], correta: 0 }
];

/* ================================================================
   CARROSSEL DE MÓDULOS
================================================================ */
const track = document.getElementById("modulosTrack");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
let currentIndex = 0;

function getCardWidth() {
    const card = track.querySelector(".modulo-card");
    return card ? card.offsetWidth + 24 : 0;
}

function updateCarousel() {
    const cards = track.querySelectorAll(".modulo-card");
    if (!cards.length) return;

    const cardWidth = getCardWidth();
    const wrapper = track.parentElement.offsetWidth;
    const totalWidth = cards.length * cardWidth - 24;
    const maxOffset = Math.max(0, totalWidth - wrapper);

    let offset = currentIndex * cardWidth;
    offset = Math.min(offset, maxOffset);

    track.style.transform = `translateX(-${offset}px)`;
}

prevBtn.addEventListener("click", () => {
    if (currentIndex > 0) {
        currentIndex--;
        updateCarousel();
    }
});

nextBtn.addEventListener("click", () => {
    const cards = track.querySelectorAll(".modulo-card");
    if (currentIndex < cards.length - 1) {
        currentIndex++;
        updateCarousel();
    }
});

window.addEventListener("resize", updateCarousel);

/* ================================================================
   NAVEGAÇÃO ENTRE MÓDULOS E EXERCÍCIOS
================================================================ */
const modulosSection = document.getElementById("modulosSection");
const timelineContainer = document.getElementById("timelineContainer");
const exerciciosLista = document.getElementById("exerciciosLista");
const moduloTitulo = document.getElementById("moduloTitulo");

function abrirModulo(moduloId) {
    const card = document.querySelector(`.modulo-card[data-modulo="${moduloId}"]`);
    if (!card) return;

    const nome = card.querySelector("h3").textContent;
    const desc = card.querySelector(".modulo-desc").textContent;
    moduloTitulo.textContent = `${nome} - ${desc}`;

    const exercicios = exerciciosPorModulo[moduloId] || [];
    exerciciosLista.innerHTML = "";

    exercicios.forEach((item, index) => {
        const div = document.createElement("div");
        div.className = "aula-item";

        div.innerHTML = `
            <div class="aula-info">
                <span class="aula-titulo">${item.titulo}</span>
                <div class="aula-meta">
                    <span>
                        <i class="fas fa-circle-play"></i>
                        Aula Vinculada: <strong class="vinculo-aula">${item.videoAula}</strong>
                    </span>
                    <span>
                        <i class="fas fa-clock"></i>
                        Duração do Vídeo: ${item.duracaoVideo}
                    </span>
                </div>
            </div>

            <div class="aula-duracao" title="Tempo médio estimado para realizar os 10 exercícios">
                <i class="fas fa-stopwatch"></i>
                ~${item.tempoMedioLista} de lista
            </div>

            <button type="button" class="btn-praticar" data-modulo="${moduloId}" data-index="${index}">
                <i class="fas fa-play"></i>
                Fazer Exercício
            </button>

            <!-- Tooltip Liquid Glass ao passar o mouse -->
            <div class="tooltip">
                <div class="tt-title">
                    <span>${item.titulo}</span>
                    <i class="fas fa-bolt" style="color:var(--verde);"></i>
                </div>
                <div class="tt-meta">
                    <span>
                        <i class="fas fa-video"></i>
                        Atribuído à Videoaula: <strong>${item.videoAula}</strong>
                    </span>
                    <span>
                        <i class="fas fa-stopwatch"></i>
                        Tempo estimado para responder: <strong>${item.tempoMedioLista}</strong>
                    </span>
                    <span>
                        <i class="fas fa-list-check"></i>
                        Formato: <strong>10 Questões Rápidas (Duolingo Style)</strong>
                    </span>
                </div>
                <div class="tt-resumo">
                    ${item.resumo}
                </div>
            </div>
        `;

        exerciciosLista.appendChild(div);
    });

    modulosSection.style.display = "none";
    timelineContainer.classList.add("active");

    window.scrollTo({
        top: timelineContainer.offsetTop - 30,
        behavior: "smooth"
    });
}

document.querySelectorAll(".btn-entrar").forEach(btn => {
    btn.addEventListener("click", function() {
        const modulo = Number(this.dataset.modulo);
        abrirModulo(modulo);
    });
});

document.getElementById("btnVoltarModulos").addEventListener("click", () => {
    window.location.href = "aprendizado.php";
});

/* ================================================================
   SISTEMA DE QUIZ INTERATIVO (DUOLINGO 10 QUESTÕES)
================================================================ */
const quizModal = document.getElementById("quizModal");
const quizProgressFill = document.getElementById("quizProgressFill");
const quizStepCount = document.getElementById("quizStepCount");
const quizMetaAula = document.getElementById("quizMetaAula");
const quizPerguntaText = document.getElementById("quizPerguntaText");
const quizOptionsGrid = document.getElementById("quizOptionsGrid");
const btnResponderQuiz = document.getElementById("btnResponderQuiz");
const fecharQuiz = document.getElementById("fecharQuiz");

let currentQuestionIndex = 0;
let selectedOptionIndex = null;
let currentExerciseMeta = null;

function iniciarQuiz(itemData) {
    currentQuestionIndex = 0;
    selectedOptionIndex = null;
    currentExerciseMeta = itemData;

    quizMetaAula.textContent = `Atribuído à Videoaula: ${itemData.videoAula}`;
    carregarPergunta(currentQuestionIndex);
    quizModal.classList.add("active");
}

function carregarPergunta(index) {
    const q = questoesDuolingo[index];
    selectedOptionIndex = null;
    btnResponderQuiz.disabled = true;
    btnResponderQuiz.textContent = index === 9 ? "Finalizar Lista" : "Verificar";

    // Progress Bar
    const pct = ((index + 1) / 10) * 100;
    quizProgressFill.style.width = `${pct}%`;
    quizStepCount.textContent = `${index + 1}/10`;

    quizPerguntaText.textContent = q.pergunta;
    quizOptionsGrid.innerHTML = "";

    const letras = ["A", "B", "C", "D"];
    q.opcoes.forEach((optText, optIdx) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "option-btn";
        btn.innerHTML = `
            <span class="option-letter">${letras[optIdx]}</span>
            <span>${optText}</span>
        `;

        btn.addEventListener("click", () => {
            document.querySelectorAll(".option-btn").forEach(b => b.classList.remove("selected"));
            btn.classList.add("selected");
            selectedOptionIndex = optIdx;
            btnResponderQuiz.disabled = false;
        });

        quizOptionsGrid.appendChild(btn);
    });
}

btnResponderQuiz.addEventListener("click", () => {
    if (selectedOptionIndex === null) return;

    const q = questoesDuolingo[currentQuestionIndex];
    const options = quizOptionsGrid.querySelectorAll(".option-btn");

    // Marcação visual de correto e errado
    options.forEach((optBtn, idx) => {
        if (idx === q.correta) {
            optBtn.classList.add("correct");
        } else if (idx === selectedOptionIndex) {
            optBtn.classList.add("wrong");
        }
        optBtn.disabled = true;
    });

    setTimeout(() => {
        if (currentQuestionIndex < 9) {
            currentQuestionIndex++;
            carregarPergunta(currentQuestionIndex);
        } else {
            // FIM DA LISTA DE 10 EXERCÍCIOS - LIGA A OFENSIVA!
            fecharQuizModal();
            ativarOfensiva();
        }
    }, 1100);
});

function fecharQuizModal() {
    quizModal.classList.remove("active");
}

fecharQuiz.addEventListener("click", fecharQuizModal);

/* Event Delegation para abrir o quiz a partir do botão 'Fazer Exercício' */
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-praticar");
    if (!btn) return;

    const modulo = Number(btn.dataset.modulo);
    const index = Number(btn.dataset.index);
    const itemData = exerciciosPorModulo[modulo][index];

    iniciarQuiz(itemData);
});

/* ================================================================
   SISTEMA DE POPUP DE OFENSIVA (STREAK FIRE POPUP)
================================================================ */
const streakModal = document.getElementById("streakModal");
const diasOfensivaCount = document.getElementById("diasOfensivaCount");
const modalStreakDays = document.getElementById("modalStreakDays");

function ativarOfensiva() {
    // Incrementa a ofensiva para efeito visual
    let atual = parseInt(diasOfensivaCount.textContent) || 0;
    let novoValor = atual + 1;

    diasOfensivaCount.textContent = novoValor;
    modalStreakDays.textContent = `🔥 ${novoValor} Dias Seguidos!`;

    // Exibe o popup animado com o foguinho
    streakModal.classList.add("active");
}

function fecharStreakModal() {
    streakModal.classList.remove("active");
}

/* Inicialização */
window.addEventListener("load", () => {
    updateCarousel();
});
</script>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>
