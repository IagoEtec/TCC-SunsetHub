<?php

/**
 * seguranca.php
 * ------------------------------------------------------------------
 * Funções centrais de segurança reutilizadas em todo o projeto:
 *   - iniciar_sessao_segura()   -> sessão com cookies HttpOnly/SameSite/Secure
 *   - aplicar_headers_seguranca() -> headers HTTP básicos de proteção
 *   - csrf_token()/csrf_campo()/csrf_exigir() -> proteção CSRF
 *   - bruteforce_*()            -> limite de tentativas de login
 *   - tratar_erro_bd()          -> log seguro de exceções (sem vazar detalhes)
 *
 * Este arquivo não altera regra de negócio nenhuma: ele só concentra
 * proteções que antes não existiam (ou existiam de forma incompleta)
 * para que cada página do projeto passe a usá-las com uma única linha.
 */

/* =====================================================================
   CONFIGURAÇÃO / SEGREDOS (.env)
   ===================================================================== */

/**
 * Lê o arquivo .env da raiz do projeto (mesmo arquivo já usado pelas
 * integrações Pluggy/Google). Nunca deve ser commitado; apenas o
 * .env.example (com placeholders) fica no repositório.
 */
function afdeCarregarEnv(): array
{
    static $env = null;
    if ($env === null) {
        $caminho = dirname(__DIR__) . '/.env';
        $env = is_file($caminho)
            ? (parse_ini_file($caminho, false, INI_SCANNER_RAW) ?: [])
            : [];
    }
    return $env;
}

/**
 * Lê uma configuração/segredo, preferindo variável de ambiente real e
 * caindo para o .env, com um valor padrão opcional (usado só para
 * configurações não sensíveis, como host/porta padrão de dev local).
 * Nunca colocamos segredos reais como padrão aqui.
 */
function afdeConfig(string $chave, string $padrao = ''): string
{
    $env = afdeCarregarEnv();
    $valor = getenv($chave);
    if ($valor !== false && $valor !== '') {
        return $valor;
    }
    return $env[$chave] ?? $padrao;
}

/**
 * Inicia a sessão com cookies endurecidos.
 *
 * Problema: sessões com cookies "soltos" podem ser lidas por JavaScript
 * malicioso injetado via XSS (quando não há HttpOnly) ou reenviadas
 * automaticamente em requisições disparadas por outros sites, o que
 * facilita ataques de CSRF (quando não há SameSite).
 *
 * Solução: HttpOnly impede leitura do cookie via JS; SameSite=Lax evita
 * o envio do cookie na maioria das requisições cross-site (mantendo
 * links normais funcionando); Secure é ativado automaticamente quando a
 * conexão já está em HTTPS, sem quebrar o ambiente de desenvolvimento
 * local em HTTP (XAMPP/Laragon), que continua funcionando normalmente.
 */
function iniciar_sessao_segura(): void
{
    // Essas diretivas só podem ser alteradas antes de a sessão existir.
    // Isso evita warnings quando outro include já chamou session_start().
    $sessaoAindaNaoIniciada = session_status() === PHP_SESSION_NONE;
    if ($sessaoAindaNaoIniciada) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
    }

    $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }

    aplicar_headers_seguranca();

    if (afdeConfig('REQUIRE_HTTPS', '1') === '1' && !$https && !in_array(strtolower((string)($_SERVER['HTTP_HOST'] ?? '')), ['localhost', '127.0.0.1', '::1'], true)) {
        $host = preg_replace('/[^a-zA-Z0-9.:-]/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: https://' . $host . $uri, true, 308);
        exit;
    }

    // Timeout por inatividade e renovação periódica do ID.
    $agora = time();
    if (!empty($_SESSION['last_activity']) && ($agora - (int)$_SESSION['last_activity']) > 1800) {
        encerrar_sessao_segura();
        session_start();
    }
    $_SESSION['last_activity'] = $agora;
}

/**
 * Encerra a sessão de forma completa: limpa as variáveis, apaga o
 * cookie no navegador e destrói os dados no servidor.
 *
 * Problema: "session_destroy()" sozinho apaga os dados no servidor mas
 * deixa o cookie antigo no navegador e as variáveis da requisição
 * atual continuam preenchidas, o que pode causar reuso indevido.
 *
 * Solução: limpamos $_SESSION, expiramos o cookie no navegador e só
 * então destruímos a sessão no servidor.
 */
function encerrar_sessao_segura(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();
}

/**
 * Aplica headers HTTP de segurança básicos.
 *
 * Não aplicamos uma Content-Security-Policy restritiva porque o projeto
 * usa vários blocos <script> inline nas páginas (carteira.php,
 * exercicios.php, videoaulas.php, etc.) e depende do CDN do Font
 * Awesome (cdnjs.cloudflare.com); uma CSP mal calibrada quebraria essas
 * páginas silenciosamente. Essa decisão está documentada no relatório
 * final, junto com a recomendação de, no futuro, mover os scripts
 * inline para arquivos .js externos para então habilitar uma CSP mais
 * restritiva com segurança.
 */
