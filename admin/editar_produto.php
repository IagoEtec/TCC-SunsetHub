<?php
require_once 'admin_auth.php';
$paginaAtual = 'produtos';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: produtos.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco     = $_POST['preco'] ?? '';
    $imagem    = trim($_POST['imagem'] ?? '');
    $estoque   = $_POST['estoque'] !== '' ? (int) $_POST['estoque'] : null;
    $categoria = trim($_POST['categoria'] ?? '');
    $status    = in_array($_POST['status'] ?? '', ['ativo', 'inativo'], true) ? $_POST['status'] : 'ativo';

    if ($nome === '' || !is_numeric($preco) || $preco < 0) {
        $erro = 'Preencha o nome e um preço válido para o produto.';
        $produto = array_merge($produto, $_POST); // mantém os dados digitados na tela
    } else {
        $update = $pdo->prepare(
            "UPDATE produtos
             SET nome = ?, descricao = ?, preco = ?, imagem = ?, estoque = ?, categoria = ?, status = ?
             WHERE id = ?"
        );
        $update->execute([$nome, $descricao ?: null, $preco, $imagem ?: null, $estoque, $categoria ?: null, $status, $id]);

        header('Location: produtos.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar produto | Admin AFDE</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/liquid-glass.css?v=101">

</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="admin-content">
    <div class="admin-topo">
        <div>
            <h1>Editar produto</h1>
            <p>#<?= $produto['id'] ?> — <?= htmlspecialchars($produto['nome']) ?></p>
        </div>
        <a href="produtos.php" class="btn btn-secundario"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>

    <?php if ($erro): ?>
        <div class="alerta erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" class="form-card">
            <?= csrf_campo() ?>

        <div class="form-group">
            <label>Nome *</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($produto['nome']) ?>">
        </div>

        <div class="form-group">
            <label>Descrição</label>
            <textarea name="descricao"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Preço (R$) *</label>
            <input type="number" name="preco" step="0.01" min="0" required value="<?= htmlspecialchars($produto['preco']) ?>">
        </div>

        <div class="form-group">
            <label>Imagem (URL)</label>
            <input type="text" name="imagem" placeholder="https://..." value="<?= htmlspecialchars($produto['imagem'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Estoque</label>
            <input type="number" name="estoque" min="0" placeholder="Deixe vazio para ilimitado" value="<?= htmlspecialchars($produto['estoque'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Categoria</label>
            <input type="text" name="categoria" value="<?= htmlspecialchars($produto['categoria'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="ativo" <?= $produto['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= $produto['status'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>

        <div class="form-acoes">
            <button type="submit" class="btn btn-primario"><i class="fas fa-check"></i> Salvar alterações</button>
            <a href="produtos.php" class="btn btn-secundario">Cancelar</a>
        </div>
    </form>
</main>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=99" defer></script>
</body>
</html>

