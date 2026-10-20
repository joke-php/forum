<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Repository;

use Vasoft\Joke\Forum\Demo\Data;

class SectionRepository
{
    /**
     * @return list<array<string,mixed>>
     */
    public function getTree(): array
    {
        return Data::getCategories();
    }
}
