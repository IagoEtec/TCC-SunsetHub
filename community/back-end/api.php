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
function api_require_friend(PDO $pdo, int $a, int $b): void {
    $lo = min($a, $b); $hi = max($a, $b);
    $s = $pdo->prepare("SELECT 1 FROM comunidade_amizades WHERE usuario_a=? AND usuario_b=? AND status='accepted'");
    $s->execute([$lo, $hi]);
    if (!$s->fetchColumn()) api_fail('Só é possível conversar com pessoas adicionadas como amigas.', 403);
}
function api_upload_dir(string $kind): string {
    $map = ['videos'=>'videos','thumbs'=>'thumbs','groups'=>'groups','social'=>'social','stories'=>'stories','chat'=>'chat'];
    if (!isset($map[$kind])) api_fail('Tipo de arquivo inválido.');
    $dir = dirname(__DIR__) . '/uploads/' . $map[$kind];
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) api_fail('Não foi possível preparar a pasta de uploads.', 500);
    if (!is_writable($dir)) api_fail('A pasta de uploads não permite gravação. Verifique as permissões do XAMPP.', 500);
    return $dir;
}
function api_store_upload(array $file, string $kind, string $expected): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file((string)$file['tmp_name'])) {
        $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) api_fail('O arquivo excede o limite de upload do PHP/XAMPP. Reduza o tamanho do arquivo ou aumente upload_max_filesize e post_max_size.');
        api_fail('Selecione o arquivo obrigatório e tente novamente.');
    }
    $size = (int)($file['size'] ?? 0);
    $max = $expected === 'video' ? 100 * 1024 * 1024 : 8 * 1024 * 1024;
    if ($size < 1 || $size > $max) api_fail($expected === 'video' ? 'O MP4 deve ter até 100 MB.' : 'A imagem deve ter até 8 MB.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string)$file['tmp_name']);
    $types = $expected === 'video'
        ? ['video/mp4'=>'mp4','application/mp4'=>'mp4']
        : ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($types[$mime])) api_fail($expected === 'video' ? 'O arquivo precisa ser um vídeo MP4 válido.' : 'Use uma imagem JPG, PNG ou WebP.');
    if ($expected === 'image' && @getimagesize((string)$file['tmp_name']) === false) api_fail('A imagem selecionada está inválida.');
    $dir = api_upload_dir($kind);
    $filename = bin2hex(random_bytes(18)) . '.' . $types[$mime];
    if (!move_uploaded_file((string)$file['tmp_name'], $dir . DIRECTORY_SEPARATOR . $filename)) api_fail('Não foi possível salvar o arquivo enviado.', 500);
    return 'uploads/' . $kind . '/' . $filename;
}
function api_file_field(string $field): array {
    $f = $_FILES[$field] ?? null;
    return is_array($f) ? $f : [];
}
function api_delete_stored(?string $path): void {
    if (!$path || !preg_match('~^uploads/(videos|thumbs|groups|social|stories|chat)/[a-f0-9]{36}\.(mp4|jpg|png|webp)$~', $path)) return;
    $full = dirname(__DIR__) . '/' . $path;
    if (is_file($full)) @unlink($full);
}
function api_cleanup_expired_stories(PDO $pdo): void {
    $rows=$pdo->query('SELECT id,caminho FROM comunidade_stories WHERE expira_em<=NOW() ORDER BY expira_em LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
    if(!$rows)return;
    $ids=array_map('intval',array_column($rows,'id'));$marks=implode(',',array_fill(0,count($ids),'?'));
    $s=$pdo->prepare("DELETE FROM comunidade_stories WHERE id IN ({$marks})");$s->execute($ids);
    foreach($rows as $row)api_delete_stored((string)$row['caminho']);
}
function api_touch(PDO $pdo, int $uid): void {
    $s = $pdo->prepare('INSERT INTO comunidade_presenca (usuario_id, visto_em) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE visto_em=NOW()');
    $s->execute([$uid]);
}
function api_notify(PDO $pdo, int $recipient, string $type, int $actor, ?int $ref, string $message): void {
    if ($recipient <= 0 || $recipient === $actor) return;
    $s=$pdo->prepare('INSERT INTO comunidade_notificacoes(usuario_id,tipo,ator_id,referencia_id,mensagem) VALUES(?,?,?,?,?)');
    $s->execute([$recipient,$type,$actor,$ref,$message]);
}
function api_notify_friends(PDO $pdo, int $uid, string $type, int $ref, string $message): void {
    $s=$pdo->prepare("SELECT IF(usuario_a=?,usuario_b,usuario_a) AS friend_id FROM comunidade_amizades WHERE status='accepted' AND (usuario_a=? OR usuario_b=?)");
    $s->execute([$uid,$uid,$uid]);
    foreach($s->fetchAll(PDO::FETCH_COLUMN) as $friend) api_notify($pdo,(int)$friend,$type,$uid,$ref,$message);
}
function api_friend_lists(PDO $pdo, int $uid): array {
    $sql = "SELECT u.id, u.nome AS name, p.visto_em AS last_seen,
        (SELECT m.corpo FROM comunidade_mensagens_privadas m WHERE (m.remetente_id=? AND m.destinatario_id=u.id) OR (m.remetente_id=u.id AND m.destinatario_id=?) ORDER BY m.id DESC LIMIT 1) AS last_message,
        (SELECT m.criado_em FROM comunidade_mensagens_privadas m WHERE (m.remetente_id=? AND m.destinatario_id=u.id) OR (m.remetente_id=u.id AND m.destinatario_id=?) ORDER BY m.id DESC LIMIT 1) AS last_at
        FROM comunidade_amizades a JOIN usuarios u ON u.id=IF(a.usuario_a=?,a.usuario_b,a.usuario_a)
        LEFT JOIN comunidade_presenca p ON p.usuario_id=u.id
        WHERE a.status='accepted' AND (a.usuario_a=? OR a.usuario_b=?) ORDER BY last_at DESC, u.nome";
    $s = $pdo->prepare($sql); $s->execute([$uid,$uid,$uid,$uid,$uid,$uid,$uid]); $friends = $s->fetchAll(PDO::FETCH_ASSOC);
    foreach ($friends as &$f) $f['online'] = !empty($f['last_seen']) && strtotime((string)$f['last_seen']) >= time() - 300;
    unset($f);
    $s = $pdo->prepare("SELECT a.solicitado_por AS requester_id, u.id, u.nome AS name, a.criado_em FROM comunidade_amizades a JOIN usuarios u ON u.id=a.solicitado_por WHERE a.status='pending' AND a.solicitado_por<>? AND (a.usuario_a=? OR a.usuario_b=?) ORDER BY a.criado_em DESC");
    $s->execute([$uid,$uid,$uid]); $incoming = $s->fetchAll(PDO::FETCH_ASSOC);
    $s = $pdo->prepare("SELECT u.id, u.nome AS name, a.criado_em FROM comunidade_amizades a JOIN usuarios u ON u.id=IF(a.usuario_a=?,a.usuario_b,a.usuario_a) WHERE a.status='pending' AND a.solicitado_por=? AND (a.usuario_a=? OR a.usuario_b=?) ORDER BY a.criado_em DESC");
    $s->execute([$uid,$uid,$uid,$uid]); $outgoing = $s->fetchAll(PDO::FETCH_ASSOC);
    return [$friends, $incoming, $outgoing];
}
function api_group_admin(PDO $pdo, int $gid, int $uid): array {
    $s = $pdo->prepare('SELECT id, nome AS name, descricao AS description, criado_por AS admin_id FROM comunidade_grupos WHERE id=?');
    $s->execute([$gid]); $g = $s->fetch(PDO::FETCH_ASSOC);
    if (!$g) api_fail('Grupo não encontrado.', 404);
    $s = $pdo->prepare('SELECT 1 FROM comunidade_membros WHERE grupo_id=? AND usuario_id=?');
    $s->execute([$gid,$uid]);
    if (!$s->fetchColumn()) api_fail('Você precisa participar do grupo para acessar essa sala.', 403);
    $g['is_admin'] = (int)$g['admin_id'] === $uid;
    return $g;
}
function api_group_messages(PDO $pdo, string $where, array $params, int $after): array {
    $sql="SELECT m.id,m.usuario_id AS owner_id,m.corpo AS body,m.anexo_path AS attachment_path,UNIX_TIMESTAMP(m.criado_em) AS created_ts,u.nome AS name,IF(g.criado_por=u.id,1,0) AS is_admin FROM comunidade_mensagens_grupo m JOIN usuarios u ON u.id=m.usuario_id JOIN comunidade_grupos g ON g.id=m.grupo_id {$where}";
    if($after>0){$sql.=' AND m.id>?';$params[]=$after;}
    $sql.=' ORDER BY m.id '.($after>0?'ASC':'DESC').' LIMIT 100';
    $s=$pdo->prepare($sql);$s->execute($params);$items=$s->fetchAll(PDO::FETCH_ASSOC);
    return $after>0?$items:array_reverse($items);
}
function api_initial(PDO $pdo, array $user): void {
    $uid=(int)$user['id']; api_touch($pdo,$uid); api_cleanup_expired_stories($pdo);
    $posts=$pdo->query("SELECT p.id,p.corpo AS body,p.curtidas AS likes,UNIX_TIMESTAMP(p.criado_em) AS created_ts,u.nome AS name,LOWER(REPLACE(u.nome,' ','')) AS username FROM comunidade_publicacoes p JOIN usuarios u ON u.id=p.usuario_id ORDER BY p.criado_em DESC,p.id DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
    $s=$pdo->prepare("SELECT v.id,v.usuario_id AS owner_id,v.titulo AS title,v.descricao AS description,v.url AS file_path,v.miniatura AS thumbnail_path,v.visualizacoes AS views,UNIX_TIMESTAMP(v.criado_em) AS created_ts,u.nome AS name,
        (SELECT COUNT(*) FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.reacao=1) AS likes,
        (SELECT COUNT(*) FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.reacao=-1) AS dislikes,
        COALESCE((SELECT r.reacao FROM comunidade_reacoes_video r WHERE r.video_id=v.id AND r.usuario_id=?) ,0) AS my_reaction,
        (SELECT COUNT(*) FROM comunidade_comentarios_video c WHERE c.video_id=v.id) AS comments
        FROM comunidade_videos v JOIN usuarios u ON u.id=v.usuario_id ORDER BY v.criado_em DESC,v.id DESC LIMIT 40");
    $s->execute([$uid]); $videos=$s->fetchAll(PDO::FETCH_ASSOC);
    $visual=$pdo->query("SELECT p.id,p.usuario_id AS owner_id,p.tipo AS type,p.legenda AS caption,UNIX_TIMESTAMP(p.criado_em) AS created_ts,u.nome AS name FROM comunidade_posts_visuais p JOIN usuarios u ON u.id=p.usuario_id ORDER BY p.criado_em DESC,p.id DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
    if ($visual) {
        $ids=array_map('intval',array_column($visual,'id')); $marks=implode(',',array_fill(0,count($ids),'?'));
        $m=$pdo->prepare("SELECT post_id, tipo AS type, caminho AS path, posicao AS position FROM comunidade_midias_visuais WHERE post_id IN ({$marks}) ORDER BY post_id,posicao,id"); $m->execute($ids); $by=[];
        foreach($m->fetchAll(PDO::FETCH_ASSOC) as $item) $by[(int)$item['post_id']][]=$item;
        foreach($visual as &$post) $post['media']=$by[(int)$post['id']]??[]; unset($post);
    }
    $groups=$pdo->prepare("SELECT g.id,g.nome AS name,g.descricao AS description,g.categoria AS category,g.cor AS accent,g.criado_por AS admin_id,gi.caminho AS image_path,(SELECT COUNT(*) FROM comunidade_membros mc WHERE mc.grupo_id=g.id) AS members,(SELECT COUNT(*) FROM comunidade_membros mj WHERE mj.grupo_id=g.id AND mj.usuario_id=?) AS joined FROM comunidade_grupos g LEFT JOIN comunidade_grupo_imagens gi ON gi.grupo_id=g.id ORDER BY joined DESC,members DESC,g.nome");
    $groups->execute([$uid]);
    $stories=$pdo->query("SELECT s.id,s.usuario_id AS owner_id,s.tipo AS type,s.caminho AS path,s.legenda AS caption,s.criado_em AS created_at,UNIX_TIMESTAMP(s.expira_em) AS expires_ts,u.nome AS name FROM comunidade_stories s JOIN usuarios u ON u.id=s.usuario_id WHERE s.expira_em>NOW() ORDER BY s.criado_em DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
    [$friends,$incoming,$outgoing]=api_friend_lists($pdo,$uid);
    $n=$pdo->prepare('SELECT COUNT(*) FROM comunidade_notificacoes WHERE usuario_id=? AND lida_em IS NULL');$n->execute([$uid]);
    comunidade_json(['ok'=>true,'csrf'=>csrf_token(),'user'=>$user,'posts'=>$posts,'videos'=>$videos,'visual_posts'=>$visual,'stories'=>$stories,'groups'=>$groups->fetchAll(PDO::FETCH_ASSOC),'friends'=>$friends,'incoming'=>$incoming,'outgoing'=>$outgoing,'unread_count'=>(int)$n->fetchColumn()]);
}

try {
    $user=comunidade_usuario($pdo); comunidade_instalar_schema($pdo); $uid=(int)$user['id'];
    if ($_SERVER['REQUEST_METHOD']==='GET') {
        $action=(string)($_GET['action']??'initial');
        if ($action==='initial') api_initial($pdo,$user);
        if ($action==='search_users') {
            $q=trim((string)($_GET['q']??'')); if(api_length($q)<2) comunidade_json(['ok'=>true,'users'=>[]]);
            $s=$pdo->prepare("SELECT u.id,u.nome AS name, a.status AS relation_status,a.solicitado_por FROM usuarios u LEFT JOIN comunidade_amizades a ON a.usuario_a=LEAST(u.id,?) AND a.usuario_b=GREATEST(u.id,?) WHERE u.id<>? AND u.nome LIKE ? ORDER BY u.nome LIMIT 20");
            $s->execute([$uid,$uid,$uid,'%'.$q.'%']); $users=$s->fetchAll(PDO::FETCH_ASSOC);
            foreach($users as &$person){$st=$person['relation_status'];$person['relation']=$st==='accepted'?'friend':($st==='pending'?((int)$person['solicitado_por']===$uid?'outgoing':'incoming'):'none');unset($person['relation_status'],$person['solicitado_por']);} unset($person);
            comunidade_json(['ok'=>true,'users'=>$users]);
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
            if($after>0){$s=$pdo->prepare("SELECT m.id,m.remetente_id AS sender_id,m.destinatario_id AS recipient_id,m.corpo AS body,m.anexo_path AS attachment_path,UNIX_TIMESTAMP(m.criado_em) AS created_ts FROM comunidade_mensagens_privadas m WHERE ((m.remetente_id=? AND m.destinatario_id=?) OR (m.remetente_id=? AND m.destinatario_id=?)) AND m.id>? ORDER BY m.id LIMIT 100");$s->execute([$uid,$other,$other,$uid,$after]);$items=$s->fetchAll(PDO::FETCH_ASSOC);}
            else{$s=$pdo->prepare("SELECT m.id,m.remetente_id AS sender_id,m.destinatario_id AS recipient_id,m.corpo AS body,m.anexo_path AS attachment_path,UNIX_TIMESTAMP(m.criado_em) AS created_ts FROM comunidade_mensagens_privadas m WHERE (m.remetente_id=? AND m.destinatario_id=?) OR (m.remetente_id=? AND m.destinatario_id=?) ORDER BY m.id DESC LIMIT 100");$s->execute([$uid,$other,$other,$uid]);$items=array_reverse($s->fetchAll(PDO::FETCH_ASSOC));}
            $s=$pdo->prepare('UPDATE comunidade_mensagens_privadas SET lida_em=NOW() WHERE remetente_id=? AND destinatario_id=? AND lida_em IS NULL');$s->execute([$other,$uid]);
            comunidade_json(['ok'=>true,'messages'=>$items]);
        }
        if ($action==='video_comments') {
            $id=filter_var($_GET['video_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Vídeo inválido.');
            $s=$pdo->prepare('SELECT c.id,c.usuario_id AS owner_id,c.corpo AS body,UNIX_TIMESTAMP(c.criado_em) AS created_ts,u.nome AS name FROM comunidade_comentarios_video c JOIN usuarios u ON u.id=c.usuario_id WHERE c.video_id=? ORDER BY c.id DESC LIMIT 100');$s->execute([$id]);
            comunidade_json(['ok'=>true,'comments'=>array_reverse($s->fetchAll(PDO::FETCH_ASSOC))]);
        }
        if ($action==='group') {
            $gid=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);if(!$gid)api_fail('Grupo inválido.');$group=api_group_admin($pdo,(int)$gid,$uid);api_touch($pdo,$uid);
            $s=$pdo->prepare('SELECT gi.caminho AS image_path FROM comunidade_grupo_imagens gi WHERE gi.grupo_id=?');$s->execute([$gid]);$group['image_path']=$s->fetchColumn()?:null;
            $s=$pdo->prepare("SELECT u.id,u.nome AS name,p.visto_em AS last_seen,IF(g.criado_por=u.id,1,0) AS is_admin FROM comunidade_membros m JOIN usuarios u ON u.id=m.usuario_id JOIN comunidade_grupos g ON g.id=m.grupo_id LEFT JOIN comunidade_presenca p ON p.usuario_id=u.id WHERE m.grupo_id=? ORDER BY is_admin DESC,u.nome");$s->execute([$gid]);$members=$s->fetchAll(PDO::FETCH_ASSOC);foreach($members as &$m)$m['online']=!empty($m['last_seen'])&&strtotime((string)$m['last_seen'])>=time()-300;unset($m);
            $tabs=[['key'=>'chat','title'=>'Bate-papo','type'=>'chat'],['key'=>'calls','title'=>'Videochamadas','type'=>'call'],['key'=>'announcements','title'=>'Avisos','type'=>'announcements']];
            $s=$pdo->prepare('SELECT id,titulo AS title,tipo AS type FROM comunidade_abas_grupo WHERE grupo_id=? ORDER BY id');$s->execute([$gid]);foreach($s->fetchAll(PDO::FETCH_ASSOC) as $t){$t['key']='custom-'.$t['id'];$tabs[]=$t;}
            $tab=(string)($_GET['tab']??'chat');$after=max(0,(int)($_GET['after']??0));$messages=[];$selected=null;
            if($tab==='chat'||$tab==='announcements')$messages=api_group_messages($pdo,'WHERE m.grupo_id=? AND m.canal=? AND m.aba_id IS NULL',[$gid,$tab],$after);
            elseif(preg_match('/^custom-(\d+)$/',$tab,$matches)){$tabId=(int)$matches[1];$s=$pdo->prepare('SELECT id,titulo AS title,tipo AS type FROM comunidade_abas_grupo WHERE id=? AND grupo_id=?');$s->execute([$tabId,$gid]);$selected=$s->fetch(PDO::FETCH_ASSOC);if(!$selected)api_fail('Esta sala não existe.',404);if($selected['type']!=='call'){$canal=$selected['type']==='announcements'?'announcements':'chat';$messages=api_group_messages($pdo,'WHERE m.grupo_id=? AND m.aba_id=? AND m.canal=?',[$gid,$tabId,$canal],$after);}}
            elseif($tab!=='calls')api_fail('Aba inválida.');
            comunidade_json(['ok'=>true,'group'=>$group,'members'=>$members,'tabs'=>$tabs,'tab'=>$tab,'selected_tab'=>$selected,'messages'=>$messages]);
        }
        api_fail('Ação desconhecida.',404);
    }
    if ($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: GET, POST');api_fail('Método não permitido.',405);}
    comunidade_exigir_csrf(); api_touch($pdo,$uid);
    $json=[];if(stripos((string)($_SERVER['CONTENT_TYPE']??''),'application/json')!==false){$json=json_decode((string)file_get_contents('php://input'),true)?:[];}
    $action=(string)($json['action']??$_POST['action']??'');
    if($action===''&&(int)($_SERVER['CONTENT_LENGTH']??0)>0&&empty($_POST)&&empty($_FILES))api_fail('O arquivo ultrapassou post_max_size do PHP. Aumente post_max_size e upload_max_filesize no php.ini e reinicie o Apache.',413);
    if($action==='heartbeat')comunidade_json(['ok'=>true]);
    if($action==='mark_notifications_read'){$id=filter_var($json['id']??$_POST['id']??null,FILTER_VALIDATE_INT);if($id){$s=$pdo->prepare('UPDATE comunidade_notificacoes SET lida_em=NOW() WHERE usuario_id=? AND id=? AND lida_em IS NULL');$s->execute([$uid,$id]);}else{$s=$pdo->prepare('UPDATE comunidade_notificacoes SET lida_em=NOW() WHERE usuario_id=? AND lida_em IS NULL');$s->execute([$uid]);}comunidade_json(['ok'=>true]);}
    if($action==='publish'){$body=trim((string)($json['body']??''));if(api_length($body)<1||api_length($body)>1000)api_fail('Escreva uma publicação de até 1.000 caracteres.');$s=$pdo->prepare('INSERT INTO comunidade_publicacoes(usuario_id,corpo) VALUES(?,?)');$s->execute([$uid,$body]);api_notify_friends($pdo,$uid,'new_post',(int)$pdo->lastInsertId(),'publicou uma nova publicação.');comunidade_json(['ok'=>true]);}
    if($action==='search_friend_action'){
        $target=filter_var($json['user_id']??null,FILTER_VALIDATE_INT);$op=(string)($json['operation']??'');if(!$target||$target===$uid)api_fail('Pessoa inválida.');
        $a=min($uid,(int)$target);$b=max($uid,(int)$target);$s=$pdo->prepare('SELECT status,solicitado_por FROM comunidade_amizades WHERE usuario_a=? AND usuario_b=?');$s->execute([$a,$b]);$existing=$s->fetch(PDO::FETCH_ASSOC);
        if($op==='request'){
            $u=$pdo->prepare('SELECT 1 FROM usuarios WHERE id=?');$u->execute([$target]);if(!$u->fetchColumn())api_fail('Usuário não encontrado.',404);
            if($existing){if($existing['status']==='accepted')api_fail('Essa pessoa já é sua amiga.',409);if((int)$existing['solicitado_por']!==$uid)api_fail('Essa pessoa já enviou uma solicitação. Aceite-a na lista de solicitações.',409);api_fail('A solicitação já foi enviada.',409);}
            $s=$pdo->prepare("INSERT INTO comunidade_amizades(usuario_a,usuario_b,solicitado_por,status) VALUES(?,?,?,'pending')");$s->execute([$a,$b,$uid]);api_notify($pdo,(int)$target,'friend_request',$uid,null,'enviou uma solicitação de amizade.');comunidade_json(['ok'=>true,'message'=>'Solicitação enviada.']);
        }
        if($op==='accept'){$q=$pdo->prepare("SELECT solicitado_por FROM comunidade_amizades WHERE usuario_a=? AND usuario_b=? AND status='pending' AND solicitado_por<>?");$q->execute([$a,$b,$uid]);$requester=$q->fetchColumn();if(!$requester)api_fail('Solicitação pendente não encontrada.',404);$s=$pdo->prepare("UPDATE comunidade_amizades SET status='accepted' WHERE usuario_a=? AND usuario_b=? AND status='pending' AND solicitado_por<>?");$s->execute([$a,$b,$uid]);if(!$s->rowCount())api_fail('Solicitação pendente não encontrada.',404);api_notify($pdo,(int)$requester,'friend_accepted',$uid,null,'aceitou sua solicitação de amizade.');comunidade_json(['ok'=>true]);}
        if($op==='decline'||$op==='cancel'){$s=$pdo->prepare("DELETE FROM comunidade_amizades WHERE usuario_a=? AND usuario_b=? AND status='pending' AND solicitado_por".($op==='decline'?'<>?':'=?'));$s->execute([$a,$b,$uid]);comunidade_json(['ok'=>true]);}
        if($op==='remove'){$s=$pdo->prepare("DELETE FROM comunidade_amizades WHERE usuario_a=? AND usuario_b=? AND status='accepted'");$s->execute([$a,$b]);comunidade_json(['ok'=>true]);}
        api_fail('Ação de amizade inválida.');
    }
    if($action==='send_message'){$data=$json+$_POST;$other=filter_var($data['user_id']??null,FILTER_VALIDATE_INT);$body=trim((string)($data['body']??''));$file=api_file_field('image');$hasImage=!empty($file['tmp_name'])&&($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_OK;if(!$other||(!$body&&!$hasImage)||api_length($body)>4000)api_fail('Escreva uma mensagem ou selecione uma imagem; o texto aceita até 4.000 caracteres.');api_require_friend($pdo,$uid,(int)$other);$image=null;try{if($hasImage)$image=api_store_upload($file,'chat','image');$s=$pdo->prepare('INSERT INTO comunidade_mensagens_privadas(remetente_id,destinatario_id,corpo,anexo_path) VALUES(?,?,?,?)');$s->execute([$uid,$other,$body,$image]);}catch(Throwable $e){api_delete_stored($image);throw $e;}comunidade_json(['ok'=>true,'id'=>(int)$pdo->lastInsertId()]);}
    if($action==='create_video'){$title=trim((string)($_POST['title']??''));$desc=trim((string)($_POST['description']??''));if(api_length($title)<3||api_length($title)>160||api_length($desc)>500)api_fail('Informe um título de 3–160 caracteres e descrição de até 500 caracteres.');$video=$thumb=null;try{$video=api_store_upload(api_file_field('video'),'videos','video');$thumb=api_store_upload(api_file_field('thumbnail'),'thumbs','image');$pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO comunidade_videos(usuario_id,titulo,descricao,url,miniatura) VALUES(?,?,?,?,?)');$s->execute([$uid,$title,$desc,$video,$thumb]);$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();api_delete_stored($video);api_delete_stored($thumb);throw $e;}api_notify_friends($pdo,$uid,'new_video',(int)$pdo->lastInsertId(),'publicou um vídeo novo.');comunidade_json(['ok'=>true,'message'=>'Vídeo publicado.']);}
    if($action==='view_video'){$id=filter_var($json['video_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Vídeo inválido.');$s=$pdo->prepare('UPDATE comunidade_videos SET visualizacoes=visualizacoes+1 WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    if($action==='react_video'){$id=filter_var($json['video_id']??null,FILTER_VALIDATE_INT);$reaction=(int)($json['reaction']??0);if(!$id||!in_array($reaction,[-1,1],true))api_fail('Reação inválida.');$s=$pdo->prepare('SELECT reacao FROM comunidade_reacoes_video WHERE video_id=? AND usuario_id=?');$s->execute([$id,$uid]);$old=$s->fetchColumn();if($old!==false&&(int)$old===$reaction){$s=$pdo->prepare('DELETE FROM comunidade_reacoes_video WHERE video_id=? AND usuario_id=?');$s->execute([$id,$uid]);}else{$s=$pdo->prepare('INSERT INTO comunidade_reacoes_video(video_id,usuario_id,reacao) VALUES(?,?,?) ON DUPLICATE KEY UPDATE reacao=VALUES(reacao)');$s->execute([$id,$uid,$reaction]);}comunidade_json(['ok'=>true]);}
    if($action==='comment_video'){$id=filter_var($json['video_id']??null,FILTER_VALIDATE_INT);$body=trim((string)($json['body']??''));if(!$id||api_length($body)<1||api_length($body)>1500)api_fail('Escreva um comentário de até 1.500 caracteres.');$s=$pdo->prepare('INSERT INTO comunidade_comentarios_video(video_id,usuario_id,corpo) VALUES(?,?,?)');$s->execute([$id,$uid,$body]);comunidade_json(['ok'=>true]);}
    if($action==='delete_video_comment'){$id=filter_var($json['comment_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Comentário inválido.');$s=$pdo->prepare('SELECT c.usuario_id,v.usuario_id AS video_owner FROM comunidade_comentarios_video c JOIN comunidade_videos v ON v.id=c.video_id WHERE c.id=?');$s->execute([$id]);$row=$s->fetch(PDO::FETCH_ASSOC);if(!$row)api_fail('Comentário não encontrado.',404);if((int)$row['usuario_id']!==$uid&&(int)$row['video_owner']!==$uid)api_fail('Somente quem comentou ou quem publicou o vídeo pode apagar esse comentário.',403);$s=$pdo->prepare('DELETE FROM comunidade_comentarios_video WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    if($action==='create_visual_post'){$type=(string)($_POST['type']??'');$caption=trim((string)($_POST['caption']??''));if(!in_array($type,['carousel','short'],true)||api_length($caption)>1500)api_fail('Tipo de publicação ou legenda inválida.');$files=$_FILES['media']??null;if(!$files)api_fail('Adicione a mídia obrigatória.');$stored=[];try{if($type==='short'){$stored[]=['type'=>'video','path'=>api_store_upload(api_file_field('media'),'social','video')];}else{if(!is_array($files['name']??null))api_fail('Selecione de 1 a 10 imagens para o carrossel.');$n=count($files['name']);if($n<1||$n>10)api_fail('O carrossel aceita de 1 a 10 imagens.');for($i=0;$i<$n;$i++){$f=[];foreach(['name','type','tmp_name','error','size'] as $key)$f[$key]=$files[$key][$i]??null;$stored[]=['type'=>'image','path'=>api_store_upload($f,'social','image')];}}}catch(Throwable $e){foreach($stored as $media)api_delete_stored($media['path']);throw $e;}
        try{$pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO comunidade_posts_visuais(usuario_id,tipo,legenda) VALUES(?,?,?)');$s->execute([$uid,$type,$caption]);$postId=(int)$pdo->lastInsertId();$s=$pdo->prepare('INSERT INTO comunidade_midias_visuais(post_id,tipo,caminho,posicao) VALUES(?,?,?,?)');foreach($stored as $i=>$media){$s->execute([$postId,$media['type'],$media['path'],$i]);}$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();foreach($stored as $media)api_delete_stored($media['path']);throw $e;}api_notify_friends($pdo,$uid,'new_visual',(int)$postId,$type==='short'?'publicou um vídeo curto.':'publicou um carrossel de imagens.');comunidade_json(['ok'=>true]);}
    if($action==='create_story'){$caption=trim((string)($_POST['caption']??''));if(api_length($caption)>500)api_fail('A legenda aceita até 500 caracteres.');$file=api_file_field('media');if(empty($file['tmp_name']))api_fail('Selecione uma foto ou um vídeo MP4.');$mime=(new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);$type=in_array($mime,['video/mp4','application/mp4'],true)?'video':'image';$path=api_store_upload($file,'stories',$type);try{$s=$pdo->prepare('INSERT INTO comunidade_stories(usuario_id,tipo,caminho,legenda,expira_em) VALUES(?,?,?,?,DATE_ADD(NOW(),INTERVAL 24 HOUR))');$s->execute([$uid,$type,$path,$caption]);}catch(Throwable $e){api_delete_stored($path);throw $e;}$storyId=(int)$pdo->lastInsertId();api_notify_friends($pdo,$uid,'new_story',$storyId,'publicou um story.');comunidade_json(['ok'=>true,'message'=>'Story publicado por 24 horas.']);}
    if($action==='delete_story'){$id=filter_var($json['story_id']??null,FILTER_VALIDATE_INT);$s=$pdo->prepare('SELECT caminho FROM comunidade_stories WHERE id=? AND usuario_id=?');$s->execute([$id,$uid]);$path=$s->fetchColumn();if(!$path)api_fail('Story não encontrado.',404);$s=$pdo->prepare('DELETE FROM comunidade_stories WHERE id=? AND usuario_id=?');$s->execute([$id,$uid]);api_delete_stored((string)$path);comunidade_json(['ok'=>true]);}
    if($action==='create_group'){$name=trim((string)($_POST['name']??''));$desc=trim((string)($_POST['description']??''));if(api_length($name)<3||api_length($name)>100||api_length($desc)<5||api_length($desc)>255)api_fail('Informe nome (3–100 caracteres) e descrição (5–255 caracteres).');$image=api_store_upload(api_file_field('image'),'groups','image');try{$pdo->beginTransaction();$s=$pdo->prepare('INSERT INTO comunidade_grupos(nome,descricao,categoria,criado_por) VALUES(?,?,?,?)');$s->execute([$name,$desc,'Comunidade',$uid]);$gid=(int)$pdo->lastInsertId();$s=$pdo->prepare('INSERT INTO comunidade_membros(grupo_id,usuario_id) VALUES(?,?)');$s->execute([$gid,$uid]);$s=$pdo->prepare('INSERT INTO comunidade_grupo_imagens(grupo_id,caminho) VALUES(?,?)');$s->execute([$gid,$image]);$pdo->commit();}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();api_delete_stored($image);if($e instanceof PDOException&&(string)$e->getCode()==='23000')api_fail('Já existe um grupo com esse nome.',409);throw $e;}comunidade_json(['ok'=>true,'group_id'=>$gid]);}
    if($action==='join_group'||$action==='leave_group'){$gid=filter_var($json['group_id']??null,FILTER_VALIDATE_INT);if(!$gid)api_fail('Grupo inválido.');if($action==='join_group'){$q=$pdo->prepare('SELECT criado_por FROM comunidade_grupos WHERE id=?');$q->execute([$gid]);$owner=$q->fetchColumn();if($owner===false)api_fail('Grupo não encontrado.',404);$s=$pdo->prepare('INSERT IGNORE INTO comunidade_membros(grupo_id,usuario_id) VALUES(?,?)');$s->execute([$gid,$uid]);if($s->rowCount()&&(int)$owner!==$uid)api_notify($pdo,(int)$owner,'group_join',$uid,(int)$gid,'entrou no seu grupo.');}else{$g=$pdo->prepare('SELECT criado_por FROM comunidade_grupos WHERE id=?');$g->execute([$gid]);$owner=$g->fetchColumn();if(!$owner)api_fail('Grupo não encontrado.',404);if((int)$owner===$uid)api_fail('O administrador não pode sair do próprio grupo.',403);$s=$pdo->prepare('DELETE FROM comunidade_membros WHERE grupo_id=? AND usuario_id=?');$s->execute([$gid,$uid]);}comunidade_json(['ok'=>true]);}
    if($action==='group_message'){$data=$json+$_POST;$gid=filter_var($data['group_id']??null,FILTER_VALIDATE_INT);$tab=(string)($data['tab']??'chat');$body=trim((string)($data['body']??''));$file=api_file_field('image');$hasImage=!empty($file['tmp_name'])&&($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_OK;if(!$gid||(!$body&&!$hasImage)||api_length($body)>1500)api_fail('Escreva uma mensagem ou selecione uma imagem; o texto aceita até 1.500 caracteres.');$group=api_group_admin($pdo,(int)$gid,$uid);$aba=null;$canal='chat';if($tab==='announcements')$canal='announcements';elseif(preg_match('/^custom-(\d+)$/',$tab,$m)){$aba=(int)$m[1];$s=$pdo->prepare('SELECT tipo FROM comunidade_abas_grupo WHERE id=? AND grupo_id=?');$s->execute([$aba,$gid]);$kind=$s->fetchColumn();if(!$kind||$kind==='call')api_fail('Essa aba não aceita mensagens.',400);$canal=$kind==='announcements'?'announcements':'chat';}elseif($tab!=='chat')api_fail('Aba inválida.');if($canal==='announcements'&&!$group['is_admin'])api_fail('Apenas o administrador pode publicar avisos.',403);$image=null;try{if($hasImage)$image=api_store_upload($file,'chat','image');$s=$pdo->prepare('INSERT INTO comunidade_mensagens_grupo(grupo_id,aba_id,canal,usuario_id,corpo,anexo_path) VALUES(?,?,?,?,?,?)');$s->execute([$gid,$aba,$canal,$uid,$body,$image]);}catch(Throwable $e){api_delete_stored($image);throw $e;}comunidade_json(['ok'=>true]);}
    if($action==='delete_group_message'){$id=filter_var($json['message_id']??null,FILTER_VALIDATE_INT);if(!$id)api_fail('Mensagem inválida.');$s=$pdo->prepare('SELECT m.usuario_id,g.criado_por FROM comunidade_mensagens_grupo m JOIN comunidade_grupos g ON g.id=m.grupo_id WHERE m.id=?');$s->execute([$id]);$r=$s->fetch(PDO::FETCH_ASSOC);if(!$r)api_fail('Mensagem não encontrada.',404);if((int)$r['usuario_id']!==$uid&&(int)$r['criado_por']!==$uid)api_fail('Somente o autor ou administrador do grupo pode apagar a mensagem.',403);$s=$pdo->prepare('DELETE FROM comunidade_mensagens_grupo WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    if($action==='create_group_tab'){$gid=filter_var($json['group_id']??null,FILTER_VALIDATE_INT);$title=trim((string)($json['title']??''));$type=(string)($json['type']??'chat');if(!$gid||api_length($title)<2||api_length($title)>80||!in_array($type,['chat','announcements','call'],true))api_fail('Informe nome e tipo de aba válidos.');$group=api_group_admin($pdo,(int)$gid,$uid);if(!$group['is_admin'])api_fail('Somente o administrador pode adicionar abas.',403);$s=$pdo->prepare('INSERT INTO comunidade_abas_grupo(grupo_id,titulo,tipo,criado_por) VALUES(?,?,?,?)');$s->execute([$gid,$title,$type,$uid]);comunidade_json(['ok'=>true]);}
    if($action==='delete_group_tab'){$id=filter_var($json['tab_id']??null,FILTER_VALIDATE_INT);$s=$pdo->prepare('SELECT a.grupo_id,g.criado_por FROM comunidade_abas_grupo a JOIN comunidade_grupos g ON g.id=a.grupo_id WHERE a.id=?');$s->execute([$id]);$r=$s->fetch(PDO::FETCH_ASSOC);if(!$r)api_fail('Aba não encontrada.',404);if((int)$r['criado_por']!==$uid)api_fail('Somente o administrador pode apagar abas.',403);$s=$pdo->prepare('DELETE FROM comunidade_abas_grupo WHERE id=?');$s->execute([$id]);comunidade_json(['ok'=>true]);}
    api_fail('Ação desconhecida.',400);
} catch (SunsetApiException $e) {
    comunidade_json(['ok'=>false,'error'=>$e->getMessage()],$e->status);
} catch (PDOException $e) {
    tratar_erro_bd($e, 'sunsethub_api');
    comunidade_json(['ok'=>false,'error'=>'Não foi possível concluir. Verifique o banco principal, o espaço disponível e as permissões de upload.'],500);
} catch (Throwable $e) {
    error_log('[Sunset Hub API] '.$e->getMessage());
    comunidade_json(['ok'=>false,'error'=>'Não foi possível concluir a operação. Confira os dados e tente novamente.'],500);
}
