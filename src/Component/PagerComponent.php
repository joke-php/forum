<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Component;

use Vasoft\Joke\Templator\Component\BaseComponent;

class PagerComponent extends BaseComponent
{
    private int $page = 1;
    private int $perPage = 10;
    private string $baseUrl = '';
    private int $count = 0;

    public static function vendor(): string
    {
        return 'vasoft';
    }

    public static function name(): string
    {
        return 'pager';
    }

    public function getDefaultTemplatePath(): string
    {
        return dirname(__DIR__, 2) . '/templates/components/vasoft/pager/';
    }

    public function setOptions(mixed $options): static
    {
        $this->page = (int) ($options['page'] ?? 1);
        $this->perPage = (int) ($options['perPage'] ?? 10);
        $this->baseUrl = (string) ($options['baseUrl'] ?? '');
        $this->count = (int) ($options['count'] ?? 0);

        return parent::setOptions($options);
    }

    protected function getContext(): array
    {
        $pageCount = (int) ceil($this->count / $this->perPage);
        $pager = [];
        for ($i = 1; $i <= $pageCount; ++$i) {
            $pager[] = [
                'active' => 1 === $i,
                'label' => $i,
                'link' => $this->baseUrl . '?page=' . $i,
            ];
        }

        return [
            'pager' => $pager,
            'needPager' => $pageCount > 1,
            'debug' => print_r([
                'count' => $this->count,
                'page' => $this->page,
                'perPage' => $this->perPage,
                'baseUrl' => $this->baseUrl,
                'pageCount' => $pageCount,
            ],true),
        ];
    }
}
