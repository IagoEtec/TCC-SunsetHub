<?php

/**
 * Carrega as configurações do arquivo .env (na raiz do projeto)
 * e oferece a função afdeConfig() usada pelo restante do código.
 */

function afdeCarregarEnv(): void
{
    static $carregado = false;
    if ($carregado) {
        return;
    }
    $carregado = true;

    // O .env fica na raiz do projeto (uma pasta acima de back-end/).
    $arquivo = dirname(__DIR__) . '/.env';

    if (!is_file($arquivo)) {
        error_log('[seguranca] Arquivo .env não encontrado em ' . $arquivo);
        return;
    }

    foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);

        // Ignora comentários e linhas sem "="
        if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor);

        // Remove aspas ao redor do valor, se houver
        if (strlen($valor) >= 2 && ($valor[0] === '"' || $valor[0] === "'") && substr($valor, -1) === $valor[0]) {
            $valor = substr($valor, 1, -1);
        }

        $_ENV[$chave] = $valor;
    }
}

/**
 * Lê uma configuração. Se não existir (ou estiver vazia), devolve $padrao.
 */
function afdeConfig(string $chave, string $padrao = ''): string
{
    afdeCarregarEnv();

    $valor = $_ENV[$chave] ?? getenv($chave);

    return ($valor === false || $valor === null || $valor === '') ? $padrao : (string) $valor;
}