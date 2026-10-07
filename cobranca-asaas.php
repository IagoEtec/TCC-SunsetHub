<?php

/**
 * POST /cobranca-asaas.php
 * Corpo (JSON): { "plano": "mensal" }
 * Cria o pedido, gera a cobrança PIX no Asaas e devolve o QR Code.
 */

require_once 'asaas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(405, ['erro' => 'Método não permitido.']);
}

$usuarioId = usuarioLogadoId();
$entrada   = lerEntrada();
$planos    = afdePlanos();
$chave     = (string) ($entrada['plano'] ?? '');

if (!isset($planos[$chave])) {
    responderJson(422, ['erro' => 'Plano inválido.']);
}

$customerId = buscarClienteAsaasLocal($usuarioId);
if ($customerId === null) {
    responderJson(409, ['erro' => 'Complete seu cadastro de pagamento antes de comprar.']);
}

$plano = $planos[$chave];
$pdo   = afdeDb();

// 1) Registra o pedido como "pendente" no nosso banco.
$st = $pdo->prepare("INSERT INTO pedidos (usuario_id, plano, valor, status) VALUES (?, ?, ?, 'pendente')");
$st->execute([$usuarioId, $chave, $plano['valor']]);
$pedidoId = (int) $pdo->lastInsertId();

// 2) Cria a cobrança no Asaas (vence em 3 dias).
try {
    $vencimento = date('Y-m-d', strtotime('+3 days'));

    $pagamento = criarCobrancaAsaas(
        $customerId,
        (float) $plano['valor'],
        $vencimento,
        $plano['nome'],
        (string) $pedidoId   // externalReference: liga a cobrança ao nosso pedido
    );

    $st = $pdo->prepare('UPDATE pedidos SET asaas_payment_id = ?, invoice_url = ? WHERE id = ?');
    $st->execute([$pagamento['id'], $pagamento['invoiceUrl'] ?? null, $pedidoId]);
} catch (Throwable $e) {
    error_log('[cobranca-asaas] ' . $e->getMessage());
    $pdo->prepare("UPDATE pedidos SET status = 'erro' WHERE id = ?")->execute([$pedidoId]);
    responderJson(502, ['erro' => 'Não foi possível gerar a cobrança. Tente novamente.']);
}

// 3) Busca o QR Code PIX. Se falhar, o usuário ainda pode pagar pelo invoiceUrl.
$qr = null;
try {
    $qr = buscarQrCodePix($pagamento['id']);
} catch (Throwable $e) {
    error_log('[cobranca-asaas] QR Code indisponível: ' . $e->getMessage());
}

responderJson(200, [
    'ok'            => true,
    'pedidoId'      => $pedidoId,
    'valor'         => $plano['valor'],
    'invoiceUrl'    => $pagamento['invoiceUrl'] ?? null,
    'pixCopiaECola' => $qr['payload'] ?? null,
    'pixImagem'     => isset($qr['encodedImage']) ? 'data:image/png;base64,' . $qr['encodedImage'] : null,
    'pixExpiraEm'   => $qr['expirationDate'] ?? null,
]);
