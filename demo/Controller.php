<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Demo;

use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Templator\TemplatedResponse;

class Controller
{
    public function index(ResponseBuilder $builder): TemplatedResponse
    {
        /** @var TemplatedResponse $response */
        $response = $builder->makeDefault();
        $response->builder->setTitle('Пример');

        return $response->show(
            'pages/forum-index.php',
            [],
            0, // ttl: без кэша, удобно на время разработки
        );
    }
    public function book(ResponseBuilder $builder): TemplatedResponse
    {
        /** @var TemplatedResponse $response */
        $response = $builder->makeDefault();
        $response->builder->setTitle('Пример');

        return $response->show(
            'pages/forum-book.php',
            [],
            0, // ttl: без кэша, удобно на время разработки
        );
    }
}
