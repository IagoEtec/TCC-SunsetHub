<?php

/**
 * POST /client-asaas.php
 * Corpo (JSON): { "nome": "...", "cpfCnpj": "...", "email": "..." }
 * Cadastra o usuário logado como cliente no Asaas (uma única vez).
 */

require_once 'asaas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(405, ['erro' => 'Método não permitido.']);
}

$usuarioId = usuarioLogadoId();
$entrada   = lerEntrada();

$nome    = trim((string) ($entrada['nome'] ?? ''));
$email   = trim((string) ($entrada['email'] ?? ''));
$cpfCnpj = preg_replace('/\D/', '', (string) ($entrada['cpfCnpj'] ?? ''));

if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responderJson(422, ['erro' => 'Informe nome e e-mail válidos.']);
}

if (!in_array(strlen($cpfCnpj), [11, 14], true)) {
    responderJson(422, ['erro' => 'CPF ou CNPJ inválido.']);
}

try {
    $customerId = garantirClienteAsaas($usuarioId, $nome, $cpfCnpj, $email);
    responderJson(200, ['ok' => true, 'customerId' => $customerId]);
} catch (Throwable $e) {
    error_log('[client-asaas] ' . $e->getMessage());
    responderJson(502, ['erro' => 'Não foi possível concluir o cadastro de pagamento. Tente novamente.']);
}
