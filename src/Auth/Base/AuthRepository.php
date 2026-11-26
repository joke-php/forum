<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth\Base;

use Vasoft\Joke\Config\Exceptions\ConfigException;
use Vasoft\Joke\Container\Exceptions\ParameterResolveException;
use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;
use Vasoft\Joke\Db\Sql\Exceptions\ConnectionException;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;

/**
 * Репозиторий для работы с аутентификацией пользователей.
 *
 * Предоставляет методы для сохранения данных пользователя при регистрации
 * и взаимодействия с таблицей пользователей в базе данных.
 */
class AuthRepository
{
    private ConnectionInterface $connection;

    /**
     * Конструктор репозитория аутентификации.
     *
     * @param ConnectionManager $connectionManager Менеджер подключений к базе данных
     *
     * @throws ConfigException           При ошибках конфигурации
     * @throws ParameterResolveException При ошибках связывания параметров
     * @throws ConnectionException       При ошибках соединения
     */
    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connection = $connectionManager->connection();
    }

    /**
     * Добавляет нового пользователя в базу данных.
     *
     * Пароль должен быть предварительно захеширован.
     *
     * @param string $email    Email адрес пользователя
     * @param string $password Хеш пароля пользователя
     *
     * @return int Идентификатор созданного пользователя (последний вставленный ID)
     *
     * @throws QueryException При ошибке выполнения запроса к базе данных
     */
    public function add(string $email, string $password): int
    {
        $this->connection->execute(
            'INSERT INTO user (email, password) values (:email, :password) ',
            ['email' => $email, 'password' => $password],
        );

        return (int) $this->connection->getLastInsertId();
    }

    public function exists(int $id): bool
    {
        $result = $this->connection->query(
            <<<'SQL'
                SELECT id
                FROM user
                WHERE id = :id
                SQL,
            [
                'id' => $id,
            ],
        );

        return false !== $result->fetch();
    }

    public function block(int $id, \DateTimeImmutable $date): bool
    {
        $this->connection->execute(
            'UPDATE user SET blocked_to = :date where id = :id',
            ['id' => $id, 'date' => $date->format('Y-m-d H:i:s')],
        );

        return true;
    }
    public function unblock(int $id): bool
    {
        $this->connection->execute(
            'UPDATE user SET blocked_to = null where id = :id',
            ['id' => $id],
        );

        return true;
    }
}
