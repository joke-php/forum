<?php

declare(strict_types=1);

use Vasoft\Joke\Forum\Auth\AuthController;
use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Http\Response\ResponseStatus;
use Vasoft\Joke\Routing\Router;
use Vasoft\Joke\Forum\Demo\Controller;
use Vasoft\Joke\Container\ServiceContainer;

/**
 * @var Router $router
 */
$router->get(
    '/',
    static function (ServiceContainer $container) {
        /** @todo доработать make контейнера для массива */
        /** @var Controller $controller */
        $controller = $container->make(Controller::class);
        $params = $container->getParameterResolver()->resolveForCallable(
            [Controller::class, 'index'],
            ['category' => '', 'sub' => ''],
        );

        return $controller->index(...$params);
    },
);
$router->get('/user/register', [AuthController::class, 'registerForm']);
$router->post('/user/register', [AuthController::class, 'register']);
$router->post('/user/login', [AuthController::class, 'auth']);
$router->get('/user/logout', [AuthController::class, 'logout']);
$router->get(
    '/{category:slug}',
    static function (ServiceContainer $container, string $category) {
        /** @todo доработать make контейнера для массива */
        /** @var Controller $controller */
        $controller = $container->make(Controller::class);
        $params = $container->getParameterResolver()->resolveForCallable(
            [Controller::class, 'index'],
            ['category' => $category, 'sub' => ''],
        );

        return $controller->index(...$params);
    },
);
$router->get('/{category:slug}/{sub:slug}', [Controller::class, 'index']);
$router->get(
    '/{*}',
    static fn(string $path, ResponseBuilder $builder) => $builder->makeDefault()->setStatus(
        ResponseStatus::NOT_FOUND,
    )->setBody('Not found ' . $path),
);
