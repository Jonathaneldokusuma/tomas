#!/bin/bash
set -e

echo "==> [start.sh] Preparing Laravel for production..."

# ── Generate app key if missing ────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    if [ -f .env ]; then
        php artisan key:generate --force
    else
        export APP_KEY=$(php artisan key:generate --show --no-interaction)
        echo "==> APP_KEY generated from runtime environment"
    fi
fi

# ── Run database migrations ────────────────────────────────────────────────
echo "==> Running migrations..."
if ! php artisan migrate --force; then
    echo "==> Migration step failed, continuing startup so the app can respond"
fi

# ── Optimize for production ────────────────────────────────────────────────
echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Create storage symlink ─────────────────────────────────────────────────
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# ── Start server ───────────────────────────────────────────────────────────
echo "==> Starting Laravel server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
