(function (window, document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        initSideMenu();
        initLogin();
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

    function initLogin() {
        const btn = document.querySelectorAll('.jf-login');
        const showLogin = (e) => {
            e.preventDefault();
            e.stopPropagation();

            const content = document.createDocumentFragment();
            const login = createEl('input');
            login.name = 'login';
            login.type = 'text';
            login.placeholder = 'Введите email...';
            const pass = createEl('input');
            pass.name = 'login';
            pass.type = 'password';
            pass.placeholder = 'Введите пароль...';
            const remember = createEl('input');
            remember.name = 'remember';
            remember.type = 'checkbox';
            remember.placeholder = 'Введите пароль...';

            const links = createEl('div', ['popup__links']);
            const link = createEl('a');
            link.href = '/user/register';
            link.innerText = 'Зарегистрироваться';
            links.appendChild(link);

            content.appendChild(login);
            content.appendChild(pass);
            content.appendChild(remember);
            content.appendChild(links);

            const btn = document.createElement('button');
            btn.textContent = 'Войти';
            btn.className = 'btn btn_primary';
            btn.addEventListener('click', () => console.log('Confirmed!'));

            showPopup('Авторизация', content, {buttons: [btn]});
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