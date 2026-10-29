<nav>
    <ul>
        {% foreach item in items %}
        <li>
            {%if item.idExists %}
            <a href="{{ item.url }}" title="{{ item.label }}" id="{{ item.id }}">{{ item.label }}</a>
            {% else %}
            <a href="{{ item.url }}" title="{{ item.label }}">{{ item.label }}</a>
            {% endif %}
            {% if item.children %}
            <ul>
                {% foreach child in item.children %}
                <li class="side-menu__item">
                    {%if child.idExists %}
                    <a href="{{ child.url }}" title="{{ child.label }}" id="{{ child.id }}">{{ child.label }}</a>
                    {% else %}
                    <a href="{{ child.url }}" title="{{ child.label }}">{{ child.label }}</a>
                    {% endif %}
                </li>
                {% endforeach %}
            </ul>
            {% endif %}
        </li>
        {% endforeach %}
    </ul>
</nav>