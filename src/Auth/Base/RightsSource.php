<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth\Base;

use Vasoft\Joke\Config\Exceptions\ConfigException;
use Vasoft\Joke\Container\Exceptions\ParameterResolveException;
use Vasoft\Joke\Contract\Auth\RightsSourceInterface;
use Vasoft\Joke\Contract\Auth\UserInterface;
use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;
use Vasoft\Joke\Db\Sql\Exceptions\ConnectionException;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;

/**
 * Источник прав доступа на основе ролевой модели (RBAC) в базе данных.
 *
 * Извлекает права пользователя путем объединения таблиц:
 * user_role -> role_right -> right_item.
 * Формат возвращаемого права: "module:name" (например, "posts:create").
 */
class RightsSource implements RightsSourceInterface
{
    private array $rights = [];
    /**
     * Активное подключение к базе данных.
     * Получено через ConnectionManager при инициализации сервиса.
     */
    private readonly ConnectionInterface $connection;

    /**
     * @param ConnectionManager $connectionManager Менеджер подключений для получения соединения по умолчанию
     *
     * @throws ConfigException
     * @throws ParameterResolveException
     * @throws ConnectionException
     */
    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connection = $connectionManager->connection();
    }

    /**
     * {@inheritDoc}
     *
     * Возвращает список прав вида ["module:name", ...].
     * Для анонимных пользователей (гостей) всегда возвращает пустой массив,
     * чтобы избежать ненужного обращения к базе данных.
     *
     * @param UserInterface $user Пользователь, для которого запрашиваются права
     *
     * @return list<string> Плоский массив строк с идентификаторами прав
     *
     * @throws QueryException При ошибке выполнения SQL-запроса
     */
    public function getRights(UserInterface $user): array
    {
        if (null === $user->id) {
            return [];
        }
        if (array_key_exists($user->id, $this->rights)) {
            return $this->rights[$user->id];
        }

        $sql = <<<'SQL'
            SELECT DISTINCT CONCAT(ri.module,':', ri.name) AS right_code
            FROM right_item ri
            WHERE ri.id IN (
                SELECT rr.right_id FROM user_role ur JOIN role_right rr ON rr.role_id = ur.role_id WHERE ur.user_id = :user_id
                UNION
                SELECT right_id FROM user_right WHERE user_id = :user_id
            );
            SQL;

        $result = $this->connection->query($sql, ['user_id' => $user->id]);
        $this->rights[$user->id] = [];
        foreach ($result as $row) {
            $this->rights[$user->id][] = (string) $row['right_code'];
        }

        return $this->rights[$user->id];
    }
}
