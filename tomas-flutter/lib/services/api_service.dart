import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';
import '../utils/json_value.dart';

class ApiService {
  static final String baseUrl = AppConfig.apiBaseUrl;

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

  // ── Auth ────────────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> login(
    String noHp,
    String password,
  ) async {
    final res = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: await _headers(),
      body: jsonEncode({'no_hp': noHp, 'password': password}),
    );
    return _parse(res);
  }

  static Future<Map<String, dynamic>> register(
    String nama,
    String noHp,
    String password,
  ) async {
    final res = await http.post(
      Uri.parse('$baseUrl/register'),
      headers: await _headers(),
      body: jsonEncode({'nama': nama, 'no_hp': noHp, 'password': password}),
    );
    return _parse(res);
  }

  static Future<void> logout() async {
    final res = await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: await _headers(auth: true),
    );
    _parse(res);
  }

  static Future<Map<String, dynamic>> me() async {
    final res = await http.get(
      Uri.parse('$baseUrl/me'),
      headers: await _headers(auth: true),
    );
    return _parse(res);
  }

  // ── Layanan ──────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getLayanan() async {
    final res = await http.get(
      Uri.parse('$baseUrl/layanan'),
      headers: await _headers(),
    );
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
    final res = await http.get(uri, headers: await _headers());
    return jsonDecode(res.body) as List;
  }

  static Future<Map<String, dynamic>> getTukangDetail(int id) async {
    final res = await http.get(
      Uri.parse('$baseUrl/tukang/$id'),
      headers: await _headers(),
    );
    return _parse(res);
  }

  static Future<List<dynamic>> getTukangByLayanan() async {
    final res = await http.get(
      Uri.parse('$baseUrl/tukang/by-layanan'),
      headers: await _headers(),
    );
    return jsonDecode(res.body) as List;
  }

  // ── Orders ───────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getOrders() async {
    final res = await http.get(
      Uri.parse('$baseUrl/orders'),
      headers: await _headers(auth: true),
    );
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
    final res = await http.post(
      Uri.parse('$baseUrl/orders'),
      headers: await _headers(auth: true),
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
    final res = await http.post(
      Uri.parse('$baseUrl/reviews/$idOrder'),
      headers: await _headers(auth: true),
      body: jsonEncode({'rating': rating, 'komentar': komentar}),
    );
    return _parse(res);
  }

  // ── Favorit ──────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getFavorit() async {
    final res = await http.get(
      Uri.parse('$baseUrl/favorit'),
      headers: await _headers(auth: true),
    );
    return jsonDecode(res.body) as List;
  }

  static Future<Map<String, dynamic>> toggleFavorit(int idTukang) async {
    final res = await http.post(
      Uri.parse('$baseUrl/favorit/$idTukang'),
      headers: await _headers(auth: true),
    );
    return _parse(res);
  }

  static Future<bool> checkFavorit(int idTukang) async {
    final res = await http.get(
      Uri.parse('$baseUrl/favorit/$idTukang/check'),
      headers: await _headers(auth: true),
    );
    final data = _parse(res);
    return data['favorited'] == true;
  }

  // ── Chat ─────────────────────────────────────────────────────────────────

  static Future<List<dynamic>> getChatList() async {
    final res = await http.get(
      Uri.parse('$baseUrl/chat'),
      headers: await _headers(auth: true),
    );
    return jsonDecode(res.body) as List;
  }

  static Future<Map<String, dynamic>> getChatMessages(int idTukang) async {
    final res = await http.get(
      Uri.parse('$baseUrl/chat/$idTukang'),
      headers: await _headers(auth: true),
    );
    return _parse(res);
  }

  static Future<Map<String, dynamic>> sendMessage(
    int idTukang,
    String pesan,
  ) async {
    final res = await http.post(
      Uri.parse('$baseUrl/chat/$idTukang'),
      headers: await _headers(auth: true),
      body: jsonEncode({'pesan': pesan}),
    );
    return _parse(res);
  }

  // ── Pembayaran ───────────────────────────────────────────────────────────

  static Future<Map<String, dynamic>> bayar(
    int idOrder,
    String nomorReferensi,
  ) async {
    final res = await http.post(
      Uri.parse('$baseUrl/pembayaran/$idOrder/pay'),
      headers: await _headers(auth: true),
      body: jsonEncode({'nomor_referensi': nomorReferensi}),
    );
    return _parse(res);
  }

  static Future<Map<String, dynamic>> getPaymentStatus(int idOrder) async {
    final res = await http.get(
      Uri.parse('$baseUrl/pembayaran/$idOrder/status'),
      headers: await _headers(auth: true),
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
    final res = await http.put(
      Uri.parse('$baseUrl/profile'),
      headers: await _headers(auth: true),
      body: jsonEncode(body),
    );
    return _parse(res);
  }

  // ── Notifikasi ───────────────────────────────────────────────────────────

  static Future<List<dynamic>> getNotifikasi() async {
    final res = await http.get(
      Uri.parse('$baseUrl/notifikasi'),
      headers: await _headers(auth: true),
    );
    return jsonDecode(res.body) as List;
  }

  static Future<int> getUnreadCount() async {
    final res = await http.get(
      Uri.parse('$baseUrl/notifikasi/unread-count'),
      headers: await _headers(auth: true),
    );
    final body = jsonDecode(res.body) as Map<String, dynamic>;
    return jsonInt(body['count']);
  }

  static Future<void> markNotifRead(int id) async {
    await http.put(
      Uri.parse('$baseUrl/notifikasi/$id/read'),
      headers: await _headers(auth: true),
    );
  }

  static Future<void> markAllNotifRead() async {
    await http.put(
      Uri.parse('$baseUrl/notifikasi/read-all'),
      headers: await _headers(auth: true),
    );
  }

  // ── FCM Token ────────────────────────────────────────────────────────────

  static Future<void> saveFcmToken(String fcmToken) async {
    try {
      await http.post(
        Uri.parse('$baseUrl/fcm-token'),
        headers: await _headers(auth: true),
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
