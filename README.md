# TOMAS

Download APK Android:

- [APK User](https://github.com/Jonathaneldokusuma/tomas/releases/latest/download/tomas-user-release.apk)
- [APK Tukang](https://github.com/Jonathaneldokusuma/tomas/releases/latest/download/tomas-tukang-release.apk)

## File Release

Agar link download GitHub langsung bisa diklik, upload file APK dengan nama berikut ke GitHub Releases:

- `tomas-user-release.apk`
- `tomas-tukang-release.apk`

## Cara Publish Release

1. Push tag versi, misalnya:

   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   ```

2. GitHub Actions akan build kedua APK.
3. GitHub Release akan otomatis dibuat dan file bisa langsung diunduh.
4. Kalau mau manual, buka tab **Releases** lalu upload:
   - `tomas-user-release.apk`
   - `tomas-tukang-release.apk`

## Catatan

- Kalau memakai nama file lain, ubah link di atas supaya sesuai.
- Link `.../releases/latest/download/...` akan selalu mengarah ke release terbaru.
- Workflow GitHub Actions ada di `.github/workflows/release-apk.yml`.
