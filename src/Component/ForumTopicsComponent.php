<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Component;

use Vasoft\Joke\Auth\AuthService;
use Vasoft\Joke\Auth\Rights\RightsChecker;
use Vasoft\Joke\Contract\Auth\UserInterface;
use Vasoft\Joke\Forum\Auth\ForumUserRepository;
use Vasoft\Joke\Forum\Repository\SectionRepository;
use Vasoft\Joke\Forum\Repository\TopicRepository;
use Vasoft\Joke\Http\Response\Html\PageBuilder;
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
    private UserInterface $user;
    /**
     * @var false|mixed
     */
    private mixed $isSubsection = false;

    public function __construct(
        private readonly TopicRepository $topicRepo,
        private readonly ForumUserRepository $forumUserRepo,
        private readonly SectionRepository $sectionRepo,
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
        return 'forum.topics';
    }

    public function setOptions(mixed $options): static
    {
        $this->sectionId = isset($options['sectionId']) ? (int) $options['sectionId'] : null;
        $this->page = (int) ($options['page'] ?? 1);
        $this->perPage = (int) ($options['perPage'] ?? 10);
        $this->basePath = $options['basePath'] ?? '';
        $this->isSubsection = $options['isSubsection'] ?? false;


        return parent::setOptions($options);
    }

    protected function beforeRender(PageBuilder $pageBuilder): void
    {
        if ($this->userCan('topic.add')) {
            $js = $this->templateFile('user.js');
            if (file_exists($js)) {
                $pageBuilder->js->addToBody($js);
            }
        }
        if ($this->userCan('topic.manage')) {
            $js = $this->templateFile('manager.js');
            if (file_exists($js)) {
                $pageBuilder->js->addToBody($js);
            }
        }
    }

    public function getDefaultTemplatePath(): string
    {
        return dirname(__DIR__, 2) . '/templates/components/vasoft/forum/topics/';
    }

    protected function getContext(): array
    {
        $count = $this->topicRepo->getCount($this->sectionId);
        $offset = ($this->page - 1) * $this->perPage;
        $topics = $this->topicRepo->getPage($this->sectionId, $offset, $this->perPage);
        $authors = [];
        foreach ($topics as $topic) {
            if (null !== $topic['user_id']) {
                $authors[$topic['user_id']] = false;
            }
        }
        $authors = $this->forumUserRepo->fillUserByIds($authors);
        $sections = $this->sectionRepo->getSectionIndex();
        $section = $this->sectionId > 0 ? $this->sectionRepo->getSectionInfoById($this->sectionId, ['description', 'parent_id']) : [];

        $items = [];
        foreach ($topics as $topic) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $topic['updated_at']);
            $sectionId = (int) $topic['section_id'];
            if (!array_key_exists($sectionId, $sections)) {
                continue;
            }

            $items[] = [
                'id' => $topic['id'],
                'new' => false,
                'name' => $topic['name'],
                'author' => null === $topic['user_id'] || !isset($authors[$topic['user_id']]) ? ['nickname' => '[Удаленный]', 'blocked' => false] : $authors[$topic['user_id']],
                'updated' => $date->format('Y-m-d H:i'),
                'messages' => $topic['messages'] - 1,
                'url' => $sections[$sectionId]['url'] . '/' . $topic['id'],
            ];
        }

        $hasNewTopicButton = $this->isSubsection && $this->userCan('topic.add');
        $hasNewSubButton = !$this->isSubsection && $this->userCan('section.add');
        $manager = $this->sectionId > 0 && $this->userCan('section.manage');

        return [
            'topicPagerConfig' => [
                'count' => $count,
                'page' => $this->page,
                'baseUrl' => $this->basePath,
            ],
            'manager' => $manager,
            'description' => $section['description'] ?? '',
            'hasDescription' => isset($section['description']) && '' !== $section['description'],
            'canManageTopic' => $this->userCan('topic.manage'),
            'hasNewTopicButton' => $hasNewTopicButton,
            'hasNewSubButton' => $hasNewSubButton,
            'hasToolbar' => $hasNewTopicButton || $hasNewSubButton || $manager,
            'sectionId' => $this->sectionId,
            'items' => $items,
            'parentId' => (int) $section['parent_id'],
        ];
    }

    private function userCan(string $right): bool
    {
        if ($this->user->data->getBool('banned', false)) {
            return false;
        }

        // @todo Роли, имя модуля в отдельный класс (enum)
        // @todo Сервис проверки прав форума
        return $this->rightsChecker->can($this->user, 'forum', $right);
    }
}
