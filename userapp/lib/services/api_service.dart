import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';
import '../utils/json_value.dart';

class ApiService {
  static final String baseUrl = AppConfig.apiBaseUrl;
  static const Duration _networkTimeout = Duration(seconds: 15);

  static Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  static Future<Map<String, String>> _headers({bool auth = false}) async {
    final h = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (auth) {
      final token = await _getToken();
      if (token != null) h['Authorization'] = 'Bearer $token';
    }
    return h;
  }

  static Future<http.Response> _get(Uri uri, {bool auth = false}) async {
    return http
        .get(uri, headers: await _headers(auth: auth))
        .timeout(_networkTimeout);
  }

  static Future<http.Response> _post(
    Uri uri, {
    bool auth = false,
    Object? body,
  }) async {
    return http
        .post(
          uri,
          headers: await _headers(auth: auth),
          body: body,
        )
        .timeout(_networkTimeout);
  }

  static Future<http.Response> _put(
    Uri uri, {
    bool auth = false,
    Object? body,
  }) async {
    return http
        .put(
          uri,
          headers: await _headers(auth: auth),
          body: body,
        )
        .timeout(_networkTimeout);
  }

  // ── Auth ────────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> login(
    String noHp,
    String password,
  ) async {
    final res = await _post(
      Uri.parse('$baseUrl/login'),
      body: jsonEncode({'no_hp': noHp, 'password': password}),
    );
    return _parse(res);
  }

  static Future<Map<String, dynamic>> register(
    String nama,
    String noHp,
    String password,
  ) async {
    final res = await _post(
      Uri.parse('$baseUrl/register'),
      body: jsonEncode({'nama': nama, 'no_hp': noHp, 'password': password}),
    );
    return _parse(res);
  }

  static Future<void> logout() async {
    final res = await _post(Uri.parse('$baseUrl/logout'), auth: true);
    _parse(res);
  }

  static Future<Map<String, dynamic>> me() async {
    final res = await _get(Uri.parse('$baseUrl/me'), auth: true);
    return _parse(res);
  }

  // ── Layanan ──────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getLayanan() async {
    final res = await _get(Uri.parse('$baseUrl/layanan'));
    return jsonDecode(res.body) as List;
  }

  // ── Tukang ───────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getTukang({String? q, String? layanan}) async {
    var uri = Uri.parse('$baseUrl/tukang').replace(
      queryParameters: {
        if (q != null && q.isNotEmpty) 'q': q,
        if (layanan != null && layanan.isNotEmpty) 'layanan': layanan,
      },
    );
    final res = await _get(uri);
    return jsonDecode(res.body) as List;
  }

  static Future<Map<String, dynamic>> getTukangDetail(int id) async {
    final res = await _get(Uri.parse('$baseUrl/tukang/$id'));
    return _parse(res);
  }

  static Future<List<dynamic>> getTukangByLayanan() async {
    final res = await _get(Uri.parse('$baseUrl/tukang/by-layanan'));
    return jsonDecode(res.body) as List;
  }

  // ── Orders ───────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getOrders() async {
    final res = await _get(Uri.parse('$baseUrl/orders'), auth: true);
    return jsonDecode(res.body) as List;
  }

  static Future<Map<String, dynamic>> createOrder(
    int idTukang,
    int idLayanan, {
    String? alamat,
    double? latitude,
    double? longitude,
    String? tanggalKerja,
    String? jamMulai,
    String? durasi,
    String? deskripsi,
    String? metodeBayar,
  }) async {
    final res = await _post(
      Uri.parse('$baseUrl/orders'),
      auth: true,
      body: jsonEncode({
        'id_tukang': idTukang,
        'id_layanan': idLayanan,
        if (alamat != null && alamat.isNotEmpty) 'alamat': alamat,
        if (latitude != null) 'latitude': latitude,
        if (longitude != null) 'longitude': longitude,
        if (tanggalKerja != null && tanggalKerja.isNotEmpty)
          'tanggal_kerja': tanggalKerja,
        if (jamMulai != null && jamMulai.isNotEmpty) 'jam_mulai': jamMulai,
        if (durasi != null && durasi.isNotEmpty) 'durasi': durasi,
        if (deskripsi != null && deskripsi.isNotEmpty) 'deskripsi': deskripsi,
        if (metodeBayar != null && metodeBayar.isNotEmpty)
          'metode_bayar': metodeBayar,
      }),
    );
    return _parse(res);
  }

  // ── Reviews ──────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> submitReview(
    int idOrder,
    int rating,
    String? komentar,
  ) async {
    final res = await _post(
      Uri.parse('$baseUrl/reviews/$idOrder'),
      auth: true,
      body: jsonEncode({'rating': rating, 'komentar': komentar}),
    );
    return _parse(res);
  }

  // ── Favorit ──────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getFavorit() async {
    final res = await _get(Uri.parse('$baseUrl/favorit'), auth: true);
    return jsonDecode(res.body) as List;
  }

  static Future<Map<String, dynamic>> toggleFavorit(int idTukang) async {
    final res = await _post(
      Uri.parse('$baseUrl/favorit/$idTukang'),
      auth: true,
    );
    return _parse(res);
  }

  static Future<bool> checkFavorit(int idTukang) async {
    final res = await _get(
      Uri.parse('$baseUrl/favorit/$idTukang/check'),
      auth: true,
    );
    final data = _parse(res);
    return data['favorited'] == true;
  }

  // ── Chat ─────────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getChatList() async {
    final res = await _get(Uri.parse('$baseUrl/chat'), auth: true);
    return jsonDecode(res.body) as List;
  }

  static Future<Map<String, dynamic>> getChatMessages(int idTukang) async {
    final res = await _get(Uri.parse('$baseUrl/chat/$idTukang'), auth: true);
    return _parse(res);
  }

  static Future<Map<String, dynamic>> sendMessage(
    int idTukang,
    String pesan,
  ) async {
    final res = await _post(
      Uri.parse('$baseUrl/chat/$idTukang'),
      auth: true,
      body: jsonEncode({'pesan': pesan}),
    );
    return _parse(res);
  }

  // ── Support Center ──────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> getSupportMessages() async {
    final res = await _get(Uri.parse('$baseUrl/support'), auth: true);
    return _parse(res);
  }

  static Future<Map<String, dynamic>> sendSupportMessage(
    String kategori,
    String pesan,
  ) async {
    final res = await _post(
      Uri.parse('$baseUrl/support'),
      auth: true,
      body: jsonEncode({'kategori': kategori, 'pesan': pesan}),
    );
    return _parse(res);
  }

  // ── Pembayaran ───────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> bayar(
    int idOrder,
    String nomorReferensi,
  ) async {
    final res = await _post(
      Uri.parse('$baseUrl/pembayaran/$idOrder/pay'),
      auth: true,
      body: jsonEncode({'nomor_referensi': nomorReferensi}),
    );
    return _parse(res);
  }

  static Future<Map<String, dynamic>> getPaymentStatus(int idOrder) async {
    final res = await _get(
      Uri.parse('$baseUrl/pembayaran/$idOrder/status'),
      auth: true,
    );
    return _parse(res);
  }

  // ── Profile ──────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> updateProfile({
    String? nama,
    String? noHp,
    String? alamat,
    String? password,
    String? passwordConfirmation,
  }) async {
    final body = <String, dynamic>{};
    if (nama != null && nama.isNotEmpty) body['nama'] = nama;
    if (noHp != null && noHp.isNotEmpty) body['no_hp'] = noHp;
    if (alamat != null) body['alamat'] = alamat;
    if (password != null && password.isNotEmpty) {
      body['password'] = password;
      body['password_confirmation'] = passwordConfirmation ?? '';
    }
    final res = await _put(
      Uri.parse('$baseUrl/profile'),
      auth: true,
      body: jsonEncode(body),
    );
    return _parse(res);
  }

  // ── Notifikasi ───────────────────────────────────────────────────────────

  static Future<List<dynamic>> getNotifikasi() async {
    final res = await _get(Uri.parse('$baseUrl/notifikasi'), auth: true);
    return jsonDecode(res.body) as List;
  }

  static Future<int> getUnreadCount() async {
    final res = await _get(
      Uri.parse('$baseUrl/notifikasi/unread-count'),
      auth: true,
    );
    final body = jsonDecode(res.body) as Map<String, dynamic>;
    return jsonInt(body['count']);
  }

  static Future<void> markNotifRead(int id) async {
    await _put(Uri.parse('$baseUrl/notifikasi/$id/read'), auth: true);
  }

  static Future<void> markAllNotifRead() async {
    await _put(Uri.parse('$baseUrl/notifikasi/read-all'), auth: true);
  }

  // ── FCM Token ────────────────────────────────────────────────────────────

  static Future<void> saveFcmToken(String fcmToken) async {
    try {
      await _post(
        Uri.parse('$baseUrl/fcm-token'),
        auth: true,
        body: jsonEncode({'token': fcmToken}),
      );
    } catch (_) {}
  }

  // ── Helper ───────────────────────────────────────────────────────────────

  static Map<String, dynamic> _parse(http.Response res) {
    final body = jsonDecode(res.body);
    if (res.statusCode >= 400) {
      final msg = body['message'] ?? 'Terjadi kesalahan.';
      throw ApiException(msg, res.statusCode);
    }
    return body as Map<String, dynamic>;
  }
}

class ApiException implements Exception {
  final String message;
  final int statusCode;
  ApiException(this.message, this.statusCode);

  @override
  String toString() => message;
}
