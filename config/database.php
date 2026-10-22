<?php

/** @var Environment $env */

declare(strict_types=1);

use Vasoft\Joke\Config\Environment;
use Vasoft\Joke\Db\Sql\DatabaseConfig;
use Vasoft\Joke\Db\Sql\Driver\Pdo\Connection;

return new DatabaseConfig()
    ->addConnection(
        'default',
        static fn(Environment $env) => new Connection(
            $env->get('MYSQL_DRIVER'),
            $env->get('MYSQL_DATABASE'),
            $env->get('MYSQL_HOST'),
            $env->get('MYSQL_PORT'),
            $env->get('MYSQL_USER'),
            $env->get('MYSQL_PASSWORD'),
        ),
    );
