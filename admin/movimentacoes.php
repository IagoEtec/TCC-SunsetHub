<?php
require_once 'admin_auth.php';
$paginaAtual = 'movimentacoes';

$mensagem = '';
$tipoMensagem = '';

// ===================== EXCLUIR MOVIMENTAÇÃO =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    csrf_exigir();
    $idExcluir = (int) $_POST['excluir_id'];

    $stmt = $pdo->prepare("DELETE FROM movimentacoes WHERE id = ?");
    $stmt->execute([$idExcluir]);
    $mensagem = 'Movimentação excluída com sucesso.';
    $tipoMensagem = 'sucesso';
}

// ===================== FILTROS E BUSCA =====================
$busca     = trim($_GET['busca'] ?? '');
$tipo      = trim($_GET['tipo'] ?? '');
$usuarioId = isset($_GET['usuario_id']) ? (int) $_GET['usuario_id'] : 0;

$whereClauses = [];
$params = [];

if ($busca !== '') {
    $whereClauses[] = "(m.descricao LIKE ? OR u.nome LIKE ?)";
    $termo = '%' . $busca . '%';
    $params[] = $termo;
    $params[] = $termo;
}

if (in_array($tipo, ['entrada', 'saida'], true)) {
    $whereClauses[] = "m.tipo = ?";
    $params[] = $tipo;
}

if ($usuarioId > 0) {
    $whereClauses[] = "m.usuario_id = ?";
    $params[] = $usuarioId;
}

// Consulta com data_criacao
$sql = "SELECT m.id, m.usuario_id, m.descricao, m.valor, m.tipo, m.categoria, m.data_criacao, u.nome AS usuario_nome
        FROM movimentacoes m
        LEFT JOIN usuarios u ON m.usuario_id = u.id";

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY m.data_criacao DESC, m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca lista de usuários para preencher o filtro drop-down
$usuariosFiltro = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações | Admin AFDE</title>
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
            <h1>Movimentações</h1>
            <p><?= count($movimentacoes) ?> movimentação(ões) encontrada(s).</p>
        </div>
    </div>

    <?php if ($mensagem): ?>
        <div class="alerta <?= $tipoMensagem ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="GET" class="filtros" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
        <input type="text" name="busca" placeholder="Pesquisar por descrição ou usuário..." value="<?= htmlspecialchars($busca) ?>">

        <select name="tipo">
            <option value="">Todos os tipos</option>
            <option value="entrada" <?= $tipo === 'entrada' ? 'selected' : '' ?>>Entrada</option>
            <option value="saida" <?= $tipo === 'saida' ? 'selected' : '' ?>>Saída</option>
        </select>

        <select name="usuario_id">
            <option value="">Todos os usuários</option>
            <?php foreach ($usuariosFiltro as $uf): ?>
                <option value="<?= $uf['id'] ?>" <?= $usuarioId === (int)$uf['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($uf['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-secundario">Filtrar</button>
        <?php if ($busca !== '' || $tipo !== '' || $usuarioId > 0): ?>
            <a href="movimentacoes.php" class="btn btn-secundario">Limpar</a>
        <?php endif; ?>
    </form>

    <div class="tabela-wrap">
        <?php if (empty($movimentacoes)): ?>
            <p class="vazio">Nenhuma movimentação registrada até o momento.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimentacoes as $m): ?>
                <tr>
                    <td>#<?= $m['id'] ?></td>
                    <td><?= htmlspecialchars($m['usuario_nome'] ?: 'Usuário Desconhecido') ?></td>
                    <td><?= htmlspecialchars($m['descricao'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($m['categoria'] ?: '—') ?></td>
                    <td>
                        <span class="badge <?= $m['tipo'] === 'entrada' ? 'ativo' : 'inativo' ?>">
                            <?= htmlspecialchars(ucfirst($m['tipo'])) ?>
                        </span>
                    </td>
                    <td style="font-weight: 600; color: <?= $m['tipo'] === 'entrada' ? 'var(--verde, #2ecc71)' : 'var(--vermelho, #e74c3c)' ?>;">
                        <?= $m['tipo'] === 'saida' ? '-' : '+' ?> R$ <?= number_format((float)$m['valor'], 2, ',', '.') ?>
                    </td>
                    <td><?= !empty($m['data_criacao']) ? date('d/m/Y H:i', strtotime($m['data_criacao'])) : '—' ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta movimentação? Esta ação não pode ser desfeita.');" style="display:inline;">
            <?= csrf_campo() ?>

                            <input type="hidden" name="excluir_id" value="<?= $m['id'] ?>">
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

