<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

final class SunsetApiException extends RuntimeException { public int $status; public function __construct(string $message, int $status=422){parent::__construct($message);$this->status=$status;} }
function api_fail(string $message, int $status = 422): void { throw new SunsetApiException($message,$status); }
function api_text(string $key, int $max): string {
    $value = trim((string)($_POST[$key] ?? ''));
    if (function_exists('mb_substr')) return mb_substr($value, 0, $max, 'UTF-8');
    return substr($value, 0, $max);
}
function api_length(string $value): int { return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value); }
function api_session_rate_limit(string $key, int $limit, int $window): void {
    $now = time();
    $stored = $_SESSION['_community_rate_limits'] ?? [];
    $all = is_array($stored) ? $stored : [];
    $events = is_array($all[$key] ?? null) ? $all[$key] : [];
    $events = array_values(array_filter($events, static fn($ts) => is_int($ts) && $ts > $now - $window));
    if (count($events) >= $limit) api_fail('Muitas ações em pouco tempo. Aguarde e tente novamente.', 429);
    $events[] = $now;
    $_SESSION['_community_rate_limits'] = [$key => $events] + array_diff_key($all, [$key => true]);
}
/** Atualiza o horário de presença do usuário sem exigir funções externas. */
function api_touch(PDO $pdo, int $uid): void {
    $s = $pdo->prepare('INSERT INTO comunidade_presenca (usuario_id, visto_em) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE visto_em = NOW()');
    $s->execute([$uid]);
}

/** Remove stories expirados e tenta apagar seus arquivos associados. */
function api_cleanup_expired_stories(PDO $pdo): void {
    $s = $pdo->prepare('SELECT caminho FROM comunidade_stories WHERE expira_em <= NOW()');
    $s->execute();
    foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $path) {
        if (is_string($path) && $path !== '') api_delete_stored($path);
    }
    $pdo->exec('DELETE FROM comunidade_stories WHERE expira_em <= NOW()');
}

