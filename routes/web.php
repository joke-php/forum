<?php

declare(strict_types=1);

use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Http\Response\ResponseStatus;
use Vasoft\Joke\Routing\Router;
use Vasoft\Joke\Forum\Demo\Controller;

/**
 * @var Router $router
 */
$router->get('/', static fn(ResponseBuilder $builder) => new Controller()->index($builder));
$router->get('/{category:slug}', static fn(ResponseBuilder $builder,$category) => new Controller()->index($builder,$category));
$router->get('/{category:slug}/{sub:slug}', [Controller::class, 'index']);
$router->get(
    '/{*}',
    static fn(string $path, ResponseBuilder $builder) => $builder->makeDefault()->setStatus(
        ResponseStatus::NOT_FOUND,
    )->setBody('Not found ' . $path),
);
