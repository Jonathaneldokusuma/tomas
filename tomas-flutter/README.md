# Tomas App Flutter

## Firebase Hosting

Firebase Hosting hanya melayani frontend statis. Backend Laravel tetap harus online di Railway, VPS, Render, atau hosting PHP lain.

### 1. Install Firebase CLI

```bash
npm install -g firebase-tools
firebase login
```

### 2. Pilih project Firebase

Jalankan dari folder ini:

```bash
cd "d:\laravel web app\tomas-flutter"
firebase use apk-encrypt-d9254
```

File `.firebaserc` sudah diarahkan ke project `apk-encrypt-d9254`, jadi perintah di atas cukup untuk memastikan CLI memakai project yang benar.

### 3. Build web dengan URL backend production

Contoh kalau backend Laravel kamu ada di Railway:

```bash
flutter build web --release --dart-define=TOMAS_SERVER_URL=https://backend-kamu.com
```

Jangan pakai IP lokal saat deploy ke Firebase Hosting, karena browser pengguna tidak bisa mengakses IP LAN kamu.

### 4. Deploy ke Firebase Hosting

File `firebase.json` di folder ini sudah dikonfigurasi untuk Flutter web.

```bash
firebase deploy --only hosting
```

### 5. Verifikasi

- Buka URL Hosting dari Firebase
- Pastikan halaman utama tampil
- Coba login dan panggil data dari API
- Jika API gagal, cek URL backend yang dipakai saat `flutter build web`

## Build lokal

Untuk web lokal dengan backend lokal:

```bash
flutter run -d chrome --dart-define=TOMAS_SERVER_URL=http://127.0.0.1:8000
```

## Catatan

- Konfigurasi Hosting ada di `firebase.json`
- Flutter web output ada di `build/web`
- CORS backend Laravel saat ini sudah membuka akses ke frontend web
