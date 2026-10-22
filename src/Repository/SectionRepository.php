<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Repository;

use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;

class SectionRepository
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionManager $manager)
    {
        $this->connection = $manager->connection();
    }

    /**
     * @return list<array<string,mixed>>
     * @throws QueryException
     */
    public function getTree(): array
    {
        $sections = $this->connection->query(
            'SELECT id, name, slug, parent_id FROM forum_section ORDER BY parent_id, sort',
        );

        $map = [];
        foreach ($sections as $section) {
            $id = (int) $section['id'];
            $parentId = null === $section['parent_id'] ? null : (int) $section['parent_id'];

            $map[$id] = [
                'name' => $section['name'],
                'url' => '/' . $section['slug'],
                'parent_id' => $parentId,
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($map as &$node) {
            if (null === $node['parent_id']) {
                $tree[] = &$node;
            } elseif (isset($map[$node['parent_id']])) {
                $node['url'] = $map[$node['parent_id']]['url'] . $node['url'];
                $map[$node['parent_id']]['children'][] = &$node;
            }
        }
        unset($node);

        return $tree;
    }
}