function api_is_blocked(PDO $pdo, int $a, int $b): bool {
    $s=$pdo->prepare('SELECT 1 FROM comunidade_bloqueios WHERE (bloqueador_id=? AND bloqueado_id=?) OR (bloqueador_id=? AND bloqueado_id=?) LIMIT 1');$s->execute([$a,$b,$b,$a]);return (bool)$s->fetchColumn();
}
function api_require_friend(PDO $pdo, int $a, int $b): void {
    if(api_is_blocked($pdo,$a,$b))api_fail('Não é possível conversar com uma pessoa bloqueada.',403);
    $s=$pdo->prepare('SELECT (SELECT COUNT(*) FROM comunidade_seguidores WHERE seguidor_id=? AND seguido_id=?)+(SELECT COUNT(*) FROM comunidade_seguidores WHERE seguidor_id=? AND seguido_id=?)');$s->execute([$a,$b,$b,$a]);
    if((int)$s->fetchColumn()!==2)api_fail('A mensagem privada só é liberada quando ambos se seguem.',403);
}
function api_filter_blocked(PDO $pdo, int $uid, array $items): array {
    $ids=array_values(array_unique(array_filter(array_map(static fn($item)=>(int)($item['owner_id']??$item['id']??0),$items))));
    if(!$ids)return $items;
    $marks=implode(',',array_fill(0,count($ids),'?'));
    $s=$pdo->prepare("SELECT bloqueador_id,bloqueado_id FROM comunidade_bloqueios WHERE (bloqueador_id=? AND bloqueado_id IN ({$marks})) OR (bloqueado_id=? AND bloqueador_id IN ({$marks}))");
    $s->execute(array_merge([$uid],$ids,[$uid],$ids));$hidden=[];
    foreach($s->fetchAll(PDO::FETCH_ASSOC) as $row){$key=(int)$row['bloqueador_id']===$uid?(int)$row['bloqueado_id']:(int)$row['bloqueador_id'];$hidden[$key]=true;}
    return array_values(array_filter($items,static fn($item)=>!isset($hidden[(int)($item['owner_id']??$item['id']??0)])));
}
function api_report_content(PDO $pdo, int $uid, string $type, int $contentId, string $reason, string $details): array {
    $targets=['profile'=>['usuarios','id'],'post'=>['comunidade_publicacoes','id'],'video'=>['comunidade_videos','id'],'carousel'=>['comunidade_posts_visuais','id'],'reel'=>['comunidade_posts_visuais','id'],'story'=>['comunidade_stories','id'],'comment'=>['comunidade_comentarios_video','id'],'dm'=>['comunidade_mensagens_privadas','id'],'group_message'=>['comunidade_mensagens_grupo','id'],'group'=>['comunidade_grupos','id']];
    if(!isset($targets[$type])||$contentId<1||api_length($reason)<1||api_length($reason)>80||api_length($details)<1||api_length($details)>1000)api_fail('Informe um tipo, motivo e detalhes válidos.');
    [$table,$key]=$targets[$type];$extra=$type==='story'?' AND expira_em>NOW()':'';$s=$pdo->prepare("SELECT {$key} FROM {$table} WHERE {$key}=?{$extra} LIMIT 1");$s->execute([$contentId]);if(!$s->fetchColumn())api_fail('Conteúdo não encontrado.',404);
    try{$s=$pdo->prepare('INSERT INTO comunidade_denuncias(usuario_id,conteudo_tipo,conteudo_id,motivo,detalhes) VALUES(?,?,?,?,?)');$s->execute([$uid,$type,$contentId,$reason,$details]);}catch(PDOException $e){if((string)$e->getCode()==='23000')api_fail('Você já denunciou este conteúdo.',409);throw $e;}
    $s=$pdo->prepare('SELECT COUNT(*) FROM comunidade_denuncias WHERE conteudo_tipo=? AND conteudo_id=?');$s->execute([$type,$contentId]);$count=(int)$s->fetchColumn();$threshold=3;$status='pending';
    if($count>=$threshold&&$type!=='profile'){$status=$type==='comment'?'removed':'hidden';$s=$pdo->prepare("UPDATE {$table} SET moderacao_status=? WHERE {$key}=?");$s->execute([$status,$contentId]);$s=$pdo->prepare('UPDATE comunidade_denuncias SET status=? WHERE conteudo_tipo=? AND conteudo_id=? AND status=\'pending\'');$s->execute([$status,$type,$contentId]);}
    return ['count'=>$count,'threshold'=>$threshold,'status'=>$status];
}
function api_is_admin(PDO $pdo, int $uid): bool { $s=$pdo->prepare("SELECT 1 FROM usuarios WHERE id=? AND tipo='admin'");$s->execute([$uid]);return (bool)$s->fetchColumn(); }
function api_filter_moderated(array $items): array { return array_values(array_filter($items,static fn($item)=>!in_array((string)($item['moderation_status']??'visible'),['hidden','removed'],true))); }
function api_notify_friends(PDO $pdo, int $uid, string $type, int $ref, string $message): void {
    $s=$pdo->prepare('SELECT seguidor_id FROM comunidade_seguidores WHERE seguido_id=?');$s->execute([$uid]);foreach($s->fetchAll(PDO::FETCH_COLUMN) as $follower)if(!api_is_blocked($pdo,$uid,(int)$follower))api_notify($pdo,(int)$follower,$type,$uid,$ref,$message);
}
function api_friend_lists(PDO $pdo, int $uid): array {
    $sql="SELECT u.id,u.nome AS name,p.visto_em AS last_seen,(SELECT m.corpo FROM comunidade_mensagens_privadas m WHERE (m.remetente_id=? AND m.destinatario_id=u.id) OR (m.remetente_id=u.id AND m.destinatario_id=?) ORDER BY m.id DESC LIMIT 1) AS last_message,(SELECT m.criado_em FROM comunidade_mensagens_privadas m WHERE (m.remetente_id=? AND m.destinatario_id=u.id) OR (m.remetente_id=u.id AND m.destinatario_id=?) ORDER BY m.id DESC LIMIT 1) AS last_at FROM comunidade_seguidores f JOIN comunidade_seguidores back ON back.seguidor_id=f.seguido_id AND back.seguido_id=f.seguidor_id JOIN usuarios u ON u.id=f.seguido_id LEFT JOIN comunidade_presenca p ON p.usuario_id=u.id WHERE f.seguidor_id=? AND NOT EXISTS (SELECT 1 FROM comunidade_bloqueios b WHERE (b.bloqueador_id=? AND b.bloqueado_id=u.id) OR (b.bloqueador_id=u.id AND b.bloqueado_id=?)) ORDER BY last_at DESC,u.nome";$s=$pdo->prepare($sql);$s->execute([$uid,$uid,$uid,$uid,$uid,$uid,$uid]);$friends=$s->fetchAll(PDO::FETCH_ASSOC);foreach($friends as &$f)$f['online']=!empty($f['last_seen'])&&strtotime((string)$f['last_seen'])>=time()-300;unset($f);
    $s=$pdo->prepare("SELECT u.id,u.nome AS name,f.criado_em FROM comunidade_seguidores f JOIN usuarios u ON u.id=f.seguidor_id WHERE f.seguido_id=? AND f.seguidor_id<>? AND NOT EXISTS (SELECT 1 FROM comunidade_seguidores back WHERE back.seguidor_id=? AND back.seguido_id=f.seguidor_id) AND NOT EXISTS (SELECT 1 FROM comunidade_bloqueios b WHERE (b.bloqueador_id=? AND b.bloqueado_id=u.id) OR (b.bloqueador_id=u.id AND b.bloqueado_id=?)) ORDER BY f.criado_em DESC");$s->execute([$uid,$uid,$uid,$uid,$uid]);$incoming=$s->fetchAll(PDO::FETCH_ASSOC);
    $s=$pdo->prepare("SELECT u.id,u.nome AS name,f.criado_em FROM comunidade_seguidores f JOIN usuarios u ON u.id=f.seguido_id WHERE f.seguidor_id=? AND NOT EXISTS (SELECT 1 FROM comunidade_seguidores back WHERE back.seguidor_id=f.seguido_id AND back.seguido_id=?) AND NOT EXISTS (SELECT 1 FROM comunidade_bloqueios b WHERE (b.bloqueador_id=? AND b.bloqueado_id=u.id) OR (b.bloqueador_id=u.id AND b.bloqueado_id=?)) ORDER BY f.criado_em DESC");$s->execute([$uid,$uid,$uid,$uid]);$outgoing=$s->fetchAll(PDO::FETCH_ASSOC);return [$friends,$incoming,$outgoing];
}
function api_group_admin(PDO $pdo, int $gid, int $uid): array {
    $s = $pdo->prepare("SELECT id, nome AS name, descricao AS description, criado_por AS admin_id FROM comunidade_grupos WHERE id=? AND moderacao_status='visible'");
    $s->execute([$gid]); $g = $s->fetch(PDO::FETCH_ASSOC);
    if (!$g) api_fail('Grupo não encontrado.', 404);
    $s = $pdo->prepare('SELECT 1 FROM comunidade_membros WHERE grupo_id=? AND usuario_id=?');
    $s->execute([$gid,$uid]);
    if (!$s->fetchColumn()) api_fail('Você precisa participar do grupo para acessar essa sala.', 403);
    $g['is_admin'] = (int)$g['admin_id'] === $uid;
    return $g;
}
function api_group_messages(PDO $pdo, int $uid, string $where, array $params, int $after): array {
    $sql="SELECT m.id,m.usuario_id AS owner_id,m.corpo AS body,m.anexo_path AS attachment_path,m.moderacao_status AS moderation_status,UNIX_TIMESTAMP(m.criado_em) AS created_ts,u.nome AS name,IF(g.criado_por=u.id,1,0) AS is_admin FROM comunidade_mensagens_grupo m JOIN usuarios u ON u.id=m.usuario_id JOIN comunidade_grupos g ON g.id=m.grupo_id {$where}";
    if($after>0){$sql.=' AND m.id>?';$params[]=$after;}
    $sql.=' ORDER BY m.id '.($after>0?'ASC':'DESC').' LIMIT 100';
    $s=$pdo->prepare($sql);$s->execute($params);$items=$s->fetchAll(PDO::FETCH_ASSOC);
    $items=api_filter_moderated(api_filter_blocked($pdo,$uid,$items));return $after>0?$items:array_reverse($items);
}
function api_feed_media(PDO $pdo, int $uid, int $videoOffset, int $reelOffset, int $carouselOffset): void {
    $videoOffset=max(0,min($videoOffset,100000));$reelOffset=max(0,min($reelOffset,100000));$carouselOffset=max(0,min($carouselOffset,100000));
    $videoLimit=8;$visualLimit=5;
    $s=$pdo->prepare("SELECT v.id,v.usuario_id AS owner_id,v.titulo AS title,v.descricao AS description,v.localizacao AS location,v.url AS file_path,v.miniatura AS thumbnail_path,v.visualizacoes AS views,v.moderacao_status AS moderation_status,UNIX_TIMESTAMP(v.criado_em) AS created_ts,u.nome AS name,u.foto AS avatar,(SELECT COUNT(*) FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.reacao=1) AS likes,(SELECT COUNT(*) FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.reacao=-1) AS dislikes,COALESCE((SELECT r.reacao FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.usuario_id=?),0) AS my_reaction,(SELECT COUNT(*) FROM comunidade_comentarios_video c WHERE c.video_id=v.id) AS comments FROM comunidade_videos v JOIN usuarios u ON u.id=v.usuario_id ORDER BY v.criado_em DESC,v.id DESC LIMIT {$videoLimit} OFFSET {$videoOffset}");$s->execute([$uid]);$videos=api_filter_moderated(api_filter_blocked($pdo,$uid,$s->fetchAll(PDO::FETCH_ASSOC)));
    $loadVisual=static function(string $type,int $offset) use($pdo,$visualLimit): array {$s=$pdo->prepare("SELECT p.id,p.usuario_id AS owner_id,p.tipo AS type,p.legenda AS caption,p.localizacao AS location,p.moderacao_status AS moderation_status,UNIX_TIMESTAMP(p.criado_em) AS created_ts,u.nome AS name,u.foto AS avatar FROM comunidade_posts_visuais p JOIN usuarios u ON u.id=p.usuario_id WHERE p.tipo=? ORDER BY p.criado_em DESC,p.id DESC LIMIT {$visualLimit} OFFSET {$offset}");$s->execute([$type]);$items=api_filter_moderated($s->fetchAll(PDO::FETCH_ASSOC));if(!$items)return []; $ids=array_map('intval',array_column($items,'id'));$marks=implode(',',array_fill(0,count($ids),'?'));$m=$pdo->prepare("SELECT post_id,tipo AS type,caminho AS path,posicao AS position FROM comunidade_midias_visuais WHERE post_id IN ({$marks}) ORDER BY post_id,posicao,id");$m->execute($ids);$by=[];foreach($m->fetchAll(PDO::FETCH_ASSOC) as $media)$by[(int)$media['post_id']][]=$media;foreach($items as &$item)$item['media']=$by[(int)$item['id']]??[];unset($item);return $items;};
    $reels=api_filter_blocked($pdo,$uid,$loadVisual('short',$reelOffset));$carousels=api_filter_blocked($pdo,$uid,$loadVisual('carousel',$carouselOffset));comunidade_json(['ok'=>true,'videos'=>$videos,'reels'=>$reels,'carousels'=>$carousels,'has_more_videos'=>count($videos)===$videoLimit,'has_more_reels'=>count($reels)===$visualLimit,'has_more_carousels'=>count($carousels)===$visualLimit]);
}
function api_initial(PDO $pdo, array $user): void {
    $uid=(int)$user['id']; api_touch($pdo,$uid); api_cleanup_expired_stories($pdo);
    $posts=$pdo->query("SELECT p.id,p.usuario_id AS owner_id,p.corpo AS body,p.localizacao AS location,p.curtidas AS likes,p.moderacao_status AS moderation_status,UNIX_TIMESTAMP(p.criado_em) AS created_ts,u.nome AS name,u.foto AS avatar,LOWER(REPLACE(u.nome,' ','')) AS username FROM comunidade_publicacoes p JOIN usuarios u ON u.id=p.usuario_id ORDER BY p.criado_em DESC,p.id DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);$posts=api_filter_moderated(api_filter_blocked($pdo,$uid,$posts));
    $s=$pdo->prepare("SELECT v.id,v.usuario_id AS owner_id,v.titulo AS title,v.descricao AS description,v.localizacao AS location,v.url AS file_path,v.miniatura AS thumbnail_path,v.visualizacoes AS views,v.moderacao_status AS moderation_status,UNIX_TIMESTAMP(v.criado_em) AS created_ts,u.nome AS name,u.foto AS avatar,
        (SELECT COUNT(*) FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.reacao=1) AS likes,
        (SELECT COUNT(*) FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.reacao=-1) AS dislikes,
        COALESCE((SELECT r.reacao FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.usuario_id=?) ,0) AS my_reaction,
        (SELECT COUNT(*) FROM comunidade_comentarios_video c WHERE c.video_id=v.id) AS comments
        FROM comunidade_videos v JOIN usuarios u ON u.id=v.usuario_id ORDER BY v.criado_em DESC,v.id DESC LIMIT 40");
    $s->execute([$uid]); $videos=api_filter_moderated(api_filter_blocked($pdo,$uid,$s->fetchAll(PDO::FETCH_ASSOC)));
    $visual=$pdo->query("SELECT p.id,p.usuario_id AS owner_id,p.tipo AS type,p.legenda AS caption,p.localizacao AS location,p.moderacao_status AS moderation_status,UNIX_TIMESTAMP(p.criado_em) AS created_ts,u.nome AS name,u.foto AS avatar FROM comunidade_posts_visuais p JOIN usuarios u ON u.id=p.usuario_id ORDER BY p.criado_em DESC,p.id DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);$visual=api_filter_moderated(api_filter_blocked($pdo,$uid,$visual));
    if ($visual) {
        $ids=array_map('intval',array_column($visual,'id')); $marks=implode(',',array_fill(0,count($ids),'?'));
        $m=$pdo->prepare("SELECT post_id, tipo AS type, caminho AS path, posicao AS position FROM comunidade_midias_visuais WHERE post_id IN ({$marks}) ORDER BY post_id,posicao,id"); $m->execute($ids); $by=[];
        foreach($m->fetchAll(PDO::FETCH_ASSOC) as $item) $by[(int)$item['post_id']][]=$item;
        foreach($visual as &$post) $post['media']=$by[(int)$post['id']]??[]; unset($post);
    }
    $groups=$pdo->prepare("SELECT g.id,g.nome AS name,g.descricao AS description,g.categoria AS category,g.cor AS accent,g.criado_por AS admin_id,g.moderacao_status AS moderation_status,gi.caminho AS image_path,(SELECT COUNT(*) FROM comunidade_membros mc WHERE mc.grupo_id=g.id) AS members,(SELECT COUNT(*) FROM comunidade_membros mj WHERE mj.grupo_id=g.id AND mj.usuario_id=?) AS joined FROM comunidade_grupos g LEFT JOIN comunidade_grupo_imagens gi ON gi.grupo_id=g.id WHERE g.moderacao_status='visible' ORDER BY joined DESC,members DESC,g.nome");
    $groups->execute([$uid]);
    $stories=$pdo->query("SELECT s.id,s.usuario_id AS owner_id,s.tipo AS type,s.caminho AS path,s.legenda AS caption,s.localizacao AS location,s.moderacao_status AS moderation_status,s.criado_em AS created_at,UNIX_TIMESTAMP(s.criado_em) AS created_ts,UNIX_TIMESTAMP(s.expira_em) AS expires_ts,u.nome AS name,u.foto AS avatar FROM comunidade_stories s JOIN usuarios u ON u.id=s.usuario_id WHERE s.expira_em>NOW() AND s.moderacao_status='visible' ORDER BY s.criado_em DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);$stories=api_filter_blocked($pdo,$uid,$stories);
    [$friends,$incoming,$outgoing]=api_friend_lists($pdo,$uid);
    $blockedStmt=$pdo->prepare('SELECT u.id,u.nome AS name FROM comunidade_bloqueios b JOIN usuarios u ON u.id=b.bloqueado_id WHERE b.bloqueador_id=? ORDER BY u.nome');$blockedStmt->execute([$uid]);$blocked=$blockedStmt->fetchAll(PDO::FETCH_ASSOC);
    $n=$pdo->prepare('SELECT COUNT(*) FROM comunidade_notificacoes WHERE usuario_id=? AND lida_em IS NULL');$n->execute([$uid]);
    comunidade_json(['ok'=>true,'csrf'=>csrf_token(),'user'=>$user,'posts'=>$posts,'videos'=>$videos,'visual_posts'=>$visual,'stories'=>$stories,'groups'=>$groups->fetchAll(PDO::FETCH_ASSOC),'friends'=>$friends,'incoming'=>$incoming,'outgoing'=>$outgoing,'blocked'=>$blocked,'unread_count'=>(int)$n->fetchColumn()]);
}

try {
    $user=comunidade_usuario($pdo); comunidade_instalar_schema($pdo); $uid=(int)$user['id'];
    if ($_SERVER['REQUEST_METHOD']==='GET') {
        $action=(string)($_GET['action']??'initial');
        comunidade_exigir_termos($pdo,$uid);
        if ($action==='search_users') api_session_rate_limit('search_users',30,60);
        elseif ($action==='messages') api_session_rate_limit('messages',180,60);
        elseif ($action==='group') api_session_rate_limit('group',180,60);
        elseif ($action==='notifications') api_session_rate_limit('notifications',90,60);
        elseif ($action==='video_comments') api_session_rate_limit('video_comments',60,60);
        elseif ($action==='feed_media') api_session_rate_limit('feed_media',90,60);
        if ($action==='initial') api_initial($pdo,$user);
        if ($action==='feed_media') api_feed_media($pdo,$uid,(int)($_GET['video_offset']??0),(int)($_GET['reel_offset']??0),(int)($_GET['carousel_offset']??0));
        if ($action==='search_users') {
            $q=trim((string)($_GET['q']??'')); if(api_length($q)<2) comunidade_json(['ok'=>true,'users'=>[]]); if(api_length($q)>80) api_fail('A busca aceita até 80 caracteres.');
            $escaped=strtr($q,['!'=>'!!','%'=>'!%','_'=>'!_']);
            $s=$pdo->prepare("SELECT u.id,u.nome AS name,CASE WHEN EXISTS(SELECT 1 FROM comunidade_seguidores f WHERE f.seguidor_id=? AND f.seguido_id=u.id) AND EXISTS(SELECT 1 FROM comunidade_seguidores f WHERE f.seguidor_id=u.id AND f.seguido_id=?) THEN 'mutual' WHEN EXISTS(SELECT 1 FROM comunidade_seguidores f WHERE f.seguidor_id=? AND f.seguido_id=u.id) THEN 'following' WHEN EXISTS(SELECT 1 FROM comunidade_seguidores f WHERE f.seguidor_id=u.id AND f.seguido_id=?) THEN 'incoming' ELSE 'none' END AS relation FROM usuarios u WHERE u.id<>? AND NOT EXISTS (SELECT 1 FROM comunidade_bloqueios b WHERE (b.bloqueador_id=? AND b.bloqueado_id=u.id) OR (b.bloqueador_id=u.id AND b.bloqueado_id=?)) AND u.nome LIKE ? ESCAPE '!' ORDER BY u.nome LIMIT 20");
            $s->execute([$uid,$uid,$uid,$uid,$uid,$uid,$uid,'%'.$escaped.'%']); $users=$s->fetchAll(PDO::FETCH_ASSOC);
            comunidade_json(['ok'=>true,'users'=>$users]);
        }
        if ($action==='moderation_queue') {
            if(!api_is_admin($pdo,$uid))api_fail('Acesso restrito à moderação.',403);
            $s=$pdo->query("SELECT d.id,d.conteudo_tipo AS content_type,d.conteudo_id AS content_id,d.motivo AS reason,d.detalhes AS details,d.status,d.criado_em AS created_at,u.nome AS reporter FROM comunidade_denuncias d JOIN usuarios u ON u.id=d.usuario_id WHERE d.status='pending' ORDER BY d.criado_em ASC,d.id ASC LIMIT 200");
            comunidade_json(['ok'=>true,'items'=>$s->fetchAll(PDO::FETCH_ASSOC)]);
        }
        if ($action==='notifications') {
            $s=$pdo->prepare('SELECT n.id,n.tipo AS type,n.referencia_id AS ref_id,n.mensagem AS message,n.lida_em AS read_at,UNIX_TIMESTAMP(n.criado_em) AS created_ts,n.ator_id AS actor_id,COALESCE(u.nome,\'Uma pessoa\') AS actor_name FROM comunidade_notificacoes n LEFT JOIN usuarios u ON u.id=n.ator_id WHERE n.usuario_id=? ORDER BY n.id DESC LIMIT 40');
            $s->execute([$uid]);$items=$s->fetchAll(PDO::FETCH_ASSOC);
            $q=$pdo->prepare('SELECT COUNT(*) FROM comunidade_notificacoes WHERE usuario_id=? AND lida_em IS NULL');$q->execute([$uid]);
            comunidade_json(['ok'=>true,'items'=>$items,'unread_count'=>(int)$q->fetchColumn()]);
        }
        if ($action==='messages') {
            $other=filter_var($_GET['user_id']??null,FILTER_VALIDATE_INT); if(!$other) api_fail('Pessoa inválida.'); api_require_friend($pdo,$uid,(int)$other);
            $after=max(0,(int)($_GET['after']??0));
            if($after>0){$s=$pdo->prepare("SELECT m.id,m.remetente_id AS sender_id,m.destinatario_id AS recipient_id,m.corpo AS body,m.anexo_path AS attachment_path,m.moderacao_status AS moderation_status,UNIX_TIMESTAMP(m.criado_em) AS created_ts FROM comunidade_mensagens_privadas m WHERE ((m.remetente_id=? AND m.destinatario_id=?) OR (m.remetente_id=? AND m.destinatario_id=?)) AND m.id>? ORDER BY m.id LIMIT 100");$s->execute([$uid,$other,$other,$uid,$after]);$items=api_filter_moderated($s->fetchAll(PDO::FETCH_ASSOC));}
            else{$s=$pdo->prepare("SELECT m.id,m.remetente_id AS sender_id,m.destinatario_id AS recipient_id,m.corpo AS body,m.anexo_path AS attachment_path,m.moderacao_status AS moderation_status,UNIX_TIMESTAMP(m.criado_em) AS created_ts FROM comunidade_mensagens_privadas m WHERE (m.remetente_id=? AND m.destinatario_id=?) OR (m.remetente_id=? AND m.destinatario_id=?) ORDER BY m.id DESC LIMIT 100");$s->execute([$uid,$other,$other,$uid]);$items=array_reverse(api_filter_moderated($s->fetchAll(PDO::FETCH_ASSOC)));}
            $s=$pdo->prepare('UPDATE comunidade_mensagens_privadas SET lida_em=NOW() WHERE remetente_id=? AND destinatario_id=? AND lida_em IS NULL');$s->execute([$other,$uid]);
            comunidade_json(['ok'=>true,'messages'=>$items]);
        }
        if ($action==='video_comments') {
            $id=filter_var($_GET['video_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Vídeo inválido.');
            $s=$pdo->prepare("SELECT c.id,c.usuario_id AS owner_id,c.corpo AS body,c.moderacao_status AS moderation_status,UNIX_TIMESTAMP(c.criado_em) AS created_ts,u.nome AS name FROM comunidade_comentarios_video c JOIN usuarios u ON u.id=c.usuario_id WHERE c.video_id=? AND c.moderacao_status='visible' ORDER BY c.id DESC LIMIT 100");$s->execute([$id]);
            comunidade_json(['ok'=>true,'comments'=>array_reverse(api_filter_blocked($pdo,$uid,$s->fetchAll(PDO::FETCH_ASSOC)))]);
        }
        if ($action==='group') {
            $gid=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);if(!$gid)api_fail('Grupo inválido.');$group=api_group_admin($pdo,(int)$gid,$uid);api_touch($pdo,$uid);
            $s=$pdo->prepare('SELECT gi.caminho AS image_path FROM comunidade_grupo_imagens gi WHERE gi.grupo_id=?');$s->execute([$gid]);$group['image_path']=$s->fetchColumn()?:null;
            $s=$pdo->prepare("SELECT u.id,u.nome AS name,p.visto_em AS last_seen,IF(g.criado_por=u.id,1,0) AS is_admin FROM comunidade_membros m JOIN usuarios u ON u.id=m.usuario_id JOIN comunidade_grupos g ON g.id=m.grupo_id LEFT JOIN comunidade_presenca p ON p.usuario_id=u.id WHERE m.grupo_id=? ORDER BY is_admin DESC,u.nome");$s->execute([$gid]);$members=$s->fetchAll(PDO::FETCH_ASSOC);foreach($members as &$m)$m['online']=!empty($m['last_seen'])&&strtotime((string)$m['last_seen'])>=time()-300;unset($m);
            $tabs=[['key'=>'chat','title'=>'Bate-papo','type'=>'chat'],['key'=>'calls','title'=>'Videochamadas','type'=>'call'],['key'=>'announcements','title'=>'Avisos','type'=>'announcements']];
            $s=$pdo->prepare('SELECT id,titulo AS title,tipo AS type FROM comunidade_abas_grupo WHERE grupo_id=? ORDER BY id');$s->execute([$gid]);foreach($s->fetchAll(PDO::FETCH_ASSOC) as $t){$t['key']='custom-'.$t['id'];$tabs[]=$t;}
            $tab=(string)($_GET['tab']??'chat');$after=max(0,(int)($_GET['after']??0));$messages=[];$selected=null;
            if($tab==='chat'||$tab==='announcements')$messages=api_group_messages($pdo,$uid,'WHERE m.grupo_id=? AND m.canal=? AND m.aba_id IS NULL',[$gid,$tab],$after);
            elseif(preg_match('/^custom-(\d+)$/',$tab,$matches)){$tabId=(int)$matches[1];$s=$pdo->prepare('SELECT id,titulo AS title,tipo AS type FROM comunidade_abas_grupo WHERE id=? AND grupo_id=?');$s->execute([$tabId,$gid]);$selected=$s->fetch(PDO::FETCH_ASSOC);if(!$selected)api_fail('Esta sala não existe.',404);if($selected['type']!=='call'){$canal=$selected['type']==='announcements'?'announcements':'chat';$messages=api_group_messages($pdo,$uid,'WHERE m.grupo_id=? AND m.aba_id=? AND m.canal=?',[$gid,$tabId,$canal],$after);}}
            elseif($tab!=='calls')api_fail('Aba inválida.');
            comunidade_json(['ok'=>true,'group'=>$group,'members'=>$members,'tabs'=>$tabs,'tab'=>$tab,'selected_tab'=>$selected,'messages'=>$messages]);
        }
        api_fail('Ação desconhecida.',404);
    }
    if ($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: GET, POST');api_fail('Método não permitido.',405);}
    comunidade_exigir_csrf(); api_touch($pdo,$uid);
    $json=[];if(stripos((string)($_SERVER['CONTENT_TYPE']??''),'application/json')!==false){$json=json_decode((string)file_get_contents('php://input'),true)?:[];}
    $action=(string)($json['action']??$_POST['action']??'');
    if($action==='accept_terms'){comunidade_registrar_aceite($pdo,$uid);comunidade_json(['ok'=>true,'message'=>'Termos aceitos.']);}
    comunidade_exigir_termos($pdo,$uid);
    if($action===''&&(int)($_SERVER['CONTENT_LENGTH']??0)>0&&empty($_POST)&&empty($_FILES))api_fail('O arquivo ultrapassou post_max_size do PHP. Aumente post_max_size e upload_max_filesize no php.ini e reinicie o Apache.',413);
    api_session_rate_limit('post_all',120,60);
    $actionLimits=['send_message'=>[60,60],'group_message'=>[60,60],'publish'=>[20,60],'comment_video'=>[30,60],'react_video'=>[60,60],'view_video'=>[120,60],'search_friend_action'=>[30,3600],'create_video'=>[5,3600],'create_visual_post'=>[20,3600],'create_story'=>[30,3600],'report_story'=>[10,3600],'report_content'=>[20,3600],'create_group'=>[5,3600],'create_group_tab'=>[20,3600],'delete_video_comment'=>[30,60]];
    if(isset($actionLimits[$action]))api_session_rate_limit('post_'.$action,$actionLimits[$action][0],$actionLimits[$action][1]);
    if($action==='heartbeat')comunidade_json(['ok'=>true]);
    if($action==='mark_notifications_read'){$id=filter_var($json['id']??$_POST['id']??null,FILTER_VALIDATE_INT);if($id){$s=$pdo->prepare('UPDATE comunidade_notificacoes SET lida_em=NOW() WHERE usuario_id=? AND id=? AND lida_em IS NULL');$s->execute([$uid,$id]);}else{$s=$pdo->prepare('UPDATE comunidade_notificacoes SET lida_em=NOW() WHERE usuario_id=? AND lida_em IS NULL');$s->execute([$uid]);}comunidade_json(['ok'=>true]);}
    if($action==='publish'){$body=sunset_conteudo_exigir((string)($json['body']??''),'texto da publicação');$location=sunset_conteudo_exigir((string)($json['location']??''),'localização');if(api_length($body)<1||api_length($body)>1000||api_length($location)>160)api_fail('Escreva uma publicação de até 1.000 caracteres e use um local válido.');$s=$pdo->prepare('INSERT INTO comunidade_publicacoes(usuario_id,corpo,localizacao) VALUES(?,?,?)');$s->execute([$uid,$body,$location?:null]);api_notify_friends($pdo,$uid,'new_post',(int)$pdo->lastInsertId(),'publicou uma nova publicação.');comunidade_json(['ok'=>true]);}
    if($action==='search_friend_action'){
        $target=filter_var($json['user_id']??null,FILTER_VALIDATE_INT);$op=(string)($json['operation']??'');if(!$target||$target===$uid)api_fail('Pessoa inválida.');
        $u=$pdo->prepare('SELECT id,nome FROM usuarios WHERE id=?');$u->execute([$target]);$person=$u->fetch(PDO::FETCH_ASSOC);if(!$person)api_fail('Usuário não encontrado.',404);
        if($op==='block'){$s=$pdo->prepare('INSERT IGNORE INTO comunidade_bloqueios(bloqueador_id,bloqueado_id) VALUES(?,?)');$s->execute([$uid,$target]);$s=$pdo->prepare('DELETE FROM comunidade_seguidores WHERE (seguidor_id=? AND seguido_id=?) OR (seguidor_id=? AND seguido_id=?)');$s->execute([$uid,$target,$target,$uid]);comunidade_json(['ok'=>true,'message'=>'Perfil bloqueado.']);}
        if($op==='unblock'){$s=$pdo->prepare('DELETE FROM comunidade_bloqueios WHERE bloqueador_id=? AND bloqueado_id=?');$s->execute([$uid,$target]);comunidade_json(['ok'=>true,'message'=>'Perfil desbloqueado.']);}
        if(api_is_blocked($pdo,$uid,$target))api_fail('Esse perfil está bloqueado.',403);
        if($op==='request'||$op==='accept'){$s=$pdo->prepare('INSERT IGNORE INTO comunidade_seguidores(seguidor_id,seguido_id) VALUES(?,?)');$s->execute([$uid,$target]);if($s->rowCount())api_notify($pdo,$target,'followed_you',$uid,null,$op==='accept'?'seguiu você de volta.':'começou a seguir você.');comunidade_json(['ok'=>true,'message'=>$op==='accept'?'Você seguiu de volta.':'Perfil seguido.']);}
        if($op==='cancel'||$op==='remove'){$s=$pdo->prepare('DELETE FROM comunidade_seguidores WHERE seguidor_id=? AND seguido_id=?');$s->execute([$uid,$target]);comunidade_json(['ok'=>true,'message'=>'Você deixou de seguir este perfil.']);}
        if($op==='decline'){$s=$pdo->prepare('DELETE FROM comunidade_seguidores WHERE seguidor_id=? AND seguido_id=?');$s->execute([$target,$uid]);comunidade_json(['ok'=>true]);}
        api_fail('Ação social inválida.');
    }
    if($action==='send_message'){$data=$json+$_POST;$other=filter_var($data['user_id']??null,FILTER_VALIDATE_INT);$body=sunset_conteudo_exigir((string)($data['body']??''),'mensagem privada');$file=api_file_field('image');$hasImage=!empty($file['tmp_name'])&&($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_OK;if(!$other||(!$body&&!$hasImage)||api_length($body)>4000)api_fail('Escreva uma mensagem ou selecione uma imagem; o texto aceita até 4.000 caracteres.');api_require_friend($pdo,$uid,(int)$other);$image=null;try{if($hasImage)$image=api_store_upload($file,'chat','image');$s=$pdo->prepare('INSERT INTO comunidade_mensagens_privadas(remetente_id,destinatario_id,corpo,anexo_path) VALUES(?,?,?,?)');$s->execute([$uid,$other,$body,$image]);}catch(Throwable $e){api_delete_stored($image);throw $e;}comunidade_json(['ok'=>true,'id'=>(int)$pdo->lastInsertId()]);}
    if($action==='create_video'){$title=sunset_conteudo_exigir((string)($_POST['title']??''),'título do vídeo');$desc=sunset_conteudo_exigir((string)($_POST['description']??''),'descrição do vídeo');$location=sunset_conteudo_exigir((string)($_POST['location']??''),'localização');if(api_length($title)<3||api_length($title)>160||api_length($desc)>500||api_length($location)>160)api_fail('Informe título, descrição e local válidos.');$video=$thumb=null;try{$video=api_store_upload(api_file_field('video'),'videos','video');$thumb=api_store_upload(api_file_field('thumbnail'),'thumbs','image');$pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO comunidade_videos(usuario_id,titulo,descricao,url,miniatura,localizacao) VALUES(?,?,?,?,?,?)');$s->execute([$uid,$title,$desc,$video,$thumb,$location?:null]);$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();api_delete_stored($video);api_delete_stored($thumb);throw $e;}api_notify_friends($pdo,$uid,'new_video',(int)$pdo->lastInsertId(),'publicou um vídeo novo.');comunidade_json(['ok'=>true,'message'=>'Vídeo publicado.']);}
    if($action==='view_video'){$id=filter_var($json['video_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Vídeo inválido.');$s=$pdo->prepare('SELECT usuario_id FROM comunidade_videos WHERE id=? AND moderacao_status=\'visible\'');$s->execute([$id]);$owner=$s->fetchColumn();if(!$owner)api_fail('Vídeo não encontrado.',404);if(api_is_blocked($pdo,$uid,(int)$owner))api_fail('Você não pode acessar o conteúdo de um perfil bloqueado.',403);$s=$pdo->prepare('UPDATE comunidade_videos SET visualizacoes=visualizacoes+1 WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    if($action==='react_video'){$id=filter_var($json['video_id']??null,FILTER_VALIDATE_INT);$reaction=(int)($json['reaction']??0);if(!$id||!in_array($reaction,[-1,1],true))api_fail('Reação inválida.');$s=$pdo->prepare('SELECT usuario_id FROM comunidade_videos WHERE id=? AND moderacao_status=\'visible\'');$s->execute([$id]);$owner=$s->fetchColumn();if(!$owner)api_fail('Vídeo não encontrado.',404);if(api_is_blocked($pdo,$uid,(int)$owner))api_fail('Você não pode interagir com o conteúdo de um perfil bloqueado.',403);$s=$pdo->prepare('SELECT reacao FROM comunidade_reacoes_video WHERE video_id=? AND usuario_id=?');$s->execute([$id,$uid]);$old=$s->fetchColumn();if($old!==false&&(int)$old===$reaction){$s=$pdo->prepare('DELETE FROM comunidade_reacoes_video WHERE video_id=? AND usuario_id=?');$s->execute([$id,$uid]);}else{$s=$pdo->prepare('INSERT INTO comunidade_reacoes_video(video_id,usuario_id,reacao) VALUES(?,?,?) ON DUPLICATE KEY UPDATE reacao=VALUES(reacao)');$s->execute([$id,$uid,$reaction]);}comunidade_json(['ok'=>true]);}
    if($action==='comment_video'){$id=filter_var($json['video_id']??null,FILTER_VALIDATE_INT);$body=sunset_conteudo_exigir((string)($json['body']??''),'comentário');if(!$id||api_length($body)<1||api_length($body)>1500)api_fail('Escreva um comentário de até 1.500 caracteres.');$s=$pdo->prepare('SELECT usuario_id FROM comunidade_videos WHERE id=?');$s->execute([$id]);$owner=$s->fetchColumn();if(!$owner)api_fail('Vídeo não encontrado.',404);if(api_is_blocked($pdo,$uid,(int)$owner))api_fail('Você não pode interagir com o conteúdo de um perfil bloqueado.',403);$s=$pdo->prepare('INSERT INTO comunidade_comentarios_video(video_id,usuario_id,corpo) VALUES(?,?,?)');$s->execute([$id,$uid,$body]);comunidade_json(['ok'=>true]);}
    if($action==='delete_video_comment'){$id=filter_var($json['comment_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Comentário inválido.');$s=$pdo->prepare('SELECT c.usuario_id,v.usuario_id AS video_owner FROM comunidade_comentarios_video c JOIN comunidade_videos v ON v.id=c.video_id WHERE c.id=?');$s->execute([$id]);$row=$s->fetch(PDO::FETCH_ASSOC);if(!$row)api_fail('Comentário não encontrado.',404);if((int)$row['usuario_id']!==$uid&&(int)$row['video_owner']!==$uid)api_fail('Somente quem comentou ou quem publicou o vídeo pode apagar esse comentário.',403);$s=$pdo->prepare('DELETE FROM comunidade_comentarios_video WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    if($action==='create_visual_post'){$type=(string)($_POST['type']??'');$caption=sunset_conteudo_exigir((string)($_POST['caption']??''),'legenda da publicação visual');if(!in_array($type,['carousel','short'],true)||api_length($caption)>1500)api_fail('Tipo de publicação ou legenda inválida.');$files=$_FILES['media']??null;if(!$files)api_fail('Adicione a mídia obrigatória.');$stored=[];try{if($type==='short'){$stored[]=['type'=>'video','path'=>api_store_upload(api_file_field('media'),'social','video')];}else{if(!is_array($files['name']??null))api_fail('Selecione de 1 a 10 imagens para o carrossel.');$n=count($files['name']);if($n<1||$n>10)api_fail('O carrossel aceita de 1 a 10 imagens.');for($i=0;$i<$n;$i++){$f=[];foreach(['name','type','tmp_name','error','size'] as $key)$f[$key]=$files[$key][$i]??null;$stored[]=['type'=>'image','path'=>api_store_upload($f,'social','image')];}}}catch(Throwable $e){foreach($stored as $media)api_delete_stored($media['path']);throw $e;}
        try{$pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO comunidade_posts_visuais(usuario_id,tipo,legenda) VALUES(?,?,?)');$s->execute([$uid,$type,$caption]);$postId=(int)$pdo->lastInsertId();$s=$pdo->prepare('INSERT INTO comunidade_midias_visuais(post_id,tipo,caminho,posicao) VALUES(?,?,?,?)');foreach($stored as $i=>$media){$s->execute([$postId,$media['type'],$media['path'],$i]);}$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();foreach($stored as $media)api_delete_stored($media['path']);throw $e;}api_notify_friends($pdo,$uid,'new_visual',(int)$postId,$type==='short'?'publicou um vídeo curto.':'publicou um carrossel de imagens.');comunidade_json(['ok'=>true]);}
    if($action==='create_story'){$caption=sunset_conteudo_exigir((string)($_POST['caption']??''),'legenda do story');$location=sunset_conteudo_exigir((string)($_POST['location']??''),'localização');if(api_length($caption)>500||api_length($location)>160)api_fail('Legenda ou local inválido.');$file=api_file_field('media');if(empty($file['tmp_name']))api_fail('Selecione uma foto ou um vídeo MP4.');$mime=(new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);$type=in_array($mime,['video/mp4','application/mp4'],true)?'video':'image';$path=api_store_upload($file,'stories',$type);try{$s=$pdo->prepare('INSERT INTO comunidade_stories(usuario_id,tipo,caminho,legenda,localizacao,expira_em) VALUES(?,?,?,?,?,DATE_ADD(NOW(),INTERVAL 24 HOUR))');$s->execute([$uid,$type,$path,$caption,$location?:null]);}catch(Throwable $e){api_delete_stored($path);throw $e;}$storyId=(int)$pdo->lastInsertId();api_notify_friends($pdo,$uid,'new_story',$storyId,'publicou um story.');comunidade_json(['ok'=>true,'message'=>'Story publicado por 24 horas.']);}
    if($action==='report_content'||$action==='report_story'){$type=$action==='report_story'?'story':trim((string)($json['content_type']??''));$contentId=(int)($action==='report_story'?($json['story_id']??0):($json['content_id']??0));$result=api_report_content($pdo,$uid,$type,$contentId,trim((string)($json['reason']??'')),trim((string)($json['details']??'')));comunidade_json(['ok'=>true,'message'=>$result['status']==='hidden'?'Conteúdo ocultado após atingir o limite de denúncias.':($result['status']==='removed'?'Comentário removido após atingir o limite de denúncias.':'Denúncia registrada.')]);}
    if($action==='moderation_review'){$type=trim((string)($json['content_type']??''));$contentId=(int)($json['content_id']??0);$status=trim((string)($json['status']??''));if(!api_is_admin($pdo,$uid)||!in_array($status,['kept','hidden','removed'],true))api_fail('Revisão de moderação inválida.',403);$targets=['profile'=>['usuarios','id'],'post'=>['comunidade_publicacoes','id'],'video'=>['comunidade_videos','id'],'carousel'=>['comunidade_posts_visuais','id'],'reel'=>['comunidade_posts_visuais','id'],'story'=>['comunidade_stories','id'],'comment'=>['comunidade_comentarios_video','id'],'dm'=>['comunidade_mensagens_privadas','id'],'group_message'=>['comunidade_mensagens_grupo','id'],'group'=>['comunidade_grupos','id']];if(!isset($targets[$type])||$contentId<1)api_fail('Conteúdo inválido.');[$table,$key]=$targets[$type];if($type!=='profile'){$s=$pdo->prepare("UPDATE {$table} SET moderacao_status=? WHERE {$key}=?");$s->execute([$status==='kept'?'visible':$status,$contentId]);}$s=$pdo->prepare('UPDATE comunidade_denuncias SET status=?,revisado_por=?,revisado_em=NOW() WHERE conteudo_tipo=? AND conteudo_id=?');$s->execute([$status,$uid,$type,$contentId]);comunidade_json(['ok'=>true]);}
    if($action==='delete_story'){$id=filter_var($json['story_id']??null,FILTER_VALIDATE_INT);$s=$pdo->prepare('SELECT caminho FROM comunidade_stories WHERE id=? AND usuario_id=?');$s->execute([$id,$uid]);$path=$s->fetchColumn();if(!$path)api_fail('Story não encontrado.',404);$s=$pdo->prepare('DELETE FROM comunidade_stories WHERE id=? AND usuario_id=?');$s->execute([$id,$uid]);api_delete_stored((string)$path);comunidade_json(['ok'=>true]);}
    if($action==='create_group'){$name=sunset_conteudo_exigir((string)($_POST['name']??''),'nome do grupo');$desc=sunset_conteudo_exigir((string)($_POST['description']??''),'descrição do grupo');if(api_length($name)<3||api_length($name)>100||api_length($desc)<5||api_length($desc)>255)api_fail('Informe nome (3–100 caracteres) e descrição (5–255 caracteres).');$image=api_store_upload(api_file_field('image'),'groups','image');try{$pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO comunidade_grupos(nome,descricao,categoria,criado_por) VALUES(?,?,?,?)');$s->execute([$name,$desc,'Comunidade',$uid]);$gid=(int)$pdo->lastInsertId();$s=$pdo->prepare('INSERT INTO comunidade_membros(grupo_id,usuario_id) VALUES(?,?)');$s->execute([$gid,$uid]);$s=$pdo->prepare('INSERT INTO comunidade_grupo_imagens(grupo_id,caminho) VALUES(?,?)');$s->execute([$gid,$image]);$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();api_delete_stored($image);if($e instanceof PDOException&&(string)$e->getCode()==='23000')api_fail('Já existe um grupo com esse nome.',409);throw $e;}comunidade_json(['ok'=>true,'group_id'=>$gid]);}
    if($action==='join_group'||$action==='leave_group'){$gid=filter_var($json['group_id']??null,FILTER_VALIDATE_INT);if(!$gid)api_fail('Grupo inválido.');if($action==='join_group'){$q=$pdo->prepare('SELECT criado_por FROM comunidade_grupos WHERE id=?');$q->execute([$gid]);$owner=$q->fetchColumn();if($owner===false)api_fail('Grupo não encontrado.',404);$s=$pdo->prepare('INSERT IGNORE INTO comunidade_membros(grupo_id,usuario_id) VALUES(?,?)');$s->execute([$gid,$uid]);if($s->rowCount()&&(int)$owner!==$uid)api_notify($pdo,(int)$owner,'group_join',$uid,(int)$gid,'entrou no seu grupo.');}else{$g=$pdo->prepare('SELECT criado_por FROM comunidade_grupos WHERE id=?');$g->execute([$gid]);$owner=$g->fetchColumn();if(!$owner)api_fail('Grupo não encontrado.',404);if((int)$owner===$uid)api_fail('O administrador não pode sair do próprio grupo.',403);$s=$pdo->prepare('DELETE FROM comunidade_membros WHERE grupo_id=? AND usuario_id=?');$s->execute([$gid,$uid]);}comunidade_json(['ok'=>true]);}
    if($action==='group_message'){$data=$json+$_POST;$gid=filter_var($data['group_id']??null,FILTER_VALIDATE_INT);$tab=(string)($data['tab']??'chat');$body=sunset_conteudo_exigir((string)($data['body']??''),'mensagem do grupo');$file=api_file_field('image');$hasImage=!empty($file['tmp_name'])&&($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_OK;if(!$gid||(!$body&&!$hasImage)||api_length($body)>1500)api_fail('Escreva uma mensagem ou selecione uma imagem; o texto aceita até 1.500 caracteres.');$group=api_group_admin($pdo,(int)$gid,$uid);$aba=null;$canal='chat';if($tab==='announcements')$canal='announcements';elseif(preg_match('/^custom-(\d+)$/',$tab,$m)){$aba=(int)$m[1];$s=$pdo->prepare('SELECT tipo FROM comunidade_abas_grupo WHERE id=? AND grupo_id=?');$s->execute([$aba,$gid]);$kind=$s->fetchColumn();if(!$kind||$kind==='call')api_fail('Essa aba não aceita mensagens.',400);$canal=$kind==='announcements'?'announcements':'chat';}elseif($tab!=='chat')api_fail('Aba inválida.');if($canal==='announcements'&&!$group['is_admin'])api_fail('Apenas o administrador pode publicar avisos.',403);$image=null;try{if($hasImage)$image=api_store_upload($file,'chat','image');$s=$pdo->prepare('INSERT INTO comunidade_mensagens_grupo(grupo_id,aba_id,canal,usuario_id,corpo,anexo_path) VALUES(?,?,?,?,?,?)');$s->execute([$gid,$aba,$canal,$uid,$body,$image]);}catch(Throwable $e){api_delete_stored($image);throw $e;}comunidade_json(['ok'=>true]);}
    if($action==='delete_group_message'){$id=filter_var($json['message_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Mensagem inválida.');$s=$pdo->prepare('SELECT m.usuario_id,g.criado_por FROM comunidade_mensagens_grupo m JOIN comunidade_grupos g ON g.id=m.grupo_id WHERE m.id=?');$s->execute([$id]);$r=$s->fetch(PDO::FETCH_ASSOC);if(!$r)api_fail('Mensagem não encontrada.',404);if((int)$r['usuario_id']!==$uid&&(int)$r['criado_por']!==$uid)api_fail('Somente o autor ou administrador do grupo pode apagar a mensagem.',403);$s=$pdo->prepare('DELETE FROM comunidade_mensagens_grupo WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    if($action==='create_group_tab'){$gid=filter_var($json['group_id']??null,FILTER_VALIDATE_INT);$title=sunset_conteudo_exigir((string)($json['title']??''),'nome da aba');$type=(string)($json['type']??'chat');if(!$gid||api_length($title)<2||api_length($title)>80||!in_array($type,['chat','announcements','call'],true))api_fail('Informe nome e tipo de aba válidos.');$group=api_group_admin($pdo,(int)$gid,$uid);if(!$group['is_admin'])api_fail('Somente o administrador pode adicionar abas.',403);$s=$pdo->prepare('INSERT INTO comunidade_abas_grupo(grupo_id,titulo,tipo,criado_por) VALUES(?,?,?,?)');$s->execute([$gid,$title,$type,$uid]);comunidade_json(['ok'=>true]);}
    if($action==='delete_group_tab'){$id=filter_var($json['tab_id']??null,FILTER_VALIDATE_INT);$s=$pdo->prepare('SELECT a.grupo_id,g.criado_por FROM comunidade_abas_grupo a JOIN comunidade_grupos g ON g.id=a.grupo_id WHERE a.id=?');$s->execute([$id]);$r=$s->fetch(PDO::FETCH_ASSOC);if(!$r)api_fail('Aba não encontrada.',404);if((int)$r['criado_por']!==$uid)api_fail('Somente o administrador pode apagar abas.',403);$s=$pdo->prepare('DELETE FROM comunidade_abas_grupo WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    api_fail('Ação desconhecida.',400);
} catch (SunsetApiException $e) {
    comunidade_json(['ok'=>false,'error'=>$e->getMessage()],$e->status);
} catch (SunsetConteudoException $e) {
    comunidade_json(['ok'=>false,'error'=>$e->getMessage()],422);
} catch (PDOException $e) {
    tratar_erro_bd($e, 'sunsethub_api');
    comunidade_json(['ok'=>false,'error'=>'Não foi possível concluir. Verifique o banco principal, o espaço disponível e as permissões de upload.'],500);
} catch (Throwable $e) {
    error_log('[Sunset Hub API] '.$e->getMessage());
    comunidade_json(['ok'=>false,'error'=>'Não foi possível concluir a operação. Confira os dados e tente novamente.'],500);
}
