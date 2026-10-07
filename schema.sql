-- Execute uma vez no seu banco MySQL/MariaDB.
-- Ajuste o nome da tabela de usuários caso queira criar chaves estrangeiras.

CREATE TABLE IF NOT EXISTS clientes_asaas (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id         INT UNSIGNED NOT NULL UNIQUE,
    asaas_customer_id  VARCHAR(50)  NOT NULL UNIQUE,   -- ex.: cus_000005...
    criado_em          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id         INT UNSIGNED NOT NULL,
    plano              VARCHAR(50)  NOT NULL,
    valor              DECIMAL(10,2) NOT NULL,
    status             VARCHAR(20)  NOT NULL DEFAULT 'pendente',
        -- pendente | pago | vencido | reembolsado | cancelado | divergente | erro
    asaas_payment_id   VARCHAR(50)  NULL UNIQUE,       -- ex.: pay_080225...
    invoice_url        VARCHAR(255) NULL,
    pago_em            DATETIME     NULL,
    criado_em          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Guarda o ID de cada evento de webhook já processado (evita processar duas vezes).
CREATE TABLE IF NOT EXISTS webhook_eventos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id     VARCHAR(100) NOT NULL UNIQUE,
    tipo          VARCHAR(60)  NOT NULL,
    recebido_em   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
