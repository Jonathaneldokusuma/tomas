# Setup Firebase (WAJIB untuk notifikasi push)

## Kenapa perlu ini?
Kedua Flutter app sekarang pakai `firebase_messaging` untuk menerima push notification dari server.
Tanpa ini, notifikasi dari admin panel / tukang tidak akan masuk ke HP user.

## Langkah-langkah

### 1. Buat project Firebase
1. Buka https://console.firebase.google.com
2. Klik **Add project** → beri nama misal `tomas-app`
3. Aktifkan **Google Analytics** (opsional)

### 2. Daftarkan kedua Android app

#### App User (`tomas-flutter`)
- Package name: `com.tomas.tomas_app` (sesuaikan dengan `applicationId` di `android/app/build.gradle.kts`)
- Download `google-services.json` → taruh di `tomas-flutter/android/app/google-services.json`

#### App Tukang (`tomas-flutter-tukang`)
- Package name: `com.tomas.tomas_flutter_tukang`
- Download `google-services.json` → taruh di `tomas-flutter-tukang/android/app/google-services.json`

### 3. Update build.gradle kedua app

Di `android/build.gradle.kts` (root), tambah di `plugins`:
```kotlin
id("com.google.gms.google-services") version "4.4.0" apply false
```

Di `android/app/build.gradle.kts`, tambah di `plugins`:
```kotlin
id("com.google.gms.google-services")
```

### 4. Set FCM_PROJECT_ID di backend (tomas-app Laravel)

Di `.env`:
```
FCM_PROJECT_ID=your-firebase-project-id
```
Dan taruh `firebase-service-account.json` di `storage/firebase-service-account.json`
(download dari Firebase Console → Project Settings → Service Accounts → Generate new private key)

### 5. Jalankan `flutter pub get` di kedua folder Flutter

---

## File yang sudah diperbaiki

### tomas-app (Laravel backend)
- `app/Http/Controllers/Admin/AdminController.php` — Admin sekarang kirim notifikasi ke user & tukang
- `app/Http/Controllers/Api/TukangDashboardController.php` — Tukang accept/reject/status kirim notif in-app + FCM
- `app/Http/Controllers/Api/OrderController.php` — Upload bukti bayar kirim notif ke tukang
- `routes/api.php` — Fix urutan route notifikasi

### tomas-flutter (App User)
- `pubspec.yaml` — Tambah `firebase_core` + `firebase_messaging`
- `lib/main.dart` — Tambah `Firebase.initializeApp()`
- `lib/services/notification_service.dart` — Integrasi FCM
- `lib/services/api_service.dart` — Tambah `saveFcmToken()`
- `lib/screens/auth/login_screen.dart` — Kirim FCM token setelah login

### tomas-flutter-tukang (App Tukang)
- `pubspec.yaml` — Tambah `firebase_core` + `firebase_messaging`
- `lib/main.dart` — Tambah `Firebase.initializeApp()`
- `lib/services/notification_service.dart` — Integrasi FCM
- `lib/services/tukang_service.dart` — Tambah `saveFcmToken()`
- `lib/screens/login_screen.dart` — Kirim FCM token setelah login
- `lib/screens/splash_screen.dart` — Kirim FCM token saat auto-login
