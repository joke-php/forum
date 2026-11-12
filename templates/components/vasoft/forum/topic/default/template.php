{% if hasNewMessageButton %}
<div class="forum-toolbar">
    <button class="jf-new-message btn btn_primary" data-topic="{{topicId}}">Добавить ответ</button>
</div>
{% endif %}
{% foreach item in items %}
<div class="forum-message{% if item.new %} forum-message_new{% endif %}" id="message{{item.id}}">
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
{% if hasNewMessageButton %}
<div class="forum-toolbar">
    <button class="jf-new-message btn btn_primary" data-topic="{{topicId}}">Добавить ответ</button>
</div>
{% endif %}

