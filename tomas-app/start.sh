#!/bin/bash
set -e

echo "==> [start.sh] Preparing Laravel for production..."

# ── Generate app key if missing ────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# ── Run database migrations ────────────────────────────────────────────────
echo "==> Running migrations..."
php artisan migrate --force

# ── Optimize for production ────────────────────────────────────────────────
echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Create storage symlink ─────────────────────────────────────────────────
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

# ── Start server ───────────────────────────────────────────────────────────
echo "==> Starting Laravel server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
