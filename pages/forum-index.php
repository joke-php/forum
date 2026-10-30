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
{% component vasoft:forum.topics default forumTopicsConfig %}
{%endlayout%}