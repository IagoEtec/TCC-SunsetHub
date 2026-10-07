<?php

/**
 * Configurações da integração. As credenciais devem ficar em variáveis de
 * ambiente ou no arquivo .env do projeto, nunca no código-fonte público.
 */
function pluggyCredentials(): array
{
    static $environment = null;

    if ($environment === null) {
        $environment = is_file(dirname(__DIR__) . '/.env')
            ? (parse_ini_file(dirname(__DIR__) . '/.env', false, INI_SCANNER_RAW) ?: [])
            : [];
    }

    return [
        'clientId' => getenv('PLUGGY_CLIENT_ID') ?: ($environment['PLUGGY_CLIENT_ID'] ?? ''),
        'clientSecret' => getenv('PLUGGY_CLIENT_SECRET') ?: ($environment['PLUGGY_CLIENT_SECRET'] ?? ''),
    ];
}

function pluggyJsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function pluggyRequest(string $method, string $path, ?string $apiKey = null, ?array $body = null): array
{
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($apiKey) {
        $headers[] = 'X-API-KEY: ' . $apiKey;
    }

    $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
        ? $path
        : 'https://api.pluggy.ai' . $path;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 45,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = is_string($response) && $response !== ''
        ? json_decode($response, true)
        : null;

    return [
        'code' => $status,
        'data' => is_array($decoded) ? $decoded : null,
        'error' => $error ?: null,
    ];
}

function pluggyAuth(): ?string
{
    $credentials = pluggyCredentials();
    if (!$credentials['clientId'] || !$credentials['clientSecret']) {
        return null;
    }

    $response = pluggyRequest('POST', '/auth', null, $credentials);
    return in_array($response['code'], [200, 201], true)
        ? ($response['data']['apiKey'] ?? null)
        : null;
}

function pluggyGet(string $path, string $apiKey): array
{
    return pluggyRequest('GET', $path, $apiKey);
}

