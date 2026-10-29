<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Forum\Contract\MenuItemInterface;

/**
 * Элемент навигационного меню.
 *
 * Реализует интерфейс MenuItemInterface, представляя собой узел в иерархической
 * структуре меню. Содержит информацию о ссылке, отображаемом тексте, состоянии
 * доступности и может иметь вложенные дочерние элементы.
 */
class MenuItem implements MenuItemInterface
{
    /**
     * Конструктор элемента меню.
     *
     * @param string $url     URL-адрес для перехода при клике на элемент
     * @param string $label   Текстовая метка (название), отображаемая пользователю
     * @param bool   $enabled Флаг доступности элемента (по умолчанию true)
     * @param string $id      Идентификатор элемента ссылки пункта меню
     */
    public function __construct(
        public readonly string $url,
        public readonly string $label,
        public readonly string $classList = '',
        public readonly string $id = '',
        public readonly bool $enabled = true,
    ) {}

    /**
     * Дочерние элементы меню (вложенная структура).
     *
     * Лениво инициализирует пустой массив при первом обращении, если
     * дочерние элементы не были установлены явно. Позволяет строить
     * многоуровневую иерархию навигации.
     *
     * @return list<MenuItemInterface> Массив дочерних элементов меню
     */
    public array $children {
        get {
            return $this->children ??= [];
        }
    }
}
