<?php
/**
 * Schema do AFDIS Arena integrado ao PTCC.
 * O banco novo já recebe este conteúdo em educacaofinanceira.sql;
 * esta rotina apenas torna a atualização automática e idempotente
 * para instalações que já tinham o banco antigo.
 */
function arenaSchemaTables(): array
{
    return ['arena_users','arena_modules','arena_lessons','arena_exercises','arena_progress','arena_comments','arena_comment_votes','arena_activities','arena_active_items'];
}

function arenaSchemaReady(PDO $pdo): bool
{
    $tables = arenaSchemaTables();
    $marks = implode(',', array_fill(0, count($tables), '?'));
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ($marks)");
    $q->execute($tables);
    return (int) $q->fetchColumn() === count($tables);
}

function arenaSchemaMessage(): string
{
    return 'A área de aprendizado ainda não foi instalada. Importe o arquivo educacaofinanceira.sql no banco educacaofinanceira e recarregue esta página.';
}

function ensureArenaSchema(PDO $pdo): void
{
    $ddl = [
        "CREATE TABLE IF NOT EXISTS arena_users (id INT NOT NULL AUTO_INCREMENT, user_id INT NOT NULL, name VARCHAR(120) NOT NULL DEFAULT 'Jogador', xp INT NOT NULL DEFAULT 0, coins INT NOT NULL DEFAULT 0, streak INT NOT NULL DEFAULT 0, best_streak INT NOT NULL DEFAULT 0, last_visit DATE NULL, last_checkin DATE NULL, freeze_count INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_arena_user_ptcc (user_id), KEY idx_arena_users_user (user_id), CONSTRAINT fk_arena_users_ptcc FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_modules (id INT NOT NULL AUTO_INCREMENT, title VARCHAR(180) NOT NULL, subtitle VARCHAR(255) NOT NULL, position INT NOT NULL, PRIMARY KEY (id), UNIQUE KEY uq_arena_modules_position (position)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_lessons (id INT NOT NULL AUTO_INCREMENT, module_id INT NOT NULL, title VARCHAR(180) NOT NULL, duration VARCHAR(20) NOT NULL, xp INT NOT NULL DEFAULT 10, position INT NOT NULL, PRIMARY KEY (id), UNIQUE KEY uq_arena_lessons_position (module_id,position), KEY idx_arena_lessons_module (module_id), CONSTRAINT fk_arena_lessons_module FOREIGN KEY (module_id) REFERENCES arena_modules(id) ON DELETE CASCADE ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_exercises (id INT NOT NULL AUTO_INCREMENT, lesson_id INT NOT NULL, question TEXT NOT NULL, options JSON NOT NULL, answer TINYINT NOT NULL, PRIMARY KEY (id), UNIQUE KEY uq_arena_exercise_question (lesson_id,question(191)), KEY idx_arena_exercises_lesson (lesson_id), CONSTRAINT fk_arena_exercises_lesson FOREIGN KEY (lesson_id) REFERENCES arena_lessons(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_progress (arena_user_id INT NOT NULL, lesson_id INT NOT NULL, kind ENUM('lesson','exercise') NOT NULL, completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (arena_user_id,lesson_id,kind), KEY idx_arena_progress_lesson (lesson_id), CONSTRAINT fk_arena_progress_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE, CONSTRAINT fk_arena_progress_lesson FOREIGN KEY (lesson_id) REFERENCES arena_lessons(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_comments (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, arena_user_id INT NOT NULL, lesson_id INT NULL, author VARCHAR(120) NOT NULL, body VARCHAR(500) NOT NULL, likes INT NOT NULL DEFAULT 0, dislikes INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), KEY idx_arena_comments_lesson (lesson_id), CONSTRAINT fk_arena_comments_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE, CONSTRAINT fk_arena_comments_lesson FOREIGN KEY (lesson_id) REFERENCES arena_lessons(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_comment_votes (comment_id BIGINT UNSIGNED NOT NULL, arena_user_id INT NOT NULL, vote TINYINT NOT NULL, PRIMARY KEY (comment_id,arena_user_id), CONSTRAINT fk_arena_votes_comment FOREIGN KEY (comment_id) REFERENCES arena_comments(id) ON DELETE CASCADE, CONSTRAINT fk_arena_votes_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_activities (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, arena_user_id INT NOT NULL, label VARCHAR(180) NOT NULL, xp INT NOT NULL DEFAULT 0, coins INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), KEY idx_arena_activities_user_date (arena_user_id,created_at), CONSTRAINT fk_arena_activities_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS arena_active_items (arena_user_id INT NOT NULL, item_id VARCHAR(50) NOT NULL, activated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, expires_at DATETIME NULL, PRIMARY KEY (arena_user_id,item_id), KEY idx_arena_items_expiration (expires_at), CONSTRAINT fk_arena_items_user FOREIGN KEY (arena_user_id) REFERENCES arena_users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
    foreach ($ddl as $statement) $pdo->exec($statement);

    $pdo->exec("INSERT IGNORE INTO arena_modules (id,title,subtitle,position) VALUES
        (1,'Fundamentos de Investimentos','Construa uma base sólida para tomar decisões financeiras.',1),
        (2,'Análise Técnica','Leia riscos, tendências e movimentos do mercado com responsabilidade.',2),
        (3,'Valuation e Carteira','Aprenda a comparar ativos e montar uma carteira consciente.',3)");
    $pdo->exec("INSERT IGNORE INTO arena_lessons (id,module_id,title,duration,xp,position) VALUES
        (1,1,'Introdução ao Mundo dos Investimentos','12:30',10,1),(2,1,'Tipos de Ativos Financeiros','18:45',10,2),(3,1,'Risco e Retorno','22:10',10,3),
        (4,2,'Montando sua Carteira Inicial','15:20',10,1),(5,2,'Gráficos e Tendências','14:50',10,2),(6,2,'Indicadores Técnicos','20:10',10,3),
        (7,3,'Suporte e Resistência','17:30',10,1),(8,3,'Padrões de Candlestick','25:00',10,2),(9,3,'O que é Valuation?','16:40',10,3)");
    $questions = [
        ['Qual é a primeira etapa antes de escolher um investimento?', ['Definir objetivo, prazo e tolerância a risco', 'Seguir a indicação mais comentada', 'Escolher sempre o ativo que mais subiu', 'Investir sem montar orçamento'], 0],
        ['Qual atitude ajuda a montar uma reserva de emergência?', ['Usar ativos de alta liquidez e baixo risco', 'Concentrar tudo em criptomoedas', 'Escolher apenas ativos sem resgate', 'Investir somente em ações'], 0],
        ['O que é diversificação de carteira?', ['Distribuir recursos entre ativos e classes diferentes', 'Comprar o mesmo ativo em várias corretoras', 'Colocar tudo no investimento mais rentável', 'Manter todo o dinheiro parado'], 0],
        ['Se a inflação sobe e o dinheiro fica parado, o que pode ocorrer?', ['O poder de compra pode diminuir', 'O saldo nominal aumenta sozinho', 'O dinheiro passa a render automaticamente', 'O risco desaparece'], 0],
        ['O que representa a liquidez de um investimento?', ['A facilidade e a rapidez para transformar o ativo em dinheiro', 'A garantia de lucro diário', 'O tamanho da empresa emissora', 'A quantidade de dividendos'], 0],
        ['Qual é uma diferença importante entre rentabilidade nominal e real?', ['A real considera o efeito da inflação', 'A nominal sempre é menor', 'A real ignora custos e inflação', 'Não existe diferença'], 0],
        ['O que é volatilidade?', ['A intensidade das oscilações de preço de um ativo', 'A certeza de receber juros', 'O prazo de vencimento de uma conta', 'A taxa de câmbio fixa'], 0],
        ['Sobre criptomoedas, qual afirmação é mais responsável?', ['Podem ter alta volatilidade e exigem gestão de risco', 'São sempre protegidas pelo FGC', 'Não sofrem oscilações', 'Garantem retorno positivo'], 0],
        ['O que é uma stablecoin?', ['Um criptoativo projetado para acompanhar o valor de uma referência', 'Uma ação de empresa estatal', 'Um título público brasileiro', 'Uma moeda sem qualquer risco'], 0],
        ['Por que não se deve compartilhar a chave privada de uma carteira cripto?', ['Quem a possui pode controlar os ativos', 'Ela serve apenas para receber promoções', 'Ela reduz a inflação', 'Ela garante lucro'], 0],
    ];
    $pdo->exec("DELETE FROM arena_exercises");
    $q = $pdo->prepare('INSERT INTO arena_exercises (lesson_id,question,options,answer) VALUES (?,?,?,?)');
    foreach (range(1, 9) as $lessonId) foreach ($questions as [$question, $options, $answer]) $q->execute([$lessonId, $question, json_encode($options, JSON_UNESCAPED_UNICODE), $answer]);
}
