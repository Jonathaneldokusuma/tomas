#!/bin/bash
set -e

echo "============================================================"
echo " TOMAS APP - Setup Installer (Linux/macOS)"
echo "============================================================"
echo ""

# 1. Copy .env
if [ ! -f .env ]; then
    echo "[1/6] Membuat file .env..."
    cp .env.example .env
    echo "      Done. Edit .env sesuai konfigurasi MySQL!"
    echo ""
    read -p "Tekan Enter setelah mengedit .env..." dummy
else
    echo "[1/6] .env sudah ada, dilewati."
fi

# 2. Composer install
echo "[2/6] Install dependencies (Composer)..."
composer install --no-dev --optimize-autoloader

# 3. App key
echo "[3/6] Generate APP_KEY..."
php artisan key:generate

# 4. Migrate + seed
echo "[4/6] Migrasi database + seeder..."
php artisan migrate --seed --force

# 5. Storage link
echo "[5/6] Storage link..."
php artisan storage:link

# 6. Optimize
echo "[6/6] Optimasi cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "============================================================"
echo " Setup selesai!"
echo " Jalankan:   php artisan serve --host=0.0.0.0 --port=8000"
echo " Admin:      http://your-ip:8000/admin"
echo " Login:      admin / admin123  (ubah di .env: ADMIN_USER/ADMIN_PASS)"
echo "============================================================"
