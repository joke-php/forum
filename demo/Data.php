<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Demo;

/**
 * Тестовые данные.
 */
class Data
{
    public static string $categoryFilter = '';
    public static string $subFilter = '';
    public const int PER_PAGE = 7;

    public static function getCategories(): array
    {
        return self::cleanCategories(self::$data['categories']);
    }

    private static function cleanCategories(array $categories): array
    {
        $result = [];
        foreach ($categories as $category) {
            if (array_key_exists('topics', $category)) {
                unset($category['topics']);
            }
            if (array_key_exists('children', $category)) {
                $category['topics'] = self::cleanCategories($category['children']);
            }
            $result[] = $category;
        }

        return $result;
    }

    public static function pageCount(int $length = self::PER_PAGE): int
    {
        return (int) ceil(count(self::getTopics(length: null)) / $length);
    }

    public static function getTopics(int $offset = 0, ?int $length = self::PER_PAGE): array
    {
        $result = [];
        foreach (self::$data['categories'] as $category) {
            if (self::$categoryFilter !== '' && !str_ends_with($category['url'], '/' . self::$categoryFilter)) {
                continue;
            }
            if (array_key_exists('children', $category)) {
                $result = array_merge($result, self::getTopicsInternal($category['children']));
            }
        }
        $res = [];
        foreach ($result as $category) {
            unset($category['answers'], $category['body']);
            $category['classNames'] = '';
            $res[] = $category;
        }
        usort($res, static fn($a, $b) => $b['lastAnswer'] <=> $a['lastAnswer']);
        foreach ($res as $i => &$category) {
            if ($i > 3) {
                break;
            }
            $category['new'] = true;
        }
        unset($category);

        return array_slice($res, $offset, $length);
    }

    private static function getTopicsInternal(array $categories): array
    {
        $result = [];
        foreach ($categories as $category) {
            if (self::$subFilter !== '' && !str_ends_with($category['url'], '/' . self::$subFilter)) {
                continue;
            }
            if (array_key_exists('topics', $category)) {
                $result = array_merge($result, $category['topics']);
            }
        }

        return $result;
    }

