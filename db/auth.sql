CREATE TABLE IF NOT EXISTS user
(
    id         INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    confirmed  BOOLEAN  DEFAULT FALSE,
    blocked_to DATETIME     NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_visit DATETIME,
    INDEX idx_email (email)
);

CREATE TABLE user_remember_tokens
(
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    expires_at DATETIME     NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS role
(
    id          INT PRIMARY KEY AUTO_INCREMENT,
    module      VARCHAR(255) NOT NULL,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    UNIQUE KEY (module, name)
);

CREATE TABLE IF NOT EXISTS user_role
(
    user_id INT UNSIGNED NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS right_item
(
    id          INT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    module      VARCHAR(255) NOT NULL,
    description TEXT,
    UNIQUE KEY (module, name)
);

CREATE TABLE IF NOT EXISTS role_right
(
    role_id  INT NOT NULL,
    right_id INT NOT NULL,
    PRIMARY KEY (role_id, right_id),
    FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE,
    FOREIGN KEY (right_id) REFERENCES right_item (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_right
(
    user_id  INT UNSIGNED NOT NULL,
    right_id INT NOT NULL,
    PRIMARY KEY (user_id, right_id),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    FOREIGN KEY (right_id) REFERENCES right_item (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_consent
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id    INT UNSIGNED,
    ip         VARCHAR(24),
    url        VARCHAR(256),
    consent    VARCHAR(64),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
);
