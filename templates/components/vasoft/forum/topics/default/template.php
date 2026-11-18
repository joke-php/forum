{% if hasToolbar %}
<div class="forum-toolbar">
    {% if hasNewSubButton %}
    <button class="jf-new-sub btn btn_primary">Добавить подраздел</button>
    {% endif %}
    {% if hasNewTopicButton %}
    <button class="jf-new-topic btn btn_primary" data-section="{{sectionId}}">Добавить тему</button>
    {% endif %}
</div>
{% endif %}
{% foreach item in items %}
<a href="{{item.url}}" class="forum-topic{% if item.new %} forum-topic_new{% endif %}">
    <span class="topic-title">{{item.name}}</span>
    <span class="topic-meta">Автор: {{item.author.nickname}} • {{item.updated}}</span>
    <span class="topic-meta">{{item.messages}} ответов</span>
</a>
{% endforeach %}
{% component vasoft:pager default topicPagerConfig %}