function getItemIdDoUsuario(PDO $pdo, int $usuarioId): ?string
{
    $stmt = $pdo->prepare('SELECT itemid FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $usuarioId]);
    $itemId = $stmt->fetchColumn();
    return is_string($itemId) && trim($itemId) !== '' ? trim($itemId) : null;
}

function pluggyResults(array $response): array
{
    if ($response['code'] < 200 || $response['code'] >= 300 || !is_array($response['data'] ?? null)) {
        return [];
    }

    return is_array($response['data']['results'] ?? null)
        ? $response['data']['results']
        : [];
}

/**
 * Lista todas as transações da conta usando o endpoint cursor-based atual.
 * Mantém fallback para o endpoint legado enquanto conectores antigos ainda o
 * exigirem.
 */
function pluggyListTransactions(string $accountId, string $apiKey): array
{
    $firstPath = '/v2/transactions?accountId=' . rawurlencode($accountId) . '&pageSize=500';
    $response = pluggyGet($firstPath, $apiKey);

    if ($response['code'] >= 200 && $response['code'] < 300) {
        $transactions = [];
        $path = $firstPath;
        $seen = [];

        for ($page = 0; $page < 100 && $path; $page++) {
            if (isset($seen[$path])) {
                break;
            }
            $seen[$path] = true;

            if ($page > 0) {
                $response = pluggyGet($path, $apiKey);
            }

            if ($response['code'] < 200 || $response['code'] >= 300) {
                break;
            }

            foreach (pluggyResults($response) as $transaction) {
                if (is_array($transaction)) {
                    $transactions[] = $transaction;
                }
            }

            $next = $response['data']['next'] ?? null;
            if (!is_string($next) || trim($next) === '') {
                $path = null;
                break;
            }

            if (str_starts_with($next, '?')) {
                $path = '/v2/transactions' . $next;
            } elseif (str_starts_with($next, '/')) {
                $path = $next;
            } else {
                $path = 'https://api.pluggy.ai/' . ltrim($next, '/');
            }
        }

        return [
            'code' => 200,
            'results' => $transactions,
            'error' => null,
        ];
    }

    // Compatibilidade com o endpoint anterior, caso o tenant/conector ainda
    // não aceite o endpoint /v2.
    $transactions = [];
    for ($page = 1; $page <= 100; $page++) {
        $legacyPath = '/transactions?accountId=' . rawurlencode($accountId)
            . '&pageSize=500&page=' . $page;
        $legacy = pluggyGet($legacyPath, $apiKey);
        if ($legacy['code'] < 200 || $legacy['code'] >= 300) {
            return [
                'code' => $legacy['code'] ?: $response['code'],
                'results' => $transactions,
                'error' => $legacy['error'] ?? null,
            ];
        }

        $pageResults = pluggyResults($legacy);
        foreach ($pageResults as $transaction) {
            if (is_array($transaction)) {
                $transactions[] = $transaction;
            }
        }

        $totalPages = (int) ($legacy['data']['totalPages'] ?? $page);
        if (!$pageResults || $page >= $totalPages) {
            break;
        }
    }

    return [
        'code' => 200,
        'results' => $transactions,
        'error' => null,
    ];
}

function pluggyDadosDoItem(string $itemId, string $apiKey): array
{
    $encodedItemId = rawurlencode($itemId);
    $accountsResponse = pluggyGet('/accounts?itemId=' . $encodedItemId . '&pageSize=500', $apiKey);
    $transactions = [];

    foreach (pluggyResults($accountsResponse) as $account) {
        if (empty($account['id'])) {
            continue;
        }

        $transactionsResponse = pluggyListTransactions((string) $account['id'], $apiKey);
        foreach ($transactionsResponse['results'] as $transaction) {
            $transaction['contaNome'] = $account['name']
                ?? $account['marketingName']
                ?? $account['number']
                ?? 'Conta conectada';
            $transaction['accountId'] = $transaction['accountId'] ?? $account['id'];
            $transactions[] = $transaction;
        }
    }

    usort($transactions, static function (array $a, array $b): int {
        return strtotime((string) ($b['date'] ?? '')) <=> strtotime((string) ($a['date'] ?? ''));
    });

    $loansResponse = pluggyGet('/loans?itemId=' . $encodedItemId . '&pageSize=500', $apiKey);
    $investmentsResponse = pluggyGet('/investments?itemId=' . $encodedItemId . '&pageSize=500', $apiKey);

    return [
        'transacoes' => $transactions,
        'emprestimos' => pluggyResults($loansResponse),
        'investimentos' => pluggyResults($investmentsResponse),
        'avisos' => array_values(array_filter([
            $accountsResponse['code'] < 200 || $accountsResponse['code'] >= 300
                ? 'Não foi possível consultar as contas da instituição.'
                : null,
            $loansResponse['code'] < 200 || $loansResponse['code'] >= 300
                ? 'Empréstimos não estão disponíveis para esta instituição.'
                : null,
            $investmentsResponse['code'] < 200 || $investmentsResponse['code'] >= 300
                ? 'Investimentos não estão disponíveis para esta instituição.'
                : null,
        ])),
    ];
}

function openFinanceEnsureSchema(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS open_finance_transacoes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            usuario_id INT NOT NULL,
            transacao_id VARCHAR(255) NOT NULL,
            account_id VARCHAR(255) DEFAULT NULL,
            conta_nome VARCHAR(255) DEFAULT NULL,
            tipo ENUM('entrada', 'saida') NOT NULL,
            status VARCHAR(30) DEFAULT 'POSTED',
            valor DECIMAL(12,2) NOT NULL DEFAULT 0,
            descricao VARCHAR(255) DEFAULT NULL,
            categoria VARCHAR(100) NOT NULL DEFAULT 'Outros',
            categoria_sugerida VARCHAR(100) NOT NULL DEFAULT 'Outros',
            categoria_origem ENUM('automatica', 'manual') NOT NULL DEFAULT 'automatica',
            moeda VARCHAR(12) DEFAULT 'BRL',
            data_transacao DATETIME NOT NULL,
            payload LONGTEXT DEFAULT NULL,
            sincronizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_open_finance_usuario_transacao (usuario_id, transacao_id),
            KEY idx_open_finance_usuario_data (usuario_id, data_transacao),
            KEY idx_open_finance_usuario_categoria (usuario_id, categoria),
            CONSTRAINT fk_open_finance_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL);
}

