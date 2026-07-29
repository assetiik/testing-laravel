<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Dev Portfolio API') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #eaf1f8;
            --muted: #93a3b5;
            --faint: #6b7a8c;
            --accent: #4fe0cf;
            --accent-ink: #05221e;
            --violet: #8b7cf6;
            --amber: #f7b955;
            --danger: #ff7a7a;
            --surface: rgba(20, 27, 36, 0.72);
            --surface-2: rgba(255, 255, 255, 0.035);
            --line: rgba(234, 241, 248, 0.1);
            --line-strong: rgba(234, 241, 248, 0.18);
            --radius: 20px;
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "IBM Plex Sans", system-ui, sans-serif;
            color: var(--ink);
            line-height: 1.5;
            background-color: #0a0f14;
            background-image:
                radial-gradient(900px 520px at 8% -8%, rgba(79, 224, 207, 0.16), transparent 62%),
                radial-gradient(760px 460px at 96% 4%, rgba(139, 124, 246, 0.16), transparent 58%),
                radial-gradient(700px 700px at 50% 110%, rgba(247, 185, 85, 0.07), transparent 60%);
            background-attachment: fixed;
        }

        .wrap {
            width: min(1120px, calc(100% - 2.5rem));
            margin: 0 auto;
            padding: 2rem 0 4.5rem;
        }

        /* ---------- header ---------- */

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1.15rem;
            margin-bottom: 3.5rem;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: var(--surface);
            backdrop-filter: blur(14px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-family: Syne, sans-serif;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .brand .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 4px rgba(79, 224, 207, 0.18);
        }

        .nav {
            display: flex;
            gap: 0.35rem;
        }

        .nav a {
            color: var(--muted);
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            transition: color 0.15s ease, background 0.15s ease;
        }

        .nav a:hover {
            color: var(--ink);
            background: var(--surface-2);
        }

        /* ---------- hero ---------- */

        .hero {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 2.5rem;
            align-items: start;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--accent);
            border: 1px solid rgba(79, 224, 207, 0.28);
            background: rgba(79, 224, 207, 0.08);
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            margin-bottom: 1.25rem;
        }

        h1 {
            font-family: Syne, sans-serif;
            font-size: clamp(2.3rem, 4.6vw, 3.5rem);
            line-height: 1.06;
            margin: 0 0 1.1rem;
            letter-spacing: -0.02em;
        }

        h1 .grad {
            background: linear-gradient(100deg, var(--accent), var(--violet));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .lead {
            color: var(--muted);
            font-size: 1.05rem;
            max-width: 34rem;
            margin: 0;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1.6rem;
        }

        .chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 0.4rem 0.85rem;
            color: var(--muted);
            font-size: 0.82rem;
            background: var(--surface-2);
        }

        .chip code {
            font-family: inherit;
            color: var(--ink);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 2rem;
            max-width: 30rem;
        }

        .stat {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 0.85rem 0.9rem;
            background: var(--surface-2);
        }

        .stat b {
            display: block;
            font-family: Syne, sans-serif;
            font-size: 1.35rem;
        }

        .stat span {
            color: var(--faint);
            font-size: 0.78rem;
        }

        /* ---------- form ---------- */

        .panel {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
            backdrop-filter: blur(16px);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.45);
            padding: 1.6rem;
        }

        .panel-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.3rem;
        }

        .panel-head h2 {
            font-family: Syne, sans-serif;
            font-size: 1.3rem;
            margin: 0;
        }

        .panel-head span {
            font-size: 0.78rem;
            color: var(--faint);
        }

        .field { margin-bottom: 1rem; }

        .field-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.5rem;
            margin-bottom: 0.4rem;
        }

        label {
            font-size: 0.82rem;
            color: var(--muted);
            font-weight: 500;
        }

        .hint {
            font-size: 0.72rem;
            color: var(--faint);
        }

        input, textarea {
            width: 100%;
            border: 1px solid var(--line-strong);
            background: rgba(8, 12, 17, 0.75);
            color: var(--ink);
            border-radius: 12px;
            padding: 0.8rem 0.95rem;
            font: inherit;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        textarea { resize: vertical; min-height: 6.5rem; }

        input::placeholder, textarea::placeholder { color: #526071; }

        input:focus, textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79, 224, 207, 0.16);
        }

        .field.invalid input,
        .field.invalid textarea {
            border-color: rgba(255, 122, 122, 0.6);
            box-shadow: 0 0 0 3px rgba(255, 122, 122, 0.12);
        }

        .field-error {
            display: none;
            margin-top: 0.4rem;
            font-size: 0.78rem;
            color: var(--danger);
        }

        .field.invalid .field-error { display: block; }

        button {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 0.9rem 1rem;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            color: var(--accent-ink);
            background: linear-gradient(120deg, var(--accent), #59c7f0);
            transition: transform 0.15s ease, opacity 0.15s ease, filter 0.15s ease;
        }

        button:hover:not(:disabled) { filter: brightness(1.07); transform: translateY(-1px); }
        button:disabled { opacity: 0.55; cursor: progress; }

        /* ---------- result ---------- */

        .result {
            display: none;
            margin-top: 1.15rem;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: var(--surface-2);
            overflow: hidden;
        }

        .result.show { display: block; animation: rise 0.25s ease-out; }

        @keyframes rise {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: none; }
        }

        .result-head {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.8rem 1rem;
            font-weight: 600;
            font-size: 0.92rem;
            border-bottom: 1px solid var(--line);
        }

        .result.ok .result-head { color: #86efd9; background: rgba(79, 224, 207, 0.1); }
        .result.err .result-head { color: #ffb0b0; background: rgba(255, 122, 122, 0.1); }

        .result-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .result.ok .result-icon { background: rgba(79, 224, 207, 0.2); }
        .result.err .result-icon { background: rgba(255, 122, 122, 0.2); }

        .result-body { padding: 1rem; }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-bottom: 0.9rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.28rem 0.62rem;
            border-radius: 999px;
            border: 1px solid var(--line-strong);
            color: var(--muted);
            background: rgba(255, 255, 255, 0.03);
        }

        .badge b { color: var(--ink); font-weight: 600; }
        .badge.positive { border-color: rgba(79, 224, 207, 0.4); color: #86efd9; }
        .badge.negative { border-color: rgba(255, 122, 122, 0.4); color: #ffb0b0; }
        .badge.high { border-color: rgba(247, 185, 85, 0.45); color: var(--amber); }
        .badge.ai { border-color: rgba(139, 124, 246, 0.45); color: #c4bbff; }

        .reply {
            border-left: 2px solid var(--accent);
            padding: 0.15rem 0 0.15rem 0.85rem;
            color: var(--ink);
            font-size: 0.92rem;
        }

        .reply-label {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--faint);
            margin-bottom: 0.35rem;
        }

        .error-list {
            margin: 0;
            padding-left: 1.1rem;
            font-size: 0.88rem;
            color: #ffc4c4;
        }

        .error-list li + li { margin-top: 0.25rem; }

        /* ---------- links ---------- */

        .links {
            margin-top: 3rem;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .links a {
            text-decoration: none;
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.15rem;
            background: var(--surface-2);
            transition: border-color 0.15s ease, transform 0.15s ease;
        }

        .links a:hover { border-color: var(--accent); transform: translateY(-2px); }
        .links b { font-family: Syne, sans-serif; font-size: 1.02rem; }
        .links span { display: block; color: var(--faint); font-size: 0.84rem; margin-top: 0.3rem; }

        @media (max-width: 900px) {
            .hero, .links { grid-template-columns: 1fr; }
            header { border-radius: 18px; flex-direction: column; align-items: flex-start; }
            .nav { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <header>
        <div class="brand">
            <span class="dot"></span>
            {{ config('app.name') }}
        </div>
        <nav class="nav">
            <a href="/api/documentation" target="_blank">Swagger</a>
            <a href="/api/health" target="_blank">Health</a>
            <a href="/api/metrics" target="_blank">Metrics</a>
        </nav>
    </header>

    <section class="hero">
        <div>
            <span class="eyebrow">Laravel · REST API · AI</span>
            <h1>Backend для формы обратной связи <span class="grad">с AI-анализом</span></h1>
            <p class="lead">
                Валидация, rate limiting, email-уведомления, файловое хранилище и логирование.
                Каждое обращение проходит через AI: тональность, категория, приоритет и черновик ответа.
            </p>

            <div class="chips">
                <span class="chip"><code>POST /api/contact</code></span>
                <span class="chip">Groq + fallback</span>
                <span class="chip">Rate limiting</span>
                <span class="chip">OpenAPI</span>
            </div>

            <div class="stats">
                <div class="stat">
                    <b id="stat-total">—</b>
                    <span>обращений</span>
                </div>
                <div class="stat">
                    <b id="stat-ai">—</b>
                    <span>AI / fallback</span>
                </div>
                <div class="stat">
                    <b id="stat-status">—</b>
                    <span>статус API</span>
                </div>
            </div>
        </div>

        <form class="panel" id="contact-form" novalidate>
            <div class="panel-head">
                <h2>Связаться</h2>
                <span>POST /api/contact</span>
            </div>

            <div class="field" data-field="name">
                <div class="field-head">
                    <label for="name">Имя</label>
                    <span class="hint">2–100 символов</span>
                </div>
                <input id="name" name="name" placeholder="Иван Петров" autocomplete="name">
                <p class="field-error"></p>
            </div>

            <div class="field" data-field="phone">
                <div class="field-head">
                    <label for="phone">Телефон</label>
                    <span class="hint">10–15 цифр</span>
                </div>
                <input id="phone" name="phone" inputmode="tel" placeholder="+7 999 123-45-67" autocomplete="tel">
                <p class="field-error"></p>
            </div>

            <div class="field" data-field="email">
                <div class="field-head">
                    <label for="email">Email</label>
                </div>
                <input id="email" name="email" type="email" placeholder="ivan@example.com" autocomplete="email">
                <p class="field-error"></p>
            </div>

            <div class="field" data-field="comment">
                <div class="field-head">
                    <label for="comment">Комментарий</label>
                    <span class="hint"><span id="comment-count">0</span>/2000</span>
                </div>
                <textarea id="comment" name="comment" rows="4"
                          placeholder="Интересует сотрудничество по Laravel-проекту..."></textarea>
                <p class="field-error"></p>
            </div>

            <button type="submit" id="submit-btn">Отправить</button>

            <div class="result" id="result">
                <div class="result-head">
                    <span class="result-icon" id="result-icon"></span>
                    <span id="result-title"></span>
                </div>
                <div class="result-body" id="result-body"></div>
            </div>
        </form>
    </section>

    <section class="links">
        <a href="/api/documentation" target="_blank">
            <b>OpenAPI / Swagger</b>
            <span>Интерактивная документация API</span>
        </a>
        <a href="/api/health" target="_blank">
            <b>Health check</b>
            <span>Статус сервиса и AI-конфигурации</span>
        </a>
        <a href="/api/metrics" target="_blank">
            <b>Metrics</b>
            <span>Статистика обращений из JSON</span>
        </a>
    </section>
</div>

<script>
    const form = document.getElementById('contact-form');
    const submitBtn = document.getElementById('submit-btn');
    const result = document.getElementById('result');
    const resultIcon = document.getElementById('result-icon');
    const resultTitle = document.getElementById('result-title');
    const resultBody = document.getElementById('result-body');
    const commentCount = document.getElementById('comment-count');

    const SENTIMENT_LABELS = { positive: 'позитивная', neutral: 'нейтральная', negative: 'негативная' };
    const CATEGORY_LABELS = {
        job_offer: 'вакансия',
        collaboration: 'сотрудничество',
        question: 'вопрос',
        feedback: 'отзыв',
        spam: 'спам',
        other: 'другое',
    };
    const PRIORITY_LABELS = { high: 'высокий', medium: 'средний', low: 'низкий' };

    form.comment.addEventListener('input', () => {
        commentCount.textContent = form.comment.value.length;
    });

    function clearFieldErrors() {
        form.querySelectorAll('.field').forEach((field) => {
            field.classList.remove('invalid');
            field.querySelector('.field-error').textContent = '';
        });
    }

    function showFieldErrors(errors) {
        Object.entries(errors).forEach(([name, messages]) => {
            const field = form.querySelector(`.field[data-field="${name}"]`);
            if (!field) return;
            field.classList.add('invalid');
            field.querySelector('.field-error').textContent = [].concat(messages)[0];
        });
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        })[char]);
    }

    function badge(label, value, modifier = '') {
        return `<span class="badge ${escapeHtml(modifier)}">${label}: <b>${escapeHtml(value)}</b></span>`;
    }

    function showSuccess(data) {
        const ai = data.ai || {};
        const sentiment = SENTIMENT_LABELS[ai.sentiment] || ai.sentiment || '—';
        const category = CATEGORY_LABELS[ai.category] || ai.category || '—';
        const priority = PRIORITY_LABELS[ai.priority] || ai.priority || '—';

        result.className = 'result show ok';
        resultIcon.textContent = '✓';
        resultTitle.textContent = 'Заявка принята и обработана';

        resultBody.innerHTML = `
            <div class="badges">
                ${badge('Категория', category)}
                ${badge('Тональность', sentiment, ai.sentiment)}
                ${badge('Приоритет', priority, ai.priority)}
                ${badge('AI', ai.used_fallback ? 'fallback' : (ai.provider || 'openai'), 'ai')}
                ${badge('Письма', data.emails_delivered ? 'отправлены' : 'не отправлены')}
            </div>
            ${ai.suggested_reply ? `
                <div class="reply">
                    <span class="reply-label">Черновик ответа</span>
                    ${escapeHtml(ai.suggested_reply)}
                </div>` : ''}
        `;
    }

    function showError(title, messages) {
        result.className = 'result show err';
        resultIcon.textContent = '!';
        resultTitle.textContent = title;
        resultBody.innerHTML = `<ul class="error-list">${messages.map((m) => `<li>${escapeHtml(m)}</li>`).join('')}</ul>`;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFieldErrors();
        result.className = 'result';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправляем...';

        const payload = {
            name: form.name.value.trim(),
            phone: form.phone.value.trim(),
            email: form.email.value.trim(),
            comment: form.comment.value.trim(),
        };

        try {
            const response = await fetch('/api/contact', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (response.status === 422 && data.errors) {
                showFieldErrors(data.errors);
                showError('Проверьте заполнение формы', Object.values(data.errors).flat());
                return;
            }

            if (response.status === 429) {
                const minutes = Math.ceil((data.retry_after || 0) / 60);
                showError('Слишком много запросов', [`Повторите попытку через ${minutes} мин.`]);
                return;
            }

            if (!response.ok) {
                showError('Ошибка сервера', [data.message || `HTTP ${response.status}`]);
                return;
            }

            showSuccess(data.data || {});
            form.reset();
            commentCount.textContent = '0';
            loadStats();
        } catch (error) {
            showError('Сеть недоступна', [error.message || 'Не удалось отправить запрос']);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Отправить';
        }
    });

    async function loadStats() {
        try {
            const [metrics, health] = await Promise.all([
                fetch('/api/metrics', { headers: { Accept: 'application/json' } }).then((r) => r.json()),
                fetch('/api/health', { headers: { Accept: 'application/json' } }).then((r) => r.json()),
            ]);

            const aiOk = metrics.data?.ai_success_count ?? 0;
            const aiFallback = metrics.data?.ai_fallback_count ?? 0;
            document.getElementById('stat-total').textContent = metrics.data?.total_contacts ?? '—';
            document.getElementById('stat-ai').textContent = `${aiOk} / ${aiFallback}`;
            document.getElementById('stat-status').textContent = health.status === 'ok' ? 'ok' : 'degraded';
        } catch {
            document.getElementById('stat-status').textContent = 'offline';
        }
    }

    loadStats();
</script>
</body>
</html>
