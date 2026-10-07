<?php
declare(strict_types=1);
require_once __DIR__ . '/../../back-end/seguranca.php';
iniciar_sessao_segura();
if (empty($_SESSION['id'])) {
    header('Location: ../../front-end/login.php?next=community', true, 302);
    exit;
}
require_once __DIR__ . '/../back-end/bootstrap.php';
try {
    $user = comunidade_usuario($pdo, true);
    comunidade_instalar_schema($pdo);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM comunidade_publicacoes WHERE usuario_id = ?');
    $stmt->execute([$user['id']]);
    $user['posts_count'] = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    if ($e instanceof PDOException) tratar_erro_bd($e, 'sunsethub_page');
    http_response_code(500);
    echo 'Não foi possível iniciar a comunidade. Confira o banco principal, a permissão CREATE e a pasta community/uploads.';
    exit;
}
$profileUrl = '../../front-end/perfil.php';
?><!doctype html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Sunset Hub: converse com amigos, participe de grupos e compartilhe vídeos e histórias.">
<title>Comunidade | Sunset Hub</title>
<link rel="stylesheet" href="assets/css/style.css?v=8">
</head><body>
<div class="ambient ambient-one"></div><div class="ambient ambient-two"></div>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<div class="app-shell">
<aside class="sidebar">
  <a class="brand" href="../../front-end/home.php"><span class="brand-mark">◒</span><span>sunset<span>hub</span></span></a>
  <p class="eyebrow sidebar-label">ÁREA DA COMUNIDADE</p>
  <nav id="main-nav" class="main-nav" aria-label="Navegação da comunidade">
    <button class="nav-item active" data-section="inicio" aria-label="Início">⌂ <span>Início</span></button>
    <button class="nav-item" data-section="mensagens" aria-label="Mensagens">◉ <span>Mensagens</span></button>
    <button class="nav-item" data-section="videos" aria-label="Vídeos">▷ <span>Vídeos</span></button>
    <button class="nav-item" data-section="feedvisual" aria-label="Reels e imagens">▧ <span>Reels e imagens</span></button>
    <button class="nav-item" data-section="comunidades" aria-label="Grupos">◈ <span>Grupos</span></button>
    <button class="nav-item nav-create" data-section="publicar" aria-label="Publicar">＋ <span>Publicar</span></button>
  </nav>
  <div class="sidebar-bottom"><a class="nav-item" href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>">◎ <span>Meu perfil no portal</span></a><div class="sidebar-note"><span class="status-dot"></span><span>Aprender fica melhor em comunidade.</span></div></div>
