<?php

declare(strict_types=1);

use Vasoft\Joke\Auth\AuthConfig;
use Vasoft\Joke\Forum\Auth\Base\CookieAuthenticator;
use Vasoft\Joke\Forum\Auth\Base\RightsSource;
use Vasoft\Joke\Forum\Auth\Base\SessionAuthenticator;
use Vasoft\Joke\Forum\Auth\ForumUserProvider;

return new AuthConfig()
    ->addAuthenticator(SessionAuthenticator::class)
    ->addAuthenticator(CookieAuthenticator::class)
    ->addUserProvider(ForumUserProvider::class)
    ->addRightsSource(RightsSource::class);
