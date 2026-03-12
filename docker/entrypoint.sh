#!/bin/sh
set -e

cd /var/www

if [ -f artisan ]; then
  echo "[entrypoint] Running Laravel bootstrap tasks..."

  # Ensure public/storage always points to storage/app/public.
  php artisan storage:link || true

  # Optional deploy tasks (enable with env vars in production).
  if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
    echo "[entrypoint] Waiting for database (${DB_HOST:-db}:${DB_PORT:-3306})..."
    i=1
    while [ $i -le 30 ]; do
      php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT")?:3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD"), [PDO::ATTR_TIMEOUT=>2]); exit(0); } catch (Throwable $e) { exit(1); }' \
        && break
      echo "[entrypoint] DB not ready yet (attempt $i/30), retrying..."
      i=$((i+1))
      sleep 2
    done
    php artisan migrate --force
  fi

  if [ "${CACHE_LARAVEL:-0}" = "1" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
  fi
fi

exec "$@"
