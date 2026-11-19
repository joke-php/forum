<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Repository;

use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;

class TopicRepository
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionManager $manager)
    {
        $this->connection = $manager->connection();
    }

    /**
     * @throws QueryException
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
                        t.section_id,
                        t.priority
                    FROM forum_topic t
                    ORDER BY t.priority, t.updated_at DESC
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
                        t.section_id,
                        t.priority
                    FROM forum_topic t
                    WHERE t.section_id IN (SELECT id FROM section_tree)
                    ORDER BY t.priority, t.updated_at DESC
                    LIMIT :offset, :limit;
                    SQL,
                ['section_id' => $sectionId, 'offset' => $offset, 'limit' => $limit],
            );
        }

        return $result->all();
    }

    public function getTopicInfo(int $sectionId, int $topicId): array|false
    {
        // @todo: Временное решение продумать механизм
        $result = $sectionId < 0
            ? $this->connection->query(
                <<<'SQL'
                    SELECT
                        t.id,
                        t.name,
                        t.user_id
                    FROM forum_topic t
                    WHERE t.id = :topic_id
                    ORDER BY t.priority, t.updated_at DESC;
                    SQL,
                ['topic_id' => $topicId],
            )
            : $this->connection->query(
                <<<'SQL'
                    SELECT
                        t.id,
                        t.name,
                        t.user_id
                    FROM forum_topic t
                    WHERE t.id = :topic_id AND t.section_id = :section_id
                    ORDER BY t.priority, t.updated_at DESC;
                    SQL,
                ['topic_id' => $topicId, 'section_id' => $sectionId],
            );

        return $result->fetch();
    }

    public function addTopic(int|string|null $id, int $sectionId, string $subject, string $message): int
    {

        $this->connection->beginTransaction();

        try {
            $result = $this->connection->execute(
                <<<'SQL'
                    INSERT INTO forum_topic ( section_id, name, user_id)
                    VALUES (:section_id, :name, :user_id);
                    SQL,
                ['section_id' => $sectionId, 'name' => $subject, 'user_id' => $id],
            );

            if (0 === $result) {
                throw new QueryException('Error adding topic');
            }
            $topicId = $this->connection->getLastInsertId();
            // @todo в контроллер
            $this->connection->execute(
                <<<'SQL'
                    INSERT INTO forum_messages (topic_id, user_id, content)
                    VALUES (:topic_id, :user_id, :message);
                    SQL,
                ['user_id' => $id, 'message' => $message, 'topic_id' => $topicId],
            );

            $this->connection->commit();

            return (int) $topicId;
        } catch (\Throwable $e) {
            if ($this->connection->transactionLevel > 0) {
                $this->connection->rollBack();
            }

            throw $e;
        }
    }

    public function exists(int $topicId): bool
    {
        $result = $this->connection->query(
            <<<'SQL'
                SELECT id
                FROM forum_topic
                WHERE id = :topic_id
                SQL,
            [
                'topic_id' => $topicId,
            ],
        );

        return false !== $result->fetch();
    }

    public function update(int $topicId, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fields = implode(', ', $fields);
        $data['topic_id'] = $topicId;
        $result = $this->connection->execute(
            <<< SQL
                UPDATE forum_topic SET {$fields} WHERE id = :topic_id
                SQL,
            $data,
        );

        return $result > 0;
    }

}
