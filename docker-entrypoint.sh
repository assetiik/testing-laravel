#!/usr/bin/env sh
set -eu

cd /app

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is empty — generating a temporary key for this container."
  export APP_KEY="$(php artisan key:generate --show --no-interaction)"
fi

# Keep SQLite file present even on ephemeral disks.
mkdir -p \
  storage/app/private/contacts \
  storage/app/private/metrics \
  storage/app/private/rate-limits \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  database

if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
fi

php artisan config:clear --no-interaction >/dev/null 2>&1 || true
php artisan migrate --force --graceful --no-interaction || true
php artisan l5-swagger:generate --no-interaction || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
