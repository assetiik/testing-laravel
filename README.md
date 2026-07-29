# Dev Landing Contact API (Laravel)

Backend-сервис для лендинг-презентации разработчика: REST API формы обратной связи, email-уведомления, rate limiting, логирование и AI-анализ обращений.

## Демо

| Что | Ссылка |
|---|---|
| Лендинг с формой | https://dev-landing-api.onrender.com |
| Swagger UI | https://dev-landing-api.onrender.com/api/documentation |
| Health check | https://dev-landing-api.onrender.com/api/health |
| Metrics | https://dev-landing-api.onrender.com/api/metrics |
| Репозиторий | https://github.com/assetiik/testing-laravel |

Демо постоянно размещено на Render через Docker. На бесплатном тарифе первый запрос после
периода бездействия может занять до минуты, пока сервис выходит из sleep-режима.

## 1. Как запустить проект

### Требования

- PHP 8.4.1+
- Composer 2.x
- Расширения PHP: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`

### Установка

```bash
git clone https://github.com/assetiik/testing-laravel.git test-back
cd test-back
composer install
cp .env.example .env
php artisan key:generate
mkdir -p storage/app/private/contacts storage/app/private/metrics storage/app/private/rate-limits
chmod -R 775 storage bootstrap/cache
touch database/database.sqlite
php artisan migrate
php artisan l5-swagger:generate
```

### Переменные окружения

Ключевые переменные в `.env`:

| Переменная | Описание |
|---|---|
| `CONTACT_OWNER_EMAIL` | Email владельца сайта (уведомление о заявке) |
| `CONTACT_RATE_LIMIT_MAX` | Макс. число заявок с одного IP |
| `CONTACT_RATE_LIMIT_DECAY` | Окно rate limit в секундах |
| `AI_ENABLED` | `true/false` — включить AI |
| `AI_PROVIDER` | Название провайдера (`groq`, `openai`, `openrouter`) |
| `AI_API_KEY` | Ключ провайдера |
| `AI_BASE_URL` | Базовый URL OpenAI-совместимого API |
| `AI_MODEL` | Модель |
| `MAIL_MAILER` | `log` для локальной разработки, `smtp` или `resend` для продакшена |
| `RESEND_API_KEY` | Ключ Resend, нужен только при `MAIL_MAILER=resend` |
| `CORS_ALLOWED_ORIGINS` | `*` или список origin через запятую |

Если `AI_API_KEY` пустой или AI недоступен — сервис **продолжает работать** через `FallbackAiService`.

### Выбор AI-провайдера

Интеграция построена на OpenAI-совместимом протоколе `chat/completions`, поэтому провайдер меняется только через `.env`, без правок кода:

| Провайдер | `AI_BASE_URL` | `AI_MODEL` |
|---|---|---|
| Groq (по умолчанию, free tier) | `https://api.groq.com/openai/v1` | `llama-3.3-70b-versatile` |
| OpenAI | `https://api.openai.com/v1` | `gpt-4o-mini` |
| OpenRouter | `https://openrouter.ai/api/v1` | `meta-llama/llama-3.3-70b-instruct:free` |

Проверить, что ключ рабочий, можно командой (она вызывает провайдера напрямую, минуя fallback):

```bash
php artisan ai:test "Здравствуйте! Хотим предложить вам работу."
```

### Проверка почты

Отправить тестовое письмо через текущий mailer (по умолчанию — на `CONTACT_OWNER_EMAIL`):

```bash
php artisan mail:test you@example.com
```

### Запуск локально

```bash
php artisan serve
```

Откройте:

- Лендинг: http://localhost:8000
- Swagger UI: http://localhost:8000/api/documentation
- Health: http://localhost:8000/api/health

### Тесты

```bash
php artisan test
```

## 2. Стек технологий

### Backend

- **PHP 8.4 / Laravel 13**
- Guzzle/Http client (вызовы AI-провайдера)
- Laravel Mail (Markdown mailables)
- darkaonline/l5-swagger (OpenAPI / Swagger UI)
- File storage (JSON) для контактов, метрик и rate limit

### AI

- **Chat Completions API** через `OpenAiCompatibleService` — работает с OpenAI, Groq и OpenRouter (по умолчанию Groq, `llama-3.3-70b-versatile`)
- Локальный **FallbackAiService** (rule-based) при недоступности провайдера

### Почему Laravel