</aside>
<main class="main-content">
  <header class="topbar"><div><p class="eyebrow">SUNSET HUB · COMUNIDADE</p><h1 id="page-title">Sua comunidade, no seu ritmo.</h1></div><div class="top-actions"><div class="notification-wrap"><button class="icon-btn notification-toggle" id="notifications-toggle" type="button" aria-label="Notificações" aria-expanded="false" title="Notificações">♢<span id="notifications-badge" class="notification-badge hidden"></span></button><div id="notifications-panel" class="notification-panel hidden"><div class="notification-heading"><strong>Notificações</strong><button id="notifications-read-all" type="button">Marcar lidas</button></div><div id="notifications-list" class="notification-list"></div></div></div><button class="icon-btn" id="search-toggle" type="button" aria-label="Buscar conteúdo" title="Buscar (/)">⌕</button><a class="top-user" aria-label="Abrir meu perfil" href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(mb_strtoupper(mb_substr($user['name'],0,1,'UTF-8'),'UTF-8'), ENT_QUOTES, 'UTF-8') ?></a></div></header>
  <div id="search-panel" class="search-panel hidden"><label for="global-search">Buscar publicações, vídeos e grupos</label><input id="global-search" type="search" placeholder="Digite para buscar…" autocomplete="off"><p id="search-hint">Pesquise por assunto, título ou grupo.</p><div id="search-results" class="search-results" role="listbox"></div></div>
  <section id="section-inicio" class="page-section active-section">
    <div class="hero-card"><div><p class="eyebrow warm">SUA COMUNIDADE FINANCEIRA</p><h2>Compartilhe ideias.<br><em>Crie conexões.</em></h2><p class="hero-copy">Converse com seus amigos, participe de grupos e aprenda com vídeos, carrosséis e stories.</p><button class="primary-btn" data-section="mensagens">Abrir mensagens <span>→</span></button></div><div class="hero-orbit"><div class="sun-core"></div><div class="orbit orbit-a"></div><div class="orbit orbit-b"></div><span class="orbit-star star-a">✦</span><span class="orbit-star star-b">✧</span></div></div>
    <div class="section-heading"><div><p class="eyebrow">POR 24 HORAS</p><h2>Stories</h2></div><button class="outline-btn" data-story-create type="button">＋ Seu story</button></div><div id="stories-row" class="stories-row"></div>
    <div class="section-heading"><div><p class="eyebrow">TROCA DE EXPERIÊNCIAS</p><h2>Publicações recentes</h2></div><button class="text-btn" data-section="feedvisual">Ver fotos e curtos →</button></div><div id="home-feed" class="content-grid"></div>
    <div class="section-heading compact"><div><p class="eyebrow">ENCONTRE SUA TURMA</p><h2>Grupos para você</h2></div><button class="text-btn" data-section="comunidades">Ver grupos →</button></div><div id="home-groups" class="community-grid"></div>
  </section>
  <section id="section-mensagens" class="page-section">
    <div class="section-heading page-heading"><div><p class="eyebrow">AMIGOS E MENSAGENS</p><h2>Converse com seus amigos.</h2></div></div>
    <div class="messages-layout">
      <aside class="friends-panel"><label for="friend-search">Encontrar pessoas</label><input id="friend-search" type="search" placeholder="Buscar pelo nome…" autocomplete="off"><div id="friend-results" class="friend-results"></div><div class="mini-heading">Solicitações recebidas <span id="request-count"></span></div><div id="friend-requests" class="friend-list"></div><div class="mini-heading">Seus amigos</div><div id="friend-list" class="friend-list"></div></aside>
      <section class="chat-panel"><div id="chat-empty" class="chat-empty"><span>◉</span><h3>Suas mensagens</h3><p>Adicione amigos e escolha alguém para começar uma conversa privada.</p></div><div id="chat-active" class="chat-active hidden"><header id="chat-header" class="chat-header"></header><div id="chat-messages" class="chat-messages" aria-live="polite"></div><form id="chat-form" class="chat-compose" enctype="multipart/form-data"><label class="attachment-button" for="chat-image" title="Anexar imagem" aria-label="Anexar imagem">▧</label><input id="chat-image" class="chat-file-input" name="image" type="file" accept="image/jpeg,image/png,image/webp"><input id="chat-input" name="body" maxlength="4000" autocomplete="off" placeholder="Escreva uma mensagem…" aria-label="Mensagem"><button class="primary-btn small" type="submit">Enviar</button></form><small class="chat-attachment-help">Imagens JPG, PNG ou WebP até 8 MB.</small></div></section>
    </div>
  </section>
  <section id="section-videos" class="page-section">
    <div class="section-heading page-heading"><div><p class="eyebrow">VÍDEOS DA COMUNIDADE</p><h2>Assista e compartilhe.</h2></div><button id="video-upload-open" class="primary-btn small" type="button">＋ Enviar vídeo</button></div>
    <div class="subnav" role="tablist"><button class="subnav-btn active" data-video-tab="youtube" role="tab" aria-selected="true">Vídeos</button><button class="subnav-btn" data-video-tab="social" role="tab" aria-selected="false">Curtos e carrosséis</button></div>
    <div id="videos-youtube" class="video-grid"></div><div id="videos-social" class="hidden"><div id="videos-social-feed" class="visual-grid"></div></div>
  </section>
  <section id="section-feedvisual" class="page-section">
    <div class="section-heading page-heading"><div><p class="eyebrow">FORMATO SOCIAL</p><h2>Carrosséis e vídeos curtos.</h2></div><button id="visual-create-open" class="primary-btn small" type="button">＋ Criar publicação</button></div>
    <div id="visual-feed" class="visual-grid"></div>
  </section>
  <section id="section-comunidades" class="page-section">
    <div class="section-heading page-heading"><div><p class="eyebrow">GRUPOS DE INTERESSE</p><h2>Encontre sua turma.</h2></div><button id="group-create-open" class="primary-btn small" type="button">＋ Criar grupo</button></div>
    <div class="section-heading compact"><div><p class="eyebrow">SUA PARTICIPAÇÃO</p><h2>Meus grupos</h2></div></div><div id="groups-joined" class="community-grid"></div>
    <div class="section-heading compact"><div><p class="eyebrow">DESCUBRA</p><h2>Grupos disponíveis</h2></div></div><div id="groups-discover" class="community-grid"></div>

  </section>
  <section id="section-publicar" class="page-section">
    <div class="section-heading"><div><p class="eyebrow warm">COMPARTILHE COM RESPEITO</p><h2>Uma ideia pode ajudar alguém.</h2></div></div>
    <div class="creator-layout"><div class="create-card"><div class="create-icon">✦</div><h3>Nova publicação de texto</h3><p>Faça uma pergunta, compartilhe uma dica ou conte uma conquista.</p><textarea id="post-body" maxlength="1000" placeholder="O que você aprendeu ou gostaria de perguntar?" aria-label="Texto da publicação"></textarea><div class="create-footer"><span id="char-count">0/1000</span><button id="publish-post" class="primary-btn" type="button">Publicar <span>→</span></button></div></div><div class="creator-options"><button class="creator-option" data-story-create type="button"><b class="option-story">◌</b><span><strong>Story de 24 horas</strong><small>Foto ou vídeo curto que expira em um dia</small></span><i>→</i></button><button class="creator-option" id="visual-create-short" type="button"><b class="option-video">▷</b><span><strong>Vídeo curto ou carrossel</strong><small>Compartilhe conteúdo visual com a comunidade</small></span><i>→</i></button><button class="creator-option" id="video-upload-create" type="button"><b class="option-photo">▶</b><span><strong>Vídeo completo</strong><small>Envie MP4 com miniatura e descrição</small></span><i>→</i></button><div class="empty-card"><span>◇</span><h3>Compartilhe com segurança</h3><p>Nunca publique senhas, cartões, documentos ou dados bancários pessoais.</p></div></div></div>
  </section>