function openFinanceCategoriasPermitidas(): array
{
    return [
        'entrada' => ['Salário', 'Freelance', 'Investimentos', 'Presente', 'Outros'],
        'saida' => ['Alimentação', 'Transporte', 'Moradia', 'Contas', 'Saúde', 'Educação', 'Lazer', 'Outros'],
    ];
}

function openFinanceDataSql(?string $date): string
{
    try {
        $parsed = new DateTime($date ?: 'now');
        $parsed->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        return $parsed->format('Y-m-d H:i:s');
    } catch (Throwable $exception) {
        return date('Y-m-d H:i:s');
    }
}

function openFinanceCategoriaSugerida(array $transaction, string $tipo): string
{
    $texto = mb_strtolower(implode(' ', array_filter([
        $transaction['category'] ?? '',
        $transaction['operationType'] ?? '',
        $transaction['operationTypeAdditionalInfo'] ?? '',
        $transaction['description'] ?? '',
        $transaction['descriptionRaw'] ?? '',
        $transaction['merchant']['category'] ?? '',
    ])), 'UTF-8');
    $textoBusca = strtr($texto, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
    ]);

    if ($tipo === 'entrada') {
        if (preg_match('/salario|folha|pagamento de salario/', $textoBusca)) {
            return 'Salário';
        }
        if (preg_match('/invest|rendimento|resgate|aplica/', $textoBusca)) {
            return 'Investimentos';
        }
        return 'Outros';
    }

    $regras = [
        'Alimentação' => '/aliment|food|restaurant|mercado|supermercado|grocery|padaria|ifood|delivery|lanch/',
        'Transporte' => '/transport|uber|99app|combust|gasoline|posto|estacion|parking|pedagio|passagem|airline/',
        'Moradia' => '/moradia|aluguel|rent|condominio|resid|casa|imobili|habita|housing/',
        'Contas' => '/conta|bill|fatura|boleto|energia|agua|luz|internet|telefone|tarifa|convenio|servico|utility/',
        'Saúde' => '/saude|health|farmacia|pharmacy|medico|hospital|dentista|consulta|plano/',
        'Educação' => '/educa|education|curso|course|faculdade|escola|school|livro|book|treinamento/',
        'Lazer' => '/lazer|leisure|entertain|cinema|stream|netflix|spotify|jogo|game|viagem|hotel|turismo|bar|show|ingresso/',
    ];

    foreach ($regras as $categoria => $regex) {
        if (preg_match($regex, $textoBusca)) {
            return $categoria;
        }
    }

    return 'Outros';
}

