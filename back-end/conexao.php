<?php

require_once __DIR__ . '/seguranca.php';

/**
 * conexao.php
 * ------------------------------------------------------------------
 * Problema: as credenciais do banco estavam fixas diretamente no
 * código-fonte. Mesmo sendo credenciais de desenvolvimento local
 * (root/sem senha, como no XAMPP/Laragon), isso é uma má prática: se o
 * arquivo for versionado ou vazado, ou se em produção alguém apenas
 * copiar este arquivo sem trocar as credenciais, o banco fica exposto.
 *
 * Solução: as credenciais agora vêm de variáveis de ambiente (ou do
 * arquivo .env, já usado pelo restante do projeto para as integrações
 * Pluggy/Google), com os mesmos valores de desenvolvimento como
 * fallback — quem já usa o projeto localmente no XAMPP/Laragon não
 * precisa mudar nada, mas quem for para produção deve definir
 * DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS no .env.
 */

$host = afdeConfig('DB_HOST', '127.0.0.1');
$port = (int) afdeConfig('DB_PORT', '3307'); // porta configurada no XAMPP deste projeto
$db = afdeConfig('DB_NAME', 'educacaofinanceira');
$user = afdeConfig('DB_USER', 'root');
$pass = afdeConfig('DB_PASS', '');

/** Instala o dump principal apenas em um banco vazio; nunca importa por cima de tabelas existentes. */
function afdeInicializarEsquemaVazio(PDO $pdo, string $host, int $port, string $db, string $user, string $pass): void
{
    $tableCount = (int)$pdo->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchColumn();
    if ($tableCount > 0) {
        $hasUsers = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios'")->fetchColumn();
        if ($hasUsers === 0) {
            throw new RuntimeException('O banco selecionado já contém tabelas, mas não possui usuarios; o esquema não foi importado para proteger os dados existentes.');
        }
        return;
    }

    $dumpPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'educacaofinanceira.sql';
    $sql = is_file($dumpPath) ? file_get_contents($dumpPath) : false;
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('O dump principal educacaofinanceira.sql não foi encontrado ou está vazio.');
    }
    if (!class_exists('mysqli')) {
        throw new RuntimeException('A extensão MySQLi do PHP é necessária para inicializar automaticamente o banco vazio.');
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_errno) {
        $message = $mysqli->connect_error;
        $mysqli->close();
        throw new RuntimeException('Não foi possível abrir a conexão de inicialização: ' . $message);
    }
    try {
        $mysqli->set_charset('utf8mb4');
        if (!$mysqli->multi_query($sql)) {
            throw new RuntimeException('Falha ao executar o esquema principal: ' . $mysqli->error);
        }
        do {
            $result = $mysqli->store_result();
            if ($result instanceof mysqli_result) $result->free();
            if (!$mysqli->more_results()) break;
            if (!$mysqli->next_result()) {
                throw new RuntimeException('Falha durante a inicialização do esquema: ' . $mysqli->error);
            }
        } while (true);
    } finally {
        $mysqli->close();
    }

    $hasUsers = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios'")->fetchColumn();
    if ($hasUsers !== 1) {
        throw new RuntimeException('A inicialização terminou sem encontrar a tabela usuarios.');
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    afdeInicializarEsquemaVazio($pdo, $host, $port, $db, $user, $pass);
} catch (PDOException $e) {
    // Problema: mostrar $e->getMessage() ao visitante pode expor host,
    // nome do banco e detalhes de configuração do servidor.
    // Solução: log no servidor + mensagem genérica na tela.
    error_log('[conexao] Falha ao conectar ao banco: ' . $e->getMessage());
    http_response_code(500);
    die("Não foi possível conectar ao banco de dados. Confirme se o MySQL está iniciado e se DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS no .env correspondem ao XAMPP (porta local deste projeto: 3307). Detalhes técnicos foram registrados no log do Apache.");
} catch (Throwable $e) {
    error_log('[conexao] Falha ao inicializar o esquema principal: ' . $e->getMessage());
    http_response_code(500);
    die("O banco contém um esquema vazio ou incompleto e não pôde ser inicializado automaticamente. O dump só é importado quando o banco está totalmente vazio, para preservar dados. Confira o log do Apache.");
}
