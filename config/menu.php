<?php

declare(strict_types=1);

use Vasoft\Joke\Forum\Menu\MenuConfig;
use Vasoft\Joke\Forum\Menu\SectionMenu;
use Vasoft\Joke\Forum\Menu\UserMenu;

return new MenuConfig()
    ->addMenu('sections', SectionMenu::class)
    ->addMenu('user', UserMenu::class);
