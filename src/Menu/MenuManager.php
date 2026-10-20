<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Config\Exceptions\ConfigException;
use Vasoft\Joke\Container\Exceptions\ParameterResolveException;
use Vasoft\Joke\Container\ServiceContainer;
use Vasoft\Joke\Forum\Contract\MenuInterface;

/**
 * Менеджер управления меню.
 *
 * Отвечает за получение, инстанцирование и кэширование экземпляров меню.
 * Использует MenuConfig для получения определений (объектов, callable или имен классов)
 * и ServiceContainer для создания экземпляров из class-string или callable.
 *
 * Реализует паттерн Singleton-like кэширования внутри жизненного цикла запроса:
 * каждое меню создается только один раз при первом обращении.
 */
class MenuManager
{
    /**
     * Кэш созданных экземпляров меню.
     *
     * Ключи — имена меню, значения — готовые объекты MenuInterface.
     *
     * @var array<string, MenuInterface>
     */
    private array $menus = [];

    /**
     * Конструктор менеджера меню.
     *
     * @param MenuConfig       $menuConfig       Конфигурация с определениями меню
     * @param ServiceContainer $serviceContainer DI-контейнер для инстанцирования сервисов
     */
    public function __construct(
        private readonly MenuConfig $menuConfig,
        private readonly ServiceContainer $serviceContainer,
    ) {}

    /**
     * Возвращает экземпляр меню по имени.
     *
     * @param string $name Имя запрашиваемого меню
     *
     * @return MenuInterface Экземпляр запрошенного меню
     *
     * @throws ConfigException           Если меню с таким именем не зарегистрировано в конфиге или созданный через
     *                                   контейнер объект не реализует MenuInterface
     * @throws ParameterResolveException При ошибках автосвязывания
     */
    public function get(string $name): MenuInterface
    {
        if (array_key_exists($name, $this->menus)) {
            return $this->menus[$name];
        }
        $definition = $this->menuConfig->get($name);
        if (null === $definition) {
            throw new ConfigException("Menu \"{$name}\" does not exist.");
        }
        if ($definition instanceof MenuInterface) {
            $this->menus[$name] = $definition;

            return $this->menus[$name];
        }
        $menu = $this->serviceContainer->make($definition);
        if ($menu instanceof MenuInterface) {
            $this->menus[$name] = $menu;

            return $this->menus[$name];
        }

        throw new ConfigException("Menu \"{$name}\" must implement MenuInterface.");
    }
}
