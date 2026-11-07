{%layout main%}
{% if hasToolbar %}
<div class="forum-toolbar">
    {% if hasNewMessageButton %}
    <button class="jf-new-message btn btn_primary">Добавить ответ</button>
    {% endif %}
</div>
{% endif %}
{% component vasoft:forum.topic default forumTopicConfig %}
{%endlayout%}