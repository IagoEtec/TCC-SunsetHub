<?php
require_once __DIR__ . '/../back-end/seguranca.php';
iniciar_sessao_segura();
require_once("../back-end/conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN banner VARCHAR(255) NULL AFTER foto");
} catch (PDOException $ignored) {
    // A coluna já existe ou o banco será atualizado pelo migration_perfil.sql.
}
try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN patente VARCHAR(30) NULL AFTER banner");
} catch (PDOException $ignored) {
    // A coluna já existe ou será criada pela migration_patente.sql.
}
try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN xp INT NOT NULL DEFAULT 0 AFTER patente");
} catch (PDOException $ignored) {
    // A coluna já existe ou será criada pela migration_xp.sql.
}

$stmt = $pdo->prepare("SELECT id, nome, email, telefone, foto, banner, patente, xp, tema FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$tema = in_array(($usuario['tema'] ?? 'sistema'), ['claro', 'escuro', 'sistema'], true)
    ? ($usuario['tema'] ?? 'sistema')
    : 'sistema';

function perfilAsset(?string $path, string $fallback): string
{
    if (!$path) {
        return $fallback;
    }
    if (preg_match('/^https?:\\/\\//i', $path)) {
        return $path;
    }
    $path = ltrim($path, '/');
    return str_starts_with($path, '../') ? $path : '../' . $path;
}

$fotoUrl = perfilAsset($usuario['foto'] ?? null, '../img/default.png');
$bannerUrl = perfilAsset($usuario['banner'] ?? null, '');
$temBanner = !empty($usuario['banner']);
$nomeEscapado = htmlspecialchars($usuario['nome'] ?? '', ENT_QUOTES, 'UTF-8');
$emailEscapado = htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8');
$telefoneEscapado = htmlspecialchars($usuario['telefone'] ?? '', ENT_QUOTES, 'UTF-8');
$patenteAtual = trim((string) ($usuario['patente'] ?? ''));
$patentes = [
    'FERRO 1' => 0, 'FERRO 2' => 50, 'FERRO 3' => 150,
    'OURO 1' => 300, 'OURO 2' => 500, 'OURO 3' => 750,
    'PLATINA 1' => 1050, 'PLATINA 2' => 1450, 'PLATINA 3' => 1950, 'ESMERALDA' => 2550
];
$patenteAtual = strtoupper($patenteAtual);
$streakAtual = 0; $melhorStreak = 0;
try {
    $streakStmt = $pdo->prepare('SELECT streak, best_streak FROM arena_users WHERE user_id = ?');
    $streakStmt->execute([$_SESSION['id']]);
    $streakData = $streakStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $streakAtual = (int)($streakData['streak'] ?? 0);
    $melhorStreak = (int)($streakData['best_streak'] ?? 0);
} catch (Throwable $ignored) {}
$patenteExibicao = $patenteAtual && isset($patentes[$patenteAtual]) ? $patenteAtual : 'FERRO 1';
$xpAtual = max(0, (int) ($usuario['xp'] ?? 0));
$patenteIndex = array_search($patenteExibicao, array_keys($patentes), true);
$patenteIndex = $patenteIndex === false ? 0 : $patenteIndex;
$patenteBaseXp = array_values($patentes)[$patenteIndex];
$proximaPatente = array_keys($patentes)[$patenteIndex + 1] ?? null;
$proximoXp = $proximaPatente ? $patentes[$proximaPatente] : $patenteBaseXp;
$faixaXp = max(1, $proximoXp - $patenteBaseXp);
$xpNaPatente = max(0, $xpAtual - $patenteBaseXp);
$xpFaltante = $proximaPatente ? max(0, $proximoXp - $xpAtual) : 0;
$progressoXp = $proximaPatente ? min(100, max(0, ($xpNaPatente / $faixaXp) * 100)) : 100;
?>
<!DOCTYPE html>
<html lang="pt-BR" class="<?= htmlspecialchars($tema, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | AFDE</title>
    <link rel="stylesheet" href="../css/style-perfil.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>
<body>
<div class="background_shapes" aria-hidden="true">
    <div class="shape shape1"></div><div class="shape shape2"></div><div class="shape shape3"></div>
</div>
<?php include_once 'navbar.php'; ?>

<main class="perfil-page">
    <?php if (isset($_GET['status'])): ?>
        <div class="perfil_alert <?= $_GET['status'] === 'sucesso' ? 'perfil_alert-success' : 'perfil_alert-error' ?>" role="status">
            <?= $_GET['status'] === 'sucesso' ? 'Alterações salvas com sucesso.' : 'Não foi possível salvar a alteração. Tente novamente.' ?>
        </div>
    <?php endif; ?>

    <section class="perfil_header-cover <?= $temBanner ? 'has-banner' : '' ?>">
        <?php if ($temBanner): ?><img class="perfil-banner-image" src="<?= htmlspecialchars($bannerUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Banner do perfil"><div class="perfil-cover-overlay"></div><?php endif; ?>

        <form id="avatarForm" class="perfil-avatar-form" action="../back-end/atualizar_foto.php" method="POST" enctype="multipart/form-data">
            <?= csrf_campo() ?>

            <div class="perfil_avatar" id="avatarTrigger" role="button" tabindex="0" aria-label="Escolher nova foto de perfil"><img id="avatarCurrent" src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil"></div>
            <label for="foto" class="btn_secondary">Alterar foto</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
            <input type="hidden" name="foto_cortada" id="fotoCortada">
        </form>

        <div class="perfil-info-cover">
            <h1><?= $nomeEscapado ?></h1>
            <p><?= $emailEscapado ?></p>
            <div class="perfil-patente" aria-label="Patente atual">
                <i class="fas fa-medal" aria-hidden="true"></i>
                <span><?= htmlspecialchars($patenteExibicao, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>

        <form class="perfil-banner-form" action="../back-end/atualizar_banner.php" method="POST" enctype="multipart/form-data">
            <?= csrf_campo() ?>

            <small>Personalize o topo do seu perfil</small>
            <label for="banner" class="btn_secondary">Adicionar banner</label>
            <input type="file" id="banner" name="banner" accept="image/jpeg,image/png,image/webp">
            <input type="hidden" name="banner_cortado" id="bannerCortado">
        </form>
    </section>

    <section class="perfil-rank-card" aria-labelledby="perfilRankTitle">
        <div class="perfil-rank-heading">
            <div>
                <span class="perfil-rank-eyebrow"><i class="fas fa-ranking-star"></i> Patente atual</span>
                <h2 id="perfilRankTitle"><?= htmlspecialchars($patenteExibicao, ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= $proximaPatente ? 'Continue estudando para alcançar ' . htmlspecialchars($proximaPatente, ENT_QUOTES, 'UTF-8') . '.' : 'Você alcançou a última patente disponível.' ?></p>
            </div>
            <div class="perfil-rank-medal" aria-hidden="true"><i class="fas fa-medal"></i></div>
        </div>
        <div class="perfil-xp-meta"><strong><i class="fas fa-bolt"></i> XP de treino — <?= $xpAtual ?> / <?= $proximoXp ?> XP</strong><span><?= $proximaPatente ? $xpFaltante . ' XP para subir' : 'Patente máxima' ?></span></div>
        <div class="perfil-xp-track" role="progressbar" aria-label="Progresso para a próxima patente" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) round($progressoXp) ?>">
            <span style="width: <?= number_format($progressoXp, 2, '.', '') ?>%"></span>
        </div>
        <div class="perfil-xp-foot"><span><?= $xpNaPatente ?> / <?= $faixaXp ?> XP nesta patente</span><span><?= (int) round($progressoXp) ?>%</span></div>
    </section>

    <section class="perfil-streak-card" aria-label="Ofensiva diária">
        <div class="perfil-streak-flame" aria-hidden="true">♨</div>
        <div><span class="perfil-streak-eyebrow">OFENSIVA DIÁRIA</span><strong><?= $streakAtual ?> <?= $streakAtual === 1 ? 'dia' : 'dias' ?> de fogo</strong><small>Melhor sequência: <?= $melhorStreak ?> <?= $melhorStreak === 1 ? 'dia' : 'dias' ?></small></div>
        <span class="perfil-streak-status"><?= $streakAtual > 0 ? 'FOGUINHO ACESO' : 'ACENDA NA ARENA' ?></span>
    </section>

    <section class="perfil_card">
        <h2>Informações pessoais</h2>
        <form action="../back-end/atualizar_perfil.php" method="POST">
            <?= csrf_campo() ?>

            <div class="form_group"><label for="nome">Nome completo</label><input id="nome" type="text" name="nome" value="<?= $nomeEscapado ?>" required></div>
            <div class="form_group"><label for="email">E-mail</label><input id="email" type="email" name="email" value="<?= $emailEscapado ?>" required></div>
            <div class="form_group"><label for="telefone">Telefone</label><input id="telefone" type="tel" name="telefone" value="<?= $telefoneEscapado ?>"></div>
            <button type="submit" class="btn_primary">Salvar alterações</button>
        </form>
    </section>

    <section class="perfil_card">
        <h2>Segurança</h2>
        <form action="../back-end/alterar_senha.php" method="POST">
            <?= csrf_campo() ?>

            <div class="form_group"><label for="senha_atual">Senha atual</label><input id="senha_atual" type="password" name="senha_atual" minlength="6" maxlength="72" required></div>
            <div class="form_group"><label for="nova_senha">Nova senha</label><input id="nova_senha" type="password" name="nova_senha" minlength="6" maxlength="72" required></div>
            <div class="form_group"><label for="confirmar_senha">Confirmar nova senha</label><input id="confirmar_senha" type="password" name="confirmar_senha" minlength="6" maxlength="72" required></div>
            <button type="submit" class="btn_primary">Alterar senha</button>
        </form>
    </section>
<div class="crop-modal" id="cropModal" hidden>
    <div class="crop-dialog" role="dialog" aria-modal="true" aria-labelledby="cropHeading">
        <button type="button" class="crop-close" id="cropClose" aria-label="Fechar">×</button>
        <p class="crop-kicker">Editar foto</p>
        <h2 class="crop-heading" id="cropHeading">Ajuste sua foto em um quadrado</h2>
        <div class="crop-workspace">
            <div>
                <div class="crop-stage" id="cropStage"><canvas id="cropCanvas" width="1024" height="1024"></canvas><div class="crop-guide" aria-hidden="true"></div></div>
                <p class="crop-help">Arraste a imagem para reposicionar. O círculo mostra como ela ficará no perfil.</p>
            </div>
            <div class="crop-preview-wrap"><img id="cropPreview" alt="Prévia da foto quadrada"><strong>Prévia 1:1</strong><label class="crop-zoom-label" for="cropZoom">Zoom <span id="cropZoomValue">50%</span></label><input class="crop-zoom" id="cropZoom" type="range" min="50" max="300" value="50"></div>
        </div>
        <div class="crop-actions"><button type="button" class="crop-button crop-button-ghost" id="cropCancel">Cancelar</button><button type="button" class="crop-button crop-button-primary" id="cropApply">Usar esta foto</button></div>
    </div>
</div>
<div class="crop-modal" id="bannerModal" hidden aria-hidden="true">
    <div class="crop-dialog" role="dialog" aria-modal="true" aria-labelledby="bannerHeading">
        <button type="button" class="crop-close" id="bannerClose" aria-label="Fechar">×</button>
        <p class="crop-kicker">Editar banner</p>
        <h2 class="crop-heading" id="bannerHeading">Ajuste o banner no formato 3:1</h2>
        <div class="crop-workspace banner-workspace">
            <div><div class="crop-stage banner-stage" id="bannerStage"><canvas id="bannerCanvas" width="1800" height="600"></canvas></div><p class="crop-help">Arraste para posicionar e use o zoom para enquadrar o cabeçalho.</p></div>
            <div class="crop-preview-wrap"><img id="bannerPreview" alt="Prévia do banner"><strong>Prévia do cabeçalho</strong><label class="crop-zoom-label" for="bannerZoom">Zoom <span id="bannerZoomValue">50%</span></label><input class="crop-zoom" id="bannerZoom" type="range" min="50" max="250" value="50"></div>
        </div>
        <div class="crop-actions"><button type="button" class="crop-button crop-button-ghost" id="bannerCancel">Cancelar</button><button type="button" class="crop-button crop-button-primary" id="bannerApply">Usar este banner</button></div>
    </div>
</div>
</main>
<script src="../JS/perfil-editor.js?v=68" defer></script>
<script src="../JS/banner-editor.js?v=68" defer></script>
<script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=100" defer></script>
</body>
</html>
