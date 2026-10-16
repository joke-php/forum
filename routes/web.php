<?php

declare(strict_types=1);

use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Http\Response\ResponseStatus;
use Vasoft\Joke\Routing\Router;
use Vasoft\Joke\Forum\Demo\Controller;

/**
 * @var Router $router
 */
$router->get('/', [Controller::class, 'book']);
$router->get('/book', [Controller::class, 'book']);
$router->get(
    '/{*}',
    static fn(string $path, ResponseBuilder $builder) => $builder->makeDefault()->setStatus(
        ResponseStatus::NOT_FOUND,
    )->setBody('Not found ' . $path),
);
