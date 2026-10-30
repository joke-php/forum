<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Component;

use Vasoft\Joke\Forum\Auth\ForumUserRepository;
use Vasoft\Joke\Forum\Repository\TopicRepository;
use Vasoft\Joke\Templator\Component\BaseComponent;

class ForumTopicsComponent extends BaseComponent
{
    private ?int $sectionId = null;
    private int $page = 1;
    private int $perPage = 10;
    /**
     * @var mixed|string
     */
    private mixed $basePath = '';

    public function __construct(
        private readonly TopicRepository $topicRepo,
        private readonly ForumUserRepository $forumUserRepo,
    ) {}

    public static function vendor(): string
    {
        return 'vasoft';
    }

    public static function name(): string
    {
        return 'forum.topics';
    }

    public function setOptions(mixed $options): static
    {
        $this->sectionId = isset($options['sectionId']) ? (int) $options['sectionId'] : null;
        $this->page = (int) ($options['page'] ?? 1);
        $this->perPage = (int) ($options['perPage'] ?? 10);
        $this->basePath = $options['basePath'] ?? '';

        return parent::setOptions($options);
    }

    public function getDefaultTemplatePath(): string
    {
        return dirname(__DIR__, 2) . '/templates/components/vasoft/forum/topics/';
    }

    protected function getContext(): array
    {
        $count = $this->topicRepo->getCount($this->sectionId);
        $topics = $this->topicRepo->getPage($this->sectionId, $this->page, $this->perPage);
        $authors = [];
        foreach ($topics as $topic) {
            $authors[$topic['user_id']] = false;
        }
        $authors = $this->forumUserRepo->fillUserByIds($authors);
        $items = [];
        foreach ($topics as $topic) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $topic['updated_at']);
            $items[] = [
                'new' => false,
                'name' => $topic['name'],
                'author' => $authors[$topic['user_id']] ?? ['nickname' => 'Удаленный', 'blocked' => false],
                'updated' => $date->format('Y-m-d H:i'),
                'messages' => $topic['messages'] - 1,
                'link' => $this->basePath.'/'.$topic['id'],
            ];
        }

        return [
            'topicPagerConfig' => [
                'count' => $count,
                'page' => 1,
                'baseUrl' => $this->basePath,
            ],
            'items' => $items,
        ];
    }
}
