CREATE TABLE IF NOT EXISTS user
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    confirmed  BOOLEAN  DEFAULT FALSE,
    blocked_to DATETIME     NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_visit DATETIME,
    INDEX idx_email (email)
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
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE RESTRICT
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
    FOREIGN KEY (right_id) REFERENCES right_item (id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS user_right
(
    user_id  INT NOT NULL,
    right_id INT NOT NULL,
    PRIMARY KEY (user_id, right_id),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    FOREIGN KEY (right_id) REFERENCES right_item (id) ON DELETE RESTRICT
);