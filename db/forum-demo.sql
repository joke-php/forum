-- 1. Пользователи (создаем базовых пользователей для форума)
# INSERT INTO user (id, email, password, confirmed, created_at, last_visit)
# VALUES (1, 'voral@example.com', '$2y$10$demo_hash_password_1', TRUE, '2026-09-01 10:00:00', '2026-10-22 12:00:00'),
#        (2, 'alex@example.com', '$2y$10$demo_hash_password_2', TRUE, '2026-09-05 14:30:00', '2026-10-21 09:15:00'),
#        (3, 'maria@example.com', '$2y$10$demo_hash_password_3', TRUE, '2026-09-10 11:20:00', '2026-10-20 18:45:00'),
#        (4, 'dmitry@example.com', '$2y$10$demo_hash_password_4', TRUE, '2026-09-15 16:00:00', '2026-10-22 08:30:00'),
#        (5, 'elena@example.com', '$2y$10$demo_hash_password_5', TRUE, '2026-09-20 09:45:00', '2026-10-19 22:10:00');

-- Профили пользователей форума (никнеймы)
INSERT INTO forum_user (user_id, nickname)
VALUES (1, 'Voral'),
       (2, 'Alex'),
       (3, 'Maria'),
       (4, 'Dmitry'),
       (5, 'Elena');

-- 2. Разделы форума (Categories & Subcategories)
-- Корневые разделы
INSERT INTO forum_section (id, name, slug, parent_id, sort, description)
VALUES (1, 'Веб-разработка', 'web-development', NULL, 100, 'Все о создании сайтов'),
       (2, 'Базы данных', 'databases', NULL, 200, 'SQL, NoSQL и оптимизация'),
       (3, 'DevOps и инструменты', 'devops', NULL, 300, 'Docker, CI/CD, Git');

-- Подразделы Веб-разработки
INSERT INTO forum_section (id, name, slug, parent_id, sort, description)
VALUES (101, 'PHP', 'php', 1, 101, 'Разработка на PHP 8+'),
       (102, 'JavaScript', 'javascript', 1, 102, 'Frontend и Node.js'),
       (103, 'CSS и верстка', 'css', 1, 103, 'Стилизация и адаптивность');

-- Подразделы Баз данных
INSERT INTO forum_section (id, name, slug, parent_id, sort, description)
VALUES (201, 'MySQL', 'mysql', 2, 201, 'Самая популярная СУБД'),
       (202, 'PostgreSQL', 'postgresql', 2, 202, 'Продвинутая объектно-реляционная СУБД');

-- Подразделы DevOps
INSERT INTO forum_section (id, name, slug, parent_id, sort, description)
VALUES (301, 'Docker', 'docker', 3, 301, 'Контейнеризация приложений'),
       (302, 'CI/CD', 'ci-cd', 3, 302, 'Непрерывная интеграция и доставка'),
       (303, 'Git', 'git', 3, 303, 'Контроль версий');

-- 3. Топики (Topics)
-- PHP Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (1001, 'DI-контейнер в PHP 8.5', 101, 1, '2026-10-10 10:00:00', '2026-10-14 12:00:00', 3, 500),
       (1002, 'Middleware паттерн', 101, 4, '2026-10-11 14:00:00', '2026-10-13 16:00:00', 2, 500),
       (1003, 'Роутинг без фреймворка', 101, 2, '2026-10-10 09:00:00', '2026-10-10 09:00:00', 0, 500),
       (1004, 'Обработка JSON запросов', 101, 3, '2026-10-12 11:00:00', '2026-10-15 10:00:00', 4, 500);

-- JavaScript Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (1005, 'Асинхронность в JS', 102, 5, '2026-10-11 10:00:00', '2026-10-12 15:00:00', 1, 500),
       (1006, 'TypeScript vs JavaScript', 102, 4, '2026-10-12 12:00:00', '2026-10-14 18:00:00', 3, 500),
       (1007, 'Webpack настройка', 102, 3, '2026-10-10 16:00:00', '2026-10-10 16:00:00', 0, 500);

-- CSS Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (1008, 'Flexbox или Grid?', 103, 2, '2026-10-10 11:00:00', '2026-10-11 14:00:00', 2, 500),
       (1009, 'CSS переменные', 103, 1, '2026-10-09 10:00:00', '2026-10-10 12:00:00', 1, 500),
       (1010, 'Адаптивная верстка', 103, 4, '2026-10-12 09:00:00', '2026-10-15 11:00:00', 4, 500);

-- MySQL Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (2001, 'Оптимизация запросов', 201, 3, '2026-10-11 10:00:00', '2026-10-13 12:00:00', 2, 500),
       (2002, 'Нормализация БД', 201, 2, '2026-10-10 15:00:00', '2026-10-10 15:00:00', 0, 500),
       (2003, 'Транзакции в MySQL', 201, 5, '2026-10-12 14:00:00', '2026-10-14 16:00:00', 3, 500);

