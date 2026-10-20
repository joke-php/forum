<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Forum\Contract\MenuInterface;
use Vasoft\Joke\Forum\Repository\SectionRepository;

/**
 * Меню разделов форума.
 *
 * Реализует интерфейс MenuInterface, предоставляя навигационное меню,
 * построенное на основе дерева разделов форума. Автоматически преобразует
 * данные из репозитория в иерархическую структуру элементов меню.
 */
class SectionMenu implements MenuInterface
{
    /**
     * Коллекция элементов меню разделов.
     *
     * Лениво загружает и кэширует элементы меню при первом обращении,
     * преобразуя дерево разделов из репозитория в объекты MenuItem.
     *
     * @var list<MenuItem>
     */
    public array $items {
        get {
            return $this->get();
        }
    }

    /**
     * Конструктор меню разделов.
     *
     * @param SectionRepository $sectionRepository Репозиторий для получения дерева разделов форума
     */
    public function __construct(private readonly SectionRepository $sectionRepository) {}

    /**
     * Получает коллекцию элементов меню.
     *
     * Запрашивает дерево разделов из репозитория и преобразует его
     * в массив объектов MenuItem с сохранением иерархической структуры.
     *
     * @return list<MenuItem> Упорядоченный список элементов меню разделов
     */
    protected function get(): array
    {
        $tree = $this->sectionRepository->getTree();

        return $this->mapItems($tree);
    }

    /**
     * Преобразует массив данных разделов в коллекцию элементов меню.
     *
     * Итерирует по входному массиву и рекурсивно преобразует каждый элемент
     * в объект MenuItem, сохраняя вложенную структуру через свойство children.
     *
     * @param list<array<string,mixed>> $items Массив данных разделов из репозитория
     *
     * @return list<MenuItem> Преобразованные элементы меню
     */
    protected function mapItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->mapItem($item);
        }

        return $result;
    }

    /**
     * Преобразует массив описывающий элемент меню в класс
     *
     * Создает экземпляр MenuItem на основе данных раздела, включая URL,
     * название и статус доступности. Если раздел имеет дочерние элементы,
     * они также рекурсивно преобразуются и добавляются как children.
     *
     * @param array<string,mixed> $item Данные одного раздела с ключами 'url', 'name' и опционально 'children'
     *
     * @return MenuItem Готовый элемент меню с возможными вложенными элементами
     */
    protected function mapItem(array $item): MenuItem
    {
        $entity = new MenuItem($item['url'], $item['name'], true);
        if (isset($item['children'])) {
            $entity->children = $this->mapItems($item['children']);
        }

        return $entity;
    }
}