- Быстрая и чистая организация REST API
- Готовые Form Request, Mail, Logging, Exception handling
- Удобно демонстрировать слоистую архитектуру на собеседовании
- OpenAPI легко подключается через атрибуты PHP 8

## 3. Архитектура

```
Request
  → Middleware (LogApiRequests, CORS)
  → Controller (тонкий слой HTTP)
  → FormRequest (валидация/санитизация)
  → Service (бизнес-логика)
      → AiService (провайдер → Fallback)
      → Repositories (JSON files)
      → Mail
  → JSON Response
```

### Структура

```
app/
  DTOs/                  # ContactData, AiAnalysisResult
  Exceptions/            # RateLimitExceededException, ContactProcessingException
  Http/
    Controllers/Api/     # Contact, Health, Metrics
    Middleware/          # LogApiRequests
    Requests/            # ContactRequest
  Mail/                  # Owner + User letters
  OpenApi/               # Root OpenAPI info
  Repositories/          # Contact, Metrics, RateLimit (file-based)
  Services/
    ContactService.php
    Ai/                  # Interface, OpenAiCompatible, Fallback, Resilient decorator
config/
  ai.php
  contact.php
  cors.php
routes/api.php
storage/app/private/     # contacts, metrics, rate-limits
storage/logs/api-requests.log
docs/postman/            # Postman collection
```

### Паттерны

- **Layered architecture** — Controllers → Services → Repositories
- **DTO** — типобезопасная передача данных между слоями
- **Strategy + Decorator** — `AiServiceInterface` + `ResilientAiService`
- **Repository** — изоляция файлового хранилища
- **Form Request** — валидация на границе HTTP

## 4. Реализация API

### Эндпоинты

| Method | Path | Описание |
|---|---|---|
| `POST` | `/api/contact` | Приём формы обратной связи |
| `GET` | `/api/health` | Статус сервиса |
| `GET` | `/api/metrics` | Статистика обращений |
| `GET` | `/api/documentation` | Swagger UI |

### POST /api/contact

**Request**

```bash
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Иван Петров",
    "phone": "+7 999 123-45-67",
    "email": "ivan@example.com",
    "comment": "Здравствуйте! Интересует сотрудничество по Laravel-проекту."
  }'
```

**Success `201`**

```json
{
  "success": true,
  "message": "Contact request processed successfully.",
  "data": {
    "id": "uuid",
    "created_at": "2026-07-29T12:00:00+00:00",
    "ai": {
      "sentiment": "positive",
      "sentiment_score": 0.55,
      "category": "collaboration",
      "priority": "high",
      "summary": "...",
      "suggested_reply": "...",
      "used_fallback": false,
      "provider": "openai"
    },
    "emails_delivered": true,
    "rate_limit": {
      "remaining": 4,
      "retry_after": 3600
    }
  }
}
```

### Валидация

- `name` — обязательно, 2–100 символов, буквы/пробелы/дефис
- `phone` — обязательно, 10–15 цифр; допускаются `+ - ( ) .` и пробелы. Значение нормализуется в E.164 (`8 (777) 123 45 67` → `+77771234567`), правило — `App\Rules\PhoneNumber`
- `email` — обязательно, RFC email
- `comment` — обязательно, 10–2000 символов
- входные строки trim + `strip_tags`

### HTTP-статусы

| Код | Когда |
|---|---|
| `201` | Заявка успешно обработана |
| `422` | Ошибка валидации |
| `429` | Rate limit exceeded |
| `503` | Health degraded |
| `500` | Непредвиденная ошибка |

### Примеры curl

```bash
# Health
curl http://localhost:8000/api/health

# Metrics
curl http://localhost:8000/api/metrics

# Validation error
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"A","phone":"12","email":"bad","comment":"short"}'
```

Postman-коллекция: `docs/postman/Dev_Landing_Contact_API.postman_collection.json`

## 5. AI-интеграция

### Что делает AI

Один вызов провайдера возвращает JSON:

1. **Тональность** (`positive|neutral|negative` + score)
2. **Классификация** (`job_offer|collaboration|question|feedback|spam|other`)
3. **Приоритет**
4. **Краткое summary**
5. **Автоответ** (`suggested_reply`) — уходит в письмо пользователю

### Fallback

`ResilientAiService`:

