@echo off
setlocal enabledelayedexpansion

echo ============================================================
echo  TOMAS APP - Setup Installer (Windows)
echo ============================================================
echo.

REM 1. Copy .env if not exists
if not exist .env (
    echo [1/6] Membuat file .env ...
    copy .env.example .env
    echo       Done. Edit .env sesuai konfigurasi MySQL kamu!
    echo.
    pause
) else (
    echo [1/6] .env sudah ada, dilewati.
)

REM 2. Install composer dependencies
echo [2/6] Menginstall dependencies (Composer)...
composer install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo ERROR: composer install gagal. Pastikan Composer sudah terinstall.
    pause & exit /b 1
)

REM 3. Generate app key
echo [3/6] Generate APP_KEY...
php artisan key:generate

REM 4. Run migrations + seeder
echo [4/6] Menjalankan migrasi database + seeder...
php artisan migrate --seed --force
if errorlevel 1 (
    echo ERROR: Migrasi gagal. Cek koneksi MySQL di .env
    pause & exit /b 1
)

REM 5. Storage link
echo [5/6] Membuat storage link...
php artisan storage:link

REM 6. Optimize
echo [6/6] Optimasi cache...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo.
echo ============================================================
echo  Setup selesai!
echo  Jalankan server dengan:  php artisan serve
echo  Admin panel:             http://localhost:8000/admin
echo  Login admin:             admin / admin123  (ubah di .env)
echo ============================================================
pause
