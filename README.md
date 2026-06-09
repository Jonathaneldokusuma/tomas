# TOMAS

TOMAS adalah aplikasi pemesanan jasa tukang dengan tiga bagian utama: backend Laravel untuk admin dan API, aplikasi Android untuk pengguna, dan aplikasi Android untuk tukang.

## Download APK

Versi Android terbaru bisa diunduh dari GitHub Releases:

- [Download TOMAS User](https://github.com/Jonathaneldokusuma/tomas/releases/latest/download/tomas-user-release.apk)
- [Download TOMAS Tukang](https://github.com/Jonathaneldokusuma/tomas/releases/latest/download/tomas-tukang-release.apk)

Kalau dibuka dari HP, tekan salah satu link di atas, tunggu file APK selesai diunduh, lalu pilih **Install**.

## Isi Project

- `tomas-app` berisi backend Laravel, API, dan dashboard admin.
- `tomas-flutter` berisi aplikasi Android untuk pengguna.
- `tomas-flutter-tukang` berisi aplikasi Android untuk tukang.

## Release Android

APK dibuat otomatis lewat GitHub Actions setiap kali tag versi baru dipush.

```bash
git tag v1.0.1
git push origin v1.0.1
```

Workflow release ada di `.github/workflows/release-apk.yml`. File yang dihasilkan akan muncul di halaman Releases dengan nama:

- `tomas-user-release.apk`
- `tomas-tukang-release.apk`

## Backend

Backend production yang dipakai aplikasi Android:

```text
https://tomas-production.up.railway.app
```

Jika URL backend berubah, sesuaikan nilai `TOMAS_SERVER_URL` pada workflow release sebelum membuat tag baru.
