<style>
    :root {
        --color-bg-primary: #FFFFFF;
        --color-bg-secondary: #F3F3F3;
        --color-bg-tertiary: #ECECEC;
        --color-text-primary: #333333;
        --color-text-secondary: #616161;
        --color-text-link: #0066B8;
        --color-border: #E5E5E5;
        --color-accent: #007ACC;
        --color-accent-hover: #005A9E;

        --color-success-bg: #F0F7F0;
        --color-success-text: #1A7F37;
        --color-success-border: #4AC26B;

        --color-error-bg: #FFF0F0;
        --color-error-text: #D13212;
        --color-error-border: #F97583;

        --color-warning-bg: #FFF8E5;
        --color-warning-text: #B87D0A;
        --color-warning-border: #DDB100;

        --color-info-bg: #EDF4FC;
        --color-info-text: #005CC5;
        --color-info-border: #6CB6FF;
    }

    [data-theme="dark"] {
        --color-bg-primary: #1E1E1E;
        --color-bg-secondary: #252526;
        --color-bg-tertiary: #2D2D30;
        --color-text-primary: #D4D4D4;
        --color-text-secondary: #858585;
        --color-text-link: #3794FF;
        --color-border: #3E3E42;
        --color-accent: #007ACC;
        --color-accent-hover: #1E88E5;

        --color-success-bg: #1E3A2A;
        --color-success-text: #89D185;
        --color-success-border: #4EC9B0;

        --color-error-bg: #3A1E1E;
        --color-error-text: #F48771;
        --color-error-border: #F14C4C;

        --color-warning-bg: #3A351E;
        --color-warning-text: #DCDCAA;
        --color-warning-border: #CCA700;

        --color-info-bg: #1E2A3A;
        --color-info-text: #9CDCFE;
        --color-info-border: #007ACC;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background-color: var(--color-bg-primary);
        color: var(--color-text-primary);
        margin: 0;
        line-height: 1.6;
        transition: background-color 0.3s, color 0.3s;
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--color-border);
        margin-bottom: 30px;
    }

    h1 {
        margin: 0;
        font-size: 1.5rem;
    }

    .theme-toggle {
        background: var(--color-bg-secondary);
        border: 1px solid var(--color-border);
        color: var(--color-text-primary);
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .theme-toggle:hover {
        border-color: var(--color-accent);
        color: var(--color-accent);
    }

    .forum-category {
        background-color: var(--color-bg-secondary);
        border: 1px solid var(--color-border);
        border-radius: 4px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .category-title {
        color: var(--color-accent);
        margin-top: 0;
        font-size: 1.2rem;
    }

    .topic-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .topic-item {
        padding: 15px 0;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .topic-item:last-child {
        border-bottom: none;
    }

    .topic-title {
        font-weight: 600;
        color: var(--color-text-primary);
        text-decoration: none;
        font-size: 1.1rem;
    }

    .topic-title:hover {
        color: var(--color-accent);
    }

    .topic-meta {
        font-size: 0.85rem;
        color: var(--color-text-secondary);
    }

    .btn {
        background-color: var(--color-accent);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        margin-top: 10px;
    }

    .btn:hover {
        background-color: var(--color-accent-hover);
    }

    .alert {
        padding: 1rem;
        border-radius: 4px;
        border: 1px solid;
    }

    .alert-success {
        background-color: var(--color-success-bg);
        color: var(--color-success-text);
        border-color: var(--color-success-border);
    }

    .alert-error {
        background-color: var(--color-error-bg);
        color: var(--color-error-text);
        border-color: var(--color-error-border);
    }

    .alert-warning {
        background-color: var(--color-warning-bg);
        color: var(--color-warning-text);
        border-color: var(--color-warning-border);
    }

    .alert-info {
        background-color: var(--color-info-bg);
        color: var(--color-info-text);
        border-color: var(--color-info-border);
    }
</style>
<div class="container">
    <header>
        <h1>Joke Forum</h1>
        <button id="themeBtn" class="theme-toggle">🌙 Темная тема</button>
    </header>

    <main>
        <div class="forum-category">
            <h2 class="category-title">Общие обсуждения</h2>
            <ul class="topic-list">
                <li class="topic-item">
                    <div>
                        <a href="#" class="topic-title">Как правильно настроить DI-контейнер?</a>
                        <div class="topic-meta">Автор: Voral • 2 часа назад</div>
                    </div>
                    <span class="topic-meta">12 ответов</span>
                </li>
                <li class="topic-item">
                    <div>
                        <a href="#" class="topic-title">Минимализм в дизайне интерфейсов</a>
                        <div class="topic-meta">Автор: Guest • Вчера</div>
                    </div>
                    <span class="topic-meta">5 ответов</span>
                </li>
            </ul>
            <button class="btn">Новая тема</button>
        </div>

        <div class="forum-category">
            <h2 class="category-title">Техническая поддержка</h2>
            <ul class="topic-list">
                <li class="topic-item">
                    <div>
                        <a href="#" class="topic-title">Ошибка 404 при ручной маршрутизации</a>
                        <div class="topic-meta">Автор: DevUser • 3 дня назад</div>
                    </div>
                    <span class="topic-meta">0 ответов</span>
                </li>
            </ul>
        </div>
    </main>
    <div class="alert alert-success" role="alert">
        текст сообщения
    </div>
    <div class="alert alert-error" role="alert">
        текст сообщения
    </div>
    <div class="alert alert-warning" role="alert">
        текст сообщения
    </div>
    <div class="alert alert-info" role="alert">
        текст сообщения
    </div>
</div>

<script>
    const btn = document.getElementById('themeBtn');
    const html = document.documentElement;

    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        html.setAttribute('data-theme', 'dark');
        btn.textContent = '☀️ Светлая тема';
    }

    btn.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            html.removeAttribute('data-theme');
            btn.textContent = '🌙 Темная тема';
            localStorage.setItem('theme', 'light');
        } else {
            html.setAttribute('data-theme', 'dark');
            btn.textContent = '☀️ Светлая тема';
            localStorage.setItem('theme', 'dark');
        }
    });
</script>