-- PostgreSQL Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (2004, 'JSONB тип данных', 202, 4, '2026-10-11 11:00:00', '2026-10-12 13:00:00', 1, 500),
       (2005, 'Миграции базы данных', 202, 1, '2026-10-12 10:00:00', '2026-10-15 09:00:00', 4, 500),
       (2006, 'Репликация PostgreSQL', 202, 2, '2026-10-10 12:00:00', '2026-10-10 12:00:00', 0, 500);

-- Docker Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (3001, 'Multi-stage builds', 301, 5, '2026-10-12 10:00:00', '2026-10-14 11:00:00', 2, 500),
       (3002, 'Docker Compose для разработки', 301, 4, '2026-10-13 09:00:00', '2026-10-15 14:00:00', 3, 500),
       (3003, 'Оптимизация Docker образов', 301, 1, '2026-10-11 16:00:00', '2026-10-13 10:00:00', 1, 500),
       (3004, 'Docker networking', 301, 3, '2026-10-10 10:00:00', '2026-10-10 10:00:00', 0, 500);

-- CI/CD Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (3005, 'GitHub Actions для PHP', 302, 2, '2026-10-10 14:00:00', '2026-10-12 16:00:00', 2, 500),
       (3006, 'Автоматический деплой', 302, 5, '2026-10-13 11:00:00', '2026-10-15 12:00:00', 4, 500),
       (3007, 'Тестирование в pipeline', 302, 3, '2026-10-10 13:00:00', '2026-10-11 15:00:00', 1, 500);

-- Git Topics
INSERT INTO forum_topic (id, name, section_id, user_id, created_at, updated_at, messages, priority)
VALUES (3008, 'Git flow стратегия', 303, 1, '2026-10-12 09:00:00', '2026-10-14 10:00:00', 3, 500),
       (3009, 'Interactive rebase', 303, 2, '2026-10-11 15:00:00', '2026-10-13 11:00:00', 2, 500),
       (3010, '.gitignore best practices', 303, 4, '2026-10-10 11:00:00', '2026-10-10 11:00:00', 0, 500);

-- 4. Сообщения (Messages)
-- Топик 1001: DI-контейнер
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1001, 'Как лучше реализовать автовайринг?', 1, '2026-10-10 10:00:00'),
       (1001, 'Используй reflection API.', 2, '2026-10-11 10:00:00'),
       (1001, 'Смотри примеры в Symfony.', 3, '2026-10-12 14:00:00'),
       (1001, 'Спасибо, разобрался!', 1, '2026-10-14 12:00:00');

-- Топик 1002: Middleware
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1002, 'Как передать данные между мидлварами?', 4, '2026-10-11 14:00:00'),
       (1002, 'Через request attributes.', 5, '2026-10-12 09:00:00'),
       (1002, 'Понял, попробую.', 4, '2026-10-13 16:00:00');

-- Топик 1004: JSON
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1004, 'Как правильно парсить php://input?', 3, '2026-10-12 11:00:00'),
       (1004, 'file_get_contents + json_decode.', 1, '2026-10-12 15:00:00'),
       (1004, 'Не забудь про проверку Content-Type.', 4, '2026-10-13 10:00:00'),
       (1004, 'И обработку ошибок JSON.', 5, '2026-10-14 09:00:00'),
       (1004, 'Отлично, всё работает!', 3, '2026-10-15 10:00:00');

-- Топик 1005: Async JS
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1005, 'Promise или async/await?', 5, '2026-10-11 10:00:00'),
       (1005, 'Async/await чище читается.', 2, '2026-10-12 15:00:00');

-- Топик 1006: TS vs JS
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1006, 'Стоит ли переходить на TS?', 4, '2026-10-12 12:00:00'),
       (1006, 'Для больших проектов - да.', 3, '2026-10-13 10:00:00'),
       (1006, 'Типизация спасает от багов.', 1, '2026-10-13 14:00:00'),
       (1006, 'Убедили, начну изучать.', 4, '2026-10-14 18:00:00');

-- Топик 1008: Flex/Grid
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1008, 'Что лучше использовать?', 2, '2026-10-10 11:00:00'),
       (1008, 'Grid для layout, Flexbox для компонентов.', 5, '2026-10-11 10:00:00'),
       (1008, 'Логично, спасибо!', 2, '2026-10-11 14:00:00');

-- Топик 1009: CSS vars
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1009, 'Поддержка в старых браузерах?', 1, '2026-10-09 10:00:00'),
       (1009, 'IE не поддерживает, нужен fallback.', 3, '2026-10-10 12:00:00');

