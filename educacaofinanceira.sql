-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/08/2026 às 21:09
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `educacaofinanceira`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `movimentacoes`
--


-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `estoque` int(11) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `itemid` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(190) NOT NULL,
  `oauth_uid` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `telefone` varchar(25) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `provedor` varchar(20) DEFAULT 'local',
  `tipo` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `tema` varchar(22) DEFAULT 'sistema',
  `idioma` varchar(22) DEFAULT 'pt-br',
  `moeda` varchar(22) DEFAULT 'BRL',
  `banner` varchar(255) DEFAULT NULL,
  `patente` varchar(30) DEFAULT NULL,
  `xp` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

-- Dados de usuários removidos por privacidade. Use o cadastro da aplicação.

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uq_usuarios_oauth_uid` (`oauth_uid`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



-- ============================================================
-- Open Finance: transações persistidas para o calendário
-- ============================================================

CREATE TABLE IF NOT EXISTS `open_finance_transacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `transacao_id` varchar(255) NOT NULL,
  `account_id` varchar(255) DEFAULT NULL,
  `conta_nome` varchar(255) DEFAULT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `status` varchar(30) DEFAULT 'POSTED',
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descricao` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) NOT NULL DEFAULT 'Outros',
  `categoria_sugerida` varchar(100) NOT NULL DEFAULT 'Outros',
  `categoria_origem` enum('automatica','manual') NOT NULL DEFAULT 'automatica',
  `moeda` varchar(12) DEFAULT 'BRL',
  `data_transacao` datetime NOT NULL,
  `payload` longtext DEFAULT NULL,
  `sincronizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_open_finance_usuario_transacao` (`usuario_id`,`transacao_id`),
  KEY `idx_open_finance_usuario_data` (`usuario_id`,`data_transacao`),
  KEY `idx_open_finance_usuario_categoria` (`usuario_id`,`categoria`),
  CONSTRAINT `fk_open_finance_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AFDIS Arena integrado ao banco principal.
