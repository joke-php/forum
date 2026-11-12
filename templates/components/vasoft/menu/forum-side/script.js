(function (window, document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        initSideMenu();
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
})(window, document);