</main></div>

<div id="group-room-modal" class="modal-backdrop hidden group-room-backdrop"><div class="group-room-modal-shell"><section id="group-room" class="group-room" role="dialog" aria-modal="true" aria-labelledby="group-room-title"><header class="group-room-head"><button id="group-back" class="outline-btn" data-close-modal="group-room-modal" type="button">Fechar</button><div id="group-room-title"></div><div id="group-admin-actions" class="hidden"><button id="group-add-tab" class="outline-btn" type="button">＋ Adicionar aba</button></div></header><div class="group-room-grid"><div class="group-main"><div id="group-tabs" class="subnav group-tabs"></div><div id="group-call-placeholder" class="call-placeholder hidden"><span>▣</span><h3>Videochamadas</h3><p>Esta área está reservada para chamadas. A função de vídeo ainda não está habilitada.</p></div><div id="group-chat-area" class="group-chat-area"><div id="group-messages" class="chat-messages"></div><form id="group-chat-form" class="chat-compose" enctype="multipart/form-data"><label class="attachment-button" for="group-chat-image" title="Anexar imagem" aria-label="Anexar imagem">▧</label><input id="group-chat-image" class="chat-file-input" name="image" type="file" accept="image/jpeg,image/png,image/webp"><input id="group-chat-input" name="body" maxlength="1500" placeholder="Escreva para os membros…" aria-label="Mensagem do grupo"><button class="primary-btn small" type="submit">Enviar</button></form><small class="chat-attachment-help">Imagens JPG, PNG ou WebP até 8 MB.</small></div></div><aside class="members-panel"><h3>Membros <span id="member-count"></span></h3><div class="mini-heading">Online</div><div id="members-online" class="member-list"></div><div class="mini-heading">Offline</div><div id="members-offline" class="member-list"></div></aside></div></section></div></div>

<div id="onboarding" class="onboarding hidden" role="dialog" aria-modal="true" aria-labelledby="tour-title"><div class="onboarding-card"><p class="eyebrow" id="tour-step">BOAS-VINDAS · 1 DE 4</p><h2 id="tour-title">Bem-vindo ao Sunset Hub.</h2><p id="tour-copy">Adicione amigos, converse, compartilhe conteúdos e participe de grupos.</p><div class="tour-progress"><i class="active"></i><i></i><i></i><i></i></div><div class="tour-actions"><button id="tour-skip" class="tour-skip" type="button">Pular apresentação</button><button id="tour-next" class="primary-btn" type="button">Próximo →</button></div></div></div>

