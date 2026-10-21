<?php
declare(strict_types=1);

/** Adiciona colunas de anexos sem migração manual e preserva mensagens existentes. */
function comunidade_garantir_colunas_anexo(PDO $pdo): void
{
    foreach (['comunidade_mensagens_privadas', 'comunidade_mensagens_grupo'] as $table) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME='anexo_path'");
        $check->execute([$table]);
        if (!(int)$check->fetchColumn()) $pdo->exec("ALTER TABLE {$table} ADD COLUMN anexo_path VARCHAR(255) NULL AFTER corpo");
    }
    foreach (['comunidade_publicacoes','comunidade_videos','comunidade_posts_visuais','comunidade_stories'] as $table) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME='localizacao'");
        $check->execute([$table]);
        if (!(int)$check->fetchColumn()) $pdo->exec("ALTER TABLE {$table} ADD COLUMN localizacao VARCHAR(160) NULL AFTER criado_em");
    }
}

function comunidade_garantir_colunas_moderacao(PDO $pdo): void
{
    foreach (['comunidade_publicacoes','comunidade_videos','comunidade_posts_visuais','comunidade_stories','comunidade_comentarios_video','comunidade_mensagens_privadas','comunidade_mensagens_grupo','comunidade_grupos','comunidade_abas_grupo'] as $table) {
        $check=$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME='moderacao_status'");$check->execute([$table]);
        if (!(int)$check->fetchColumn()) $pdo->exec("ALTER TABLE {$table} ADD COLUMN moderacao_status ENUM('visible','hidden','removed') NOT NULL DEFAULT 'visible'");
    }
    $check=$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='comunidade_denuncias' AND COLUMN_NAME='status'");
    if (!(int)$check->fetchColumn()) $pdo->exec("ALTER TABLE comunidade_denuncias ADD COLUMN status ENUM('pending','kept','hidden','removed') NOT NULL DEFAULT 'pending', ADD COLUMN revisado_por INT NULL, ADD COLUMN revisado_em TIMESTAMP NULL DEFAULT NULL, ADD KEY idx_denuncias_status (status)");
}

