<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Component;

use Vasoft\Joke\Forum\Auth\ForumUserRepository;
use Vasoft\Joke\Forum\Repository\MessageRepository;
use Vasoft\Joke\Forum\Repository\TopicRepository;
use Vasoft\Joke\Templator\Component\BaseComponent;

class ForumTopicComponent extends BaseComponent
{
    private ?int $sectionId = null;
    private int $page = 1;
    private int $perPage = 10;
    /**
     * @var mixed|string
     */
    private mixed $basePath = '';
    private ?int $topicId = null;
    private int $messageCount = 0;

    public function __construct(
        private readonly MessageRepository $messageRepo,
        private readonly TopicRepository $topicRepo,
        private readonly ForumUserRepository $forumUserRepo,
    ) {}

    public static function vendor(): string
    {
        return 'vasoft';
    }

    public static function name(): string
    {
        return 'forum.topic';
    }

    public function setOptions(mixed $options): static
    {
        $this->topicId = isset($options['topicId']) ? (int) $options['topicId'] : null;
        $this->page = (int) ($options['page'] ?? 1);
        $this->perPage = (int) ($options['perPage'] ?? 10);
        // @todo: Если не передано - запросить из топика
        $this->messageCount = (int) ($options['messageCount'] ?? 0);
        $this->basePath = $options['basePath'] ?? '';

        return parent::setOptions($options);
    }

    public function getDefaultTemplatePath(): string
    {
        return dirname(__DIR__, 2) . '/templates/components/vasoft/forum/topic/';
    }

    protected function getContext(): array
    {
        $messages = $this->messageRepo->getMessages($this->topicId, $this->page, $this->perPage);
        $authors = [];
        foreach ($messages as $topic) {
            $authors[$topic['user_id']] = false;
        }
        $authors = $this->forumUserRepo->fillUserByIds($authors);
        $items = [];
        foreach ($messages as $topic) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $topic['created_at']);
            $items[] = [
                'new' => false,
                'content' => $topic['content'],
                'author' => $authors[$topic['user_id']] ?? ['nickname' => 'Удаленный', 'blocked' => false],
                'created' => $date->format('Y-m-d H:i'),
            ];
        }

        return [
            'topicPagerConfig' => [
                'count' => $this->messageCount,
                'page' => $this->page,
                'baseUrl' => $this->basePath,
            ],
            'items' => $items,
        ];
    }
}
