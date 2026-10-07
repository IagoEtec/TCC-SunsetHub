<?php
/**
 * API do AFDIS Arena integrada ao usuário autenticado do PTCC.
 * Todas as regras de recompensa são revalidadas no servidor.
 */
require_once __DIR__ . '/seguranca.php';
iniciar_sessao_segura();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/arena_schema.php';
ensureArenaSchema($pdo);

if (empty($_SESSION['id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'message' => 'Sessão expirada.']);
    exit;
}

$ptccUserId = (int) $_SESSION['id'];
$items = [
    'xp_boost' => ['name'=>'2x XP','detail'=>'Dobra os ganhos por 24 horas','price'=>153,'icon'=>'⚡'],
    'coin_boost' => ['name'=>'2x Coins','detail'=>'Dobra as moedas ganhas por 24 horas','price'=>204,'icon'=>'✦'],
    'money_rain' => ['name'=>'Chuva de dinheiro','detail'=>'Efeito visual a cada tarefa finalizada','price'=>306,'icon'=>'☂'],
    'profile_banner' => ['name'=>'Banner personalizado','detail'=>'Moldura dourada no perfil e progresso','price'=>510,'icon'=>'◈'],
    'streak_freeze' => ['name'=>'Congelador de streak','detail'=>'Protege 1 dia sem entrar ou fazer tarefas','price'=>102,'icon'=>'❄'],
];
$ranks = [
 ['name'=>'FERRO 1','min'=>0,'max'=>49,'color'=>'#a8b4c5'],['name'=>'FERRO 2','min'=>50,'max'=>149,'color'=>'#b7c4d5'],['name'=>'FERRO 3','min'=>150,'max'=>299,'color'=>'#d5e0ea'],['name'=>'OURO 1','min'=>300,'max'=>499,'color'=>'#ffd166'],['name'=>'OURO 2','min'=>500,'max'=>749,'color'=>'#f7b955'],['name'=>'OURO 3','min'=>750,'max'=>1049,'color'=>'#ffe08a'],['name'=>'PLATINA 1','min'=>1050,'max'=>1449,'color'=>'#74e5ef'],['name'=>'PLATINA 2','min'=>1450,'max'=>1949,'color'=>'#54c8df'],['name'=>'PLATINA 3','min'=>1950,'max'=>2549,'color'=>'#78b9ef'],['name'=>'ESMERALDA','min'=>2550,'max'=>PHP_INT_MAX,'color'=>'#61e6ad']
];

function arenaJson(array $data, int $status = 200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit;
}
function arenaUser(PDO $pdo, int $ptccId): array {
    $q = $pdo->prepare('SELECT au.*, u.nome AS ptcc_name, u.patente AS ptcc_patente FROM arena_users au JOIN usuarios u ON u.id=au.user_id WHERE au.user_id=?');
    $q->execute([$ptccId]); $user = $q->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        // Usuários recém-criados começam sem moedas; depois do primeiro XP, o saldo é preservado.
        if ((int)$user['xp'] === 0) {
            $reset = $pdo->prepare('UPDATE arena_users SET coins=0 WHERE id=? AND xp=0 AND NOT EXISTS (SELECT 1 FROM arena_activities WHERE arena_user_id=arena_users.id)');
            $reset->execute([(int)$user['id']]);
            $user['coins'] = 0;
        }
        return $user;
    }
    $q = $pdo->prepare("INSERT IGNORE INTO arena_users (user_id,name) SELECT id, LEFT(COALESCE(NULLIF(nome,''),'Jogador'),120) FROM usuarios WHERE id=?");
    $q->execute([$ptccId]);
    $q = $pdo->prepare('SELECT au.*, u.nome AS ptcc_name, u.patente AS ptcc_patente FROM arena_users au JOIN usuarios u ON u.id=au.user_id WHERE au.user_id=?');
    $q->execute([$ptccId]); return $q->fetch(PDO::FETCH_ASSOC) ?: arenaJson(['ok'=>false,'message'=>'Não foi possível criar o perfil Arena.'],500);
}
function arenaRank(int $xp, array $ranks): array { foreach ($ranks as $i=>$rank) if ($xp >= $rank['min'] && $xp <= $rank['max']) return $rank + ['index'=>$i]; return $ranks[0] + ['index'=>0]; }
function arenaCanCheckin(PDO $pdo, int $uid): bool {
    $q = $pdo->prepare('SELECT 1 FROM arena_progress WHERE arena_user_id=? LIMIT 1');
    $q->execute([$uid]);
    return (bool) $q->fetchColumn();
}
function arenaHasItem(PDO $pdo, int $uid, string $id): bool { $q=$pdo->prepare("SELECT 1 FROM arena_active_items WHERE arena_user_id=? AND item_id=? AND (expires_at IS NULL OR expires_at>NOW())");$q->execute([$uid,$id]);return (bool)$q->fetchColumn(); }
function arenaCheckin(PDO $pdo, array &$u): array { $today=date('Y-m-d'); if ($u['last_checkin']===$today) return ['streak'=>(int)$u['streak'],'activated'=>false]; $yesterday=date('Y-m-d',strtotime('-1 day')); $streak=$u['last_checkin']===$yesterday?(int)$u['streak']+1:1; $q=$pdo->prepare('UPDATE arena_users SET streak=?,best_streak=GREATEST(best_streak,?),last_checkin=?,last_visit=? WHERE id=?');$q->execute([$streak,$streak,$today,$today,$u['id']]);$u['streak']=$streak;$u['last_checkin']=$today;$u['last_visit']=$today;return ['streak'=>$streak,'activated'=>true]; }
function arenaState(PDO $pdo, array $u, array $ranks): array {
    $rank=arenaRank((int)$u['xp'],$ranks);$next=$rank['index']<count($ranks)-1?$ranks[$rank['index']+1]:null;$range=$next?max(1,$next['min']-$rank['min']):1;$within=$next?max(0,(int)$u['xp']-$rank['min']):1;
    $q=$pdo->prepare("SELECT item_id FROM arena_active_items WHERE arena_user_id=? AND (expires_at IS NULL OR expires_at>NOW())");$q->execute([$u['id']]);$active=array_column($q->fetchAll(PDO::FETCH_ASSOC),'item_id');
    $canCheckin = arenaCanCheckin($pdo, (int)$u['id']);
    $modules=$pdo->query('SELECT m.*,COUNT(l.id) lesson_count FROM arena_modules m LEFT JOIN arena_lessons l ON l.module_id=m.id GROUP BY m.id ORDER BY m.position')->fetchAll(PDO::FETCH_ASSOC);
    $q=$pdo->prepare('SELECT l.*,m.title module_title,(SELECT COUNT(*) FROM arena_progress p WHERE p.arena_user_id=? AND p.lesson_id=l.id AND p.kind="lesson") lesson_done,(SELECT COUNT(*) FROM arena_progress p WHERE p.arena_user_id=? AND p.lesson_id=l.id AND p.kind="exercise") exercise_done FROM arena_lessons l JOIN arena_modules m ON m.id=l.module_id ORDER BY m.position,l.position');$q->execute([$u['id'],$u['id']]);$lessons=$q->fetchAll(PDO::FETCH_ASSOC);
    $q=$pdo->prepare('SELECT label,xp,coins,created_at FROM arena_activities WHERE arena_user_id=? ORDER BY id DESC LIMIT 8');$q->execute([$u['id']]);
    $name=(string)($u['ptcc_name']??$u['name']);$name=$name!==''?$name:'Jogador';$initials=mb_strtoupper(mb_substr(preg_replace('/[^\p{L}\s]/u','',$name),0,2));
    return ['xp'=>(int)$u['xp'],'coins'=>(int)$u['coins'],'rank'=>$rank,'next'=>$next,'withinPercent'=>min(100,(int)round($within/$range*100)),'totalPercent'=>min(100,(int)round((int)$u['xp']/2550*100)),'active'=>$active,'streak'=>(int)$u['streak'],'bestStreak'=>(int)$u['best_streak'],'lastCheckin'=>$u['last_checkin'],'freezeCount'=>(int)$u['freeze_count'],'canCheckin'=>$canCheckin,'modules'=>$modules,'lessons'=>$lessons,'activities'=>$q->fetchAll(PDO::FETCH_ASSOC),'userName'=>$name,'initials'=>$initials];
}
function arenaGrant(PDO $pdo, array &$u, string $label, array $items): array { $fire=arenaCheckin($pdo,$u);$xp=10*(arenaHasItem($pdo,(int)$u['id'],'xp_boost')?2:1);$coins=intdiv($xp,5)*(arenaHasItem($pdo,(int)$u['id'],'coin_boost')?2:1);$q=$pdo->prepare('UPDATE arena_users SET xp=xp+?,coins=coins+? WHERE id=?');$q->execute([$xp,$coins,$u['id']]);$q=$pdo->prepare('INSERT INTO arena_activities (arena_user_id,label,xp,coins) VALUES (?,?,?,?)');$q->execute([$u['id'],$label,$xp,$coins]);$u['xp']=(int)$u['xp']+$xp;$u['coins']=(int)$u['coins']+$coins;return ['xpGain'=>$xp,'coinGain'=>$coins,'streak'=>$fire['streak'],'streakActivated'=>$fire['activated'],'rain'=>arenaHasItem($pdo,(int)$u['id'],'money_rain')]; }

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) {
if (!arenaSchemaReady($pdo)) arenaJson(['ok'=>false,'message'=>arenaSchemaMessage()], 503);
$u=arenaUser($pdo,$ptccUserId);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') arenaJson(['ok'=>true,'state'=>arenaState($pdo,$u,$ranks)]);
csrf_exigir();
$action=(string)($_POST['action']??'');$uid=(int)$u['id'];
if ($action==='checkin') { if (!arenaCanCheckin($pdo, $uid)) arenaJson(['ok'=>false,'message'=>'Conclua uma videoaula ou um exercício antes de acender o foguinho.','state'=>arenaState($pdo,$u,$ranks)]); $fire=arenaCheckin($pdo,$u); arenaJson(['ok'=>$fire['activated'],'message'=>$fire['activated']?'Foguinho aceso! Ofensiva de '.$fire['streak'].' dia(s).':'Seu foguinho já foi aceso hoje.','state'=>arenaState($pdo,$u,$ranks)]); }
if ($action==='get_exercise') {$id=(int)($_POST['lesson_id']??0);$q=$pdo->prepare('SELECT id,question,options FROM arena_exercises WHERE lesson_id=? ORDER BY id');$q->execute([$id]);$rows=$q->fetchAll(PDO::FETCH_ASSOC);foreach($rows as &$row)$row['options']=json_decode($row['options'],true)?:[];if(!$rows)arenaJson(['ok'=>false,'message'=>'Exercício inválido.'],404);arenaJson(['ok'=>true,'exercise'=>$rows]);}
if ($action==='complete_lesson') {$id=(int)($_POST['lesson_id']??0);$q=$pdo->prepare('SELECT id FROM arena_lessons WHERE id=?');$q->execute([$id]);if(!$q->fetchColumn())arenaJson(['ok'=>false,'message'=>'Aula inválida.'],404);$q=$pdo->prepare('SELECT 1 FROM arena_progress WHERE arena_user_id=? AND lesson_id=? AND kind="lesson"');$q->execute([$uid,$id]);if($q->fetchColumn())arenaJson(['ok'=>false,'message'=>'Esta aula já foi concluída.']);$pdo->beginTransaction();try{$pdo->prepare('INSERT INTO arena_progress (arena_user_id,lesson_id,kind) VALUES (?,? ,"lesson")')->execute([$uid,$id]);$reward=arenaGrant($pdo,$u,'Aula concluída',$items);$pdo->commit();arenaJson(['ok'=>true]+$reward+['state'=>arenaState($pdo,$u,$ranks)]);}catch(Throwable $e){$pdo->rollBack();arenaJson(['ok'=>false,'message'=>'Não foi possível registrar a aula.'],500);}}
if ($action==='complete_exercise') {$id=(int)($_POST['lesson_id']??0);$answers=json_decode((string)($_POST['answers']??'[]'),true);if(!is_array($answers)||count($answers)>20)arenaJson(['ok'=>false,'message'=>'Respostas inválidas.'],422);$q=$pdo->prepare('SELECT answer FROM arena_exercises WHERE lesson_id=? ORDER BY id');$q->execute([$id]);$rows=$q->fetchAll(PDO::FETCH_ASSOC);if(!$rows)arenaJson(['ok'=>false,'message'=>'Exercício inválido.'],404);$q=$pdo->prepare('SELECT 1 FROM arena_progress WHERE arena_user_id=? AND lesson_id=? AND kind="exercise"');$q->execute([$uid,$id]);if($q->fetchColumn())arenaJson(['ok'=>false,'message'=>'Este exercício já foi concluído.']);$score=0;foreach($rows as $i=>$row)if(isset($answers[$i])&&(int)$answers[$i]===(int)$row['answer'])$score++;$pdo->beginTransaction();try{$pdo->prepare('INSERT INTO arena_progress (arena_user_id,lesson_id,kind) VALUES (?,? ,"exercise")')->execute([$uid,$id]);$reward=arenaGrant($pdo,$u,'Exercício concluído',$items);$pdo->commit();arenaJson(['ok'=>true,'score'=>$score,'total'=>count($rows)]+$reward+['state'=>arenaState($pdo,$u,$ranks)]);}catch(Throwable $e){$pdo->rollBack();arenaJson(['ok'=>false,'message'=>'Não foi possível registrar o exercício.'],500);}}
if ($action==='buy') {$id=(string)($_POST['item']??'');if(!isset($items[$id]))arenaJson(['ok'=>false,'message'=>'Item não encontrado.'],404);$item=$items[$id];if($id!=='streak_freeze'&&arenaHasItem($pdo,$uid,$id))arenaJson(['ok'=>false,'message'=>'Este item já está ativo.']);$pdo->beginTransaction();$q=$id==='streak_freeze'?$pdo->prepare('UPDATE arena_users SET coins=coins-?,freeze_count=freeze_count+1 WHERE id=? AND coins>=?'):$pdo->prepare('UPDATE arena_users SET coins=coins-? WHERE id=? AND coins>=?');$q->execute([$item['price'],$uid,$item['price']]);if($q->rowCount()<1){$pdo->rollBack();arenaJson(['ok'=>false,'message'=>'AFDIS insuficientes para esta compra.']);}if($id!=='streak_freeze'){$pdo->prepare('DELETE FROM arena_active_items WHERE arena_user_id=? AND item_id=?')->execute([$uid,$id]);$pdo->prepare('INSERT INTO arena_active_items (arena_user_id,item_id,expires_at) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 1 DAY))')->execute([$uid,$id]);}$pdo->commit();$u=arenaUser($pdo,$ptccUserId);arenaJson(['ok'=>true,'message'=>$item['name'].' adquirido com sucesso!','state'=>arenaState($pdo,$u,$ranks)]);}
if ($action==='get_comments') {$id=(int)($_POST['lesson_id']??0);$q=$pdo->prepare('SELECT c.id,c.author,c.body,c.likes,c.dislikes,c.created_at,COALESCE(v.vote,0) my_vote FROM arena_comments c LEFT JOIN arena_comment_votes v ON v.comment_id=c.id AND v.arena_user_id=? WHERE c.lesson_id=? ORDER BY c.id');$q->execute([$uid,$id]);arenaJson(['ok'=>true,'comments'=>$q->fetchAll(PDO::FETCH_ASSOC)]);}
if ($action==='comment') {$id=(int)($_POST['lesson_id']??0);$text=trim((string)($_POST['text']??''));if($id<1||$text===''||mb_strlen($text)>180)arenaJson(['ok'=>false,'message'=>'Comentário inválido.'],422);$pdo->prepare('INSERT INTO arena_comments (arena_user_id,lesson_id,author,body) VALUES (?,?,?,?)')->execute([$uid,$id,mb_substr((string)$u['name'],0,120),$text]);$q=$pdo->prepare('SELECT id,author,body,likes,dislikes,created_at,0 my_vote FROM arena_comments WHERE lesson_id=? ORDER BY id');$q->execute([$id]);arenaJson(['ok'=>true,'comments'=>$q->fetchAll(PDO::FETCH_ASSOC)]);}
if ($action==='reset') {
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM arena_comment_votes WHERE arena_user_id=?')->execute([$uid]);
        $pdo->prepare('DELETE v FROM arena_comment_votes v JOIN arena_comments c ON c.id=v.comment_id WHERE c.arena_user_id=?')->execute([$uid]);
        $pdo->prepare('DELETE FROM arena_comments WHERE arena_user_id=?')->execute([$uid]);
        $pdo->prepare('DELETE FROM arena_progress WHERE arena_user_id=?')->execute([$uid]);
        $pdo->prepare('DELETE FROM arena_activities WHERE arena_user_id=?')->execute([$uid]);
        $pdo->prepare('DELETE FROM arena_active_items WHERE arena_user_id=?')->execute([$uid]);
        $pdo->prepare('UPDATE arena_users SET xp=0,coins=0,streak=0,best_streak=0,last_visit=NULL,last_checkin=NULL,freeze_count=0 WHERE id=?')->execute([$uid]);
        $pdo->commit();
        arenaJson(['ok'=>true,'message'=>'Seu progresso Arena foi reiniciado.']);
    } catch (Throwable $e) { $pdo->rollBack(); arenaJson(['ok'=>false,'message'=>'Não foi possível reiniciar seu progresso.'],500); }
}
if ($action==='vote_comment') {$comment=(int)($_POST['comment_id']??0);$vote=(int)($_POST['vote']??0);if(!in_array($vote,[1,-1],true))arenaJson(['ok'=>false,'message'=>'Voto inválido.'],422);$pdo->beginTransaction();$q=$pdo->prepare('SELECT vote FROM arena_comment_votes WHERE comment_id=? AND arena_user_id=? FOR UPDATE');$q->execute([$comment,$uid]);$old=$q->fetchColumn();if($old===false){$pdo->prepare('INSERT INTO arena_comment_votes VALUES (?,?,?)')->execute([$comment,$uid,$vote]);$pdo->prepare('UPDATE arena_comments SET likes=likes+?,dislikes=dislikes+? WHERE id=?')->execute([$vote===1?1:0,$vote===-1?1:0,$comment]);}elseif((int)$old===$vote){$pdo->prepare('DELETE FROM arena_comment_votes WHERE comment_id=? AND arena_user_id=?')->execute([$comment,$uid]);$pdo->prepare('UPDATE arena_comments SET likes=GREATEST(0,likes-?),dislikes=GREATEST(0,dislikes-?) WHERE id=?')->execute([$vote===1?1:0,$vote===-1?1:0,$comment]);}else{$pdo->prepare('UPDATE arena_comment_votes SET vote=? WHERE comment_id=? AND arena_user_id=?')->execute([$vote,$comment,$uid]);$pdo->prepare('UPDATE arena_comments SET likes=likes+?,dislikes=dislikes+? WHERE id=?')->execute([$vote===1?1:-1,$vote===-1?1:-1,$comment]);}$pdo->commit();arenaJson(['ok'=>true]);}
arenaJson(['ok'=>false,'message'=>'Ação inválida.'],400);

}