1. Если `AI_ENABLED=false` → fallback
2. Если нет API key / HTTP ошибка / таймаут / битый JSON → fallback
3. Fallback использует keyword heuristics (RU/EN) и шаблонный ответ

Сервис **не падает**, если провайдер недоступен.

### Промпт (system)

См. `config/ai.php` → `prompts.system`.

Кратко: вернуть только JSON нужной схемы, отвечать на языке комментария, не выдумывать факты.

## 6. Что сделано с помощью AI

При разработке использовался AI-ассистент Cursor:

- **Сгенерировано с помощью AI:** каркас слоёв (DTO/Services/Repositories), OpenAPI-атрибуты, markdown-письма, landing page, черновик README
- **Промпты:** описание ТЗ тестового задания + требования к слоистой архитектуре Laravel + AI fallback
- **Исправлено вручную:**
  - точная схема валидации и статус-кодов
  - file-based rate limiting вместо встроенного Redis/cache-only
  - graceful email failure (заявка сохраняется даже если mailer упал)
  - тестовый harness с изоляцией JSON-файлов
  - вычитка архитектуры под критерии собеседования

## 7. Хранение данных

| Данные | Где |
|---|---|
| Логи API-запросов | `storage/logs/api-requests.log` (daily channel `api`) |
| Заявки | `storage/app/private/contacts/contacts.json` |
| Метрики | `storage/app/private/metrics/metrics.json` |
| Rate limit | `storage/app/private/rate-limits/<sha256(ip)>.json` |
| Письма (local) | `storage/logs/laravel.log` при `MAIL_MAILER=log` |

БД не обязательна для бизнес-логики контактов. SQLite используется только стандартным Laravel scaffolding.

## 8. Деплой

### Вариант A: Render (Docker)

У Render нет нативного PHP-окружения, поэтому сервис собирается из `Dockerfile`.
Блюпринт лежит в `render.yaml`.

1. Render → **New** → **Blueprint** → выбрать этот репозиторий
2. Render прочитает `render.yaml` и создаст web-сервис с `runtime: docker`
3. Заполнить секреты, помеченные в блюпринте как `sync: false`:
   `AI_API_KEY`, `CONTACT_OWNER_EMAIL`, `RESEND_API_KEY`, `MAIL_FROM_ADDRESS`
4. Health check настроен на `/api/health`

#### Почта в продакшене: Resend вместо SMTP

Render (как и большинство PaaS на free-тарифе) блокирует исходящие SMTP-соединения,
поэтому Gmail SMTP на проде уходит в таймаут. Продакшен использует HTTP API Resend —
код при этом не меняется, транспорт `resend` встроен в Laravel:

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_...
MAIL_FROM_ADDRESS="noreply@ваш-домен.ru"
```

Важно про адрес отправителя: Resend разрешает слать письма только с **верифицированного домена**.
Без своего домена доступен sandbox-адрес `onboarding@resend.dev`, но письма с него уходят
только на email аккаунта Resend — копия пользователю на произвольный адрес будет отклонена.
Заявка при этом всё равно сохраняется, а API возвращает `201` с `emails_delivered: false`.

#### Эфемерное хранилище

Файловая система инстанса на Render не персистентна: при каждом деплое
`storage/app/private/*` обнуляется, поэтому `/api/metrics` после релиза стартует с нуля.
Для продакшена сюда бы встали БД или Redis — репозитории изолируют хранилище за интерфейсом,
так что замена не затрагивает сервисный слой.

Локально образ можно проверить так:

```bash
docker build -t dev-landing-api .
docker run --rm -p 8000:8000 --env-file .env dev-landing-api
```

### Вариант B: ngrok (локально)

```bash
php artisan serve
ngrok http 8000
```

ngrok выдаст публичный HTTPS-адрес — его и передаём проверяющему.
Туннель живёт, пока запущены `php artisan serve` и `ngrok`.

### Если деплой невозможен

Достаточно локального запуска по инструкции выше + Postman/Swagger примеры.

## 9. Безопасность

- Валидация и санитизация входа
- Rate limiting по IP (file-based)
- CORS через `config/cors.php`
- Секреты только в `.env` (не в репозитории)
- Глобальный JSON error handler для `api/*`
- Логирование запросов без записи чувствительных паролей (их нет в API)

## 10. Frontend (бонус)

На `/` есть простой лендинг с формой, которая бьёт в `POST /api/contact` и показывает AI-классификацию ответа.
