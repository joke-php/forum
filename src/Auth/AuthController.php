<?php

declare(strict_types=1);

namespace Vasoft\Joke\Forum\Auth;

use Vasoft\Joke\Config\Environment;
use Vasoft\Joke\Config\Exceptions\ConfigException;
use Vasoft\Joke\Container\Exceptions\ContainerException;
use Vasoft\Joke\Container\Exceptions\ParameterResolveException;
use Vasoft\Joke\Container\Exceptions\ServiceNotFoundException;
use Vasoft\Joke\Container\ServiceContainer;
use Vasoft\Joke\Contract\Mail\TransportInterface;
use Vasoft\Joke\Db\Sql\ConnectionManager;
use Vasoft\Joke\Db\Sql\Exceptions\ConnectionException;
use Vasoft\Joke\Db\Sql\Exceptions\QueryException;
use Vasoft\Joke\Db\Sql\Exceptions\TransactionException;
use Vasoft\Joke\Exceptions\ConversionException;
use Vasoft\Joke\Exceptions\FileSystemException;
use Vasoft\Joke\Forum\Auth\Base\ConsentRepository;
use Vasoft\Joke\Forum\Auth\Base\CookieAuthenticator;
use Vasoft\Joke\Forum\Auth\Base\SessionAuthenticator;
use Vasoft\Joke\Forum\Demo\Data;
use Vasoft\Joke\Http\Csrf\CsrfTokenManager;
use Vasoft\Joke\Http\HttpRequest;
use Vasoft\Joke\Http\Response\JsonResponse;
use Vasoft\Joke\Http\Response\ResponseBuilder;
use Vasoft\Joke\Mail\Address;
use Vasoft\Joke\Mail\Email;
use Vasoft\Joke\Mail\MailException;
use Vasoft\Joke\Templator\Exceptions\TemplatorException;
use Vasoft\Joke\Templator\TemplatedResponse;
use Random\RandomException;

/**
 * Контроллер аутентификации пользователей.
 *
 * Обрабатывает запросы на регистрацию новых пользователей, включая валидацию данных,
 * создание учетной записи, сохранение согласия и отправку подтверждающего письма.
 */
