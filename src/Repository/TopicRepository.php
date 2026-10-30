<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Repository;

use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;

class TopicRepository
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionManager $manager)
    {
        $this->connection = $manager->connection();
    }

    /**
     * @param int|null $sectionId
     * @return int
     * @throws \Vasoft\Joke\Db\Sql\Exceptions\QueryException
     *
     * @todo брать информацию из поля?
     */
    public function getCount(?int $sectionId): int
    {
        if (null === $sectionId) {
            $result = $this->connection->query(
                'SELECT count(id) AS cnt FROM forum_topic;',
            );
        } else {
            $result = $this->connection->query(
                <<<'SQL'
                    WITH RECURSIVE section_tree AS (
                        SELECT id FROM forum_section WHERE id = :section_id

                        UNION ALL

                        SELECT fs.id
                        FROM forum_section fs
                        INNER JOIN section_tree st ON fs.parent_id = st.id
                        )
                    SELECT
                        count(t.id) AS cnt
                    FROM forum_topic t
                    WHERE t.section_id IN (SELECT id FROM section_tree);
                    SQL,
                ['section_id' => $sectionId],
            );
        }

        return (int) $result->fetch()['cnt'];
    }

    public function getPage(?int $sectionId, int $offset, int $limit): array
    {
        if (null === $sectionId) {
            $result = $this->connection->query(
                <<<'SQL'
                    SELECT
                        t.id,
                        t.name,
                        t.user_id,
                        t.created_at,
                        t.updated_at,
                        t.messages,
                        t.priority
                    FROM forum_topic t
                    ORDER BY t.priority, t.created_at DESC
                    LIMIT :offset, :limit;
                    SQL,
                ['offset' => $offset, 'limit' => $limit],
            );
        } else {
            $result = $this->connection->query(
                <<<'SQL'
                    WITH RECURSIVE section_tree AS (
                        SELECT id FROM forum_section WHERE id = :section_id

                        UNION ALL

                        SELECT fs.id
                        FROM forum_section fs
                        INNER JOIN section_tree st ON fs.parent_id = st.id
                        )
                    SELECT
                        t.id,
                        t.name,
                        t.user_id,
                        t.created_at,
                        t.updated_at,
                        t.messages,
                        t.priority
                    FROM forum_topic t
                    WHERE t.section_id IN (SELECT id FROM section_tree)
                    ORDER BY t.priority, t.created_at DESC
                    LIMIT :offset, :limit;
                    SQL,
                ['section_id' => $sectionId, 'offset' => $offset, 'limit' => $limit],
            );
        }

        return $result->all();
    }
}
