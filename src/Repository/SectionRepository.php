<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Repository;

use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;

class SectionRepository
{
    private ConnectionInterface $connection;
    private ?array $sections = null;

    public function __construct(ConnectionManager $manager)
    {
        $this->connection = $manager->connection();
    }

    public function getSubSectionInfo(string $section, string $sub): array|false
    {
        $result = $this->connection->query(
            <<<'SQL'
                    SELECT s1.id, s1.name, s1.slug, s1.description, s2.name AS parent_name, s2.slug AS parent_slug  FROM forum_section s1
                                                LEFT JOIN forum_section s2 ON s2.id = s1.parent_id
                    WHERE s1.slug = :sub_slug AND s2.slug = :section_slug LIMIT 1
                SQL,
            ['sub_slug' => $sub, 'section_slug' => $section],
        );

        return $result->fetch();
    }

    public function getSectionInfo(string $section): array|false
    {
        $result = $this->connection->query(
            <<<'SQL'
                    SELECT id, name, slug, description FROM forum_section
                    WHERE slug = :section_slug LIMIT 1
                SQL,
            ['section_slug' => $section],
        );

        return $result->fetch();
    }

    /**
     * @throws QueryException
     */
    public function getSectionIndex(): array|false
    {
        if (null !== $this->sections) {
            return $this->sections;
        }
        $this->sections = [];
        $result = $this->connection->query(
            'SELECT id, name, slug, parent_id FROM forum_section ORDER BY parent_id, sort',
        );
        foreach ($result as $section) {
            $this->sections[(int) $section['id']] = $section;
        }
        // @todo: Путь вычислять в роутере
        foreach ($this->sections as &$section) {
            $parentId = (int) $section['parent_id'];

            if ($parentId > 0 && array_key_exists($parentId, $this->sections)) {
                $section['url'] = '/' . $this->sections[$parentId]['slug'] . '/' . $section['slug'];
            } else {
                $section['url'] = '/' . $section['slug'];
                $section['parent_id'] = null;
            }
        }

        return $this->sections;
    }

    /**
     * @return list<array<string,mixed>>
     *
     * @throws QueryException
     */
    public function getTree(): array
    {
        $sections = $this->getSectionIndex();

        $map = [];
        foreach ($sections as $id => $section) {
            $map[$id] = [
                'name' => $section['name'],
                'url' =>  $section['url'],
                'parent_id' => $section['parent_id'],
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($map as &$node) {
            if (null === $node['parent_id']) {
                $tree[] = &$node;
            } elseif (isset($map[$node['parent_id']])) {
                $map[$node['parent_id']]['children'][] = &$node;
            }
        }
        unset($node);

        return $tree;
    }
}
