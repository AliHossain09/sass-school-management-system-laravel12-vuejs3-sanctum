#!/bin/sh
set -e

cd /var/www

if [ -f artisan ]; then
  echo "[entrypoint] Running Laravel bootstrap tasks..."

  # Ensure public/storage always points to storage/app/public.
  php artisan storage:link || true

  # Optional deploy tasks (enable with env vars in production).
  if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
    php artisan migrate --force
  fi

  if [ "${CACHE_LARAVEL:-0}" = "1" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
  fi
fi

exec "$@"
