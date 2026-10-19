{%include css __layout.css%}
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
        {%raw __layout.content%}
    </main>
</div>
<footer>
    2026, JokeForum
    <button id="themeBtn" class="theme-toggle">🌙 Темная тема</button>
</footer>