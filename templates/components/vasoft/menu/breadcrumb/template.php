{% if show %}
<nav aria-label="Хлебные крошки">
    <ol class="breadcrumb">
        {% foreach item in items %}
        <li class="breadcrumb__item">
            <a href="{{ item.url }}" title="{{ item.label }}" class="breadcrumb__link">{{ item.label }}</a>
        </li>
        {% endforeach %}
    </ol>
</nav>
{% endif %}