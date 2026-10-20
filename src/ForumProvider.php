<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum;

use Vasoft\Joke\Config\AbstractConfig;
use Vasoft\Joke\Config\Exceptions\UnknownConfigException;
use Vasoft\Joke\Container\ServiceContainer;
use Vasoft\Joke\Contract\Provider\ConfigurableServiceProviderInterface;
use Vasoft\Joke\Forum\Menu\MenuComponent;
use Vasoft\Joke\Forum\Menu\MenuConfig;
use Vasoft\Joke\Forum\Menu\MenuManager;
use Vasoft\Joke\Forum\Repository\SectionRepository;
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
    }

    public function boot(): void
    {
        /** @var ComponentCollection $components */
        $components = $this->serviceContainer->get(ComponentCollection::class);
        $components->set(MenuComponent::componentName(), MenuComponent::class);
    }

    public function provides(): array
    {
        return [
            SectionRepository::class,
            MenuManager::class,
        ];
    }
}
