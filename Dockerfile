FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libsqlite3-dev \
        pkg-config \
    && docker-php-ext-install -j"$(nproc)" bcmath pdo_sqlite zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install dependencies first so Docker can cache this layer between code changes.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p \
        storage/app/private/contacts \
        storage/app/private/metrics \
        storage/app/private/rate-limits \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        database \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x artisan docker-entrypoint.sh

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stack \
    LOG_STACK=single,api \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync \
    DB_CONNECTION=sqlite

EXPOSE 8000

ENTRYPOINT ["./docker-entrypoint.sh"]
