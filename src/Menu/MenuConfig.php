<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Config\AbstractConfig;
use Vasoft\Joke\Config\Exceptions\ConfigException;
use Vasoft\Joke\Forum\Contract\MenuInterface;

/**
 * Конфигурационный класс для управления меню.
 *
 * Предоставляет возможность регистрации и получения конфигурации меню.
 * Поддерживает три способа хранения:
 * 1. Готовый объект MenuInterface
 * 2. Callable-фабрика для ленивой инициализации
 * 3. Строка с именем класса (class-string), реализующего MenuInterface
 */
class MenuConfig extends AbstractConfig
{
    /**
     * Хранилище зарегистрированных меню.
     *
     * Ключи — уникальные имена меню.
     * Значения могут быть:
     * - Экземпляром MenuInterface
     * - Callable, возвращающим MenuInterface
     * - Строкой имени класса (class-string<MenuInterface>)
     *
     * @var array<string, callable|class-string<MenuInterface>|MenuInterface>
     */
    private array $menus = [];

    /**
     * Регистрирует новое меню в конфигурации.
     *
     * Значения могут быть:
     * - Экземпляром MenuInterface
     * - Callable, возвращающим MenuInterface
     * - Строкой имени класса (class-string<MenuInterface>)
     *
     * @param string                                             $name Уникальное имя меню
     * @param callable|class-string<MenuInterface>|MenuInterface $menu Экземпляр меню или фабрика для его создания
     *
     * @return static Для поддержки цепочки вызовов (fluent interface)
     *
     * @throws ConfigException Если меню с указанным именем уже зарегистрировано
     */
    public function addMenu(string $name, MenuInterface|callable|string $menu): static
    {
        if (array_key_exists($name, $this->menus)) {
            throw new ConfigException("Menu \"{$name}\" already exists.");
        }
        $this->menus[$name] = $menu;

        return $this;
    }

    /**
     * Возвращает сырые данные зарегистрированного меню по имени.
     *
     * @param string $name Имя запрашиваемого меню
     *
     * @return null|callable|class-string<MenuInterface>|MenuInterface Зарегистрированное значение или null, если не найдено
     */
    public function get(string $name): callable|string|MenuInterface|null
    {
        return $this->menus[$name] ?? null;
    }
}
