CREATE TABLE categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT uq_categories_name
        UNIQUE (name),

    CONSTRAINT chk_categories_name
        CHECK (
            CHAR_LENGTH(TRIM(name)) > 0
            AND BINARY name = BINARY TRIM(name)
        ),

    CONSTRAINT chk_categories_is_active
        CHECK (is_active IN (0, 1)),

    INDEX idx_categories_active_name (is_active, name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;