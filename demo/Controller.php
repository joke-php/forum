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
    private const int MIN_SUBJECT_LENGTH = 10;
    private const int MAX_SUBJECT_LENGTH = 255;

    private const int MIN_SECTION_NAME_LENGTH = 5;
    private const int MAX_SECTION_NAME_LENGTH = 100;

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
        $context = [
            'forumTopicsConfig' => ['sectionId' => $sectionId, 'basePath' => $this->request->getPath(), 'isSubsection' => '' !== $sub],
            'sectionMenuConfig' => ['id' => 'sections', 'context' => ['manager' => $this->userCan('section.manage')]],
            'userMenuConfig' => ['id' => 'user'],
            'breadcrumbConfig' => ['id' => 'breadcrumb'],
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
            'sectionMenuConfig' => ['id' => 'sections', 'context' => ['manager' => $this->userCan('section.manage')]],
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
            $result['id'] = $this->messageRepo->addMessage($this->user->id, $topicId, $message);
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    private function normalizedMessage(string $message): string
    {
        return trim(strip_tags($message));
    }

    public function updateMessage(ResponseBuilder $builder, HttpRequest $request): JsonResponse
    {
        $message = (string) ($request->json['message'] ?? '');
        $note = (string) ($request->json['note'] ??  '');
        $messageId = (int) ($request->json['messageId'] ??  0);

        $message = $this->normalizedMessage($message);
        $note = $this->normalizedMessage($note);

        $exists = $this->messageRepo->exists($messageId);
        $result = [
            'success' => false,
        ];

        if ($exists) {
            $this->messageRepo->updateMessage($messageId, [
                'content' => $message,
                'admin_note' => $note,
            ]);
            $result['success'] = true;
        } else {
            $result['message'] = 'Сообщение отсутствует.';
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    public function deleteMessage(ResponseBuilder $builder, HttpRequest $request, int $id): JsonResponse
    {
        $exists = $this->messageRepo->exists($id);
        $result = [
            'success' => false,
        ];

        if ($exists) {
            $this->messageRepo->deleteMessage($id);
            $result['success'] = true;
        } else {
            $result['message'] = 'Сообщение отсутствует.';
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    public function addTopic(ResponseBuilder $builder, HttpRequest $request): JsonResponse
    {
        $message = $request->post->getString('message', '');
        $subject = $request->post->getString('subject', '');
        $sectionId = $request->post->getInt('sectionId', 0);
        $message = $this->normalizedMessage($message);
        $messageLength = mb_strlen($message);
        $subjectLength = mb_strlen($subject);
        $result = [
            'success' => true,
            'message' => '',
        ];
        $answers = [];

        if ($messageLength < self::MIN_MESSAGE_LENGTH || $messageLength > self::MAX_MESSAGE_LENGTH) {
            $result['success'] = false;
            $answers[]
                = $messageLength < self::MIN_MESSAGE_LENGTH
                ? sprintf('Сообщение слишком короткое. Минимум %d символов.', self::MIN_MESSAGE_LENGTH)
                : sprintf('Сообщение слишком длинное. Максимум %d символов.', self::MAX_MESSAGE_LENGTH);
        }
        if ($subjectLength < self::MIN_SUBJECT_LENGTH || $subjectLength > self::MAX_SUBJECT_LENGTH) {
            $result['success'] = false;
            $answers[]
                = $subjectLength < self::MIN_SUBJECT_LENGTH
                ? sprintf('Название темы слишком короткое. Минимум %d символов.', self::MIN_SUBJECT_LENGTH)
                : sprintf('Название темы слишком длинное. Максимум %d символов.', self::MAX_SUBJECT_LENGTH);
        }
        $section = $this->sectionRepo->getSectionInfoById($sectionId, ['parent_id', 'slug']);
        if (false === $section || 0 === (int) $section['parent_id']) {
            $result['success'] = false;
            $answers[] = 'Раздел не найден или удален.';
        }

        if ($result['success']) {
            try {
                $result['topicId'] = $this->topicRepo->addTopic((int) $this->user->id, $sectionId, $subject, $message);
                $parentSection = $this->sectionRepo->getSectionInfoById((int) $section['parent_id'], ['slug']);

                // @todo Получать от роутера
                $result['topicUrl'] = '/' . $parentSection['slug'] . '/' . $section['slug'] . '/' . $result['topicId'];
            } catch (\Throwable $e) {
                $result['success'] = false;
                $answers[] = 'Ошибка при добавлении темы.' . $e->getMessage();
                // @todo Сделать нормальную обработку ошибок. сейчас текст ошибки теряется
            }
        }
        if ([] !== $answers) {
            // @todo Сделать нормальный вывод ошибок
            $result['message'] = implode('<br>', $answers);
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    public function updateTopic(ResponseBuilder $builder, HttpRequest $request, int $topicId): JsonResponse
    {
        $subject = (string) ($request->json['subject'] ?? '');
        $subject = $this->normalizedMessage($subject);
        $exists = $this->topicRepo->exists($topicId);
        $result = [
            'success' => false,
        ];

        if ($exists) {
            $this->topicRepo->update($topicId, [
                'name' => $subject,
            ]);
            $result['success'] = true;
        } else {
            $result['message'] = 'Тема отсутствует.';
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    public function deleteTopic(ResponseBuilder $builder, int $id): JsonResponse
    {
        $exists = $this->topicRepo->exists($id);
        $result = [
            'success' => false,
        ];

        if ($exists) {
            $result['success'] = $this->topicRepo->delete($id);
        } else {
            $result['message'] = 'Тема отсутствует.';
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    public function addSection(ResponseBuilder $builder, HttpRequest $request): JsonResponse
    {
        $name = $request->post->getString('name', '');
        $slug = $request->post->getString('slug', '');
        $description = $request->post->getString('description', '');
        $parentId = $request->post->getInt('parentId', 0);
        $name = $this->normalizedMessage($name);
        $description = $this->normalizedMessage($description);
        $length = mb_strlen($name);
        $result = [
            'success' => true,
            'message' => '',
        ];
        $answers = [];

        if ($length < self::MIN_SECTION_NAME_LENGTH || $length > self::MAX_SECTION_NAME_LENGTH) {
            $result['success'] = false;
            $answers[]
                = $length < self::MIN_SECTION_NAME_LENGTH
                ? sprintf('Название раздела слишком короткое. Минимум %d символов.', self::MIN_SECTION_NAME_LENGTH)
                : sprintf('Название раздела слишком длинное. Максимум %d символов.', self::MAX_SECTION_NAME_LENGTH);
        }
        $slugLength = mb_strlen($name);
        if ($slugLength < self::MIN_SECTION_NAME_LENGTH || $slugLength > self::MAX_SECTION_NAME_LENGTH) {
            $result['success'] = false;
            $answers[]
                = $slugLength < self::MIN_SECTION_NAME_LENGTH
                ? sprintf('Символьный код слишком короткий. Минимум %d символов.', self::MIN_SECTION_NAME_LENGTH)
                : sprintf('Символьный код слишком длинный. Максимум %d символов.', self::MAX_SECTION_NAME_LENGTH);
        }
        if ($parentId > 0) {
            $section = $this->sectionRepo->getSectionInfoById($parentId, ['parent_id', 'slug']);
            if (false === $section || 0 !== (int) $section['parent_id']) {
                $result['success'] = false;
                $answers[] = 'Не корректный родительский раздел.';
            }
        }

        if ($result['success']) {
            try {
                $result['sectionId'] = $this->sectionRepo->add($parentId, $name, $slug, $description);
            } catch (\Throwable $e) {
                $result['success'] = false;
                $answers[] = 'Ошибка при добавлении раздела.' . $e->getMessage();
                // @todo Сделать нормальную обработку ошибок. сейчас текст ошибки теряется
            }
        }
        if ([] !== $answers) {
            // @todo Сделать нормальный вывод ошибок
            $result['message'] = implode('<br>', $answers);
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    public function updateSection(ResponseBuilder $builder, HttpRequest $request, int $id): JsonResponse
    {
        $name = (string) ($request->json['name'] ?? '');
        $slug = (string) ($request->json['slug'] ?? '');
        $description = (string) ($request->json['description'] ?? '');
        $name = $this->normalizedMessage($name);
        $description = $this->normalizedMessage($description);

        $exists = $this->sectionRepo->exists($id);
        $result = [
            'success' => false,
        ];

        if ($exists) {
            $this->sectionRepo->update($id, [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
            ]);
            $result['success'] = true;
        } else {
            $result['message'] = 'Раздел отсутствует.';
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }

    public function deleteSection(ResponseBuilder $builder, int $id): JsonResponse
    {
        $exists = $this->sectionRepo->exists($id);
        $result = [
            'success' => false,
        ];

        if ($exists) {
            $result['success'] = $this->sectionRepo->delete($id);
        } else {
            $result['message'] = 'Раздел отсутствует.';
        }

        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $response->setBody($result);

        return $response;
    }
}