    private static array $data = [
        'categories' => [
            [
                'id' => 1,
                'name' => 'Веб-разработка',
                'url' => '/web-development',
                'children' => [
                    [
                        'id' => 101,
                        'name' => 'PHP',
                        'url' => '/web-development/php',
                        'topics' => [
                            [
                                'id' => 1001,
                                'title' => 'DI-контейнер в PHP 8.5',
                                'author' => [
                                    'nick' => 'Voral',
                                    'id' => 1,
                                ],
                                'answerCount' => 3,
                                'lastAnswer' => '2026-10-14',
                                'body' => 'Как лучше реализовать автовайринг?',
                                'answers' => [
                                    [
                                        'id' => 5001,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Используй reflection API.',
                                    ],
                                    [
                                        'id' => 5002,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'Смотри примеры в Symfony.',
                                    ],
                                    [
                                        'id' => 5003,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Спасибо, разобрался!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 1002,
                                'title' => 'Middleware паттерн',
                                'author' => [
                                    'nick' => 'Dmitry',
                                    'id' => 4,
                                ],
                                'answerCount' => 2,
                                'lastAnswer' => '2026-10-13',
                                'body' => 'Как передать данные между мидлварами?',
                                'answers' => [
                                    [
                                        'id' => 5004,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Через request attributes.',
                                    ],
                                    [
                                        'id' => 5005,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'Понял, попробую.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 1003,
                                'title' => 'Роутинг без фреймворка',
                                'author' => [
                                    'nick' => 'Alex',
                                    'id' => 2,
                                ],
                                'answerCount' => 0,
                                'lastAnswer' => '2026-10-10',
                                'body' => 'Стоит ли писать свой роутер?',
                                'answers' => [],
                            ],
                            [
                                'id' => 1004,
                                'title' => 'Обработка JSON запросов',
                                'author' => [
                                    'nick' => 'Maria',
                                    'id' => 3,
                                ],
                                'answerCount' => 4,
                                'lastAnswer' => '2026-10-15',
                                'body' => 'Как правильно парсить php://input?',
                                'answers' => [
                                    [
                                        'id' => 5006,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'file_get_contents + json_decode.',
                                    ],
                                    [
                                        'id' => 5007,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'Не забудь про проверку Content-Type.',
                                    ],
                                    [
                                        'id' => 5008,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'И обработку ошибок JSON.',
                                    ],
                                    [
                                        'id' => 5009,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'Отлично, всё работает!',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 102,
                        'name' => 'JavaScript',
                        'url' => '/web-development/javascript',
                        'topics' => [
                            [
                                'id' => 1005,
                                'title' => 'Асинхронность в JS',
                                'author' => [
                                    'nick' => 'Elena',
                                    'id' => 5,
                                ],
                                'answerCount' => 1,
                                'lastAnswer' => '2026-10-12',
                                'body' => 'Promise или async/await?',
                                'answers' => [
                                    [
                                        'id' => 5010,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Async/await чище читается.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 1006,
                                'title' => 'TypeScript vs JavaScript',
                                'author' => [
                                    'nick' => 'Dmitry',
                                    'id' => 4,
                                ],
                                'answerCount' => 3,
                                'lastAnswer' => '2026-10-14',
                                'body' => 'Стоит ли переходить на TS?',
                                'answers' => [
                                    [
                                        'id' => 5011,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'Для больших проектов - да.',
                                    ],
                                    [
                                        'id' => 5012,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Типизация спасает от багов.',
                                    ],
                                    [
                                        'id' => 5013,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'Убедили, начну изучать.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 1007,
                                'title' => 'Webpack настройка',
                                'author' => [
                                    'nick' => 'Maria',
                                    'id' => 3,
                                ],
                                'answerCount' => 0,
                                'lastAnswer' => '2026-10-10',
                                'body' => 'Как оптимизировать сборку?',
                                'answers' => [],
                            ],
                        ],
                    ],
                    [
                        'id' => 103,
                        'name' => 'CSS и верстка',
                        'url' => '/web-development/css',
                        'topics' => [
                            [
                                'id' => 1008,
                                'title' => 'Flexbox или Grid?',
                                'author' => [
                                    'nick' => 'Alex',
                                    'id' => 2,
                                ],
                                'answerCount' => 2,
                                'lastAnswer' => '2026-10-11',
                                'body' => 'Что лучше использовать?',
                                'answers' => [
                                    [
                                        'id' => 5014,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Grid для layout, Flexbox для компонентов.',
                                    ],
                                    [
                                        'id' => 5015,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Логично, спасибо!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 1009,
                                'title' => 'CSS переменные',
                                'author' => [
                                    'nick' => 'Voral',
                                    'id' => 1,
                                ],
                                'answerCount' => 1,
                                'lastAnswer' => '2026-10-10',
                                'body' => 'Поддержка в старых браузерах?',
                                'answers' => [
                                    [
                                        'id' => 5016,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'IE не поддерживает, нужен fallback.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 1010,
                                'title' => 'Адаптивная верстка',
                                'author' => [
                                    'nick' => 'Dmitry',
                                    'id' => 4,
                                ],
                                'answerCount' => 4,
                                'lastAnswer' => '2026-10-15',
                                'body' => 'Mobile-first или desktop-first?',
                                'answers' => [
                                    [
                                        'id' => 5017,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Mobile-first современнее.',
                                    ],
                                    [
                                        'id' => 5018,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Меньше медиа-запросов нужно.',
                                    ],
                                    [
                                        'id' => 5019,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Согласен с mobile-first.',
                                    ],
                                    [
                                        'id' => 5020,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'Переделаю проект под mobile-first.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Базы данных',
                'url' => '/databases',
                'children' => [
                    [
                        'id' => 201,
                        'name' => 'MySQL',
                        'url' => '/databases/mysql',
                        'topics' => [
                            [
                                'id' => 2001,
                                'title' => 'Оптимизация запросов',
                                'author' => [
                                    'nick' => 'Maria',
                                    'id' => 3,
                                ],
                                'answerCount' => 2,
                                'lastAnswer' => '2026-10-13',
                                'body' => 'Как ускорить медленные SELECT?',
                                'answers' => [
                                    [
                                        'id' => 5021,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Добавь индексы на часто используемые поля.',
                                    ],
                                    [
                                        'id' => 5022,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'Проверю EXPLAIN, спасибо!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 2002,
                                'title' => 'Нормализация БД',
                                'author' => [
                                    'nick' => 'Alex',
                                    'id' => 2,
                                ],
                                'answerCount' => 0,
                                'lastAnswer' => '2026-10-10',
                                'body' => 'До какой формы нормализовать?',
                                'answers' => [],
                            ],
                            [
                                'id' => 2003,
                                'title' => 'Транзакции в MySQL',
                                'author' => [
                                    'nick' => 'Elena',
                                    'id' => 5,
                                ],
                                'answerCount' => 3,
                                'lastAnswer' => '2026-10-14',
                                'body' => 'Когда использовать транзакции?',
                                'answers' => [
                                    [
                                        'id' => 5023,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'При атомарных операциях.',
                                    ],
                                    [
                                        'id' => 5024,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Для целостности данных обязательно.',
                                    ],
                                    [
                                        'id' => 5025,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Поняла, внедрю в проект.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 202,
                        'name' => 'PostgreSQL',
                        'url' => '/databases/postgresql',
                        'topics' => [
                            [
                                'id' => 2004,
                                'title' => 'JSONB тип данных',
                                'author' => [
                                    'nick' => 'Dmitry',
                                    'id' => 4,
                                ],
                                'answerCount' => 1,
                                'lastAnswer' => '2026-10-12',
                                'body' => 'Преимущества JSONB перед JSON?',
                                'answers' => [
                                    [
                                        'id' => 5026,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'JSONB бинарный, быстрее индексация.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 2005,
                                'title' => 'Миграции базы данных',
                                'author' => [
                                    'nick' => 'Voral',
                                    'id' => 1,
                                ],
                                'answerCount' => 4,
                                'lastAnswer' => '2026-10-15',
                                'body' => 'Какой инструмент для миграций выбрать?',
                                'answers' => [
                                    [
                                        'id' => 5027,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Phinx или Doctrine Migrations.',
                                    ],
                                    [
                                        'id' => 5028,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Liquibase тоже хорош.',
                                    ],
                                    [
                                        'id' => 5029,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'Я за Phinx, простой и понятный.',
                                    ],
                                    [
                                        'id' => 5030,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Попробую Phinx.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 2006,
                                'title' => 'Репликация PostgreSQL',
                                'author' => [
                                    'nick' => 'Alex',
                                    'id' => 2,
                                ],
                                'answerCount' => 0,
                                'lastAnswer' => '2026-10-10',
                                'body' => 'Как настроить master-slave?',
                                'answers' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 3,
                'name' => 'DevOps и инструменты',
                'url' => '/devops',
                'children' => [
                    [
                        'id' => 301,
                        'name' => 'Docker',
                        'url' => '/devops/docker',
                        'topics' => [
                            [
                                'id' => 3001,
                                'title' => 'Multi-stage builds',
                                'author' => [
                                    'nick' => 'Elena',
                                    'id' => 5,
                                ],
                                'answerCount' => 2,
                                'lastAnswer' => '2026-10-14',
                                'body' => 'Зачем нужны multi-stage сборки?',
                                'answers' => [
                                    [
                                        'id' => 5031,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Уменьшают размер финального образа.',
                                    ],
                                    [
                                        'id' => 5032,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Круто, внедрю в проект!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 3002,
                                'title' => 'Docker Compose для разработки',
                                'author' => [
                                    'nick' => 'Dmitry',
                                    'id' => 4,
                                ],
                                'answerCount' => 3,
                                'lastAnswer' => '2026-10-15',
                                'body' => 'Как организовать dev окружение?',
                                'answers' => [
                                    [
                                        'id' => 5033,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'Volume для кода, отдельный сервис для БД.',
                                    ],
                                    [
                                        'id' => 5034,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'И не забудь про .env файлы.',
                                    ],
                                    [
                                        'id' => 5035,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'Отлично, всё настроил!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 3003,
                                'title' => 'Оптимизация Docker образов',
                                'author' => [
                                    'nick' => 'Voral',
                                    'id' => 1,
                                ],
                                'answerCount' => 1,
                                'lastAnswer' => '2026-10-13',
                                'body' => 'Alpine или Debian base image?',
                                'answers' => [
                                    [
                                        'id' => 5036,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Alpine меньше, но могут быть проблемы с glibc.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 3004,
                                'title' => 'Docker networking',
                                'author' => [
                                    'nick' => 'Maria',
                                    'id' => 3,
                                ],
                                'answerCount' => 0,
                                'lastAnswer' => '2026-10-10',
                                'body' => 'Как связать контейнеры между собой?',
                                'answers' => [],
                            ],
                        ],
                    ],
                    [
                        'id' => 302,
                        'name' => 'CI/CD',
                        'url' => '/devops/ci-cd',
                        'topics' => [
                            [
                                'id' => 3005,
                                'title' => 'GitHub Actions для PHP',
                                'author' => [
                                    'nick' => 'Alex',
                                    'id' => 2,
                                ],
                                'answerCount' => 2,
                                'lastAnswer' => '2026-10-12',
                                'body' => 'Как настроить PHPUnit в CI?',
                                'answers' => [
                                    [
                                        'id' => 5037,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Используй shivammathur/setup-php action.',
                                    ],
                                    [
                                        'id' => 5038,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Работает, спасибо!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 3006,
                                'title' => 'Автоматический деплой',
                                'author' => [
                                    'nick' => 'Elena',
                                    'id' => 5,
                                ],
                                'answerCount' => 4,
                                'lastAnswer' => '2026-10-15',
                                'body' => 'Лучшие практики деплоя?',
                                'answers' => [
                                    [
                                        'id' => 5039,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'Blue-green deployment надежнее.',
                                    ],
                                    [
                                        'id' => 5040,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'И rollback план обязателен.',
                                    ],
                                    [
                                        'id' => 5041,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Canary releases тоже хороши.',
                                    ],
                                    [
                                        'id' => 5042,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Много полезной информации!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 3007,
                                'title' => 'Тестирование в pipeline',
                                'author' => [
                                    'nick' => 'Maria',
                                    'id' => 3,
                                ],
                                'answerCount' => 1,
                                'lastAnswer' => '2026-10-11',
                                'body' => 'Какие тесты запускать в CI?',
                                'answers' => [
                                    [
                                        'id' => 5043,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Unit и integration обязательно.',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 303,
                        'name' => 'Git и контроль версий',
                        'url' => '/devops/git',
                        'topics' => [
                            [
                                'id' => 3008,
                                'title' => 'Git flow стратегия',
                                'author' => [
                                    'nick' => 'Voral',
                                    'id' => 1,
                                ],
                                'answerCount' => 3,
                                'lastAnswer' => '2026-10-14',
                                'body' => 'Стоит ли использовать Git Flow?',
                                'answers' => [
                                    [
                                        'id' => 5044,
                                        'author' => [
                                            'nick' => 'Elena',
                                            'id' => 5,
                                        ],
                                        'body' => 'Для больших команд - да.',
                                    ],
                                    [
                                        'id' => 5045,
                                        'author' => [
                                            'nick' => 'Dmitry',
                                            'id' => 4,
                                        ],
                                        'body' => 'GitHub Flow проще для небольших проектов.',
                                    ],
                                    [
                                        'id' => 5046,
                                        'author' => [
                                            'nick' => 'Voral',
                                            'id' => 1,
                                        ],
                                        'body' => 'Выберу GitHub Flow тогда.',
                                    ],
                                ],
                            ],
                            [
                                'id' => 3009,
                                'title' => 'Interactive rebase',
                                'author' => [
                                    'nick' => 'Alex',
                                    'id' => 2,
                                ],
                                'answerCount' => 2,
                                'lastAnswer' => '2026-10-13',
                                'body' => 'Когда использовать rebase вместо merge?',
                                'answers' => [
                                    [
                                        'id' => 5047,
                                        'author' => [
                                            'nick' => 'Maria',
                                            'id' => 3,
                                        ],
                                        'body' => 'Rebase для чистой истории.',
                                    ],
                                    [
                                        'id' => 5048,
                                        'author' => [
                                            'nick' => 'Alex',
                                            'id' => 2,
                                        ],
                                        'body' => 'Но осторожно с публичными ветками!',
                                    ],
                                ],
                            ],
                            [
                                'id' => 3010,
                                'title' => '.gitignore best practices',
                                'author' => [
                                    'nick' => 'Dmitry',
                                    'id' => 4,
                                ],
                                'answerCount' => 0,
                                'lastAnswer' => '2026-10-10',
                                'body' => 'Что обязательно игнорировать?',
                                'answers' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];
}
