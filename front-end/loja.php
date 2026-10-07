<?php
// Inclui a conexão e sessão
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Loja</title>
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../img/favicon.png" />
    <style>
        /* =========================================================
           BASE – MODO ESCURO
           ========================================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0A2540; /* azul escuro profundo */
            color: #fff;
            min-height: 100vh;
        }

        /* =========================================================
           HERO
           ========================================================= */
        .hero-loja {
            padding: 140px 20px 60px;
            background: linear-gradient(145deg, #0A2540 0%, #1a2f4a 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-loja::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: rgba(22, 226, 138, 0.05);
            right: -200px;
            top: -150px;
            filter: blur(120px);
        }

        .hero-loja::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(22, 226, 138, 0.03);
            left: -150px;
            bottom: -100px;
            filter: blur(120px);
        }

        .hero-container {
            max-width: 1300px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .hero-texto h1 {
            font-size: clamp(2.2rem, 4.5vw, 3.6rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #fff;
        }

        .hero-texto h1 .destaque {
            background: linear-gradient(135deg, #16E28A, #0db873);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-texto p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            max-width: 500px;
            margin-top: 10px;
            line-height: 1.6;
        }

        /* =========================================================
           SALDO – CARD LIQUID GLASS ESCURO
           ========================================================= */
        .saldo-card {
            background: rgba(255, 255, 255, 0.04);  /* bem transparente */
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 32px;
            padding: 30px 40px;
            min-width: 220px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            transition: 0.3s;
        }

        .saldo-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .moeda-simbolo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .moeda-icone {
            width: 48px;
            height: 48px;
            background: radial-gradient(circle at 30% 30%, #16E28A, #0db873);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: 800;
            color: #0A2540;
            box-shadow: 0 0 20px rgba(22, 226, 138, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.15);
        }

        .moeda-nome {
            font-size: 1.3rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 0.5px;
        }

        .saldo-valor {
            font-size: 3.2rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin: 5px 0;
        }

        .saldo-valor small {
            font-size: 1.2rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.4);
            margin-left: 4px;
        }

        .saldo-extra {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 6px;
        }

        .saldo-extra i {
            color: #16E28A;
            margin-right: 4px;
        }

        /* =========================================================
           COMO GANHAR – LIQUID GLASS ESCURO
           ========================================================= */
        .como-ganhar {
            max-width: 1300px;
            margin: -20px auto 40px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        .como-ganhar-box {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px;
            padding: 20px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .como-ganhar-box i {
            color: #16E28A;
            font-size: 1.4rem;
        }

        .como-ganhar-box strong {
            color: #fff;
            font-weight: 700;
        }

        .como-ganhar-box span {
            background: rgba(22, 226, 138, 0.10);
            padding: 4px 14px;
            border-radius: 999px;
            font-weight: 600;
            color: #16E28A;
            font-size: 0.85rem;
            border: 1px solid rgba(22, 226, 138, 0.15);
        }

        /* =========================================================
           CARDS DOS ITENS – LIQUID GLASS ESCURO
           ========================================================= */
        .loja-grid {
            max-width: 1300px;
            margin: 0 auto 80px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 2;
        }

        .item-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 28px;
            padding: 28px 22px 22px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.04);
            display: flex;
            flex-direction: column;
        }

        .item-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.06);
        }

        .item-icone {
            font-size: 2.8rem;
            color: #16E28A;
            background: rgba(22, 226, 138, 0.08);
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            margin: 0 auto 16px;
            transition: 0.3s;
        }

        .item-card:hover .item-icone {
            background: rgba(22, 226, 138, 0.18);
            transform: scale(1.05);
        }

        .item-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .item-desc {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.9rem;
            line-height: 1.5;
            flex: 1;
            margin-bottom: 18px;
        }

        .item-preco {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(22, 226, 138, 0.10);
            padding: 6px 16px;
            border-radius: 999px;
            font-weight: 700;
            color: #16E28A;
            border: 1px solid rgba(22, 226, 138, 0.15);
            font-size: 0.95rem;
            margin-bottom: 18px;
            align-self: center;
        }

        .item-preco .moeda-mini {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .btn-comprar {
            background: linear-gradient(135deg, #16E28A, #0db873);
            border: none;
            border-radius: 999px;
            padding: 14px 0;
            font-weight: 700;
            font-size: 1rem;
            color: #0A2540;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 6px 20px rgba(22, 226, 138, 0.2);
            width: 100%;
            margin-top: auto;
        }

        .btn-comprar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(22, 226, 138, 0.35);
        }

        .btn-comprar:active {
            transform: scale(0.97);
        }

        /* =========================================================
           RODAPÉ – LIQUID GLASS ESCURO
           ========================================================= */
        .aviso-loja {
            max-width: 1300px;
            margin: 0 auto 60px;
            padding: 20px 30px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 28px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        .aviso-loja i {
            color: #16E28A;
            margin: 0 6px;
        }

        .aviso-loja strong {
            color: #fff;
        }

        /* =========================================================
           RESPONSIVO
           ========================================================= */
        @media (max-width: 768px) {
            .hero-container {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .hero-texto p {
                margin-left: auto;
                margin-right: auto;
            }

            .saldo-card {
                padding: 24px 20px;
                min-width: unset;
            }

            .saldo-valor {
                font-size: 2.6rem;
            }

            .loja-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 500px) {
            .loja-grid {
                grid-template-columns: 1fr;
            }

            .como-ganhar-box {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>
<body>

<?php include_once 'navbar.php'; ?>

<main>

    <!-- ========== HERO ========== -->
    <section class="hero-loja">
        <div class="hero-container">
            <div class="hero-texto">
                <h1>Loja <span class="destaque">Premium</span></h1>
                <p>Ganhe <strong>Afidis</strong> estudando e troque por itens especiais. Sem pay‑to‑win, só dedicação.</p>
                <a href="aprendizado.php" class="btn-voltar-aprendizado"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>

            <div class="saldo-card">
                <div class="moeda-simbolo">
                    <div class="moeda-icone">$</div>
                    <span class="moeda-nome">Afidis</span>
                </div>
                <div class="saldo-valor">
                    343 <small>Af</small>
                </div>
                <div class="saldo-extra">
                    <i class="fas fa-arrow-up"></i> +236 esta semana
                </div>
            </div>
        </div>
    </section>

    <!-- ========== COMO GANHAR ========== -->
    <div class="como-ganhar">
        <div class="como-ganhar-box">
            <i class="fas fa-coins"></i>
            <span>Como ganhar</span>
            <span>a cada 10 XP → 1 Afidis</span>
            <span>Treino do Dia</span>
            <span>Desafios</span>
            <span style="background:rgba(255,255,255,0.05);border-color:transparent;color:rgba(255,255,255,0.5);">
                <i class="fas fa-gem" style="color:#FFA502;"></i> + bônus
            </span>
        </div>
    </div>

    <!-- ========== GRID DE ITENS ========== -->
    <section class="loja-grid">

        <div class="item-card">
            <div class="item-icone"><i class="fas fa-snowflake"></i></div>
            <h3>Congelar Streak</h3>
            <p class="item-desc">Mantenha sua sequência de estudos ativa por um dia, mesmo sem estudar.</p>
            <div class="item-preco">
                <i class="fas fa-coins" style="color:#16E28A;font-size:0.9rem;"></i> 170 <span class="moeda-mini">Af</span>
            </div>
            <button class="btn-comprar" onclick="comprar('Congelar Streak', 170)">Comprar</button>
        </div>

        <div class="item-card">
            <div class="item-icone"><i class="fas fa-image"></i></div>
            <h3>Banner de Perfil</h3>
            <p class="item-desc">Personalize seu perfil com um banner exclusivo e destaque-se na plataforma.</p>
            <div class="item-preco">
                <i class="fas fa-coins" style="color:#16E28A;font-size:0.9rem;"></i> 590 <span class="moeda-mini">Af</span>
            </div>
            <button class="btn-comprar" onclick="comprar('Banner de Perfil', 590)">Comprar</button>
        </div>

        <div class="item-card">
            <div class="item-icone"><i class="fas fa-coins" style="color:#FFD700;"></i></div>
            <h3>Double Coins</h3>
            <p class="item-desc">Dobre os Afidis ganhos durante 24 horas — ideal para acelerar sua evolução.</p>
            <div class="item-preco">
                <i class="fas fa-coins" style="color:#16E28A;font-size:0.9rem;"></i> 450 <span class="moeda-mini">Af</span>
            </div>
            <button class="btn-comprar" onclick="comprar('Double Coins', 450)">Comprar</button>
        </div>

        <div class="item-card">
            <div class="item-icone"><i class="fas fa-star" style="color:#FFA502;"></i></div>
            <h3>Double XP</h3>
            <p class="item-desc">Dobre sua experiência (XP) durante um dia inteiro — suba de nível mais rápido.</p>
            <div class="item-preco">
                <i class="fas fa-coins" style="color:#16E28A;font-size:0.9rem;"></i> 450 <span class="moeda-mini">Af</span>
            </div>
            <button class="btn-comprar" onclick="comprar('Double XP', 450)">Comprar</button>
        </div>

    </section>

    <!-- ========== AVISO ========== -->
    <div class="aviso-loja">
        <i class="fas fa-shield-alt"></i> Sem pay‑to‑win. Afidis só compra cosméticos e utilidades — nada que dê vantagem no estudo. <strong>Dedicação e técnica vencem força.</strong>
    </div>

</main>

<script>
    function comprar(nome, preco) {
        const saldoAtual = 343;
        if (saldoAtual >= preco) {
            alert(`🛒 Compra realizada!\n\nItem: ${nome}\nPreço: ${preco} Afidis\nSaldo restante: ${saldoAtual - preco} Afidis`);
        } else {
            alert(`❌ Saldo insuficiente!\n\nVocê tem ${saldoAtual} Afidis e o item custa ${preco} Afidis.\nEstude mais para ganhar mais Afidis!`);
        }
    }
</script>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>
