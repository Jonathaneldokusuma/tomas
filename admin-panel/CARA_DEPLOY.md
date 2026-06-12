# 🚀 Deploy Tomas App ke Railway.app (ONLINE — Gratis)

Railway.app memungkinkan kamu deploy Laravel + MySQL ke internet **tanpa beli server**.
URL app kamu nanti akan seperti: `https://tomas-app.up.railway.app`

---

## Prasyarat

- [ ] Akun [Railway.app](https://railway.app) (bisa login pakai GitHub)
- [ ] Akun [GitHub](https://github.com)
- [ ] Project ini sudah di-push ke repositori GitHub kamu

---

## Langkah 1 — Upload kode ke GitHub

Jika belum ada, buat repository di GitHub lalu push root repository TOMAS:

```bash
cd "d:\laravel web app"
git init
git add .
git commit -m "initial commit"
git branch -M main
git remote add origin https://github.com/USERNAME/tomas.git
git push -u origin main
```

> **Penting:** Pastikan `.env` ada di `.gitignore` agar password tidak ter-upload.

---

## Langkah 2 — Buat Project di Railway

1. Buka [railway.app](https://railway.app) → **New Project**
2. Pilih **Deploy from GitHub repo** → pilih repo `tomas`
3. Railway akan membaca `railway.json` di root repo dan menjalankan Laravel dari folder `admin-panel`

---

## Langkah 3 — Tambah MySQL Database

1. Di dalam project Railway, klik **+ New** → **Database** → **MySQL**
2. Railway otomatis membuat database dan mengisi variabel berikut:
   - `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`
   - atau format lama `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE`
3. App Laravel sudah dikonfigurasi untuk membaca kedua format variabel ini secara otomatis ✅

---

## Langkah 4 — Set Environment Variables

Di Railway → **Variables**, tambahkan:

```
APP_NAME=TomasApp
APP_ENV=production
APP_DEBUG=false
APP_KEY=               ← Akan otomatis di-generate saat start
SESSION_DRIVER=cookie
CACHE_DRIVER=database
QUEUE_CONNECTION=sync
ADMIN_USER=admin
ADMIN_PASS=admin123
FCM_PROJECT_ID=tomas-app-2e185
```

> **APP_KEY** akan di-generate otomatis oleh `start.sh`. Tapi jika ingin set manual,
> jalankan `php artisan key:generate --show` di lokal dan copy hasilnya.

Untuk Firebase Push di Railway, tambahkan salah satu:

```text
FIREBASE_SERVICE_ACCOUNT_JSON=<isi service-account JSON atau base64 JSON>
```

atau upload file service-account lewat halaman **Admin Panel → Firebase Push** setelah deploy berhasil.

---

## Langkah 5 — Set APP_URL Setelah Deploy

Setelah Railway berhasil deploy dan memberikan URL (contoh: `https://tomas-abc123.up.railway.app`):

1. Kembali ke **Variables** di Railway
2. Tambahkan:
   ```
   APP_URL=https://tomas-abc123.up.railway.app
   ```

Admin panel web akan tersedia di:

```text
https://tomas-abc123.up.railway.app/admin
```

---

## Langkah 6 — Build APK dengan URL Backend Production

Kedua aplikasi Flutter sekarang membaca URL backend dari `--dart-define`, jadi tidak perlu edit source code setiap kali deploy.

Build APK user:

```bash
cd "d:\laravel web app\userapp"
flutter build apk --release --dart-define=TOMAS_SERVER_URL=https://tomas-abc123.up.railway.app
```

Build APK tukang:

```bash
cd "d:\laravel web app\pekerjaapp"
flutter build apk --release --dart-define=TOMAS_SERVER_URL=https://tomas-abc123.up.railway.app
```

---

## Langkah 7 — Verifikasi

Buka browser, akses:
- `https://tomas-abc123.up.railway.app/api/tukang` → harus return JSON
- `https://tomas-abc123.up.railway.app/admin` → admin login panel

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Deploy gagal | Cek tab **Deployments** → klik deployment → lihat log |
| Database error | Pastikan MySQL plugin sudah ditambahkan di Railway |
| APP_KEY error | Tambahkan variabel `APP_KEY` di Railway Variables |
| 500 error | Set `APP_DEBUG=true` sementara untuk lihat pesan error |
| Flutter tidak konek | Pastikan saat build APK kamu memakai `--dart-define=TOMAS_SERVER_URL=...` |

---

## Mode Lokal (dengan XAMPP)

Untuk testing lokal, jalankan app dengan `--dart-define` ke server lokal:

```bash
flutter run --dart-define=TOMAS_SERVER_URL=http://192.168.x.x:8000
```

---

## Alternatif Hosting (jika Railway tidak cocok)

| Platform | Kelebihan | Harga |
|----------|-----------|-------|
| **Railway.app** | Paling mudah, otomatis | Gratis s/d $5/bln |
| **Render.com** | Mirip Railway | Gratis (lambat cold start) |
| **VPS Contabo** | Murah, full control | ~€3.99/bln |
| **Niagahoster** | Indonesia, support Bahasa | ~Rp20rb/bln |
