(function (window) {
    const menus = document.querySelectorAll('.side-menu');
    menus.forEach(menu => {
        const menuBtn = menu.querySelector('.side-menu__title');
        menuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            menu.classList.toggle('side-menu_active');
            return false;
        })

    })
})();
