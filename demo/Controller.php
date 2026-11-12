<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Demo;

use Vasoft\Joke\Auth\AuthService;
use Vasoft\Joke\Auth\Rights\RightsChecker;
use Vasoft\Joke\Contract\Auth\UserInterface;
use Vasoft\Joke\Forum\Menu\Breadcrumb;
use Vasoft\Joke\Forum\Menu\MenuItem;
use Vasoft\Joke\Forum\Menu\MenuManager;
use Vasoft\Joke\Forum\Repository\MessageRepository;
use Vasoft\Joke\Forum\Repository\SectionRepository;
use Vasoft\Joke\Forum\Repository\TopicRepository;
use Vasoft\Joke\Http\Csrf\CsrfTokenManager;
use Vasoft\Joke\Http\HttpRequest;
use Vasoft\Joke\Http\Response\JsonResponse;
use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Templator\TemplatedResponse;

readonly class Controller
{
    private const int MIN_MESSAGE_LENGTH = 10;
    private const int MAX_MESSAGE_LENGTH = 5000;

    private UserInterface $user;

    public function __construct(
        private CsrfTokenManager $tokenManager,
        private HttpRequest $request,
        private RightsChecker $rightsChecker,
        private SectionRepository $sectionRepo,
        private TopicRepository $topicRepo,
        private MessageRepository $messageRepo,
        private MenuManager $menuManager,
        AuthService $authService,
    ) {
        $this->user = $authService->getUser();
    }

    private function userCan(string $right): bool
    {
        // @todo Роли, имя модуля в отдельный класс (enum)
        // @todo Сервис проверки прав форума
        return $this->rightsChecker->can($this->user, 'forum', $right);
    }

    public function index(ResponseBuilder $builder, string $category = '', string $sub = ''): TemplatedResponse
    {
        /** @var TemplatedResponse $response */
        $response = $builder->makeDefault();
        $response->builder->setTitle('Форум');
        $response->builder->addMeta('csrf-token', $this->tokenManager->getServerToken($this->request));
        /** @var Breadcrumb $menu */
        $menu = $this->menuManager->get('breadcrumb');

        /**
         * @todo получить текущий роут и получить путь
         *        $route = $this->router->route('test');
         */
        $sectionInfo = false;
        $sectionId = null;
        if ('' !== $sub) {
            $sectionInfo = $this->sectionRepo->getSubSectionInfo($category, $sub);
            $sectionUrl = '/' . $sectionInfo['parent_slug'] . '/' . $sectionInfo['slug'];
            $menu->register(new MenuItem('/' . $sectionInfo['parent_slug'], $sectionInfo['parent_name']));
            $menu->register(new MenuItem($sectionUrl, $sectionInfo['name'], enabled: false));
            $sectionId = (int) $sectionInfo['id'];
        } elseif ('' !== $category) {
            $sectionInfo = $this->sectionRepo->getSectionInfo($category);
            $menu->register(new MenuItem('/' . $sectionInfo['slug'], $sectionInfo['name'], enabled: false));
            $sectionId = (int) $sectionInfo['id'];
        }
        $response->builder->setTitle($sectionInfo['name'] ?? 'Форум');

        $hasNewTopicButton = '' !== $sub && $this->userCan('topic.add');
        $hasNewSubButton = '' === $sub && $this->userCan('section.add');

        $context = [
            'forumTopicsConfig' => ['sectionId' => $sectionId, 'basePath' => $this->request->getPath()],
            'sectionMenuConfig' => ['id' => 'sections'],
            'userMenuConfig' => ['id' => 'user'],
            'breadcrumbConfig' => ['id' => 'breadcrumb'],
            'hasNewTopicButton' => $hasNewTopicButton,
            'hasNewSubButton' => $hasNewSubButton,
            'hasToolbar' => $hasNewTopicButton || $hasNewSubButton,
            'needHeader' => '' !== $category || '' !== $sub, // @todo Свойство респонза?
            'notFirstLevel' => null !== $sectionId,
        ];

        return $response->show(
            'pages/forum-index.php',
            $context,
            0, // ttl: без кэша, удобно на время разработки
        );
    }

    public function topic(ResponseBuilder $builder, string $category, string $sub, int $topicId): TemplatedResponse
    {
        /** @var TemplatedResponse $response */
        $response = $builder->makeDefault();
        $response->builder->setTitle('Форум');
        $response->builder->addMeta('csrf-token', $this->tokenManager->getServerToken($this->request));
        /** @var Breadcrumb $menu */
        $menu = $this->menuManager->get('breadcrumb');

        /**
         * @todo получить текущий роут и получить путь
         *        $route = $this->router->route('test');
         */
        $sectionInfo = $this->sectionRepo->getSubSectionInfo($category, $sub);
        $sectionId = (int) $sectionInfo['id'];
        $topicInfo = $this->topicRepo->getTopicInfo($sectionId, $topicId);

        $sectionUrl = '/' . $sectionInfo['parent_slug'] . '/' . $sectionInfo['slug'];
        $path = $sectionUrl . '/' . $topicInfo['id'];
        $menu->register(new MenuItem('/' . $sectionInfo['parent_slug'], $sectionInfo['parent_name']));
        $menu->register(new MenuItem($sectionUrl, $sectionInfo['name']));
        $menu->register(new MenuItem($path, $topicInfo['name'], enabled: false));

        $response->builder->setTitle($topicInfo['name'] ?? 'Форум');

        $hasNewMessageButton = $this->userCan('message.add');

        $context = [
            'forumTopicConfig' => [
                'sectionId' => $sectionId,
                'topicId' => $topicId,
                'messageCount' => $topicInfo['messages'],
                'basePath' => $this->request->getPath(),
            ],
            'sectionMenuConfig' => ['id' => 'sections'],
            'userMenuConfig' => ['id' => 'user'],
            'breadcrumbConfig' => ['id' => 'breadcrumb'],
            'hasNewMessageButton' => $hasNewMessageButton,
            'needHeader' => '' !== $category || '' !== $sub, // @todo Свойство респонза?
            'notFirstLevel' => null !== $sectionId,
        ];

        return $response->show(
            'pages/forum-topic.php',
            $context,
            0, // ttl: без кэша, удобно на время разработки
        );
    }

    public function addMessage(ResponseBuilder $builder, HttpRequest $request): JsonResponse
    {
        $message = $request->post->getString('message', '');
        $topicId = $request->post->getInt('topicId', 0);
        $message = trim(strip_tags($message));
        $length = mb_strlen($message);
        $result = [
            'success' => true,
            'message' => '',
        ];

        if ($length < self::MIN_MESSAGE_LENGTH || $length > self::MAX_MESSAGE_LENGTH) {
            $result['success'] = false;
            $result['message']
                = $length < self::MIN_MESSAGE_LENGTH
                    ? sprintf('Сообщение слишком короткое. Минимум %d символов.', self::MIN_MESSAGE_LENGTH)
                    : sprintf('Сообщение слишком длинное. Максимум %d символов.', self::MAX_MESSAGE_LENGTH);
        } else {
            $topic = $this->topicRepo->getTopicInfo(-1, $topicId);
            if (false === $topic) {
                $result['success'] = false;
                $result['message'] = 'Тема не найдена или удалена. Обратитесь к администратору для получения дополнительной информации.';
            }
        }
        if ($result['success']) {
            $result['id'] = $this->messageRepo->addMessage($this->user->id, $topicId,  $message);
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }
}
