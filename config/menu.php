<?php

declare(strict_types=1);
use Vasoft\Joke\Forum\Menu\MenuConfig;
use Vasoft\Joke\Forum\Menu\SectionMenu;
use Vasoft\Joke\Forum\Repository\SectionRepository;

return new MenuConfig()
    ->addMenu(
        'sections',
        static fn() => new SectionMenu(new SectionRepository()),
    );