function openFinanceTransacaoNormalizada(array $transaction): ?array
{
    $transacaoId = trim((string) ($transaction['id'] ?? ''));
    if ($transacaoId === '') {
        return null;
    }

    $type = strtoupper((string) ($transaction['type'] ?? ''));
    $amount = (float) ($transaction['amount'] ?? 0);
    $tipo = $type === 'CREDIT'
        ? 'entrada'
        : ($type === 'DEBIT' ? 'saida' : ($amount < 0 ? 'saida' : 'entrada'));
    $valor = abs($amount);
    $descricao = trim((string) (
        $transaction['description']
        ?? $transaction['descriptionRaw']
        ?? $transaction['merchant']['name']
        ?? 'Transação bancária'
    ));

    if ($descricao === '') {
        $descricao = 'Transação bancária';
    }

    return [
        'transacao_id' => $transacaoId,
        'account_id' => (string) ($transaction['accountId'] ?? ''),
        'conta_nome' => trim((string) ($transaction['contaNome'] ?? 'Conta conectada')),
        'tipo' => $tipo,
        'status' => strtoupper((string) ($transaction['status'] ?? 'POSTED')),
        'valor' => $valor,
        'descricao' => mb_substr($descricao, 0, 255),
        'categoria_sugerida' => openFinanceCategoriaSugerida($transaction, $tipo),
        'moeda' => (string) ($transaction['currencyCode'] ?? 'BRL'),
        'data_transacao' => openFinanceDataSql((string) ($transaction['date'] ?? 'now')),
        'payload' => json_encode($transaction, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
}

function openFinancePersistirTransacoes(PDO $pdo, int $usuarioId, array $transactions): int
{
    openFinanceEnsureSchema($pdo);

    $sql = <<<SQL
        INSERT INTO open_finance_transacoes
            (usuario_id, transacao_id, account_id, conta_nome, tipo, status, valor,
             descricao, categoria, categoria_sugerida, moeda, data_transacao, payload)
        VALUES
            (:usuario_id, :transacao_id, :account_id, :conta_nome, :tipo, :status, :valor,
             :descricao, :categoria_sugerida, :categoria_sugerida, :moeda, :data_transacao, :payload)
        ON DUPLICATE KEY UPDATE
            account_id = VALUES(account_id),
            conta_nome = VALUES(conta_nome),
            tipo = VALUES(tipo),
            status = VALUES(status),
            valor = VALUES(valor),
            descricao = VALUES(descricao),
            categoria = IF(categoria_origem = 'manual', categoria, VALUES(categoria_sugerida)),
            categoria_sugerida = VALUES(categoria_sugerida),
            moeda = VALUES(moeda),
            data_transacao = VALUES(data_transacao),
            payload = VALUES(payload)
    SQL;
    $stmt = $pdo->prepare($sql);
    $count = 0;

    foreach ($transactions as $transaction) {
        if (!is_array($transaction)) {
            continue;
        }
        $normalized = openFinanceTransacaoNormalizada($transaction);
        if (!$normalized) {
            continue;
        }

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':transacao_id' => $normalized['transacao_id'],
            ':account_id' => $normalized['account_id'] ?: null,
            ':conta_nome' => $normalized['conta_nome'] ?: 'Conta conectada',
            ':tipo' => $normalized['tipo'],
            ':status' => $normalized['status'],
            ':valor' => $normalized['valor'],
            ':descricao' => $normalized['descricao'],
            ':categoria_sugerida' => $normalized['categoria_sugerida'],
            ':moeda' => $normalized['moeda'],
            ':data_transacao' => $normalized['data_transacao'],
            ':payload' => $normalized['payload'],
        ]);
        $count++;
    }

    return $count;
}

function openFinanceSincronizarUsuario(PDO $pdo, int $usuarioId): array
{
    $itemId = getItemIdDoUsuario($pdo, $usuarioId);
    if (!$itemId) {
        return [
            'connected' => false,
            'sincronizado' => 0,
            'avisos' => ['Nenhuma conta bancária conectada.'],
        ];
    }

    $apiKey = pluggyAuth();
    if (!$apiKey) {
        return [
            'connected' => true,
            'sincronizado' => 0,
            'avisos' => ['A integração Pluggy não está configurada. Defina PLUGGY_CLIENT_ID e PLUGGY_CLIENT_SECRET.'],
        ];
    }

    try {
        $dados = pluggyDadosDoItem($itemId, $apiKey);
        $count = openFinancePersistirTransacoes($pdo, $usuarioId, $dados['transacoes'] ?? []);
        return [
            'connected' => true,
            'sincronizado' => $count,
            'avisos' => $dados['avisos'] ?? [],
            'ultima_sincronizacao' => date('c'),
        ];
    } catch (Throwable $exception) {
        error_log('[open-finance/pluggy-helper] ' . $exception->getMessage());
        return [
            'connected' => true,
            'sincronizado' => 0,
            'avisos' => ['Não foi possível sincronizar as transações agora. Os dados salvos anteriormente continuam disponíveis.'],
        ];
    }
}

function openFinanceFormatoData(?string $date): string
{
    if (!$date) {
        return 'Atualizado agora';
    }

    try {
        return (new DateTime($date, new DateTimeZone('America/Sao_Paulo')))
            ->format('d/m/Y');
    } catch (Throwable $exception) {
        return 'Data não informada';
    }
}

?>
