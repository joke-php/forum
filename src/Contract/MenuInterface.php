<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Contract;

use Vasoft\Joke\Http\Response\Html\PageBuilder;

/**
 * Интерфейс меню.
 *
 * Определяет контракт для объектов меню, содержащих коллекцию элементов навигации.
 * Меню представляет собой плоский или иерархический список элементов,
 * где каждый элемент реализует MenuItemInterface и может иметь собственные вложенные элементы.
 */
interface MenuInterface
{
    /**
     * Коллекция элементов меню.
     *
     * Возвращает список всех корневых элементов меню. Каждый элемент
     * реализует MenuItemInterface и может содержать дочерние элементы
     * через свойство $children, образуя древовидную структуру навигации.
     *
     * @var list<MenuItemInterface> Упорядоченный список элементов меню
     */
    public array $items {
        get;
    }

    /**
     * Дополнительный контекст для передачи в шаблон.
     */
    public function getContext(): array;

    public function onRender(PageBuilder $pageBuilder): void;
}