function aplicar_headers_seguranca(): void
{
    static $aplicado = false;
    if ($aplicado || headers_sent()) {
        return;
    }
    $aplicado = true;

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: frame-ancestors 'self'; object-src 'none'; base-uri 'self'");
    header('Cache-Control: no-store, max-age=0');

    $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if ($https) {
        // HSTS só faz sentido (e só é seguro) quando a conexão já é HTTPS.
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/* =====================================================================
   CSRF
   ===================================================================== */

/**
 * Retorna o token CSRF da sessão atual, gerando um novo se necessário.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Campo oculto pronto para uso dentro de qualquer <form method="POST">.
 */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Valida (sem interromper a execução) o token CSRF de uma requisição.
 *
 * Problema: sem um token imprevisível ligado à sessão, qualquer site
 * malicioso pode montar um formulário escondido que envia uma
 * requisição para nossos endpoints usando o cookie de sessão já
 * autenticado do usuário (Cross-Site Request Forgery).
 *
 * Solução: comparamos o token da sessão com o token enviado usando
 * hash_equals(), que faz a comparação em tempo constante e evita
 * ataques de timing.
 */
function csrf_validar(): bool
{
    $recebido = $_POST['csrf_token'] ?? '';
    return isset($_SESSION['csrf_token'])
        && is_string($recebido)
        && $recebido !== ''
        && hash_equals($_SESSION['csrf_token'], $recebido);
}

/**
 * Interrompe a requisição com 403 caso o token CSRF seja inválido.
 * Deve ser chamada logo no início de qualquer handler POST sensível.
 */
function csrf_exigir(): void
{
    if (!csrf_validar()) {
        http_response_code(403);
        echo "<script>alert('Sua sessão expirou ou a requisição é inválida. Recarregue a página e tente novamente.'); window.history.back();</script>";
        exit;
    }
}

/**
 * Variante para chamadas via fetch()/JavaScript (sem formulário HTML):
 * o token é enviado no header "X-CSRF-Token" em vez de um campo POST.
 * Útil para endpoints que respondem em JSON.
 */
function csrf_validar_header(): bool
{
    $recebido = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return isset($_SESSION['csrf_token'])
        && is_string($recebido)
        && $recebido !== ''
        && hash_equals($_SESSION['csrf_token'], $recebido);
}

/**
 * Interrompe a requisição (retornando JSON 403) caso o header CSRF seja
 * inválido. Usada em endpoints JSON chamados via fetch().
 */
function csrf_exigir_header(): void
{
    if (!csrf_validar_header()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Requisição inválida ou expirada. Recarregue a página e tente novamente.']);
        exit;
    }
}

/* =====================================================================
   PROTEÇÃO CONTRA FORÇA BRUTA (LOGIN)
   ===================================================================== */

/**
 * Garante que a tabela de controle de tentativas existe (idempotente).
 */
function rate_limit_preparar_tabela(PDO $pdo): void
{
    static $pronto = false;
    if ($pronto) {
        return;
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tentativas_login (
            email VARCHAR(190) NOT NULL,
            ip VARCHAR(45) NOT NULL,
            tentativas INT NOT NULL DEFAULT 0,
            ultima_tentativa DATETIME NULL,
            bloqueado_ate DATETIME NULL,
            PRIMARY KEY (email, ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pronto = true;
}

/**
 * Retorna quantos segundos faltam para o desbloqueio, ou null se a
 * combinação e-mail+IP não estiver bloqueada.
 *
 * Problema: sem limite de tentativas, um script pode testar milhares de
 * senhas por minuto contra uma única conta (força bruta) ou testar uma
 * lista vazada de senhas contra várias contas (credential stuffing).
 *
 * Solução: contamos falhas por e-mail + IP (não só IP, para não travar
 * um usuário legítimo por causa de outra pessoa no mesmo IP; não só
 * e-mail, para não permitir que um estranho bloqueie deliberadamente a
 * conta de outra pessoa disparando falhas de qualquer lugar) e aplicamos
 * um bloqueio temporário que cresce a cada nova falha, até um teto de
 * 15 minutos, liberando sozinho depois desse tempo.
 */
function bruteforce_esta_bloqueado(PDO $pdo, string $email, string $ip): ?int
{
    rate_limit_preparar_tabela($pdo);

    $stmt = $pdo->prepare('SELECT bloqueado_ate FROM tentativas_login WHERE email = ? AND ip = ?');
    $stmt->execute([$email, $ip]);
    $bloqueadoAte = $stmt->fetchColumn();

    if (!$bloqueadoAte) {
        return null;
    }

    $restante = strtotime($bloqueadoAte) - time();
    return $restante > 0 ? $restante : null;
}

/**
 * Registra uma tentativa de login malsucedida e aplica bloqueio
 * progressivo a partir da 5ª falha consecutiva.
 */
function bruteforce_registrar_falha(PDO $pdo, string $email, string $ip): void
{
    rate_limit_preparar_tabela($pdo);

    $stmt = $pdo->prepare('SELECT tentativas, ultima_tentativa FROM tentativas_login WHERE email = ? AND ip = ?');
    $stmt->execute([$email, $ip]);
    $estado = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $ultima = !empty($estado['ultima_tentativa']) ? strtotime($estado['ultima_tentativa']) : 0;
    // O contador expira após 24h; evita bloqueio permanente e overflow.
    $tentativasAtuais = ($ultima && (time() - $ultima) <= 86400) ? (int)($estado['tentativas'] ?? 0) : 0;
    $tentativas = min(20, $tentativasAtuais + 1);

    $bloqueadoAte = null;
    if ($tentativas >= 5) {
        // 5ª falha: 30s; cada falha seguinte dobra o tempo, até 900s (15min).
        $segundos = min(900, 30 * (2 ** ($tentativas - 5)));
        $bloqueadoAte = date('Y-m-d H:i:s', time() + $segundos);
    }

    $upsert = $pdo->prepare(
        'INSERT INTO tentativas_login (email, ip, tentativas, ultima_tentativa, bloqueado_ate)
         VALUES (:email, :ip, :tentativas, NOW(), :bloqueado_ate)
         ON DUPLICATE KEY UPDATE
             tentativas = :tentativas2,
             ultima_tentativa = NOW(),
             bloqueado_ate = :bloqueado_ate2'
    );
    $upsert->execute([
        ':email' => $email,
        ':ip' => $ip,
        ':tentativas' => $tentativas,
        ':bloqueado_ate' => $bloqueadoAte,
        ':tentativas2' => $tentativas,
        ':bloqueado_ate2' => $bloqueadoAte,
    ]);

    $emailLog = preg_replace('/[^a-zA-Z0-9@._+\-]/', '', $email);
    $ipLog = preg_replace('/[^0-9a-fA-F:.]/', '', $ip);
    error_log("[bruteforce] falha de login para email={$emailLog} ip={$ipLog} tentativas={$tentativas}");
}

/**
 * Limpa o contador após um login bem-sucedido.
 */
function bruteforce_registrar_sucesso(PDO $pdo, string $email, string $ip): void
{
    rate_limit_preparar_tabela($pdo);
    $del = $pdo->prepare('DELETE FROM tentativas_login WHERE email = ? AND ip = ?');
    $del->execute([$email, $ip]);
}

/**
 * IP do cliente. Observação: cabeçalhos como X-Forwarded-For podem ser
 * falsificados por quem faz a requisição diretamente ao servidor, então
 * só devem ser usados quando há um proxy/load balancer confiável na
 * frente da aplicação que sobrescreve esse cabeçalho. Por padrão,
 * usamos REMOTE_ADDR (definido pelo próprio servidor, não pelo cliente).
 */
function obter_ip_cliente(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Limite simples de criação de contas por IP (janela de 1 hora). */
function cadastro_rate_limit_excedido(PDO $pdo, string $ip, int $limite = 5): bool
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS tentativas_cadastro (
        ip VARCHAR(45) NOT NULL PRIMARY KEY,
        quantidade INT NOT NULL DEFAULT 0,
        janela_inicio DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $stmt = $pdo->prepare('SELECT quantidade, janela_inicio FROM tentativas_cadastro WHERE ip = ?');
    $stmt->execute([$ip]);
    $estado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$estado || (time() - strtotime($estado['janela_inicio'])) >= 3600) {
        $stmt = $pdo->prepare('REPLACE INTO tentativas_cadastro (ip, quantidade, janela_inicio) VALUES (?, 1, NOW())');
        $stmt->execute([$ip]);
        return false;
    }
    $quantidade = (int)$estado['quantidade'] + 1;
    $stmt = $pdo->prepare('UPDATE tentativas_cadastro SET quantidade = ? WHERE ip = ?');
    $stmt->execute([$quantidade, $ip]);
    return $quantidade > $limite;
}

/* =====================================================================
   TRATAMENTO DE ERROS
   ===================================================================== */

/**
 * Registra a exceção no log do servidor (nunca na tela) e permite
 * mostrar uma mensagem genérica ao usuário. Evita vazar mensagens de
 * erro do banco, caminhos internos ou nomes de tabelas para quem está
 * navegando a aplicação.
 */
function tratar_erro_bd(Throwable $e, string $contexto): void
{
    error_log("[{$contexto}] " . $e->getMessage());
}
