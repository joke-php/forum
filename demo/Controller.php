<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Demo;

use Vasoft\Joke\Auth\AuthService;
use Vasoft\Joke\Auth\Rights\RightsChecker;
use Vasoft\Joke\Forum\Menu\Breadcrumb;
use Vasoft\Joke\Forum\Menu\MenuItem;
use Vasoft\Joke\Forum\Menu\MenuManager;
use Vasoft\Joke\Forum\Repository\SectionRepository;
use Vasoft\Joke\Http\Csrf\CsrfTokenManager;
use Vasoft\Joke\Http\HttpRequest;
use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Routing\Router;
use Vasoft\Joke\Templator\TemplatedResponse;
use Vasoft\Joke\Contract\Auth\UserInterface;

readonly class Controller
{
    private UserInterface $user;

    public function __construct(
        private CsrfTokenManager $tokenManager,
        private HttpRequest $request,
        private RightsChecker $rightsChecker,
        private SectionRepository $sectionRepo,
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

        // @todo получить текущий роут и получить путь
//        $route = $this->router->route('test');

        $sectionInfo = false;
        $sectionId = null;
        if ('' !== $sub) {
            $sectionInfo = $this->sectionRepo->getSubSectionInfo($category, $sub);
            $menu->register(new MenuItem('/' . $sectionInfo['parent_slug'], $sectionInfo['parent_name']));
            $menu->register(new MenuItem('/' . $sectionInfo['slug'], $sectionInfo['name'], enabled: false));
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
}
