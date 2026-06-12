#!/bin/bash
set -e

echo "==> [start.sh] Preparing Laravel for production..."

# ── Ensure runtime directories exist ───────────────────────────────────────
mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions storage/logs bootstrap/cache

# ── Generate app key if missing ────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    if [ -f .env ]; then
        php artisan key:generate --force
    else
        export APP_KEY=$(php artisan key:generate --show --no-interaction)
        echo "==> APP_KEY generated from runtime environment"
    fi
fi

# ── Run database migrations (retry until DB is ready) ────────────────────────
echo "==> Running migrations..."
# Retry loop: try migrate up to MAX_RETRIES times with a sleep between attempts
MAX_RETRIES=12
SLEEP_SECONDS=5
attempt=1
until php artisan migrate --force
do
    if [ "$attempt" -ge "$MAX_RETRIES" ]; then
        echo "==> Migration step failed after $attempt attempts, continuing startup"
        break
    fi
    echo "==> Migration attempt $attempt failed, retrying in $SLEEP_SECONDS seconds..."
    attempt=$((attempt+1))
    sleep $SLEEP_SECONDS
done

# ── Optimize for production ────────────────────────────────────────────────
echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache || echo "==> Route cache skipped; continuing startup"
php artisan view:cache

# ── Create storage symlink ─────────────────────────────────────────────────
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# ── Start server ───────────────────────────────────────────────────────────
echo "==> Starting Laravel server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
