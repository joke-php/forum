{% foreach item in items %}
<div class="forum-message{% if item.new %} forum-message_new{% endif %}">
    <div class="forum-message__info">
        <span class="forum-message__author">{{item.author.nickname}}</span>
        <span class="forum-message__date">{{item.created}}</span>
    </div>
    <div class="forum-message__content">{{item.content}}</div>
</div>
{% endforeach %}
{% component vasoft:pager default topicPagerConfig %}

