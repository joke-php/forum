<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Menu;

use Vasoft\Joke\Auth\AuthService;
use Vasoft\Joke\Auth\Rights\RightsChecker;
use Vasoft\Joke\Auth\User;
use Vasoft\Joke\Forum\Contract\MenuInterface;
use Vasoft\Joke\Http\Response\Html\PageBuilder;

class UserMenu implements MenuInterface
{
    public readonly User $user;

    public array $items {
        get {
            return $this->get();
        }
    }

    public function __construct(AuthService $auth, private readonly RightsChecker $checker)
    {
        $this->user = $auth->getUser();
    }

    /**
     * Получает коллекцию элементов меню.
     *
     * Запрашивает дерево разделов из репозитория и преобразует его
     * в массив объектов MenuItem с сохранением иерархической структуры.
     *
     * @return list<MenuItem> Упорядоченный список элементов меню разделов
     */
    protected function get(): array
    {
        if ($this->user->authorized) {
            $items = [
                new MenuItem('#', 'Профиль'),
                new MenuItem('#', 'Изменить пароль'),
            ];
            if ($this->checker->can($this->user, 'forum', 'sections.add')) {
                $items[] = new MenuItem('#', 'Добавить раздел');
            }
            $items[] = new MenuItem('javascript:void(0)', 'Выход', classList: 'jf-logout');

            return $items;
        }

        return [
            new MenuItem('javascript:void(0)', 'Вход', classList: 'jf-login'),
        ];
    }

    public function getContext(): array
    {
        return [
            'authorized' => $this->user->authorized,
        ];
    }

    public function onRender(PageBuilder $pageBuilder): void
    {
        // TODO: Implement onRender() method.
    }
}
