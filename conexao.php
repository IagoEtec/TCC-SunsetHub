<?php

require_once __DIR__ . '/back-end/conexao.php';

/**
 * Retorna uma conexão PDO única (reaproveitada em toda a requisição).
 * Se seu projeto já tem uma conexão, troque o conteúdo desta função
 * para devolver a sua.
 */
function afdeDb(): PDO
{
    global $pdo;
    return $pdo;
}
