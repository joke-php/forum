<?php

use Vasoft\Joke\Forum\Controllers\HomeController;
use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Http\Response\ResponseStatus;
use Vasoft\Joke\Routing\Router;

/**
 * @var Router $router
 */
$router->get('/', HomeController::class);
$router->get('/hi/{name:slug}', static fn(string $name) => 'Hi ' . $name);
$router->get(
    '/{*}',
    static fn(string $path, ResponseBuilder $builder) => $builder->makeDefault()->setStatus(
        ResponseStatus::NOT_FOUND
    )->setBody('Not found ' . $path)
);
