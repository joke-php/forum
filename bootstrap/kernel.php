<?php

declare(strict_types=1);

use Vasoft\Joke\Application\KernelConfig;
use Vasoft\Joke\Db\Sql\DatabaseProvider;
use Vasoft\Joke\Templator\TemplatorProvider;
use Vasoft\Joke\Forum\ForumProvider;

return new KernelConfig()
    ->addProvider(DatabaseProvider::class)
    ->addProvider(TemplatorProvider::class)
    ->addProvider(ForumProvider::class);
