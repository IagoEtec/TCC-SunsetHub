<?php
require_once 'admin_auth.php';
$paginaAtual = 'produtos';

$mensagem = '';
$tipoMensagem = '';

// ===================== ATIVAR / DESATIVAR =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    csrf_exigir();
    $idToggle = (int) $_POST['toggle_id'];

    $stmt = $pdo->prepare("SELECT status FROM produtos WHERE id = ?");
    $stmt->execute([$idToggle]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        $novoStatus = $produto['status'] === 'ativo' ? 'inativo' : 'ativo';
        $update = $pdo->prepare("UPDATE produtos SET status = ? WHERE id = ?");
        $update->execute([$novoStatus, $idToggle]);
        $mensagem = 'Status do produto atualizado.';
        $tipoMensagem = 'sucesso';
    }
}

// ===================== EXCLUIR =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    csrf_exigir();
    $idExcluir = (int) $_POST['excluir_id'];
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$idExcluir]);
    $mensagem = 'Produto excluído com sucesso.';
    $tipoMensagem = 'sucesso';
}

// ===================== LISTAGEM =====================
$produtos = $pdo->query(
    "SELECT id, nome, descricao, preco, imagem, estoque, categoria, status, data_criacao
     FROM produtos ORDER BY data_criacao DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos | Admin AFDE</title>
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
            <h1>Produtos / Loja</h1>
            <p><?= count($produtos) ?> produto(s) cadastrado(s).</p>
        </div>
        <a href="adicionar_produto.php" class="btn btn-primario">
            <i class="fas fa-plus"></i> Adicionar produto
        </a>
    </div>

    <?php if ($mensagem): ?>
        <div class="alerta <?= $tipoMensagem ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <div class="tabela-wrap">
        <?php if (empty($produtos)): ?>
            <p class="vazio">Nenhum produto cadastrado ainda. Clique em "Adicionar produto" para começar.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['categoria'] ?: '—') ?></td>
                    <td>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></td>
                    <td><?= $p['estoque'] !== null ? (int)$p['estoque'] : '—' ?></td>
                    <td><span class="badge <?= $p['status'] ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                    <td style="display:flex; gap:8px; align-items:center;">
                        <a href="editar_produto.php?id=<?= $p['id'] ?>" class="btn btn-secundario btn-sm">
                            <i class="fas fa-pen"></i> Editar
                        </a>

                        <form method="POST" style="display:inline;">
            <?= csrf_campo() ?>

                            <input type="hidden" name="toggle_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-secundario btn-sm">
                                <?= $p['status'] === 'ativo' ? '<i class="fas fa-eye-slash"></i> Desativar' : '<i class="fas fa-eye"></i> Ativar' ?>
                            </button>
                        </form>

                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.');" style="display:inline;">
            <?= csrf_campo() ?>

                            <input type="hidden" name="excluir_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-perigo btn-sm">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</main>


    <!-- Widget de Acessibilidade — integrado em todas as páginas -->
    <script src="../JS/acessibilidade.js" defer></script>
    <script src="../JS/liquid-glass.js?v=99" defer></script>
</body>
</html>

