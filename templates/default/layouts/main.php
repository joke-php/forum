{% include css __layout.css head %}
<header>
    <div class="title">Joke Forum</div>
    {% component vasoft:menu user userMenuConfig %}
</header>
<div class="content">
    {% component vasoft:menu forum-side sectionMenuConfig %}
    <main>
        {% if notFirstLevel %}
        <h1>{%defer page.title%}</h1>
        {% endif %}
        {%raw __layout.content%}
    </main>
</div>
<footer>
    2026, JokeForum
    <button id="themeBtn" class="btn btn_secondary">🌙 Темная тема</button>
</footer>