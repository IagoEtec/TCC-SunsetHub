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