<div id="video-modal" class="modal-backdrop hidden"><div class="auth-modal"><button class="modal-close" data-close-modal="video-modal" aria-label="Fechar">×</button><div class="auth-symbol">▷</div><h2>Enviar vídeo</h2><p class="modal-copy">Publique um MP4 para assistir no Sunset Hub. A miniatura é obrigatória.</p><form id="video-upload-form" class="upload-form" enctype="multipart/form-data"><label>Título<input name="title" required minlength="3" maxlength="160"></label><label>Descrição<textarea name="description" maxlength="500" rows="3"></textarea></label><label>Arquivo de vídeo MP4<input name="video" type="file" accept="video/mp4,.mp4" required></label><label>Miniatura obrigatória (JPG, PNG ou WebP)<input name="thumbnail" type="file" accept="image/jpeg,image/png,image/webp" required></label><p class="upload-help">Limite do Sunset Hub: 100 MB por vídeo e 8 MB por imagem. O XAMPP também precisa permitir esse tamanho no php.ini.</p><div class="form-feedback" data-form-feedback></div><button class="primary-btn full" type="submit">Enviar vídeo</button></form></div></div>
<div id="group-modal" class="modal-backdrop hidden"><div class="auth-modal"><button class="modal-close" data-close-modal="group-modal" aria-label="Fechar">×</button><div class="auth-symbol">◈</div><h2>Criar grupo</h2><p class="modal-copy">Você será o administrador e o primeiro membro.</p><form id="group-create-form" class="upload-form" enctype="multipart/form-data"><label>Nome do grupo<input name="name" required minlength="3" maxlength="100"></label><label>Descrição curta<textarea name="description" required minlength="5" maxlength="255" rows="3"></textarea></label><label>Imagem do grupo (obrigatória)<input name="image" type="file" accept="image/jpeg,image/png,image/webp" required></label><p class="upload-help">JPG, PNG ou WebP, até 8 MB.</p><div class="form-feedback" data-form-feedback></div><button class="primary-btn full" type="submit">Criar grupo</button></form></div></div>
<div id="group-tab-modal" class="modal-backdrop hidden"><div class="auth-modal"><button class="modal-close" data-close-modal="group-tab-modal" aria-label="Fechar">×</button><div class="auth-symbol">＋</div><h2>Adicionar aba</h2><p class="modal-copy">Crie mais um espaço para os membros do grupo.</p><form id="group-tab-form" class="upload-form"><label>Nome da aba<input name="title" required minlength="2" maxlength="80"></label><label>Tipo<select name="type"><option value="chat">Bate-papo</option><option value="announcements">Avisos (somente admin publica)</option><option value="call">Videochamada (em breve)</option></select></label><div class="form-feedback" data-form-feedback></div><button class="primary-btn full" type="submit">Adicionar aba</button></form></div></div>
<div id="visual-modal" class="modal-backdrop hidden"><div class="auth-modal"><button class="modal-close" data-close-modal="visual-modal" aria-label="Fechar">×</button><div class="auth-symbol">▧</div><h2>Publicação visual</h2><p class="modal-copy">Crie um carrossel informativo ou publique um vídeo curto.</p><form id="visual-create-form" class="upload-form" enctype="multipart/form-data"><label>Formato<select name="type" id="visual-type"><option value="carousel">Carrossel de imagens (1–10)</option><option value="short">Vídeo curto MP4</option></select></label><label>Legenda<textarea name="caption" maxlength="1500" rows="3"></textarea></label><label id="visual-file-label">Imagens do carrossel<input id="visual-media" name="media[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required></label><p id="visual-upload-help" class="upload-help">Selecione de 1 a 10 imagens, cada uma com até 8 MB.</p><div class="form-feedback" data-form-feedback></div><button class="primary-btn full" type="submit">Publicar</button></form></div></div>
<div id="story-modal" class="modal-backdrop hidden"><div class="auth-modal"><button class="modal-close" data-close-modal="story-modal" aria-label="Fechar">×</button><div class="auth-symbol">◌</div><h2>Novo story</h2><p class="modal-copy">O story some automaticamente após 24 horas.</p><form id="story-create-form" class="upload-form" enctype="multipart/form-data"><label>Foto ou vídeo MP4<input name="media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4" required></label><label>Legenda (opcional)<input name="caption" maxlength="500"></label><p class="upload-help">Imagem até 8 MB; vídeo MP4 até 100 MB.</p><div class="form-feedback" data-form-feedback></div><button class="primary-btn full" type="submit">Publicar story</button></form></div></div>
<div id="video-viewer" class="modal-backdrop hidden"><div class="video-viewer-dialog" role="dialog" aria-modal="true" aria-labelledby="viewer-title"><button class="modal-close" data-close-modal="video-viewer" aria-label="Fechar">×</button><video id="viewer-player" controls playsinline preload="metadata"></video><div class="video-viewer-body"><h2 id="viewer-title"></h2><div id="viewer-owner" class="video-viewer-owner"></div><p id="viewer-description" class="video-viewer-description"></p><div class="video-viewer-actions"><span id="viewer-views"></span><button id="viewer-like" type="button" data-react-video="1">👍 <span>0</span></button><button id="viewer-dislike" type="button" data-react-video="-1">👎 <span>0</span></button></div><h3>Comentários</h3><div id="video-comments" class="video-comments"></div><form id="comment-form" class="chat-compose"><input id="comment-input" maxlength="1500" placeholder="Escreva um comentário…" required><button class="primary-btn small" type="submit">Enviar</button></form></div></div></div>
<div id="story-viewer" class="modal-backdrop hidden"><div class="story-view-card"><button class="modal-close" data-close-modal="story-viewer" aria-label="Fechar">×</button><div id="story-view-content"></div></div></div>
<script>window.SUNSET_USER=<?= json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="assets/js/app.js?v=8" defer></script>
</body></html>
