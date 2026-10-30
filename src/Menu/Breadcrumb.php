<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Forum\Contract\MenuInterface;
use Vasoft\Joke\Forum\Contract\MenuItemInterface;
use Vasoft\Joke\Http\Response\Html\PageBuilder;

class Breadcrumb implements MenuInterface
{
    public private(set) array $items = [];

    public function __construct()
    {
        $this->items = [new MenuItem('/', 'Главная')];
    }

    public function register(MenuItemInterface $item): static
    {
        $this->items[] = $item;

        return $this;
    }
    private function show(): bool
    {
        return count($this->items) > 1;
    }
    public function getContext(): array
    {
        return [
            'show' => $this->show()
        ];
    }

    public function onRender(PageBuilder $pageBuilder): void
    {
        if ($this->show()) {
            $data = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [],
            ];
            foreach ($this->items as $index => $item) {
                $data['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item->label,
                    'item' => $item->url,
                ];
            }
            $pageBuilder->bottomString->add('<script type="application/ld+json">' . json_encode($data) . '</script>');
        }
    }
}
