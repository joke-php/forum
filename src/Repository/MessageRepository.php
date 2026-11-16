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
                SELECT id, user_id, content, admin_note, created_at
                FROM forum_messages
                WHERE topic_id = :topic_id
                ORDER BY created_at
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

    public function exists(int $messageId): bool
    {
        $result = $this->connection->query(
            <<<'SQL'
                SELECT id
                FROM forum_messages
                WHERE id = :message_id
                SQL,
            [
                'message_id' => $messageId,
            ],
        );

        return false !== $result->fetch();
    }

    public function addMessage(int $userId, int $topicId, string $message): int
    {
        $this->connection->execute(
            'INSERT INTO forum_messages (topic_id, user_id, content) VALUES(:topic_id, :user_id, :content)',
            [
                'topic_id' => $topicId,
                'user_id' => $userId,
                'content' => $message,
            ],
        );

        return (int) $this->connection->getLastInsertId();
    }

    public function updateMessage(int $messageId, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fields = implode(', ', $fields);
        $data['message_id'] = $messageId;
        $result = $this->connection->execute(
            <<< SQL
                UPDATE forum_messages SET {$fields} WHERE id = :message_id
                SQL,
            $data,
        );

        return $result > 0;
    }
}
