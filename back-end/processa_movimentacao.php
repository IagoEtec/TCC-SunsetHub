<?php

require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once("conexao.php");

if (!isset($_SESSION['id'])) {
    header("Location: ../front-end/login.php");
    exit();
}

// CSRF: sem o token, outro site poderia lançar movimentações
// financeiras falsas na conta da vítima usando a sessão dela.
csrf_exigir();

$usuario_id = $_SESSION['id'];

$tipo = trim((string) ($_POST['tipo'] ?? ''));
$categoria = trim((string) ($_POST['categoria'] ?? ''));
$descricao = trim((string) ($_POST['descricao'] ?? ''));
$valorBruto = (string) ($_POST['valor'] ?? '');

// ===================== VALIDAÇÃO SERVER-SIDE =====================
// Problema: o formulário confiava apenas nas restrições do HTML
// (type="number", required, etc.), que não valem nada quando a
// requisição é enviada diretamente ao endpoint. Sem validar aqui,
// "tipo" poderia vir com qualquer texto (quebrando os relatórios que
// somam entradas/saídas) e "valor" poderia ser negativo, não numérico
// ou absurdamente grande.
if (!in_array($tipo, ['entrada', 'saida'], true)) {
    echo "<script>alert('Tipo de movimentação inválido.'); window.history.back();</script>";
    exit;
}

// Aceita tanto ponto quanto vírgula como separador decimal.
$valorNormalizado = str_replace(',', '.', $valorBruto);
if (!is_numeric($valorNormalizado)) {
    echo "<script>alert('Informe um valor numérico válido.'); window.history.back();</script>";
    exit;
}
$valor = round((float) $valorNormalizado, 2);
if ($valor <= 0 || $valor > 999999999.99) {
    echo "<script>alert('O valor informado é inválido.'); window.history.back();</script>";
    exit;
}

if ($categoria === '' || mb_strlen($categoria) > 100) {
    echo "<script>alert('Informe uma categoria válida.'); window.history.back();</script>";
    exit;
}

if (mb_strlen($descricao) > 255) {
    echo "<script>alert('A descrição é muito longa (máximo 255 caracteres).'); window.history.back();</script>";
    exit;
}

$sql = "
INSERT INTO movimentacoes
(
    usuario_id,
    tipo,
    categoria,
    descricao,
    valor
)
VALUES
(
    ?,
    ?,
    ?,
    ?,
    ?
)
";

try {
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $usuario_id,
        $tipo,
        $categoria,
        $descricao,
        $valor
    ]);

    header("Location: ../front-end/carteira.php");
    exit();
} catch (PDOException $e) {
    tratar_erro_bd($e, 'processa_movimentacao');
    echo "<script>alert('Não foi possível salvar a movimentação agora.'); window.history.back();</script>";
    exit;
}

