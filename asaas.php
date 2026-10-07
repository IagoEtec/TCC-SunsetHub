<?php

require_once 'seguranca.php';
require_once 'conexao.php';

/* ------------------------------------------------------------------
 * Utilitários gerais
 * ---------------------------------------------------------------- */

/** Envia uma resposta JSON e encerra o script. */
function responderJson(int $status, array $dados)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Lê os dados enviados (JSON no corpo ou formulário comum). */
function lerEntrada(): array
{
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : $_POST;
}

/**
 * Devolve o ID do usuário logado ou responde 401.
 * ATENÇÃO: ajuste a chave da sessão ('usuario_id') para a que
 * o seu sistema de login realmente usa.
 */
function usuarioLogadoId(): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $id = (int) ($_SESSION['usuario_id'] ?? 0);

    if ($id <= 0) {
        responderJson(401, ['erro' => 'Faça login para continuar.']);
    }

    return $id;
}

/* ------------------------------------------------------------------
 * Planos (o valor SEMPRE é definido aqui, no servidor, nunca pelo navegador)
 * ---------------------------------------------------------------- */

function afdePlanos(): array
{
    return [
        'mensal' => ['nome' => 'Plano AFDE Mensal', 'valor' => 50.00],
        'anual'  => ['nome' => 'Plano AFDE Anual',  'valor' => 480.00],
    ];
}

/* ------------------------------------------------------------------
 * Função central de comunicação com a API do Asaas
 * ---------------------------------------------------------------- */

/**
 * @param string     $metodo  GET, POST...
 * @param string     $caminho ex.: '/customers'
 * @param array|null $dados   corpo da requisição (para POST)
 * @return array resposta da API já decodificada
 * @throws RuntimeException em caso de falha de conexão ou erro da API
 */
function asaasRequest(string $metodo, string $caminho, ?array $dados = null): array
{
    $apiKey = afdeConfig('ASAAS_API_KEY', '');
    $base   = rtrim(afdeConfig('ASAAS_BASE_URL', 'https://api-sandbox.asaas.com/v3'), '/');

    if ($apiKey === '') {
        throw new RuntimeException('ASAAS_API_KEY não configurada.');
    }

    $ch = curl_init($base . $caminho);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CUSTOMREQUEST  => strtoupper($metodo),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'User-Agent: AFDE/1.0',
            'access_token: ' . $apiKey,
        ],
    ]);

    if ($dados !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    }

    $resposta = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erroCurl = curl_error($ch);
    curl_close($ch);

    if ($resposta === false) {
        throw new RuntimeException('Falha de conexão com o Asaas: ' . $erroCurl);
    }

    $json = json_decode($resposta, true);
    $json = is_array($json) ? $json : [];

    if ($status >= 400) {
        $mensagem = $json['errors'][0]['description'] ?? 'Erro desconhecido no Asaas.';
        error_log("[asaas] HTTP $status em $metodo $caminho: " . $resposta);
        throw new RuntimeException($mensagem, $status);
    }

    return $json;
}

/* ------------------------------------------------------------------
 * Clientes
 * ---------------------------------------------------------------- */

/** Busca no NOSSO banco o ID do cliente Asaas de um usuário (ou null). */
function buscarClienteAsaasLocal(int $usuarioId): ?string
{
    $st = afdeDb()->prepare('SELECT asaas_customer_id FROM clientes_asaas WHERE usuario_id = ?');
    $st->execute([$usuarioId]);
    $id = $st->fetchColumn();

    return $id !== false ? (string) $id : null;
}

/**
 * Garante que o usuário tem cadastro no Asaas.
 * Se já existe, reaproveita; se não, cria e guarda o ID no banco.
 */
function garantirClienteAsaas(int $usuarioId, string $nome, string $cpfCnpj, string $email): string
{
    $existente = buscarClienteAsaasLocal($usuarioId);
    if ($existente !== null) {
        return $existente;
    }

    $cliente = asaasRequest('POST', '/customers', [
        'name'              => $nome,
        'cpfCnpj'           => $cpfCnpj,
        'email'             => $email,
        'externalReference' => (string) $usuarioId,
    ]);

    $customerId = $cliente['id'] ?? null;
    if (!$customerId) {
        throw new RuntimeException('O Asaas não retornou o ID do cliente.');
    }

    $st = afdeDb()->prepare('INSERT INTO clientes_asaas (usuario_id, asaas_customer_id) VALUES (?, ?)');
    $st->execute([$usuarioId, $customerId]);

    return $customerId;
}

/* ------------------------------------------------------------------
 * Cobranças
 * ---------------------------------------------------------------- */

function criarCobrancaAsaas(
    string $customerId,
    float $valor,
    string $vencimento,
    string $descricao,
    string $referenciaExterna
): array {
    return asaasRequest('POST', '/payments', [
        'customer'          => $customerId,
        'billingType'       => 'PIX',
        'value'             => $valor,
        'dueDate'           => $vencimento,
        'description'       => $descricao,
        'externalReference' => $referenciaExterna,
    ]);
}

/** Retorna ['encodedImage' => base64, 'payload' => copia-e-cola, 'expirationDate' => ...] */
function buscarQrCodePix(string $paymentId): array
{
    return asaasRequest('GET', '/payments/' . rawurlencode($paymentId) . '/pixQrCode');
}
