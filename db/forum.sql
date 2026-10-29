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
# На время разработки возможность перезаписи

delete
from right_item
where module = 'forum';

delete
from role
where module = 'forum';

INSERT IGNORE INTO role (module, name, description) VALUES ('forum', 'root', 'Администраторы');
INSERT IGNORE INTO role (module, name, description) VALUES ('forum', 'user', 'Пользователи');

INSERT IGNORE INTO right_item (name, module, description) VALUES ('section.add', 'forum', 'Добавление раздела');
INSERT IGNORE INTO right_item (name, module, description) VALUES ('topic.add', 'forum', 'Добавление темы');

SET @root_role_id = (SELECT id FROM role WHERE module = 'forum' AND name = 'root' LIMIT 1);
SET @user_role_id = (SELECT id FROM role WHERE module = 'forum' AND name = 'user' LIMIT 1);

SET @right_section_add = (SELECT id FROM right_item WHERE module = 'forum' AND name = 'section.add' LIMIT 1);
SET @right_topic_add = (SELECT id FROM right_item WHERE module = 'forum' AND name = 'topic.add' LIMIT 1);

IF @root_role_id IS NULL OR @user_role_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Roles not found';
END IF;

IF @right_section_add IS NULL OR @right_topic_add IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Rights not found';
END IF;

INSERT IGNORE INTO role_right (role_id, right_id)
VALUES (@root_role_id, @right_section_add);

INSERT IGNORE INTO role_right (role_id, right_id)
VALUES (@user_role_id, @right_topic_add);

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.id, @user_role_id
FROM user u;