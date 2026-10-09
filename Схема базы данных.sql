-- migrations/001_init.sql
CREATE TABLE banks (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(50)  NOT NULL UNIQUE,   -- 'sberbank', 'tinkoff'
    name        VARCHAR(100) NOT NULL,          -- 'Сбербанк'
    url         VARCHAR(255) NOT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE currencies (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        CHAR(3)      NOT NULL UNIQUE,   -- 'USD', 'EUR', 'CNY'
    name        VARCHAR(50)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rates (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_id       INT UNSIGNED NOT NULL,
    currency_id   INT UNSIGNED NOT NULL,
    buy           DECIMAL(12,4) NULL,   -- банк покупает у клиента
    sell          DECIMAL(12,4) NULL,   -- банк продаёт клиенту
    parsed_at     DATETIME     NOT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bank_currency_time (bank_id, currency_id, parsed_at),
    FOREIGN KEY (bank_id)     REFERENCES banks(id)      ON DELETE CASCADE,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE parse_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_id     INT UNSIGNED NOT NULL,
    status      ENUM('success','error') NOT NULL,
    message     TEXT NULL,
    duration_ms INT UNSIGNED NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bank_id) REFERENCES banks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Начальные данные
INSERT INTO currencies (code, name) VALUES
    ('USD','Доллар США'), ('EUR','Евро'), ('CNY','Юань'), ('GBP','Фунт');
