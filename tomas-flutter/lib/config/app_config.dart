/// ─────────────────────────────────────────────────────────────────────────
/// Konfigurasi server untuk Tomas App
///
/// LOKAL (XAMPP / php artisan serve):
///   Ganti serverUrl ke IP PC kamu, contoh:
///   'http://192.168.1.10:8000'
///
/// ONLINE (Railway / VPS / Hosting):
///   Ganti serverUrl ke URL server kamu, contoh:
///   'https://tomas-production.up.railway.app'
/// ─────────────────────────────────────────────────────────────────────────
class AppConfig {
  /// Base URL server backend (tanpa trailing slash, tanpa /api)
  static const String serverUrl = 'http://10.50.15.205:8000';

  /// Full API base URL (digunakan oleh ApiService)
  static const String apiBaseUrl = '$serverUrl/api';
}
