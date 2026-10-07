<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/style-perfil.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php

if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../back-end/seguranca.php';
    iniciar_sessao_segura();
}

/* ==========================================
   VERIFICAR SE O USUÁRIO É ADMIN
========================================== */

$isAdmin = false;

if (isset($_SESSION['id'])) {

    require_once __DIR__ . '/../back-end/conexao.php';

    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN patente VARCHAR(30) NULL AFTER banner");
    } catch (PDOException $ignored) {
        // A coluna já existe.
    }
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN xp INT NOT NULL DEFAULT 0 AFTER patente");
    } catch (PDOException $ignored) {
        // A coluna já existe.
    }

    $stmt = $pdo->prepare(
        "SELECT nome, foto, tipo, patente, xp FROM usuarios WHERE id = ?"
    );

    $stmt->execute([
        $_SESSION['id']
    ]);

    $dadosTipo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dadosTipo && ($dadosTipo['tipo'] ?? '') === 'admin') {
        $isAdmin = true;
    }
}

?>

<header>

    <!-- ==========================================
         LOGO
    ========================================== -->

    <a href="../front-end/home.php">

        <img class="logo" src="../img/logo.png" alt="Logo">

    </a>


    <!-- ==========================================
         NAVBAR
    ========================================== -->

    <nav>

        <ul>

            <!-- INÍCIO -->

            <li>

                <a href="home.php">
                    Início
                </a>

            </li>


            <li class="education-dropdown">

                <a href="aprendizado.php" class="education-trigger">
                    Educação
                    <i class="fas fa-chevron-down education-arrow"></i>
                </a>

                <div class="education-dropdown-menu">

                    <a href="teste_diagnostico.php">
                        <i class="fas fa-clipboard-check"></i>

                        <div>
                            <strong>Teste Diagnóstico</strong>
                            <span>Descubra seu nível</span>
                        </div>
                    </a>


                    <a href="aprendizado.php?recurso=videoaulas">
                        <i class="fas fa-video"></i>

                        <div>
                            <strong>Videoaulas</strong>
                            <span>Conteúdos para aprender</span>
                        </div>
                    </a>


                    <a href="aprendizado.php?recurso=exercicios">
                        <i class="fas fa-pen"></i>

                        <div>
                            <strong>Exercícios</strong>
                            <span>Pratique seus conhecimentos</span>
                        </div>
                    </a>


                    <a href="aprendizado.php?recurso=loja">
                        <i class="fas fa-store"></i>

                        <div>
                            <strong>Loja</strong>
                            <span>Veja os recursos disponíveis</span>
                        </div>
                    </a>

                </div>

            </li>


            <!-- CALCULADORA -->

            <li>

                <a href="calculadora.php">
                    Calculadora
                </a>

            </li>


            <!-- CARTEIRA -->

            <li>

                <a href="carteira.php">
                    Carteira
                </a>

            </li>


            <!-- INVESTIMENTOS COM DROPDOWN -->

            <li class="investment-dropdown">

                    <a href="investimentos_Diversificacao.php" class="investment-trigger">
                    Investimentos

                    <i class="fas fa-chevron-down investment-arrow"></i>
                </a>


                <div class="investment-dropdown-menu">


                    <!-- DIVERSIFICAÇÃO -->

                    <a href="investimentos_Diversificacao.php">

                        <i class="fas fa-chart-pie"></i>

                        <div>

                            <strong>Diversificação</strong>

                            <span>
                                Distribua melhor seus investimentos
                            </span>

                        </div>

                    </a>


                    <!-- RENDA FIXA -->

                    <a href="investimentos_RendaFixa.php">

                        <i class="fas fa-shield-halved"></i>

                        <div>

                            <strong>Renda Fixa</strong>

                            <span>
                                Segurança e previsibilidade
                            </span>

                        </div>

                    </a>


                    <!-- RENDA VARIÁVEL -->

                    <a href="investimentos_RendaVariavel.php">

                        <i class="fas fa-chart-line"></i>

                        <div>

                            <strong>Renda Variável</strong>

                            <span>
                                Potencial de crescimento
                            </span>

                        </div>

                    </a>


                </div>

            </li>


            <!-- ANALISADOR -->

            <li>

                <a href="analisador.php">
                    Analisador
                </a>

            </li>


            <!-- COMUNIDADE SUNSET HUB -->
            <li class="community-nav-item">
                <a href="<?= isset($_SESSION['id']) ? '../community/front-end/index.php' : 'login.php' ?>" aria-label="Abrir comunidade Sunset Hub">
                    Comunidade
                </a>
            </li>


            <!-- ADMIN -->

            <?php if ($isAdmin): ?>

                <li>

                    <a href="../admin/index.php">
                        Admin
                    </a>

                </li>

            <?php endif; ?>


        </ul>

    </nav>


    <a class="community-mobile-link" href="<?= isset($_SESSION['id']) ? '../community/front-end/index.php' : 'login.php' ?>">Comunidade</a>


    <!-- ==========================================
         ÁREA DO USUÁRIO
    ========================================== -->

    <div class="home_bot">


        <?php if (isset($_SESSION['id'])): ?>


            <?php

            $sessaoUsuario = $_SESSION['usuario'] ?? [];
            $tipoUsuario = $dadosTipo ?? [];
            $usuario = array_merge(is_array($sessaoUsuario) ? $sessaoUsuario : [], is_array($tipoUsuario) ? $tipoUsuario : []);


            /* NOME */

            $nomeCompleto = trim(
                $usuario['nome'] ?? ''
            );


            $partes = $nomeCompleto !== ''

                ? preg_split(
                    '/\s+/',
                    $nomeCompleto
                )

                : ['Usuário'];


            $primeiroNome = $partes[0];


            $ultimoNome = count($partes) > 1

                ? $partes[count($partes) - 1]

                : null;


            $nomeExibicao = $ultimoNome

                ? $primeiroNome . " " . $ultimoNome

                : $primeiroNome;


            /* INICIAL DO USUÁRIO */

            $inicial = mb_strtoupper(

                mb_substr(
                    $primeiroNome,
                    0,
                    1
                )

            );


            /* FOTO */

            $temFoto = !empty($usuario['foto']);


            $fotoUsuario = null;
            if ($temFoto) {
                $fotoUsuario = $usuario['foto'];
                if (!preg_match('/^https?:\/\//i', $fotoUsuario)) {
                    $fotoUsuario = str_starts_with($fotoUsuario, '../') ? $fotoUsuario : '../' . ltrim($fotoUsuario, '/');
                }
            }

            ?>


            <!-- ==========================================
                 USUÁRIO + PERFIL
            ========================================== -->

            <div class="user_area">


                <!-- NOME -->

                <span class="user_name">

                    <strong>

                        <?= htmlspecialchars($nomeExibicao) ?>

                    </strong>

                </span>


                <!-- DROPDOWN DO USUÁRIO -->

                <div class="user_dropdown">


                    <!-- AVATAR: abre diretamente o painel de perfil -->

                    <a
                        id="avatarBtn"
                        class="avatar_btn"
                        aria-label="Abrir painel do perfil">


                        <?php if ($temFoto): ?>


                            <img src="<?= htmlspecialchars($fotoUsuario) ?>" class="avatar_img" alt="Avatar">


                        <?php else: ?>


                            <div class="avatar_placeholder">

                                <?= htmlspecialchars($inicial) ?>

                            </div>


                        <?php endif; ?>


                    </a>


                    <!-- ==========================================
                         MENU DO PERFIL
                    ========================================== -->

                    <div class="dropdown_menu" id="dropdownMenu">


                        <!-- CABEÇALHO -->

                        <div class="dropdown_header">


                            <?php if ($temFoto): ?>


                                <img src="<?= htmlspecialchars($fotoUsuario) ?>" alt="Avatar" class="avatar_img">


                            <?php else: ?>


                                <div class="avatar_placeholder">

                                    <?= htmlspecialchars($inicial) ?>

                                </div>


                            <?php endif; ?>


                            <div>


                                <strong>

                                    <?= htmlspecialchars($nomeExibicao) ?>

                                </strong>


                                <small>

                                    Usuário

                                </small>


                            </div>


                        </div>


                        <!-- VER PERFIL -->

                        <a href="perfil.php" class="dropdown_item">

                            <i class="fas fa-user"></i>

                            Ver Perfil

                        </a>


                        <!-- CONFIGURAÇÕES -->

                        <a href="configuracoes.php" class="dropdown_item">

                            <i class="fas fa-gear"></i>

                            Configurações

                        </a>


                        <hr>


                        <!-- SAIR -->

                        <a href="../back-end/logout.php" class="dropdown_item logout">

                            <i class="fas fa-right-from-bracket"></i>

                            Sair

                        </a>


                    </div>


                </div>


            </div>


        <?php else: ?>


            <!-- ==========================================
                 USUÁRIO NÃO LOGADO
            ========================================== -->


            <a href="cadastro.php">

                <button type="button" class="button_log">

                    Registrar

                </button>

            </a>


            <a href="login.php">

                <button type="button" class="button_log conectar">

                    Entrar

                </button>

            </a>


        <?php endif; ?>


    </div>


</header>


<!-- ==========================================
     SCRIPT: ABRIR/FECHAR DROPDOWN DO PERFIL
========================================== -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarBtn = document.getElementById('avatarBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    if (!avatarBtn || !dropdownMenu) return;

    avatarBtn.addEventListener('click', function (e) {
        e.preventDefault();

        dropdownMenu.classList.toggle('active');

    });

    document.addEventListener('click', function (e) {
        if (
            !dropdownMenu.contains(e.target) &&
            !avatarBtn.contains(e.target)
        ) {
            dropdownMenu.classList.remove('active');

        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdownMenu.classList.remove('active');

        }
    });
});
</script>
