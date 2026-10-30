<nav class="side-menu" aria-label="Основная навигация">
    <div class="side-menu__title"></div>
    <ul class="side-menu__list">
        {% foreach item in items %}
        <li class="side-menu__item">
            <a href="{{ item.url }}" class="side-menu__link" title="{{ item.label }}">{{ item.label }}</a>
            {% if item.children %}
            <ul class="side-menu__sub">
                {% foreach child in item.children %}
                <li class="side-menu__item">
                    <a href="{{ child.url }}" class="side-menu__link" title="{{ child.label }}">{{ child.label }}</a>
                </li>
                {% endforeach %}
            </ul>
            {% endif %}
        </li>
        {% endforeach %}
    </ul>
</nav>