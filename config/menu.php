<?php

declare(strict_types=1);

use Vasoft\Joke\Forum\Menu\MenuConfig;
use Vasoft\Joke\Forum\Menu\SectionMenu;
use Vasoft\Joke\Forum\Menu\UserMenu;
use Vasoft\Joke\Forum\Menu\Breadcrumb;

return new MenuConfig()
    ->addMenu('sections', SectionMenu::class)
    ->addMenu('breadcrumb', Breadcrumb::class)
    ->addMenu('user', UserMenu::class);