class AuthController
{
    /**
     * Генерирует случайную часть пароля из 4 символов.
     *
     * Использует криптографически стойкий генератор случайных чисел
     * для создания подстроки из буквенно-цифровых символов.
     *
     * @return string Случайная строка из 4 символов
     *
     * @throws RandomException При ошибке генерации случайного числа
     *
     * @todo Перенести в PasswordHasher
     */
    private function getPasswordPart(): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < 4; ++$i) {
            $password .= $characters[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * Генерирует случайный пароль формата XXXX-XXXX-XXXX.
     *
     * Создает пароль из трех частей по 4 символа, разделенных дефисом.
     *
     * @return string Сгенерированный пароль
     *
     * @throws RandomException При ошибке генерации случайного числа
     *
     * @todo Перенести в PasswordHasher
     */
    private function getPassword(): string
    {
        return implode('-', [
            $this->getPasswordPart(),
            $this->getPasswordPart(),
            $this->getPasswordPart(),
        ]);
    }

    /**
     * Обрабатывает регистрацию нового пользователя.
     *
     * Выполняет валидацию входных данных (email и nickname), проверяет уникальность,
     * генерирует временный пароль, создает запись пользователя, сохраняет согласие
     * и отправляет письмо с данными для входа. Все операции выполняются в транзакции.
     *
     * @param HttpRequest         $request           HTTP-запрос с данными формы
     * @param ResponseBuilder     $builder           Билдер ответов
     * @param ConnectionManager   $connections       Менеджер подключений к БД
     * @param ForumUserRepository $repository        Репозиторий пользователей форума
     * @param TransportInterface  $emailTransport    Транспорт для отправки email
     * @param Environment         $env               Окружение с конфигурацией
     * @param ConsentRepository   $consentRepository Репозиторий согласий
     *
     * @return TemplatedResponse Ответ с шаблоном страницы регистрации
     *
     * @throws ConfigException           При ошибках конфигурации
     * @throws ConnectionException       При ошибках соединения с БД
     * @throws ContainerException        При ошибках контейнера
     * @throws ConversionException       При ошибках преобразования типов
     * @throws MailException             При ошибках отправки email
     * @throws ParameterResolveException При ошибках связывания параметров
     * @throws QueryException            При ошибках выполнения запросов
     * @throws RandomException           Ошибка генерации пароля
     * @throws ServiceNotFoundException  При отсутствии сервиса в контейнере
     * @throws TransactionException      При ошибках транзакции
     *
     * @todo Шаблоны писем
     * @todo реализовать систему валидаторов и нормализаторов
     */
    public function register(
        HttpRequest $request,
        ResponseBuilder $builder,
        ConnectionManager $connections,
        ForumUserRepository $repository,
        TransportInterface $emailTransport,
        Environment $env,
        ConsentRepository $consentRepository,
    ): TemplatedResponse {
        /** @todo реализовать систему валидаторов и нормализаторов */
        $email = trim(strip_tags($request->post->getString('email', '')));
        $nickname = trim(strip_tags($request->post->getString('nickname', '')));
        $errors = [];
        if (empty($email) || empty($nickname)) {
            $errors[] = 'Все поля обязательны';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введен не корректны адрес электронной почты';
        }

        $user = $repository->getByNicknameOrEmail($email, $nickname);
        if (null !== $user) {
            $errors[] = 'Пользователь с таким именем или ником существует';
        }
        if ([] === $errors) {
            $connection = $connections->connection();
            $connection->beginTransaction();

            /** @var JsonResponse $response */
            try {
                $password = $this->getPassword();
                $userId = $repository->add($email, $password, $nickname);
                if ($userId > 0) {
                    $consentRepository->add(
                        $request->server->getClientIp(),
                        $request->getPath(),
                        'register',
                        $userId,
                    );
                    $emailMessage = new Email(
                        from: new Address($env->getOrFail('FORUM_EMAIL_FROM', 'Не задан Email отправителя')),
                        to: [new Address($email)],
                        subject: 'Подтверждение регистрации',
                        html: <<<HTML
                            <h1>Добро пожаловать на форум</h1>
                            <p>Ваш пароль: <b>{$password}</b></p>
                            <p>После авторизации вы можете изменить пароль в личном кабинете.</p>
                            <p>Авторизуясь на сайте вы подтверждаете согласие с правилами форума.</p>
                            HTML,
                    );
                    $message = 'Пользователь зарегистрирован - вам на почту отправлено письмо с паролем.';
                    $emailTransport->send($emailMessage);
                } else {
                    $errors[] = 'Не удалось создать пользователя';
                }
                $connection->commit();
            } catch (\Exception $e) {
                if ($connection->transactionLevel > 0) {
                    $connection->rollBack();
                }

                throw $e;
            }
        }
        $response = $builder->makeDefault();
        $response->builder->setTitle('Регистрация нового пользователя');

        /**
         * @todo Общие сущности вынести в общий класс. Вероятно переопределить TemplatedResponse
         */
        return $response->show(
            'pages/forum-register.php',
            [
                'sectionMenuConfig' => ['id' => 'sections'],
                'userMenuConfig' => ['id' => 'user'],
                'categories' => Data::getCategories(),
                'errors' => $errors,
                'message' => $message ?? '',
                'nickname' => $nickname,
                'email' => $email,
            ],
            0, // ttl: без кэша, удобно на время разработки
        );
    }

    /**
     * Отображает форму регистрации нового пользователя.
     *
     * Возвращает страницу с формой регистрации без предварительной обработки данных.
     *
     * @param ResponseBuilder $builder Билдер ответов
     *
     * @return TemplatedResponse Ответ с шаблоном формы регистрации
     *
     * @throws ContainerException        При ошибках DI контейнера
     * @throws ConversionException       При ошибках приведения значений параметров конфигурации
     * @throws ParameterResolveException При ошибках связывания параметров
     * @throws ServiceNotFoundException  При отсутствии сервиса в контейнере
     * @throws FileSystemException       При ошибках файловой системы
     * @throws TemplatorException        При ошибках шаблонизатора
     */
    public function registerForm(
        ResponseBuilder $builder,
    ): TemplatedResponse {
        /** @var TemplatedResponse $response */
        $response = $builder->makeDefault();
        $response->builder->setTitle('Регистрация нового пользователя');

        return $response->show(
            'pages/forum-register.php',
            [
                'sectionMenuConfig' => ['id' => 'sections'],
                'userMenuConfig' => ['id' => 'user'],
                'categories' => Data::getCategories(),
                'message' => '',
                'errors' => [],
            ],
            0, // ttl: без кэша, удобно на время разработки
        );
    }

    public function logout(
        HttpRequest $request,
        ResponseBuilder $builder,
        CookieAuthenticator $cookieAuthenticator,
        CsrfTokenManager $csrfTokenManager,
    ): JsonResponse {
        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);
        $cookieAuthenticator->revokeCurrent($response);
        $request->session->set(SessionAuthenticator::VAR_USER_DATA, '');
        $csrfTokenManager->reset($request, $response);

        return $response;
    }

    /**
     * Обрабатывает запрос на авторизацию пользователя.
     *
     * Проверяет учетные данные (email/password) через репозиторий пользователей.
     * При успешной аутентификации устанавливает сессию.
     * Если указан флаг 'remember', дополнительно сохраняет куку с токеном авторизации.
     *
     * @param HttpRequest         $request   Текущий HTTP-запрос с данными формы
     * @param ResponseBuilder     $builder   Фабрика для создания ответов
     * @param ForumUserRepository $userRepo  Репозиторий для поиска пользователей по credentials
     * @param ServiceContainer    $container DI-контейнер для получения зависимостей (например, CookieAuthenticator)
     *
     * @return JsonResponse JSON-ответ со статусом операции и списком ошибок (если есть)
     *
     * @throws ContainerException        Если произошла общая ошибка контейнера
     * @throws ParameterResolveException Если не удалось разрешить параметры зависимостей
     * @throws ServiceNotFoundException  Если сервис (например, CookieAuthenticator) не зарегистрирован
     * @throws ConversionException       Если возникла ошибка конвертации данных (редко, зависит от реализации)
     */
    public function auth(
        HttpRequest $request,
        ResponseBuilder $builder,
        ForumUserRepository $userRepo,
        ServiceContainer $container,
    ): JsonResponse {
        $email = trim(strip_tags($request->post->getString('login', '')));
        $password = trim(strip_tags($request->post->getString('password', '')));
        $remember = trim(strip_tags($request->post->getString('remember', '')));
        /** @var JsonResponse $response */
        $response = $builder->buildResponse(JsonResponse::class);

        $errors = [];
        if (empty($email) || empty($password)) {
            $errors[] = 'Все поля обязательны';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введен не корректны адрес электронной почты';
        }
        if ([] === $errors) {
            $user = $userRepo->getByEmailAndPassword($email, $password);
            if (null === $user) {
                $errors[] = 'Пользователь с таким именем и паролем не существует';
            } else {
                $request->session->set(SessionAuthenticator::VAR_USER_DATA, $user->id);
                if ('ok' === $remember) {
                    /** @var CookieAuthenticator $authenticator */
                    $authenticator = $container->make(CookieAuthenticator::class);
                    $authenticator->save($user, $response);
                }
                if ($user->data->getString('last_visit','') === '') {
                    // @todo Подтверждение соглашений
                    $userRepo->setVisit($user);
                }
            }
        }

        $response->setBody([
            'email' => $email,
            'errors' => $errors,
        ]);

        return $response;
    }
}
