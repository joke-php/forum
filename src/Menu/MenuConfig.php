<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Config\AbstractConfig;
use Vasoft\Joke\Config\Exceptions\ConfigException;
use Vasoft\Joke\Forum\Contract\MenuInterface;

/**
 * Конфигурационный класс для управления меню форума.
 *
 * Предоставляет возможность регистрации и получения экземпляров меню,
 * поддерживая как готовые объекты MenuInterface, так и callable-фабрики
 * для ленивой инициализации.
 */
class MenuConfig extends AbstractConfig
{
    /**
     * Хранилище зарегистрированных меню.
     *
     * Ключи — имена меню, значения — экземпляры MenuInterface или callable,
     * возвращающие MenuInterface при вызове.
     *
     * @var array<string, callable|MenuInterface>
     */
    private array $menus = [];

    /**
     * Регистрирует новое меню в конфигурации.
     *
     * Поддерживает два способа регистрации:
     * - Готовый объект, реализующий MenuInterface
     * - Callable (замыкание/функция), который будет вызван при первом обращении
     *   к меню через get() и должен вернуть экземпляр MenuInterface
     *
     * @param string                 $name Уникальное имя меню
     * @param callable|MenuInterface $menu Экземпляр меню или фабрика для его создания
     *
     * @return static Для поддержки цепочки вызовов (fluent interface)
     *
     * @throws ConfigException Если меню с указанным именем уже зарегистрировано
     */
    public function addMenu(string $name, MenuInterface|callable $menu): static
    {
        if (array_key_exists($name, $this->menus)) {
            throw new ConfigException("Menu \"{$name}\" already exists.");
        }
        $this->menus[$name] = $menu;

        return $this;
    }

    /**
     * Возвращает зарегистрированное меню по имени.
     *
     * Если меню было зарегистрировано как callable, оно будет выполнено
     * при первом обращении, а результат закэширован для последующих вызовов.
     *
     * @param string $name Имя запрашиваемого меню
     *
     * @return null|MenuInterface Экземпляр меню или null, если меню не найдено
     *
     * @throws ConfigException Если callable не вернул экземпляр MenuInterface
     */
    public function get(string $name): ?MenuInterface
    {
        $menu = $this->menus[$name] ?? null;
        if ($menu instanceof MenuInterface) {
            return $menu;
        }
        if (is_callable($menu)) {
            $menu = $menu();
            if (!$menu instanceof MenuInterface) {
                throw new ConfigException("Menu \"{$name}\" must implement " . MenuInterface::class);
            }

            return $menu;
        }

        return null;
    }
}
