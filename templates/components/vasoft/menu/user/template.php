<nav class="user-menu">
    {% if authorized %}
    <div class="user-menu_head">Кабинет</div>
    <ul class="user-menu_items">
        {% foreach item in items %}
        <li class="user-menu_item">
            <a href="{{ item.url }}" title="{{ item.label }}" class="user-menu_link {{ item.classList }}">{{ item.label }}</a>
        </li>
        {% endforeach %}
    </ul>
    {% else %}
    <ul class="user-menu_items-single">
        {% foreach item in items %}
        <li class="user-menu_item">
            <a href="{{ item.url }}" title="{{ item.label }}" class="user-menu_link {{ item.classList }}">{{ item.label }}</a>
        </li>
        {% endforeach %}
    </ul>
    {% endif %}
</nav>