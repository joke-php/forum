CREATE TABLE IF NOT EXISTS forum_user
(
    user_id  INT PRIMARY KEY,
    nickname VARCHAR(255) NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_section
(
    id          INT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    slug        VARCHAR(255) NOT NULL UNIQUE,
    parent_id   INT          NULL,
    sort        INT DEFAULT 500,
    description TEXT,
    FOREIGN KEY (parent_id) REFERENCES forum_section (id) ON DELETE RESTRICT,
    INDEX idx_parent (parent_id)
);

CREATE TABLE IF NOT EXISTS forum_topic
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(255)       NOT NULL,
    section_id INT                NOT NULL,
    user_id    INT                NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    messages   INT      DEFAULT 0 NOT NULL,
    priority   INT      DEFAULT 500,
    UNIQUE KEY unique_topic_in_section (section_id, name),
    FOREIGN KEY (section_id) REFERENCES forum_section (id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);

CREATE TABLE IF NOT EXISTS forum_messages
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    topic_id   INT      NOT NULL,
    content    TEXT     NOT NULL,
    user_id    INT      NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (topic_id) REFERENCES forum_topic (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_topic (topic_id),
    INDEX idx_topic_date (topic_id, created_at)
);

CREATE TABLE IF NOT EXISTS forum_read_tracker
(
    user_id   INT NOT NULL,
    topic_id  INT NOT NULL,
    last_read DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, topic_id),
    FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES forum_topic (id) ON DELETE CASCADE
);

DELIMITER //
CREATE TRIGGER IF NOT EXISTS after_message_insert
    AFTER INSERT
    ON forum_messages
    FOR EACH ROW
BEGIN
    UPDATE forum_topic
    SET messages = messages + 1
    WHERE id = NEW.topic_id;
END//
CREATE TRIGGER IF NOT EXISTS after_message_delete
    AFTER DELETE
    ON forum_messages
    FOR EACH ROW
BEGIN
    UPDATE forum_topic
    SET messages = messages - 1
    WHERE id = OLD.topic_id;
END//
DELIMITER ;

delete
from role
where module = 'forum';
insert into role(module, name, description)
values ('forum', 'root', 'Администраторы');
insert into role(module, name, description)
values ('forum', 'user', 'Пользователи');