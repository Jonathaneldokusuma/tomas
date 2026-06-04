import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';

class TukangService {
  static final String baseUrl = AppConfig.apiBaseUrl;

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('tukang_token');
  }

  static Future<Map<String, String>> _headers() async {
    final token = await getToken();
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token != null) {
      headers['X-Tukang-Token'] = token;
    }
    return headers;
  }

  static Future<Map<String, dynamic>> register({
    required String nama,
    required String username,
    required String noHp,
    required String password,
    String? noKtp,
    String? alamat,
    double? latitude,
    double? longitude,
  }) async {
    final body = <String, dynamic>{
      'nama': nama,
      'username': username,
      'no_hp': noHp,
      'password': password,
    };
    if (noKtp != null && noKtp.isNotEmpty) {
      body['no_ktp'] = noKtp;
    }
    if (alamat != null && alamat.isNotEmpty) {
      body['alamat'] = alamat;
    }
    if (latitude != null) {
      body['latitude'] = latitude;
    }
    if (longitude != null) {
      body['longitude'] = longitude;
    }
    final res = await http.post(
      Uri.parse('$baseUrl/tukang/register'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(body),
    );
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> login({
    required String username,
    required String password,
  }) async {
    final res = await http.post(
      Uri.parse('$baseUrl/tukang/login'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'username': username, 'password': password}),
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode == 200 && data['token'] != null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('tukang_token', data['token']);
      await prefs.setString('tukang_nama', data['tukang']['nama'] ?? '');
      await prefs.setInt('tukang_id', data['tukang']['id_tukang'] ?? 0);
    }
    return {'statusCode': res.statusCode, ...data};
  }

  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('tukang_token');
    await prefs.remove('tukang_nama');
    await prefs.remove('tukang_id');
  }

  static Future<Map<String, dynamic>> getOrders() async {
    final res = await http.get(
      Uri.parse('$baseUrl/tukang/orders'),
      headers: await _headers(),
    );
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> acceptOrder(int id) async {
    final res = await http.post(
      Uri.parse('$baseUrl/tukang/orders/$id/accept'),
      headers: await _headers(),
    );
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> rejectOrder(
    int id, {
    String catatan = '',
  }) async {
    final res = await http.post(
      Uri.parse('$baseUrl/tukang/orders/$id/reject'),
      headers: await _headers(),
      body: jsonEncode({'catatan': catatan}),
    );
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> updateStatus(
    int id,
    String status,
  ) async {
    final res = await http.post(
      Uri.parse('$baseUrl/tukang/orders/$id/status'),
      headers: await _headers(),
      body: jsonEncode({'status': status}),
    );
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> confirmPayment(int id) async {
    final res = await http.post(
      Uri.parse('$baseUrl/tukang/orders/$id/confirm-payment'),
      headers: await _headers(),
    );
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> getProfile() async {
    final res = await http.get(
      Uri.parse('$baseUrl/tukang/profile'),
      headers: await _headers(),
    );
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> updateProfile(
    Map<String, dynamic> data,
  ) async {
    final res = await http.put(
      Uri.parse('$baseUrl/tukang/profile'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  // ── CHAT ─────────────────────────────────────────────────────

  static Future<List<dynamic>> getChatInbox() async {
    final res = await http.get(
      Uri.parse('$baseUrl/tukang/chat'),
      headers: await _headers(),
    );
    final data = jsonDecode(res.body);
    return (data['conversations'] as List?) ?? [];
  }

  static Future<Map<String, dynamic>> getChatMessages(int idUser) async {
    final res = await http.get(
      Uri.parse('$baseUrl/tukang/chat/$idUser'),
      headers: await _headers(),
    );
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> sendChatMessage(
    int idUser,
    String pesan,
  ) async {
    final res = await http.post(
      Uri.parse('$baseUrl/tukang/chat/$idUser'),
      headers: await _headers(),
      body: jsonEncode({'pesan': pesan}),
    );
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return {'statusCode': res.statusCode, ...data};
  }

  // ── FCM Token ─────────────────────────────────────────────────────────────

  static Future<void> saveFcmToken(String fcmToken) async {
    try {
      await http.post(
        Uri.parse('$baseUrl/tukang/fcm-token'),
        headers: await _headers(),
        body: jsonEncode({'token': fcmToken}),
      );
    } catch (_) {}
  }

  // ── BROADCAST (Pesan dari Pusat) ─────────────────────────────

  static Future<List<dynamic>> getBroadcasts() async {
    final res = await http.get(
      Uri.parse('$baseUrl/tukang/broadcast'),
      headers: await _headers(),
    );
    final data = jsonDecode(res.body);
    return (data['broadcasts'] as List?) ?? [];
  }
}
