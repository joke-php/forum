<header>
    <div class="title">Joke Forum</div>
</header>
<div class="content">
    <nav class="side-menu">
        <div class="side-menu__title"></div>
        <ul class="side-menu__list">
            {% foreach category in categories %}
            <li class="side-menu__item">
                <a href="{{ category.url }}" class="side-menu__link" title="{{ category.name }}">{{ category.name }}</a>
                {% if category.children %}
                <ul class="side-menu__sub">
                    {% foreach child in category.children %}
                    <li class="side-menu__item">
                        <a href="{{ child.url }}" class="side-menu__link" title="{{ child.name }}">{{ child.name }}</a>
                    </li>
                    {% endforeach %}
                </ul>
                {% endif %}
            </li>
            {% endforeach %}
        </ul>
    </nav>
    <main>
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
    </main>
</div>
<footer>
    2026, JokeForum
    <button id="themeBtn" class="theme-toggle">🌙 Темная тема</button>
</footer>