-- AFDIS Arena integrado ao PTCC.
-- Execute depois do educacaofinanceira.sql e das migrations existentes.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS arena_users (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  name VARCHAR(120) NOT NULL DEFAULT 'Jogador',
  xp INT NOT NULL DEFAULT 0,
  coins INT NOT NULL DEFAULT 0,
  streak INT NOT NULL DEFAULT 0,
  best_streak INT NOT NULL DEFAULT 0,
  last_visit DATE NULL,
  last_checkin DATE NULL,
  freeze_count INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_arena_user_ptcc (user_id),
  KEY idx_arena_users_user (user_id),
  CONSTRAINT fk_arena_users_ptcc FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_modules (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(180) NOT NULL,
  subtitle VARCHAR(255) NOT NULL,
  position INT NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY uq_arena_modules_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_lessons (
  id INT NOT NULL AUTO_INCREMENT,
  module_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  duration VARCHAR(20) NOT NULL,
  xp INT NOT NULL DEFAULT 10,
  position INT NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY uq_arena_lessons_position (module_id,position), KEY idx_arena_lessons_module (module_id),
  CONSTRAINT fk_arena_lessons_module FOREIGN KEY (module_id) REFERENCES arena_modules(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_exercises (
  id INT NOT NULL AUTO_INCREMENT,
  lesson_id INT NOT NULL,
  question TEXT NOT NULL,
  options JSON NOT NULL,
  answer TINYINT NOT NULL,
  PRIMARY KEY (id), UNIQUE KEY uq_arena_exercise_question (lesson_id,question(191)), KEY idx_arena_exercises_lesson (lesson_id),
  CONSTRAINT fk_arena_exercises_lesson FOREIGN KEY (lesson_id) REFERENCES arena_lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_progress (
  arena_user_id INT NOT NULL,
  lesson_id INT NOT NULL,
  kind ENUM('lesson','exercise') NOT NULL,
  completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (arena_user_id,lesson_id,kind), KEY idx_arena_progress_lesson (lesson_id),
  CONSTRAINT fk_arena_progress_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_arena_progress_lesson FOREIGN KEY (lesson_id) REFERENCES arena_lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_comments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  arena_user_id INT NOT NULL,
  lesson_id INT NULL,
  author VARCHAR(120) NOT NULL,
  body VARCHAR(500) NOT NULL,
  likes INT NOT NULL DEFAULT 0,
  dislikes INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_arena_comments_lesson (lesson_id),
  CONSTRAINT fk_arena_comments_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_arena_comments_lesson FOREIGN KEY (lesson_id) REFERENCES arena_lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_comment_votes (
  comment_id BIGINT UNSIGNED NOT NULL,
  arena_user_id INT NOT NULL,
  vote TINYINT NOT NULL,
  PRIMARY KEY (comment_id,arena_user_id),
  CONSTRAINT fk_arena_votes_comment FOREIGN KEY (comment_id) REFERENCES arena_comments(id) ON DELETE CASCADE,
  CONSTRAINT fk_arena_votes_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_activities (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  arena_user_id INT NOT NULL,
  label VARCHAR(180) NOT NULL,
  xp INT NOT NULL DEFAULT 0,
  coins INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_arena_activities_user_date (arena_user_id,created_at),
  CONSTRAINT fk_arena_activities_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS arena_active_items (
  arena_user_id INT NOT NULL,
  item_id VARCHAR(50) NOT NULL,
  activated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NULL,
  PRIMARY KEY (arena_user_id,item_id), KEY idx_arena_items_expiration (expires_at),
  CONSTRAINT fk_arena_items_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conteúdo inicial da trilha.
INSERT IGNORE INTO arena_modules (id,title,subtitle,position) VALUES
(1,'Fundamentos de Investimentos','Construa uma base sólida para tomar decisões financeiras.',1),
(2,'Análise Técnica','Leia riscos, tendências e movimentos do mercado com responsabilidade.',2),
(3,'Valuation e Carteira','Aprenda a comparar ativos e montar uma carteira consciente.',3);
INSERT IGNORE INTO arena_lessons (id,module_id,title,duration,xp,position) VALUES
(1,1,'Introdução ao Mundo dos Investimentos','12:30',10,1),(2,1,'Tipos de Ativos Financeiros','18:45',10,2),(3,1,'Risco e Retorno','22:10',10,3),
(4,2,'Montando sua Carteira Inicial','15:20',10,1),(5,2,'Gráficos e Tendências','14:50',10,2),(6,2,'Indicadores Técnicos','20:10',10,3),
(7,3,'Suporte e Resistência','17:30',10,1),(8,3,'Padrões de Candlestick','25:00',10,2),(9,3,'O que é Valuation?','16:40',10,3);
INSERT IGNORE INTO arena_exercises (id,lesson_id,question,options,answer) VALUES
(1,1,'O que significa investir?',JSON_ARRAY('Guardar sem possibilidade de perda','Colocar dinheiro em um ativo buscando retorno futuro','Gastar para aumentar patrimônio','Guardar apenas em conta corrente'),1),
(2,1,'Qual é a diferença entre poupar e investir?',JSON_ARRAY('Poupar guarda dinheiro; investir busca fazê-lo render','Investir nunca envolve riscos','Poupar sempre rende mais','Não existe diferença'),0),
(3,1,'O que pode acontecer quando os preços aumentam e o dinheiro fica parado?',JSON_ARRAY('O dinheiro necessariamente rende','O poder de compra pode diminuir','O valor nominal cai exatamente 5%','O dinheiro rende automaticamente'),1),
(4,1,'O que é inflação?',JSON_ARRAY('Aumento generalizado dos preços ao longo do tempo','Redução dos juros bancários','Aumento do salário médio','Crescimento de investimentos'),0),
(5,1,'Investir R$ 1.000 e terminar com R$ 1.100 representa rentabilidade nominal de:',JSON_ARRAY('1%','5%','10%','11%'),2),
(6,1,'O que representa o risco de um investimento?',JSON_ARRAY('A certeza de lucro','A possibilidade de resultados diferentes do esperado','A taxa fixa de retorno','A ausência de liquidez'),1),
(7,1,'Qual investimento tende a oscilar mais no curto prazo?',JSON_ARRAY('Ações','Poupança','Tesouro Selic','Conta corrente'),0);
-- Replica as 7 questões para as demais aulas sem revelar respostas ao navegador.
INSERT IGNORE INTO arena_exercises (lesson_id,question,options,answer)
SELECT l.id,e.question,e.options,e.answer FROM arena_lessons l CROSS JOIN arena_exercises e WHERE e.lesson_id=1 AND l.id>1;

-- Sunset Hub: extensão aditiva do banco principal, vinculada a usuarios.id.
CREATE TABLE IF NOT EXISTS comunidade_publicacoes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  corpo VARCHAR(1000) NOT NULL,
  curtidas INT UNSIGNED NOT NULL DEFAULT 0,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comunidade_publicacoes_criado (criado_em, id),
  KEY idx_comunidade_publicacoes_usuario (usuario_id),
  CONSTRAINT fk_comunidade_publicacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_videos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  descricao VARCHAR(500) NULL,
  url VARCHAR(500) NULL,
  miniatura VARCHAR(255) NULL,
  visualizacoes INT UNSIGNED NOT NULL DEFAULT 0,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comunidade_videos_criado (criado_em, id),
  CONSTRAINT fk_comunidade_videos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_grupos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  descricao VARCHAR(255) NOT NULL,
  categoria VARCHAR(60) NOT NULL DEFAULT 'Educação financeira',
  cor VARCHAR(20) NOT NULL DEFAULT '#ff7248',
  criado_por INT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_comunidade_grupos_nome (nome),
  CONSTRAINT fk_comunidade_grupos_criador FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_membros (
  grupo_id INT UNSIGNED NOT NULL,
  usuario_id INT NOT NULL,
  entrou_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (grupo_id, usuario_id),
  KEY idx_comunidade_membros_usuario (usuario_id),
  CONSTRAINT fk_comunidade_membros_grupo FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE,
  CONSTRAINT fk_comunidade_membros_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO comunidade_grupos (nome, descricao, categoria, cor) VALUES
('Finanças sem mistério', 'Perguntas e conversas para organizar melhor o dinheiro no dia a dia.', 'Organização', '#ff7248'),
('Investidores iniciantes', 'Aprenda conceitos e discuta investimentos com responsabilidade.', 'Investimentos', '#a873ff'),
('Desafio de economia', 'Compartilhe metas realistas, progresso e pequenas conquistas.', 'Metas', '#ffad52');


-- Recursos sociais do Sunset Hub (mesmo banco do portal)
CREATE TABLE IF NOT EXISTS comunidade_amizades (
  usuario_a INT NOT NULL,
  usuario_b INT NOT NULL,
  solicitado_por INT NOT NULL,
  status ENUM('pending','accepted') NOT NULL DEFAULT 'pending',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_a, usuario_b),
  KEY idx_amizades_destino (usuario_b, status),
  KEY idx_amizades_solicitante (solicitado_por),
  CONSTRAINT fk_amizade_a FOREIGN KEY (usuario_a) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_amizade_b FOREIGN KEY (usuario_b) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_amizade_solicitante FOREIGN KEY (solicitado_por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_mensagens_privadas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  remetente_id INT NOT NULL,
  destinatario_id INT NOT NULL,
  corpo VARCHAR(4000) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lida_em TIMESTAMP NULL DEFAULT NULL,
  anexo_path VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_dm_par (remetente_id, destinatario_id, id),
  KEY idx_dm_destino (destinatario_id, remetente_id, id),
  CONSTRAINT fk_dm_remetente FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_dm_destinatario FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_reacoes_video (
  video_id INT UNSIGNED NOT NULL,
  usuario_id INT NOT NULL,
  reacao TINYINT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (video_id, usuario_id),
  KEY idx_reacoes_usuario (usuario_id),
  CONSTRAINT fk_reacao_video FOREIGN KEY (video_id) REFERENCES comunidade_videos(id) ON DELETE CASCADE,
  CONSTRAINT fk_reacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_comentarios_video (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  video_id INT UNSIGNED NOT NULL,
  usuario_id INT NOT NULL,
  corpo VARCHAR(1500) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comentarios_video (video_id, id),
  CONSTRAINT fk_comentario_video FOREIGN KEY (video_id) REFERENCES comunidade_videos(id) ON DELETE CASCADE,
  CONSTRAINT fk_comentario_video_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_posts_visuais (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  tipo ENUM('carousel','short') NOT NULL,
  legenda VARCHAR(1500) NOT NULL DEFAULT '',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_post_visual_data (criado_em, id),
  CONSTRAINT fk_post_visual_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_midias_visuais (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id BIGINT UNSIGNED NOT NULL,
  tipo ENUM('image','video') NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  posicao SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_midias_post (post_id, posicao),
  CONSTRAINT fk_midia_post_visual FOREIGN KEY (post_id) REFERENCES comunidade_posts_visuais(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_stories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT NOT NULL,
  tipo ENUM('image','video') NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  legenda VARCHAR(500) NOT NULL DEFAULT '',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expira_em TIMESTAMP NOT NULL,
  PRIMARY KEY (id),
  KEY idx_story_expira (expira_em),
  KEY idx_story_usuario (usuario_id, criado_em),
  CONSTRAINT fk_story_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_presenca (
  usuario_id INT NOT NULL,
  visto_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id),
  KEY idx_presenca_visto (visto_em),
  CONSTRAINT fk_presenca_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_grupo_imagens (
  grupo_id INT UNSIGNED NOT NULL,
  caminho VARCHAR(255) NOT NULL,
  PRIMARY KEY (grupo_id),
  CONSTRAINT fk_grupo_imagem FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_abas_grupo (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  grupo_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(80) NOT NULL,
  tipo ENUM('chat','announcements','call') NOT NULL DEFAULT 'chat',
  criado_por INT NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_abas_grupo (grupo_id, id),
  CONSTRAINT fk_aba_grupo FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE,
  CONSTRAINT fk_aba_criador FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunidade_mensagens_grupo (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  grupo_id INT UNSIGNED NOT NULL,
  aba_id INT UNSIGNED NULL DEFAULT NULL,
  canal ENUM('chat','announcements') NOT NULL DEFAULT 'chat',
  usuario_id INT NOT NULL,
  corpo VARCHAR(1500) NOT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  anexo_path VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_msg_grupo (grupo_id, canal, aba_id, id),
  CONSTRAINT fk_msg_grupo FOREIGN KEY (grupo_id) REFERENCES comunidade_grupos(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_aba FOREIGN KEY (aba_id) REFERENCES comunidade_abas_grupo(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_grupo_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grupos já criados continuam acessíveis a seus administradores.
INSERT IGNORE INTO comunidade_membros (grupo_id, usuario_id)
SELECT id, criado_por FROM comunidade_grupos WHERE criado_por IS NOT NULL;


-- Sunset Hub: notificações no mesmo banco do portal.
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
