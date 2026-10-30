<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum;

use Vasoft\Joke\Config\AbstractConfig;
use Vasoft\Joke\Config\Environment;
use Vasoft\Joke\Config\Exceptions\UnknownConfigException;
use Vasoft\Joke\Container\ServiceContainer;
use Vasoft\Joke\Contract\Provider\ConfigurableServiceProviderInterface;
use Vasoft\Joke\Forum\Auth\Base\AuthRepository;
use Vasoft\Joke\Forum\Auth\Base\ConsentRepository;
use Vasoft\Joke\Forum\Auth\Base\CookieAuthenticator;
use Vasoft\Joke\Forum\Auth\ForumUserRepository;
use Vasoft\Joke\Forum\Auth\PasswordHasher;
use Vasoft\Joke\Forum\Component\ForumTopicsComponent;
use Vasoft\Joke\Forum\Component\PagerComponent;
use Vasoft\Joke\Forum\Menu\MenuComponent;
use Vasoft\Joke\Forum\Menu\MenuConfig;
use Vasoft\Joke\Forum\Menu\MenuManager;
use Vasoft\Joke\Forum\Repository\SectionRepository;
use Vasoft\Joke\Forum\Repository\TopicRepository;
use Vasoft\Joke\Provider\AbstractProvider;
use Vasoft\Joke\Templator\Component\ComponentCollection;

class ForumProvider extends AbstractProvider implements ConfigurableServiceProviderInterface
{
    public function __construct(
        private readonly ServiceContainer $serviceContainer,
    ) {}

    public static function provideConfigs(): array
    {
        return [
            MenuConfig::class,
        ];
    }

    public static function buildConfig(string $configClass, ServiceContainer $container): AbstractConfig
    {
        if (MenuConfig::class === $configClass) {
            return new MenuConfig();
        }

        throw new UnknownConfigException($configClass);
    }

    public function register(): void
    {
        $this->serviceContainer->registerSingleton(SectionRepository::class, SectionRepository::class);
        $this->serviceContainer->registerSingleton(MenuManager::class, MenuManager::class);
        $this->serviceContainer->registerSingleton(ForumUserRepository::class, ForumUserRepository::class);
        $this->serviceContainer->registerSingleton(AuthRepository::class, AuthRepository::class);
        $this->serviceContainer->registerSingleton(ConsentRepository::class, ConsentRepository::class);
        $this->serviceContainer->registerSingleton(CookieAuthenticator::class, CookieAuthenticator::class);
        $this->serviceContainer->registerSingleton(TopicRepository::class, TopicRepository::class);
    }

    public function boot(): void
    {
        /** @var ComponentCollection $components */
        $components = $this->serviceContainer->get(ComponentCollection::class);
        $components->set(MenuComponent::componentName(), MenuComponent::class);
        $components->set(ForumTopicsComponent::componentName(), ForumTopicsComponent::class);
        $components->set(PagerComponent::componentName(), PagerComponent::class);
        /** @var Environment $env */
        $env = $this->serviceContainer->get(Environment::class);
        $this->serviceContainer->registerSingleton(
            PasswordHasher::class,
            new PasswordHasher($env->get('PASSWORD_PEPPER', '')),
        );
    }

    public function provides(): array
    {
        return [
            SectionRepository::class,
            MenuManager::class,
            ForumUserRepository::class,
            AuthRepository::class,
        ];
    }
}
