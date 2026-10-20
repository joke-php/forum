<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Forum\Contract\MenuInterface;
use Vasoft\Joke\Forum\Contract\MenuItemInterface;
use Vasoft\Joke\Http\Response\Html\PageBuilder;
use Vasoft\Joke\Templator\Component\BaseComponent;
use Vasoft\Joke\Templator\Exceptions\TemplatorException;

/**
 * Компонент шаблонизатора для отображения навигационного меню.
 *
 * Интегрируется с системой шаблонов Joke, получая данные о структуре меню
 * через MenuConfig. Поддерживает иерархическую вложенность пунктов меню
 * и автоматическое подключение CSS/JS ассетов, если они присутствуют
 * в директории шаблонов компонента.
 *
 * Идентификатор компонента: vasoft:menu
 */
class MenuComponent extends BaseComponent
{
    /**
     * Имя текущего отображаемого меню.
     *
     * Устанавливается через опции компонента (ключ 'id') и используется
     * для поиска соответствующей конфигурации в MenuConfig.
     */
    private string $menuName;

    public function __construct(private readonly MenuConfig $config) {}

    public static function vendor(): string
    {
        return 'vasoft';
    }

    public static function name(): string
    {
        return 'menu';
    }

    public function getDefaultTemplatePath(): string
    {
        return dirname(__DIR__, 2) . '/templates/components/vasoft/menu/';
    }

    public function setOptions(mixed $options): static
    {
        if (!is_array($options) || !array_key_exists('id', $options) || '' === trim($options['id'])) {
            throw new TemplatorException('Option "menu" is required.');
        }
        $this->menuName = trim($options['id']);

        return parent::setOptions($options);
    }

    protected function getContext(): array
    {
        $entity = $this->config->get($this->menuName);
        $menu = $this->mapMenu($entity);
        file_put_contents('testik.log', print_r([__METHOD__, $menu], true), FILE_APPEND);

        return ['items' => $menu];
    }

    /**
     * Преобразует объект MenuInterface в массив для шаблона.
     *
     * Итерирует по корневых элементам меню, пропуская отключенные ($enabled = false),
     * и рекурсивно преобразует каждый пункт через mapItem().
     *
     * @param MenuInterface $menu Объект меню для преобразования
     *
     * @return list<array<string, mixed>> Массив данных пунктов меню первого уровня
     */
    protected function mapMenu(MenuInterface $menu): array
    {
        $result = [];
        /** @var MenuItemInterface $item */
        foreach ($menu->items as $item) {
            if (!$item->enabled) {
                continue;
            }
            $result[] = $this->mapItem($item);
        }

        return $result;
    }

    /**
     * Рекурсивно преобразует элемент меню в массив данных.
     *
     * Извлекает URL и метку пункта. Если у пункта есть активные дочерние элементы,
     * они также преобразуются и добавляются в ключ 'children'.
     *
     * @param MenuItemInterface $item Элемент меню для преобразования
     *
     * @return array<string, mixed> Массив с ключами 'url', 'label' и опционально 'children'
     */
    protected function mapItem(MenuItemInterface $item): array
    {
        $result = [
            'url' => $item->url,
            'label' => $item->label,
        ];
        if ([] === $item->children) {
            return $result;
        }
        $result['children'] = [];
        foreach ($item->children as $child) {
            if (!$child->enabled) {
                continue;
            }
            $result['children'][] = $this->mapItem($child);
        }

        return $result;
    }

    /**
     * Хук перед рендерингом компонента.
     *
     * Автоматически подключает файлы style.css и script.js из директории
     * шаблонов компонента к телу страницы (body), если они существуют.
     *
     * @param PageBuilder $pageBuilder Билдер страницы для добавления ассетов
     */
    protected function beforeRender(PageBuilder $pageBuilder): void
    {
        $css = $this->templateFile('style.css');
        if (file_exists($css)) {
            $pageBuilder->css->addToBody($css);
        }
        $js = $this->templateFile('script.js');
        if (file_exists($js)) {
            $pageBuilder->js->addToBody($js);
        }
    }
}
