# TOMAS

TOMAS adalah aplikasi pemesanan jasa tukang yang terdiri dari admin panel, aplikasi pengguna, dan aplikasi pekerja.

## Struktur Repository

- `admin-panel` - backend Laravel, REST API, dan dashboard admin.
- `userapp` - aplikasi Flutter untuk pengguna yang memesan jasa.
- `pekerjaapp` - aplikasi Flutter untuk pekerja/tukang yang menerima pekerjaan.
- `Dockerfile` - image production untuk Railway.
- `railway.json` - konfigurasi Railway agar deployment membaca backend dari folder `admin-panel`.

## Download APK

APK terbaru tersedia di halaman GitHub Releases:

- [TOMAS User App](https://github.com/Jonathaneldokusuma/tomas/releases/latest/download/tomas-user-release.apk)
- [TOMAS Pekerja App](https://github.com/Jonathaneldokusuma/tomas/releases/latest/download/tomas-tukang-release.apk)

## Backend Production

```text
https://tomas-production.up.railway.app
```

Deployment Railway memakai `railway.json` di root repo. Config itu menjalankan build dan start command dari folder `admin-panel`, jadi service Railway tetap aman walaupun repository berisi tiga aplikasi.

## Menjalankan Admin Panel

```bash
cd admin-panel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Build Aplikasi Android

User app:

```bash
cd userapp
flutter pub get
flutter build apk --release --dart-define=TOMAS_SERVER_URL=https://tomas-production.up.railway.app
```

Pekerja app:

```bash
cd pekerjaapp
flutter pub get
flutter build apk --release --dart-define=TOMAS_SERVER_URL=https://tomas-production.up.railway.app
```
