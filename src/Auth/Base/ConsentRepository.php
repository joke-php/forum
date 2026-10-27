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
 * Репозиторий для работы с согласиями пользователей.
 *
 * Предоставляет методы для сохранения записей о согласии пользователя
 * на обработку данных или выполнение определенных действий.
 */
class ConsentRepository
{
    private ConnectionInterface $connection;

    /**
     * Конструктор репозитория согласий.
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
     * Добавляет запись о согласии пользователя.
     *
     * @param string   $ip          IP-адрес пользователя
     * @param string   $url         URL страницы, на которой было дано согласие
     * @param string   $consentType Тип согласия (например, 'privacy_policy', 'terms_of_service')
     * @param null|int $userId      Идентификатор пользователя (null для неавторизованных пользователей)
     *
     * @return int Идентификатор созданной записи о согласии
     *
     * @throws QueryException При ошибке выполнения запроса к базе данных
     */
    public function add(string $ip, string $url, string $consentType, ?int $userId = null): int
    {
        $this->connection->execute(
            'INSERT INTO user_consent (user_id, ip, url, consent) values (:user_id, :ip, :url, :consent)',
            [
                'user_id' => $userId,
                'ip' => $ip,
                'url' => $url,
                'consent' => $consentType,
            ],
        );

        return (int) $this->connection->getLastInsertId();
    }
}
