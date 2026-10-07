-- Migração do calendário financeiro integrado ao Open Finance.
-- Execute este arquivo no banco educacaofinanceira antes de usar a sincronização.

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
