{% foreach item in items %}
<div class="forum-message{% if item.new %} forum-message_new{% endif %}">
    <div class="forum-message__info">
        <img src="{{item.author.avatar}}"
             class="forum-message__avatar"
             alt="{{item.author.nickname}}">
        <div>
            <div class="forum-message__author">{{item.author.nickname}}</div>
            <div class="forum-message__date">{{item.created}}</div>
        </div>
    </div>
    <div class="forum-message__content">{{item.content}}</div>
</div>
{% endforeach %}
{% component vasoft:pager default topicPagerConfig %}

