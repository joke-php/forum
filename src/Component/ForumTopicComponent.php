<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Component;

use DateTime;
use Vasoft\Joke\Auth\AuthService;
use Vasoft\Joke\Auth\Rights\RightsChecker;
use Vasoft\Joke\Contract\Auth\UserInterface;
use Vasoft\Joke\Forum\Auth\ForumUserRepository;
use Vasoft\Joke\Forum\Repository\MessageRepository;
use Vasoft\Joke\Http\Response\Html\PageBuilder;
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
    private UserInterface $user;

    public function __construct(
        private readonly MessageRepository $messageRepo,
        private readonly ForumUserRepository $forumUserRepo,
        private readonly RightsChecker $rightsChecker,
        AuthService $authService,
    ) {
        $this->user = $authService->getUser();
    }

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

    protected function beforeRender(PageBuilder $pageBuilder): void
    {
        if ($this->userCan('message.add')) {
            $js = $this->templateFile('user.js');
            if (file_exists($js)) {
                $pageBuilder->js->addToBody($js);
            }
        }
    }
    protected function getContext(): array
    {
        $offset = ($this->page - 1) * $this->perPage;
        $messages = $this->messageRepo->getMessages($this->topicId, $offset, $this->perPage);
        $authors = [];
        foreach ($messages as $topic) {
            if ($topic['user_id'] !== null) {
                $authors[$topic['user_id']] = false;
            }
        }
        $authors = $this->forumUserRepo->fillUserByIds($authors);

        $deleted  = [
            'nickname' => '[Удаленный]',
            'blocked' => false,
            // @todo получать из роута
            'avatar' => '/avatar/'.urlencode('!!')
        ];

        $items = [];
        foreach ($messages as $topic) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $topic['created_at']);
            $items[] = [
                'id' => $topic['id'],
                'new' => false,
                'content' => $topic['content'],
                'author' => $topic['user_id'] === null || !isset($authors[$topic['user_id']]) ? $deleted : $authors[$topic['user_id']],
                'created' => $date->format('Y-m-d H:i'),
            ];
        }

        return [
            'topicPagerConfig' => [
                'count' => $this->messageCount,
                'page' => $this->page,
                'baseUrl' => $this->basePath,
            ],
            'topicId' => $this->topicId,
            'items' => $items,
            'hasNewMessageButton' => $this->userCan('message.add'),
        ];
    }

    private function userCan(string $right): bool
    {
        // @todo Роли, имя модуля в отдельный класс (enum)
        // @todo Сервис проверки прав форума
        return $this->rightsChecker->can($this->user, 'forum', $right);
    }
}
