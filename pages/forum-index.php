{%layout main%}
{% if hasToolbar %}
<div class="forum-toolbar">
    {% if hasNewSubButton %}
    <button class="jf-new-sub btn btn_primary">Добавить подраздел</button>
    {% endif %}
    {% if hasNewTopicButton %}
    <button class="jf-new-sub btn btn_primary">Добавить тему</button>
    {% endif %}
</div>
{% endif %}
{% foreach item in items %}
<a href="#" class="forum-topic{% if item.new %} forum-topic_new{% endif %}">
    <span class="topic-title">{{item.title}}</span>
    <span class="topic-meta">Автор: {{item.author.nick}} • {{item.lastAnswer}}</span>
    <span class="topic-meta">{{item.answerCount}} ответов</span>
</a>
{% endforeach %}
{% if needPager %}
<nav class="pager">
    {% foreach page in pager %}
    {% if page.active %}
    <span class="pager__item pager__item_active">{{ page.label }}</span>
    {% else %}
    <a href="#" class="pager__item">{{ page.label }}</a>
    {% endif %}
    {% endforeach %}
</nav>
{% endif %}
{%endlayout%}