# Tomas Flutter Tukang

## Build APK Production

App ini membaca URL backend dari `--dart-define`, jadi build production harus menyertakan domain backend Laravel kamu.

Contoh:

```bash
cd pekerjaapp
flutter build apk --release --dart-define=TOMAS_SERVER_URL=https://backend-kamu.com
```

## Build Lokal

```bash
flutter run --dart-define=TOMAS_SERVER_URL=http://127.0.0.1:8000
```

## Catatan

- Backend API tetap berasal dari `admin-panel`
- Admin panel web ada di `https://backend-kamu.com/admin`
