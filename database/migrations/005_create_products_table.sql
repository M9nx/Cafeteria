CREATE TABLE products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255) NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,

    PRIMARY KEY (id),

    CONSTRAINT fk_products_category_id
        FOREIGN KEY (category_id)
        REFERENCES categories (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT chk_products_name
        CHECK (
            CHAR_LENGTH(TRIM(name)) > 0
            AND BINARY name = BINARY TRIM(name)
        ),

    CONSTRAINT chk_products_price_positive
        CHECK (price > 0),

    CONSTRAINT chk_products_is_available
        CHECK (is_available IN (0, 1)),

    CONSTRAINT chk_products_deleted_unavailable
        CHECK (
            deleted_at IS NULL
            OR is_available = 0
        ),

    INDEX idx_products_category_id (category_id),

    INDEX idx_products_catalog (
        is_available,
        deleted_at,
        category_id,
        name
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;