-- Топик 1010: Adaptive
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (1010, 'Mobile-first или desktop-first?', 4, '2026-10-12 09:00:00'),
       (1010, 'Mobile-first современнее.', 5, '2026-10-13 10:00:00'),
       (1010, 'Меньше медиа-запросов нужно.', 1, '2026-10-14 11:00:00'),
       (1010, 'Согласен с mobile-first.', 2, '2026-10-14 16:00:00'),
       (1010, 'Переделаю проект под mobile-first.', 4, '2026-10-15 11:00:00');

-- Топик 2001: MySQL Opt
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (2001, 'Как ускорить медленные SELECT?', 3, '2026-10-11 10:00:00'),
       (2001, 'Добавь индексы на часто используемые поля.', 1, '2026-10-12 10:00:00'),
       (2001, 'Проверю EXPLAIN, спасибо!', 3, '2026-10-13 12:00:00');

-- Топик 2003: Transactions
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (2003, 'Когда использовать транзакции?', 5, '2026-10-12 14:00:00'),
       (2003, 'При атомарных операциях.', 4, '2026-10-13 09:00:00'),
       (2003, 'Для целостности данных обязательно.', 1, '2026-10-13 14:00:00'),
       (2003, 'Поняла, внедрю в проект.', 5, '2026-10-14 16:00:00');

-- Топик 2004: JSONB
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (2004, 'Преимущества JSONB перед JSON?', 4, '2026-10-11 11:00:00'),
       (2004, 'JSONB бинарный, быстрее индексация.', 3, '2026-10-12 13:00:00');

-- Топик 2005: Migrations
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (2005, 'Какой инструмент для миграций выбрать?', 1, '2026-10-12 10:00:00'),
       (2005, 'Phinx или Doctrine Migrations.', 2, '2026-10-13 10:00:00'),
       (2005, 'Liquibase тоже хорош.', 5, '2026-10-14 09:00:00'),
       (2005, 'Я за Phinx, простой и понятный.', 4, '2026-10-14 14:00:00'),
       (2005, 'Попробую Phinx.', 1, '2026-10-15 09:00:00');

-- Топик 3001: Multi-stage
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3001, 'Зачем нужны multi-stage сборки?', 5, '2026-10-12 10:00:00'),
       (3001, 'Уменьшают размер финального образа.', 1, '2026-10-13 10:00:00'),
       (3001, 'Круто, внедрю в проект!', 5, '2026-10-14 11:00:00');

-- Топик 3002: Compose
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3002, 'Как организовать dev окружение?', 4, '2026-10-13 09:00:00'),
       (3002, 'Volume для кода, отдельный сервис для БД.', 3, '2026-10-14 10:00:00'),
       (3002, 'И не забудь про .env файлы.', 2, '2026-10-14 16:00:00'),
       (3002, 'Отлично, всё настроил!', 4, '2026-10-15 14:00:00');

-- Топик 3003: Alpine
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3003, 'Alpine или Debian base image?', 1, '2026-10-11 16:00:00'),
       (3003, 'Alpine меньше, но могут быть проблемы с glibc.', 5, '2026-10-13 10:00:00');

-- Топик 3005: GitHub Actions
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3005, 'Как настроить PHPUnit в CI?', 2, '2026-10-10 14:00:00'),
       (3005, 'Используй shivammathur/setup-php action.', 1, '2026-10-11 10:00:00'),
       (3005, 'Работает, спасибо!', 2, '2026-10-12 16:00:00');

-- Топик 3006: Deploy
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3006, 'Лучшие практики деплоя?', 5, '2026-10-13 11:00:00'),
       (3006, 'Blue-green deployment надежнее.', 4, '2026-10-14 09:00:00'),
       (3006, 'И rollback план обязателен.', 3, '2026-10-14 14:00:00'),
       (3006, 'Canary releases тоже хороши.', 1, '2026-10-15 10:00:00'),
       (3006, 'Много полезной информации!', 5, '2026-10-15 12:00:00');

-- Топик 3007: Testing
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3007, 'Какие тесты запускать в CI?', 3, '2026-10-10 13:00:00'),
       (3007, 'Unit и integration обязательно.', 2, '2026-10-11 15:00:00');

-- Топик 3008: Git Flow
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3008, 'Стоит ли использовать Git Flow?', 1, '2026-10-12 09:00:00'),
       (3008, 'Для больших команд - да.', 5, '2026-10-13 10:00:00'),
       (3008, 'GitHub Flow проще для небольших проектов.', 4, '2026-10-13 16:00:00'),
       (3008, 'Выберу GitHub Flow тогда.', 1, '2026-10-14 10:00:00');

-- Топик 3009: Rebase
INSERT INTO forum_messages (topic_id, content, user_id, created_at)
VALUES (3009, 'Когда использовать rebase вместо merge?', 2, '2026-10-11 15:00:00'),
       (3009, 'Rebase для чистой истории.', 3, '2026-10-12 10:00:00'),
       (3009, 'Но осторожно с публичными ветками!', 2, '2026-10-13 11:00:00');