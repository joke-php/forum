{% include css __layout.css head %}
<header>
    <div class="title">Joke Forum</div>
</header>
<div class="content">
    {% component vasoft:menu forum-side sectionMenuConfig %}
    <main>
        {%raw __layout.content%}
    </main>
</div>
<footer>
    2026, JokeForum
    <button id="themeBtn" class="theme-toggle">🌙 Темная тема</button>
</footer>