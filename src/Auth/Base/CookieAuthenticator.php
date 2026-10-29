<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth\Base;

use Vasoft\Joke\Auth\User;
use Vasoft\Joke\Contract\Auth\AuthenticatorInterface;
use Vasoft\Joke\Contract\Auth\UserInterface;
use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;
use Vasoft\Joke\Http\HttpRequest;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;
use Vasoft\Joke\Http\Response\Response;

class CookieAuthenticator implements AuthenticatorInterface
{
    public const string VAR_USER_DATA = 'JOKE_AUTH_USER';
    private readonly ConnectionInterface $connection;

    public function __construct(
        private readonly HttpRequest $request,
        ConnectionManager $connectionManager,
    ) {
        $this->connection = $connectionManager->connection();
    }

    public function authenticate(): ?UserInterface
    {
        $token = $this->request->cookies->getString(self::VAR_USER_DATA, '');
        if ('' === $token) {
            return null;
        }
        $result = $this->connection->query(
            <<<'SQL'
                SELECT user_id FROM user_remember_tokens
                WHERE token = :token AND expires_at >= NOW()
                LIMIT 1
                SQL,
            ['token' => $token],
        );
        $exists = $result->fetch();
        if (false !== $exists) {
            return new User($exists['user_id']);
        }

        return null;
    }

    public function revokeCurrent(Response $response): void
    {
        $response->cookies->add(static::VAR_USER_DATA, '', lifetime: 0, secure: true, httpOnly: true);
        $token = $this->request->cookies->getString(self::VAR_USER_DATA, '');
        if ('' === $token) {
            return;
        }
        $this->revokeToken($token);
    }

    public function save(UserInterface $user, Response $response): void
    {
        // 30 дней
        $ttl = 2592000;
        $token = bin2hex(random_bytes(32));
        $response->cookies->add(
            static::VAR_USER_DATA,
            $token,
            lifetime: $ttl,
            secure: true,
            httpOnly: true,
        );
        $expiresAt = new \DateTimeImmutable()->modify("+{$ttl} seconds");
        $this->connection->execute(
            <<<'SQL'
                INSERT INTO user_remember_tokens (user_id, token, expires_at)
                VALUES (:user_id, :token, :expires_at)
                SQL,
            [
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            ],
        );
    }

    /**
     * Удаляет токен (при выходе или использовании).
     *
     * @param string $token Токен для удаления
     *
     * @throws QueryException
     */
    public function revokeToken(string $token): void
    {
        $this->connection->execute(
            'DELETE FROM user_remember_tokens WHERE token = :token',
            ['token' => $token],
        );
    }

    /**
     * Удаляет все токены пользователя.
     *
     * @param int $userId ID пользователя
     *
     * @throws QueryException
     */
    public function revokeAllUserTokens(int $userId): void
    {
        $this->connection->execute(
            'DELETE FROM user_remember_tokens WHERE user_id = :user_id',
            ['user_id' => $userId],
        );
    }

    /**
     * Очищает истёкшие токены.
     */
    public function cleanupExpiredTokens(): void
    {
        $this->connection->execute(
            'DELETE FROM user_remember_tokens WHERE expires_at <= NOW()',
        );
    }
}
