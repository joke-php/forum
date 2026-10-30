<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth;

use Vasoft\Joke\Auth\User;
use Vasoft\Joke\Collections\PropsCollection;
use Vasoft\Joke\Config\Exceptions\ConfigException;
use Vasoft\Joke\Container\Exceptions\ParameterResolveException;
use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;
use Vasoft\Joke\Db\Sql\Exceptions\ConnectionException;
use Vasoft\Joke\Forum\Auth\Base\AuthRepository;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;

/**
 * Репозиторий для работы с пользователями форума.
 *
 * Предоставляет методы для управления данными пользователей форума,
 * включая поиск по ник/email и регистрацию новых пользователей.
 */
class ForumUserRepository
{
    private ConnectionInterface $connection;

    /**
     * Конструктор репозитория пользователей форума.
     *
     * @param ConnectionManager $connectionManager Менеджер подключений к базе данных
     * @param AuthRepository    $authRepository    Репозиторий базовой аутентификации
     *
     * @throws ConfigException           При ошибках конфигурации
     * @throws ParameterResolveException При ошибках связывания параметров
     * @throws ConnectionException       При ошибках соединения
     */
    public function __construct(
        ConnectionManager $connectionManager,
        private readonly AuthRepository $authRepository,
        private readonly PasswordHasher $passwordHasher,
    ) {
        $this->connection = $connectionManager->connection();
    }

    /**
     * Ищет пользователя по nickname или email.
     *
     * Выполняет поиск пользователя в таблице forum_user с объединением
     * с таблицей user для получения email. Возвращает первого найденного
     * пользователя или null, если пользователь не найден.
     *
     * @param string $email    Email адрес для поиска
     * @param string $nickname Nickname пользователя для поиска
     *
     * @return null|User Объект пользователя или null, если не найден
     *
     * @throws QueryException При ошибке выполнения запроса к базе данных
     *
     * @todo Прямой запрос к таблице другого модуля использовать построитель запросов когда будет готов
     */
    public function getByNicknameOrEmail(string $email, string $nickname): ?User
    {
        $result = $this->connection->query(
            <<<'SQL'
                    SELECT u.id, u.email, f.nickname FROM forum_user f
                                   INNER JOIN user u ON u.id = f.user_id
                                   WHERE f.nickname = :nickname OR u.email = :email
                                   LIMIT 1
                SQL,
            ['nickname' => $nickname, 'email' => $email],
        );

        $exists = $result->fetch();
        if ($exists) {
            $props = new PropsCollection([
                'forum_nickname' => $exists['nickname'],
                'email' => $exists['email'],
            ]);

            return new User($exists['id'], $props);
        }

        return null;
    }

    public function getByEmailAndPassword(string $email, string $password): ?User
    {
        $result = $this->connection->query(
            <<<'SQL'
                    SELECT u.id, u.email, f.nickname, u.password FROM forum_user f
                                   INNER JOIN user u ON u.id = f.user_id
                                   WHERE u.email = :email
                                   LIMIT 1
                SQL,
            ['email' => $email],
        );

        $exists = $result->fetch();
        if ($exists && $this->passwordHasher->verify($password, $exists['password'])) {
            $props = new PropsCollection([
                'forum_nickname' => $exists['nickname'],
                'email' => $exists['email'],
            ]);

            return new User($exists['id'], $props);
        }

        return null;
    }

    public function getActive(int $id): ?User
    {
        $result = $this->connection->query(
            <<<'SQL'
                    SELECT u.id, u.email, f.nickname, u.last_visit FROM forum_user f
                                   INNER JOIN user u ON u.id = f.user_id
                                   WHERE u.id = :id and (u.blocked_to <= NOW() or u.blocked_to IS NULL)
                                   LIMIT 1
                SQL,
            ['id' => $id],
        );

        $exists = $result->fetch();
        if ($exists) {
            $props = new PropsCollection([
                'forum_nickname' => $exists['nickname'],
                'email' => $exists['email'],
                'last_visit' => $exists['last_visit'],
            ]);

            return new User((int) $exists['id'], $props);
        }

        return null;
    }

    /**
     * Добавляет нового пользователя форума.
     *
     * Сначала создает запись в базовой таблице пользователей через AuthRepository,
     * затем добавляет специфичные для форума данные (nickname) в таблицу forum_user.
     *
     * @param string $email    Email адрес пользователя
     * @param string $password Хеш пароля пользователя
     * @param string $nickname Ник пользователя на форуме
     *
     * @return int Идентификатор созданного пользователя
     *
     * @throws QueryException При ошибке выполнения запроса к базе данных
     */
    public function add(string $email, string $password, string $nickname): int
    {
        $passwordHash = $this->passwordHasher->hash($password);
        $id = $this->authRepository->add($email, $passwordHash);

        $this->connection->execute(
            'INSERT INTO forum_user (user_id, nickname) values (:user_id, :nickname) ',
            ['user_id' => $id, 'nickname' => $nickname],
        );

        $result = $this->connection->query("select id from role where module = 'forum' and name='user'");
        $exist = $result->fetch();
        if ($exist) {
            $this->connection->execute(
                'INSERT INTO user_role (user_id, role_id) values (:user_id, :role_id) ',
                ['user_id' => $id, 'role_id' => $exist['id']],
            );
        }

        return $id;
    }

    public function setVisit(User $user): void
    {
        $this->connection->execute(
            'UPDATE user SET last_visit = :last_visit where id = :user_id',
            ['user_id' => $user->id, 'last_visit' => new \DateTime()->format('Y-m-d H:i:s')],
        );
    }

    public function fillUserByIds(array $users): array
    {
        if (empty($users)) {
            return [];
        }
        $ids = array_keys($users);

        $placeholders = implode(
            ',',
            array_map(
                static fn(int $i): string => ":id_{$i}",
                array_keys($ids),
            ),
        );

        $params = array_combine(
            array_map(static fn(int $i): string => ":id_{$i}", array_keys($ids)),
            $ids,
        );

        $queryResult = $this->connection->query(
            <<<SQL
                    SELECT u.id, f.nickname, IF((u.blocked_to <= NOW() or u.blocked_to IS NULL), 0, 1) AS blocked
                    FROM forum_user f
                    INNER JOIN user u ON u.id = f.user_id
                    WHERE u.id IN ({$placeholders})
                SQL,
            $params,
        );

        foreach ($queryResult as $user) {
            $users[(int) $user['id']] = [
                'nickname' => $user['nickname'],
                'blocked' => 1 === $user['blocked'],
            ];
        }

        return $users;
    }
}
