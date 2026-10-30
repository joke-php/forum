{% foreach item in items %}
<a href="{{item.link}}" class="forum-topic{% if item.new %} forum-topic_new{% endif %}">
    <span class="topic-title">{{item.name}}</span>
    <span class="topic-meta">Автор: {{item.author.nickname}} • {{item.updated}}</span>
    <span class="topic-meta">{{item.messages}} ответов</span>
</a>
{% endforeach %}
{% component vasoft:pager default topicPagerConfig %}

