<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Repository;

use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Contract\ConnectionInterface;

class MessageRepository
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionManager $manager)
    {
        $this->connection = $manager->connection();
    }

    public function getMessages(?int $topicId, int $offset, int $limit): array
    {

        return $this->connection->query(
            <<<'SQL'
                SELECT id, user_id, content, created_at
                FROM forum_messages
                WHERE topic_id = :topic_id
                LIMIT :limit
                OFFSET :offset
                SQL,
            [
                'topic_id' => $topicId,
                'limit' => $limit,
                'offset' => $offset,
            ],
        )->all();
    }
}