const COMUNIDADE_TERMOS_VERSAO = '2026-10-21';
function comunidade_garantir_tabela_termos(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS comunidade_termos_aceites (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, usuario_id INT NOT NULL, versao VARCHAR(30) NOT NULL,
      aceito_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ip_address VARCHAR(45) NOT NULL, user_agent VARCHAR(500) NOT NULL,
      PRIMARY KEY (id), UNIQUE KEY uq_termos_usuario_versao (usuario_id, versao), KEY idx_termos_usuario (usuario_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
function comunidade_tem_aceite(PDO $pdo, int $uid): bool
{
    $s=$pdo->prepare('SELECT 1 FROM comunidade_termos_aceites WHERE usuario_id=? AND versao=? LIMIT 1');$s->execute([$uid,COMUNIDADE_TERMOS_VERSAO]);return (bool)$s->fetchColumn();
}
function comunidade_registrar_aceite(PDO $pdo, int $uid): void
{
    $ip=substr((string)($_SERVER['REMOTE_ADDR']??''),0,45);$agent=substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,500);
    $s=$pdo->prepare('INSERT INTO comunidade_termos_aceites(usuario_id,versao,ip_address,user_agent) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE aceito_em=CURRENT_TIMESTAMP,ip_address=VALUES(ip_address),user_agent=VALUES(user_agent)');$s->execute([$uid,COMUNIDADE_TERMOS_VERSAO,$ip,$agent]);
}

/** Cria somente tabelas sociais faltantes; nunca altera nem apaga dados existentes. */
function comunidade_instalar_schema(PDO $pdo): void
{
    static $ready = false;
    if ($ready) return;
    comunidade_garantir_tabela_termos($pdo);

    $tables = [
        'comunidade_publicacoes', 'comunidade_videos', 'comunidade_grupos', 'comunidade_membros',
        'comunidade_amizades', 'comunidade_seguidores', 'comunidade_mensagens_privadas', 'comunidade_reacoes_video',
        'comunidade_comentarios_video', 'comunidade_posts_visuais', 'comunidade_midias_visuais',
        'comunidade_stories', 'comunidade_presenca', 'comunidade_grupo_imagens',
        'comunidade_abas_grupo', 'comunidade_mensagens_grupo', 'comunidade_notificacoes', 'comunidade_denuncias', 'comunidade_bloqueios', 'comunidade_termos_aceites',
    ];
    $marks = implode(',', array_fill(0, count($tables), '?'));
    $check = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$marks})");
    $check->execute($tables);
    if ((int)$check->fetchColumn() === count($tables)) {
        comunidade_garantir_colunas_anexo($pdo);
        comunidade_garantir_colunas_moderacao($pdo);
        $pdo->exec('INSERT IGNORE INTO comunidade_membros (grupo_id, usuario_id) SELECT id, criado_por FROM comunidade_grupos WHERE criado_por IS NOT NULL');
        $pdo->exec("INSERT IGNORE INTO comunidade_seguidores (seguidor_id, seguido_id) SELECT usuario_a, usuario_b FROM comunidade_amizades WHERE status='accepted' UNION SELECT usuario_b, usuario_a FROM comunidade_amizades WHERE status='accepted'");
        $ready = true;
        return;
    }

    $statements = [
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_seguidores (
  seguidor_id INT NOT NULL, seguido_id INT NOT NULL, criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (seguidor_id, seguido_id), KEY idx_seguidores_seguido (seguido_id),
  CONSTRAINT fk_seguidor_usuario FOREIGN KEY (seguidor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_seguido_usuario FOREIGN KEY (seguido_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_bloqueios (
  bloqueador_id INT NOT NULL,
  bloqueado_id INT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (bloqueador_id, bloqueado_id), KEY idx_bloqueios_bloqueado (bloqueado_id),
  CONSTRAINT fk_bloqueio_bloqueador FOREIGN KEY (bloqueador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_bloqueio_bloqueado FOREIGN KEY (bloqueado_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_publicacoes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  corpo VARCHAR(1000) NOT NULL,
  curtidas INT UNSIGNED NOT NULL DEFAULT 0,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_comunidade_publicacoes_criado (criado_em, id), KEY idx_comunidade_publicacoes_usuario (usuario_id),
  CONSTRAINT fk_comunidade_publicacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_videos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  descricao VARCHAR(500) NULL,
  url VARCHAR(500) NULL,
  miniatura VARCHAR(255) NULL,
  visualizacoes INT UNSIGNED NOT NULL DEFAULT 0,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_comunidade_videos_criado (criado_em, id),
  CONSTRAINT fk_comunidade_videos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_grupos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  descricao VARCHAR(255) NOT NULL,
  categoria VARCHAR(60) NOT NULL DEFAULT 'Educação financeira',
  cor VARCHAR(20) NOT NULL DEFAULT '#ff7248',
  criado_por INT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_comunidade_grupos_nome (nome),
  CONSTRAINT fk_comunidade_grupos_criador FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_membros (
  grupo_id INT UNSIGNED NOT NULL,
  usuario_id INT NOT NULL,
  entrou_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (grupo_id, usuario_id), KEY idx_comunidade_membros_usuario (usuario_id),
  CONSTRAINT fk_comunidade_membros_grupo FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE,
  CONSTRAINT fk_comunidade_membros_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_amizades (
  usuario_a INT NOT NULL,
  usuario_b INT NOT NULL,
  solicitado_por INT NOT NULL,
  status ENUM('pending','accepted') NOT NULL DEFAULT 'pending',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_a, usuario_b), KEY idx_amizades_destino (usuario_b, status), KEY idx_amizades_solicitante (solicitado_por),
  CONSTRAINT fk_amizade_a FOREIGN KEY (usuario_a) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_amizade_b FOREIGN KEY (usuario_b) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_amizade_solicitante FOREIGN KEY (solicitado_por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_mensagens_privadas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  remetente_id INT NOT NULL,
  destinatario_id INT NOT NULL,
  corpo VARCHAR(4000) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lida_em TIMESTAMP NULL DEFAULT NULL,
  anexo_path VARCHAR(255) NULL,
  PRIMARY KEY (id), KEY idx_dm_par (remetente_id, destinatario_id, id), KEY idx_dm_destino (destinatario_id, remetente_id, id),
  CONSTRAINT fk_dm_remetente FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_dm_destinatario FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_reacoes_video (
  video_id INT UNSIGNED NOT NULL,
  usuario_id INT NOT NULL,
  reacao TINYINT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (video_id, usuario_id), KEY idx_reacoes_usuario (usuario_id),
  CONSTRAINT fk_reacao_video FOREIGN KEY (video_id) REFERENCES comunidade_videos(id) ON DELETE CASCADE,
  CONSTRAINT fk_reacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_comentarios_video (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  video_id INT UNSIGNED NOT NULL,
  usuario_id INT NOT NULL,
  corpo VARCHAR(1500) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_comentarios_video (video_id, id),
  CONSTRAINT fk_comentario_video FOREIGN KEY (video_id) REFERENCES comunidade_videos(id) ON DELETE CASCADE,
  CONSTRAINT fk_comentario_video_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_posts_visuais (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  tipo ENUM('carousel','short') NOT NULL,
  legenda VARCHAR(1500) NOT NULL DEFAULT '',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_post_visual_data (criado_em, id),
  CONSTRAINT fk_post_visual_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_midias_visuais (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id BIGINT UNSIGNED NOT NULL,
  tipo ENUM('image','video') NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  posicao SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id), KEY idx_midias_post (post_id, posicao),
  CONSTRAINT fk_midia_post_visual FOREIGN KEY (post_id) REFERENCES comunidade_posts_visuais(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_stories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  tipo ENUM('image','video') NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  legenda VARCHAR(500) NOT NULL DEFAULT '',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expira_em DATETIME NOT NULL,
  PRIMARY KEY (id), KEY idx_story_expira (expira_em), KEY idx_story_usuario (usuario_id, criado_em),
  CONSTRAINT fk_story_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_denuncias (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  conteudo_tipo VARCHAR(30) NOT NULL,
  conteudo_id BIGINT UNSIGNED NOT NULL,
  motivo VARCHAR(80) NOT NULL,
  detalhes VARCHAR(1000) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_denuncia_usuario_conteudo (usuario_id, conteudo_tipo, conteudo_id),
  KEY idx_denuncias_conteudo (conteudo_tipo, conteudo_id),
  CONSTRAINT fk_denuncia_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_presenca (
  usuario_id INT NOT NULL,
  visto_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id), KEY idx_presenca_visto (visto_em),
  CONSTRAINT fk_presenca_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_grupo_imagens (
  grupo_id INT UNSIGNED NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  PRIMARY KEY (grupo_id),
  CONSTRAINT fk_grupo_imagem FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_abas_grupo (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  grupo_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(80) NOT NULL,
  tipo ENUM('chat','announcements','call') NOT NULL DEFAULT 'chat',
  criado_por INT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_abas_grupo (grupo_id, id),
  CONSTRAINT fk_aba_grupo FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE,
  CONSTRAINT fk_aba_criador FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_mensagens_grupo (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  grupo_id INT UNSIGNED NOT NULL,
  aba_id INT UNSIGNED NULL DEFAULT NULL,
  canal ENUM('chat','announcements') NOT NULL DEFAULT 'chat',
  usuario_id INT NOT NULL,
  corpo VARCHAR(1500) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  anexo_path VARCHAR(255) NULL,
  PRIMARY KEY (id), KEY idx_msg_grupo (grupo_id, canal, aba_id, id),
  CONSTRAINT fk_msg_grupo FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_aba FOREIGN KEY (aba_id) REFERENCES comunidade_abas_grupo(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_grupo_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
        <<<SQL
CREATE TABLE IF NOT EXISTS comunidade_notificacoes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  tipo VARCHAR(40) NOT NULL,
  ator_id INT NULL,
  referencia_id BIGINT UNSIGNED NULL,
  mensagem VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lida_em TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id), KEY idx_notificacoes_usuario (usuario_id, lida_em, id),
  CONSTRAINT fk_notificacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_notificacao_ator FOREIGN KEY (ator_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL,
    ];

    foreach ($statements as $sql) $pdo->exec($sql);
    comunidade_garantir_colunas_anexo($pdo);
    comunidade_garantir_colunas_moderacao($pdo);
    $pdo->exec("INSERT IGNORE INTO comunidade_grupos (nome, descricao, categoria, cor) VALUES
        ('Finanças sem mistério', 'Perguntas e conversas para organizar melhor o dinheiro no dia a dia.', 'Organização', '#ff7248'),
        ('Investidores iniciantes', 'Aprenda conceitos e discuta investimentos com responsabilidade.', 'Investimentos', '#a873ff'),
        ('Desafio de economia', 'Compartilhe metas realistas, progresso e pequenas conquistas.', 'Metas', '#ffad52')");
    $pdo->exec('INSERT IGNORE INTO comunidade_membros (grupo_id, usuario_id) SELECT id, criado_por FROM comunidade_grupos WHERE criado_por IS NOT NULL');
    $pdo->exec("INSERT IGNORE INTO comunidade_seguidores (seguidor_id, seguido_id) SELECT usuario_a, usuario_b FROM comunidade_amizades WHERE status='accepted' UNION SELECT usuario_b, usuario_a FROM comunidade_amizades WHERE status='accepted'");
    $ready = true;
}
