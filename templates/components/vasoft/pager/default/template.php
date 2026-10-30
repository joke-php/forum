{% if needPager %}
<nav class="pager">
    {% foreach page in pager %}
    {% if page.active %}
    <span class="pager__item pager__item_active">{{ page.label }}</span>
    {% else %}
    <a href="{{ page.link }}" class="pager__item">{{ page.label }}</a>
    {% endif %}
    {% endforeach %}
</nav>
{% endif %}