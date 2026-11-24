{% if hasDescription %}
<div class="forum-section__description">{{description}}</div>
{% endif %}
{% if hasToolbar %}
<div class="forum-toolbar">
    {% if hasNewSubButton %}
    <button class="jf-adm-section-add btn btn_primary" data-parent="{{sectionId}}">Добавить подраздел</button>
    {% endif %}
    {% if hasNewTopicButton %}
    <button class="jf-new-topic btn btn_primary" data-section="{{sectionId}}">Добавить тему</button>
    {% endif %}
    {% if manager %}
    <div class="forum-toolbar__manager">
        <svg class="icon tool-btn jf-adm-section-edit" data-id="{{sectionId}}"><use href="#icon-edit"></use></svg>
        <svg class="icon tool-btn tool-btn_error jf-adm-section-delete" data-id="{{sectionId}}"><use href="#icon-delete"></use></svg>
    </div>
    {% endif %}
</div>
{% endif %}
{% foreach item in items %}
<a href="{{item.url}}" class="forum-topic{% if item.new %} forum-topic_new{% endif %}" id="topic{{item.id}}">
    <span class="topic-title">
        {{item.name}}
    </span>
    <span class="topic-meta">Автор: {{item.author.nickname}} • {{item.updated}}</span>
    <span class="topic-meta">{{item.messages}} ответов</span>
    {% if canManageTopic %}
    <span class="forum-topic__toolbar">
        <svg class="icon tool-btn jf-adm-topic-edit" data-id="{{item.id}}"><use href="#icon-edit"></use></svg>
        <svg class="icon tool-btn jf-adm-topic-move"><use href="#icon-move"></use></svg>
        <svg class="icon tool-btn tool-btn_error jf-adm-topic-delete" data-id="{{item.id}}"><use href="#icon-delete"></use></svg>
    </span>
    {% endif %}
</a>
{% endforeach %}
{% component vasoft:pager default topicPagerConfig %}

