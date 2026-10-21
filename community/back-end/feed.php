<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

try {
    $user = comunidade_usuario($pdo);
    comunidade_instalar_schema($pdo);
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $posts = $pdo->query("SELECT p.id, p.corpo AS body, p.curtidas AS likes, UNIX_TIMESTAMP(p.criado_em) AS created_ts, u.nome AS name, LOWER(SUBSTRING_INDEX(u.email, '@', 1)) AS username, '#ffad52' AS avatar_color FROM comunidade_publicacoes p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.criado_em DESC, p.id DESC LIMIT 40")->fetchAll(PDO::FETCH_ASSOC);
        $videos = $pdo->query("SELECT v.id, v.titulo AS title, v.descricao AS description, v.miniatura AS thumbnail, v.url, v.visualizacoes AS views, u.nome AS name, LOWER(SUBSTRING_INDEX(u.email, '@', 1)) AS username FROM comunidade_videos v JOIN usuarios u ON u.id = v.usuario_id ORDER BY v.criado_em DESC, v.id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        $groups = $pdo->prepare("SELECT g.id, g.nome AS name, g.descricao AS description, g.categoria AS category, g.cor AS accent, COUNT(m.usuario_id) AS members, MAX(m.usuario_id = ?) AS joined FROM comunidade_grupos g LEFT JOIN comunidade_membros m ON m.grupo_id = g.id GROUP BY g.id ORDER BY members DESC, g.id");
        $groups->execute([$user['id']]);
        $count = $pdo->prepare('SELECT COUNT(*) FROM comunidade_publicacoes WHERE usuario_id = ?');
        $count->execute([$user['id']]);
        comunidade_json(['ok' => true, 'csrf' => csrf_token(), 'user' => $user, 'posts' => $posts, 'videos' => $videos, 'communities' => $groups->fetchAll(PDO::FETCH_ASSOC), 'posts_count' => (int)$count->fetchColumn()]);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: GET, POST');
        comunidade_json(['ok' => false, 'error' => 'Método não permitido.'], 405);
    }
    comunidade_exigir_csrf();
    $data = json_decode((string)file_get_contents('php://input'), true) ?: [];
    $action = (string)($data['action'] ?? 'publish');
    if ($action === 'publish') {
        $body = sunset_conteudo_exigir((string)($data['body'] ?? ''), 'texto da publicação');
        $length = function_exists('mb_strlen') ? mb_strlen($body, 'UTF-8') : strlen($body);
        if ($length < 1 || $length > 1000) comunidade_json(['ok' => false, 'error' => 'Escreva uma publicação de até 1.000 caracteres.'], 422);
        $stmt = $pdo->prepare('INSERT INTO comunidade_publicacoes (usuario_id, corpo) VALUES (?, ?)');
        $stmt->execute([$user['id'], $body]);
        comunidade_json(['ok' => true, 'message' => 'Publicação compartilhada.']);
    }
    if ($action === 'join' || $action === 'leave') {
        $groupId = filter_var($data['community_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$groupId) comunidade_json(['ok' => false, 'error' => 'Comunidade inválida.'], 422);
        if ($action === 'join') {
            $stmt = $pdo->prepare('INSERT IGNORE INTO comunidade_membros (grupo_id, usuario_id) SELECT id, ? FROM comunidade_grupos WHERE id = ?');
            $stmt->execute([$user['id'], $groupId]);
        } else {
            $stmt = $pdo->prepare('DELETE FROM comunidade_membros WHERE grupo_id = ? AND usuario_id = ?');
            $stmt->execute([$groupId, $user['id']]);
        }
        comunidade_json(['ok' => true]);
    }
    if ($action === 'create_video') {
        $title = sunset_conteudo_exigir((string)($data['title'] ?? ''), 'título do vídeo');
        $url = trim((string)($data['url'] ?? ''));
        $description = sunset_conteudo_exigir((string)($data['description'] ?? ''), 'descrição do vídeo');
        if (strlen($title) < 3 || strlen($title) > 160 || strlen($description) > 500 || strlen($url) > 500 || !filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($url, PHP_URL_SCHEME)), ['https', 'http'], true)) {
            comunidade_json(['ok' => false, 'error' => 'Informe um título válido e um link público HTTP ou HTTPS.'], 422);
        }
        $stmt = $pdo->prepare('INSERT INTO comunidade_videos (usuario_id, titulo, descricao, url) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user['id'], $title, $description, $url]);
        comunidade_json(['ok' => true]);
    }
    if ($action === 'create_community') {
        $name = sunset_conteudo_exigir((string)($data['name'] ?? ''), 'nome do grupo');
        $description = sunset_conteudo_exigir((string)($data['description'] ?? ''), 'descrição do grupo');
        if (strlen($name) < 3 || strlen($name) > 100 || strlen($description) < 5 || strlen($description) > 255) comunidade_json(['ok' => false, 'error' => 'Informe um nome (3–100 caracteres) e uma descrição (5–255 caracteres).'], 422);
        try {
            $stmt = $pdo->prepare('INSERT INTO comunidade_grupos (nome, descricao, categoria, criado_por) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $description, 'Comunidade', $user['id']]);
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23000') comunidade_json(['ok' => false, 'error' => 'Já existe uma comunidade com esse nome.'], 409);
            throw $e;
        }
        comunidade_json(['ok' => true]);
    }
    comunidade_json(['ok' => false, 'error' => 'Ação desconhecida.'], 400);
} catch (SunsetConteudoException $e) {
    comunidade_json(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (PDOException $e) {
    tratar_erro_bd($e, 'sunsethub_feed');
    comunidade_json(['ok' => false, 'error' => 'Não foi possível iniciar a comunidade. Verifique se o banco principal existe e se o usuário MySQL pode criar tabelas.'], 500);
} catch (Throwable $e) {
    error_log('[Sunset Hub integrado] ' . $e->getMessage());
    comunidade_json(['ok' => false, 'error' => 'Não foi possível concluir a operação.'], 500);
}
