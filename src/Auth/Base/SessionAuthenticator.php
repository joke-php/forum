<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth\Base;

use Vasoft\Joke\Auth\User;
use Vasoft\Joke\Contract\Auth\AuthenticatorInterface;
use Vasoft\Joke\Contract\Auth\UserInterface;
use Vasoft\Joke\Exceptions\ConversionException;
use Vasoft\Joke\Http\HttpRequest;

/**
 * Аутентификатор на основе HTTP-сессии.
 *
 * Извлекает идентификатор пользователя из сессии по ключу {@see VAR_USER_DATA}.
 * Если значение отсутствует или равно 0, считается, что пользователь не авторизован.
 */
class SessionAuthenticator implements AuthenticatorInterface
{
    /**
     * Ключ для хранения идентификатора пользователя в сессии.
     */
    public const string VAR_USER_DATA = 'JOKE_AUTH_USER';

    /**
     * @param HttpRequest $request Текущий HTTP-запрос, содержащий объект сессии
     */
    public function __construct(
        private readonly HttpRequest $request,
    ) {}

    /**
     * Пытается определить пользователя по данным сессии.
     *
     * Метод обращается к сессии за значением по ключу {@see VAR_USER_DATA}.
     *
     * @return null|UserInterface Объект пользователя с черновым ID или null, если пользователь не найден/не авторизован
     */
    public function authenticate(): ?UserInterface
    {
        try {
            $id = $this->request->session->getString(static::VAR_USER_DATA, '');
        } catch (ConversionException $e) {
            return null;
        }

        return '' === $id ? null : new User($id);
    }
}
