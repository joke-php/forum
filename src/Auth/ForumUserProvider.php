<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth;

use Vasoft\Joke\Contract\Auth\UserInterface;
use Vasoft\Joke\Contract\Auth\UserProviderInterface;

class ForumUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly ForumUserRepository $userRepo,
    ) {}

    /**
     * @todo  В пользователя добавить признак аутентификатора, что бы не выполнять enrich
     */
    public function loadUserById(string $id): ?UserInterface
    {
        return $this->userRepo->getActive((int) $id);
    }

    public function enrich(UserInterface $user): ?UserInterface
    {
        return $this->userRepo->getActive((int)$user->id);
    }
}
