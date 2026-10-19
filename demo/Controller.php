<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Demo;

use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Templator\TemplatedResponse;

class Controller
{
    public function index(ResponseBuilder $builder, string $category = '', string $sub = ''): TemplatedResponse
    {
        /** @var TemplatedResponse $response */
        $response = $builder->makeDefault();
        $response->builder->setTitle('Пример');

//        $response->builder->

        Data::$categoryFilter = $category;
        Data::$subFilter = $sub;

        $pager = [];
        for ($i = 1; $i <= Data::pageCount(); ++$i) {
            $pager[] = [
                'active' => 1 === $i,
                'label' => $i,
                'link' => '#',
            ];
        }

        $context = [
            'categories' => Data::getCategories(),
            'items' => Data::getTopics(),
            'pager' => $pager,
            'needPager' => count($pager) > 1,
        ];

        return $response->show(
            'pages/forum-index.php',
            $context,
            0, // ttl: без кэша, удобно на время разработки
        );
    }
}
