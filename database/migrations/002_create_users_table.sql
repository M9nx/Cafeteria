CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(254)
        CHARACTER SET ascii
        COLLATE ascii_general_ci
        NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('USER', 'ADMIN') NOT NULL DEFAULT 'USER',
    room_id BIGINT UNSIGNED NULL,
    extension VARCHAR(20) NULL,
    profile_image_path VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT uq_users_email
        UNIQUE (email),

    CONSTRAINT chk_users_email_normalized
        CHECK (BINARY email = BINARY LOWER(TRIM(email))),

    CONSTRAINT chk_users_is_active
        CHECK (is_active IN (0, 1)),

    CONSTRAINT fk_users_room
        FOREIGN KEY (room_id)
        REFERENCES rooms (id)
        ON UPDATE RESTRICT
        ON DELETE SET NULL,

    INDEX idx_users_room_id (room_id),
    INDEX idx_users_role_active (role, is_active)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_0900_ai_ci;