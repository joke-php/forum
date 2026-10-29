(function (window, document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        initSideMenu();
        initLogin();
        initLogout();
    });

    function initSideMenu() {
        const menus = document.querySelectorAll('.side-menu');

        const toggleMenu = (e) => {
            e.preventDefault();
            e.stopPropagation();

            const menu = e.currentTarget.closest('.side-menu');
            menu.classList.toggle('side-menu_active');

            return false;
        };

        menus.forEach(menu => {
            const menuBtn = menu.querySelector('.side-menu__title');
            menuBtn.addEventListener('click', toggleMenu);
        })
    }

    function initLogout() {
        const btn = document.querySelectorAll('.jf-logout');
        btn.forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                e.stopPropagation();
                fetch('/user/logout', {}).then(res => document.location.reload());
            })
        })
    }

    function initLogin() {
        const btn = document.querySelectorAll('.jf-login');
        const showLogin = (e) => {
            e.preventDefault();
            e.stopPropagation();

            const form = document.createElement('form');
            form.classList.add('form');
            const messages = createEl('div', ['message', 'message_error']);
            messages.style.display = 'none';
            const login = createTextInput('text', 'login', 'Введите email', [], [], {
                autocomplete: 'username',
            });
            const pass = createTextInput('password', 'password', 'Введите пароль', [], [], {
                autocomplete: 'current-password',
            });

            const remember = createCheckbox('remember', 'Запомнить меня', 'ok');
            const links = createEl('div', ['form__links']);
            const link = createEl('a');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            /**
             *
             * @param {string[]} message
             */
            const showError = (message) => {
                let html = '';
                message.forEach(msg => {
                    html += msg;
                })
                messages.innerHTML = html;
                messages.style.display = 'block';
            }


            link.href = '/user/register';
            link.innerText = 'Зарегистрироваться';
            links.appendChild(link);

            form.appendChild(messages);
            form.appendChild(login);
            form.appendChild(pass);
            form.appendChild(remember);
            form.appendChild(links);

            const btn = document.createElement('button');
            btn.textContent = 'Войти';
            btn.className = 'btn btn_primary';
            btn.addEventListener('click', async () => {
                messages.style.display = 'none';
                e.preventDefault();
                const formData = new FormData(form);

                try {
                    const response = await fetch('/user/login', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-Token': csrfToken,
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.errors.length === 0) {
                        document.location.reload();
                    } else {
                        showError(result.errors);
                    }

                } catch (error) {
                    console.error('Login error:', error);
                    showError(['Произошла ошибка при попытке входа']);
                }
            });

            showPopup('Авторизация', form, {buttons: [btn]});
            return false;
        };
        btn.forEach(btn => {
            btn.addEventListener('click', showLogin)
        })
    }

    /**
     * Обработчик события клика для закрытия попапа.
     * Использует делегирование событий: проверяет, был ли клик совершен по кнопке закрытия
     * или по затемненной области (shadow).
     *
     * @param {MouseEvent} e - Событие клика.
     */
    const closePopup = (e) => {
        const isCloseBtn = e.target.closest('.jf-popup-close');
        const isShadow = e.target.classList.contains('popup__shadow');

        if (!isCloseBtn && !isShadow) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        const popup = e.target.closest('.popup');
        if (popup) {
            popup.remove();
        }
        if (!document.querySelector('.popup')) {
            document.body.classList.remove('has-popup');
        }
    }
    const createCheckbox = (name, html, value, boxClasses = []) => {
        const el = document.createElement('input');
        el.type = 'checkbox';
        el.classList.add('form__checkbox-input');
        el.name = name;
        el.value = value;

        const label = document.createElement('span');
        label.innerHTML = html;
        label.classList.add('form__checkbox-label');

        const box = document.createElement('label');
        box.classList.add('form__row');
        box.classList.add('form__checkbox');
        if (boxClasses.length) el.classList.add(...boxClasses);
        box.appendChild(el);
        box.appendChild(label);
        return box;
    };
    const createTextInput = (type, name, placeholder, classes = [], boxClasses = [], options = {}) => {
        const el = document.createElement('input');
        el.type = type;
        el.placeholder = placeholder;
        el.name = name;
        el.classList.add('form__input');
        if (classes.length) el.classList.add(...classes);
        if (options.autocomplete) el.setAttribute('autocomplete', options.autocomplete);

        const box = document.createElement('label');
        box.classList.add('form__row');
        if (boxClasses.length) el.classList.add(...boxClasses);
        box.appendChild(el);
        return box;
    };
    const createEl = (tag, classes = [], content = '') => {
        const el = document.createElement(tag);
        if (classes.length) el.classList.add(...classes);
        if (content) el.textContent = content;
        return el;
    };

    /**
     * Приводит контент к единому виду (DOM Node).
     * Если передана строка — создает div и вставляет HTML.
     * Если передан элемент — возвращает его как есть.
     *
     * @param {string|HTMLElement|DocumentFragment} content
     * @returns {HTMLElement|DocumentFragment}
     */
    const normalizeContent = (content) => {
        if (typeof content === 'string') {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = content;
            const fragment = document.createDocumentFragment();
            while (wrapper.firstChild) {
                fragment.appendChild(wrapper.firstChild);
            }
            return fragment;
        }
        return content;
    };
    /**
     * Отображает модальное окно (попап) с заданным заголовком и содержимым.
     * Автоматически добавляет обработчики для закрытия по крестику, кнопке "Закрыть"
     * или клику на затемненный фон.
     *
     * @param {string} title - Заголовок попапа (вставляется как текст).
     * @param {string|HTMLElement|DocumentFragment} content - Тело попапа.
     * @param {Object} [options] - Объект с настройками.
     * @param {string[]} [options.bodyClasses] - CSS классы для контейнера тела.
     * @param {HTMLElement[]} [options.buttons] - HTML-разметка для кнопок в футере. Если не передано,
     *                             будет создана стандартная кнопка "Закрыть".
     * @param {function(HTMLElement): void} [options.afterInit] - Callback-функция, вызываемая после
     *                                                     добавления попапа в DOM.
     *                                                     В качестве аргумента получает ссылку на элемент попапа.
     */
    const showPopup = (title, content, options = {}) => {
        const popup = createEl('div', ['popup']);
        const shadow = createEl('div', ['popup__shadow']);
        const box = createEl('div', ['popup__box']);

        const head = createEl('div', ['popup__head']);
        const titleEl = createEl('div', ['popup__title'], title);
        const closeBtn = createEl('div', ['popup__close', 'jf-popup-close']);
        head.appendChild(titleEl);
        head.appendChild(closeBtn);

        const body = createEl('div', ['popup__body']);
        if (options.bodyClasses && options.bodyClasses.length) {
            body.classList.add(...options.bodyClasses);
        }
        body.appendChild(normalizeContent(content));

        const footer = createEl('div', ['popup__footer']);
        if (options.buttons && Array.isArray(options.buttons)) {
            options.buttons.forEach(btn => {
                if (btn instanceof HTMLElement) {
                    footer.appendChild(btn);
                }
            });
        } else {
            const defaultBtn = createEl('button', ['jf-popup-close', 'btn', 'btn_default'], 'Закрыть');
            footer.appendChild(defaultBtn);
        }

        box.appendChild(head);
        box.appendChild(body);
        box.appendChild(footer);

        popup.appendChild(shadow);
        popup.appendChild(box);

        popup.addEventListener('click', closePopup);
        document.body.appendChild(popup);
        document.body.classList.add('has-popup');

        requestAnimationFrame(() => {
            popup.classList.add('popup_visible');
        });

        if (typeof options.afterInit === 'function') {
            options.afterInit(popup);
        }
    }

    window.JokeForum = {
        showPopup: showPopup,
        closePopup: closePopup,
        createEl: createEl,
    };
})(window, document);