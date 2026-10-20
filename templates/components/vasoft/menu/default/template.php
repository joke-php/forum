<nav>
    <ul>
        {% foreach item in items %}
        <li>
            <a href="{{ item.url }}" title="{{ item.label }}">{{ item.label }}</a>
            {% if item.children %}
            <ul>
                {% foreach child in item.children %}
                <li class="side-menu__item">
                    <a href="{{ child.url }}" title="{{ child.label }}">{{ child.label }}</a>
                </li>
                {% endforeach %}
            </ul>
            {% endif %}
        </li>
        {% endforeach %}
    </ul>
</nav>