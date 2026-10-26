<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth;

use Vasoft\Joke\Exceptions\JokeException;

/**
 * Сервис для безопасного хэширования и проверки паролей.
 *
 * Использует алгоритмы хэширования (Argon2id или bcrypt) с автоматической
 * генерацией криптографически стойкой соли и дополнительным "перцем" (pepper) для
 * усиления безопасности.
 *
 * Особенности:
 * - Автоматическая генерация уникальной соли для каждого пароля
 * - Поддержка pepper (секретного ключа приложения) для дополнительной защиты
 * - Возможность проверки необходимости пере-хэширования при изменении параметров
 * - Защита от timing-атак через использование password_verify()
 *
 * @see https://www.php.net/manual/ru/function.password-hash.php
 */
readonly class PasswordHasher
{
    /**
     * Алгоритм хэширования.
     */
    private string $algorithm;

    /**
     * Параметры алгоритма хэширования.
     *
     * Для Argon2id: memory_cost, time_cost, threads
     * Для bcrypt: cost
     *
     * @var array{cost?: int}|array{memory_cost?: int, time_cost?: int, threads?: int}
     */
    private array $options;

    /**
     * Инициализирует сервис хэширования паролей.
     *
     * @param string                                                                          $pepper    Секретный ключ приложения для дополнительного усиления безопасности. Должен быть минимум 16 символов и храниться в защищённом месте (например, .env файл), а не в базе данных
     * @param null|int|string                                                                 $algorithm Алгоритм хэширования. По умолчанию используется PASSWORD_ARGON2ID. Доступные значения: PASSWORD_ARGON2ID, PASSWORD_BCRYPT
     * @param null|array{cost?: int}|array{memory_cost?: int, time_cost?: int, threads?: int} $options   Параметры алгоритма. Если не указаны, используются безопасные значения по умолчанию: для Argon2id - memory_cost=65536, time_cost=4, threads=3; для bcrypt - cost=12
     *
     * @throws JokeException Если pepper пустой, слишком короткий или передан неподдерживаемый алгоритм
     */
    public function __construct(
        #[\SensitiveParameter]
        private string $pepper,
        string|int|null $algorithm = null,
        ?array $options = null,
    ) {
        if (strlen($this->pepper) < 16) {
            throw new JokeException('Pepper must be at least 16 characters long for security');
        }

        $this->algorithm = $algorithm ?? PASSWORD_ARGON2ID;

        $this->options = $options ?? match ($this->algorithm) {
            PASSWORD_ARGON2ID => [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3,
            ],
            PASSWORD_BCRYPT => [
                'cost' => 12,
            ],
            default => throw new \InvalidArgumentException('Unsupported algorithm'),
        };
    }

    /**
     * Хэширует пароль с использованием заданного алгоритма и pepper.
     *
     * Pepper добавляется к паролю перед хэшированием для дополнительной защиты.
     *
     * @param string $password Пароль в открытом виде для хэширования
     *
     * @return string Хэш пароля, включающий алгоритм, параметры, соль и хэш. Формат зависит от алгоритма:
     *                - Argon2id: $argon2id$v=19$m=65536,t=4,p=3$salt$hash
     *                - bcrypt: $2y$12$saltandhash...
     *
     * @throws \RuntimeException Если хэширование не удалось (например, недостаточно памяти)
     */
    public function hash(#[\SensitiveParameter] string $password): string
    {
        $combined = $password . $this->pepper;
        $hash = password_hash($combined, $this->algorithm, $this->options);

        if (false === $hash) {
            throw new \RuntimeException('Failed to hash password');
        }

        return $hash;
    }

    /**
     * Проверяет соответствие пароля сохранённому хэшу.
     *
     * Безопасно сравнивает предоставленный пароль с хэшем, используя
     * constant-time сравнение для защиты от timing-атак.
     * Автоматически извлекает соль и параметры из хэша.
     *
     * @param string $password Пароль в открытом виде для проверки
     * @param string $hash     Сохранённый хэш пароля из базы данных
     *
     * @return bool true если пароль совпадает с хэшем, false в противном случае
     */
    public function verify(#[\SensitiveParameter] string $password, string $hash): bool
    {
        $combined = $password . $this->pepper;

        return password_verify($combined, $hash);
    }

    /**
     * Проверяет необходимость пере-хэширования пароля.
     *
     * Возвращает true если:
     * - Изменился алгоритм хэширования
     * - Изменились параметры алгоритма (cost, memory_cost и т.д.)
     * - Хэш был создан другим алгоритмом
     *
     * @param string $hash Сохранённый хэш пароля из базы данных
     *
     * @return bool true если требуется пере-хэширование с текущими параметрами, false если параметры актуальны
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->algorithm, $this->options);
    }
}
