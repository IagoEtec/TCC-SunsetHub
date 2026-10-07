<?php

require_once __DIR__ . '/back-end/seguranca.php';

/**
 * Problema: sem validar a origem do webhook, qualquer pessoa na
 * internet pode enviar um POST para este endpoint simulando um evento
 * "PAYMENT_RECEIVED" e, quando a integração estiver completa, marcar um
 * pedido como pago sem ter pago de verdade.
 *
 * Solução: o Asaas permite configurar um "token de autenticação" que é
 * enviado no header "asaas-access-token" em toda chamada de webhook.
 * Validamos esse token (comparação em tempo constante) antes de
 * processar qualquer evento. Configure o mesmo valor em
 * Configurações > Webhooks no painel do Asaas e na variável de ambiente
 * ASAAS_WEBHOOK_TOKEN (.env). Enquanto essa variável não for definida,
 * o webhook é recusado por padrão (falha segura), em vez de aceitar
 * qualquer requisição sem verificação.
 */
$tokenEsperado = afdeConfig('ASAAS_WEBHOOK_TOKEN', '');
$tokenRecebido = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? '';

if ($tokenEsperado === '' || !hash_equals($tokenEsperado, $tokenRecebido)) {
    error_log('[webhooks-asaas] Requisição recusada: token de webhook ausente/inválido.');
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$dados = json_decode(
    file_get_contents('php://input'),
    true
);

$evento = $dados['event'] ?? null;

$pagamento = $dados['payment'] ?? null;

if ($evento === 'PAYMENT_RECEIVED') {

    $idPagamento = $pagamento['id'] ?? null;

    // Atualizar o pedido no banco
    // status = pago
    // (quando esta integração for implementada, use SEMPRE prepared
    // statements com $idPagamento como parâmetro vinculado — nunca
    // concatenado diretamente na query.)

}

http_response_code(200);

echo json_encode([
    'status' => 'ok'
]);
