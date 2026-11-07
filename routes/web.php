<?php

declare(strict_types=1);

use Vasoft\Joke\Container\ServiceContainer;
use Vasoft\Joke\Forum\Auth\AuthController;
use Vasoft\Joke\Forum\Demo\Controller;
use Vasoft\Joke\Http\HttpRequest;
use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Http\Response\ResponseStatus;
use Vasoft\Joke\Routing\Router;

/**
 * @var Router $router
 */
$router->get('/user/register', [AuthController::class, 'registerForm']);
$router->post('/user/register', [AuthController::class, 'register']);
$router->post('/user/login', [AuthController::class, 'auth']);
$router->get('/user/logout', [AuthController::class, 'logout']);
$router->get(
    '/[{category:slug}[/{sub:slug}]]',
    static function (ServiceContainer $container, HttpRequest $request) {
        /** @todo доработать make контейнера для массива */
        /** @var Controller $controller */
        $controller = $container->make(Controller::class);
        $params = $container->getParameterResolver()->resolveForCallable(
            [Controller::class, 'index'],
            [
                'category' => $request->props->getString('category', ''),
                'sub' => $request->props->getString('sub', ''),
            ],
        );

        return $controller->index(...$params);
    },
);
$router->get(
    '/{category:slug}/{sub:slug}/{topicId:int}',
    static function (ServiceContainer $container, HttpRequest $request) {
        /** @todo доработать make контейнера для массива */
        /** @var Controller $controller */
        $controller = $container->make(Controller::class);
        $params = $container->getParameterResolver()->resolveForCallable(
            [Controller::class, 'topic'],
            [
                'category' => $request->props->getString('category', ''),
                'sub' => $request->props->getString('sub', ''),
                'topicId' => $request->props->getInt('topicId', 0),
            ],
        );

        return $controller->topic(...$params);
    },
);

$router->get(
    '/{*}',
    static fn(string $path, ResponseBuilder $builder) => $builder->makeDefault()->setStatus(
        ResponseStatus::NOT_FOUND,
    )->setBody('Not found ' . $path),
);
