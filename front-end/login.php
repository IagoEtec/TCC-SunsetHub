<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | AFDE</title>

    <link rel="stylesheet" href="../css/login.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>

<body>

    <div class="background_shapes">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
    </div>

    <a href="../front-end/home.php" class="btn_voltar">
        ← Voltar
    </a>

    <main class="login_container">

        <section class="login_left">

            <img src="../img/logo.png" alt="AFDE" class="brand_logo">

            <span class="badge">
                Plataforma de Educação Financeira
            </span>

            <h1>
                Assuma o controle das suas finanças.
            </h1>

            <p>
                Aprenda educação financeira, organize seus gastos,
                acompanhe metas e desenvolva hábitos financeiros
                inteligentes para o seu futuro.
            </p>

            <div class="benefits">

                <div class="benefit">
                    <span class="benefit_icon">✓</span>
                    <p>Educação financeira prática</p>
                </div>

                <div class="benefit">
                    <span class="benefit_icon">✓</span>
                    <p>Calculadora financeira exclusiva</p>
                </div>

                <div class="benefit">
                    <span class="benefit_icon">✓</span>
                    <p>Conteúdo para iniciantes</p>
                </div>

            </div>

        </section>

        <section class="login_right">

            <div class="login_card">

                <div class="card_header">

                    <h2>Entrar</h2>

                    <p>
                        Acesse sua conta para continuar.
                    </p>

                </div>

                <form action="../back-end/processa_login.php" method="POST" class="login_form">

                    <?= csrf_campo() ?>
                    <?php if (($_GET['next'] ?? '') === 'community'): ?>
                        <input type="hidden" name="next" value="community">
                    <?php endif; ?>

                    <div class="input_group">

                        <label>Email</label>

                        <input
                            type="email"
                            name="email"
                            placeholder="Digite seu email"
                            required
                        >

                    </div>

                    <div class="input_group">

                        <label>Senha</label>

                        <input
                            type="password"
                            name="senha"
                            placeholder="Digite sua senha"
                            required
                            minlength="6"
                            maxlength="128"
                        >

                    </div>

                    <button href="../back-end/processa_login.php" type="submit" class="login_btn">
                        Entrar na Conta
                    </button>
                    <button type="button" class="google_btn" onclick="window.location.href='../back-end/google-callback.php'">
                        <svg class="google_icon" viewBox="0 0 48 48" width="20" height="20">
                            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12
                                c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24
                                c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039
                                l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36
                                c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571
                                c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24
                                C44,22.659,43.862,21.35,43.611,20.083z"/>
                        </svg>
                        Continuar com Google
                    </button>

                </form>



                <div class="divider">
                    <span>ou</span>
                </div>

                <div class="register_link">

                    <p>
                        Ainda não possui uma conta?
                    </p>

                    <a href="../front-end/cadastro.php">
                        Criar conta gratuitamente
                    </a>

                </div>

            </div>

        </section>

    </main>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>
