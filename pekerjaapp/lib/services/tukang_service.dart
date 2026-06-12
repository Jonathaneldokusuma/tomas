import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';
import '../utils/json_value.dart';

class TukangService {
  static final String baseUrl = AppConfig.apiBaseUrl;
  static const Duration _networkTimeout = Duration(seconds: 15);
  static const List<String> defaultCategories = [
    'Servis AC',
    'Instalasi Listrik',
    'Perbaikan Pipa & Plumbing',
    'Pengecatan Rumah',
    'Perbaikan Atap',
    'Bersih-Bersih Rumah',
    'Perbaikan Pintu & Jendela',
    'Taman & Lanskap',
  ];

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

  static Future<Map<String, String>> _multipartHeaders() async {
    final headers = await _headers();
    headers.remove('Content-Type');
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
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/register'),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonEncode(body),
        )
        .timeout(_networkTimeout);
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode >= 200 &&
        res.statusCode < 300 &&
        data['token'] != null) {
      await _persistSession(data);
    }
    return {'statusCode': res.statusCode, ...data};
  }

  static Future<Map<String, dynamic>> login({
    required String username,
    required String password,
  }) async {
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/login'),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonEncode({'username': username, 'password': password}),
        )
        .timeout(_networkTimeout);
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode == 200 && data['token'] != null) {
      await _persistSession(data);
    }
    return {'statusCode': res.statusCode, ...data};
  }

  static Future<void> _persistSession(Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    final tukang = data['tukang'] as Map<String, dynamic>? ?? {};
    await prefs.setString('tukang_token', jsonString(data['token']));
    await prefs.setString('tukang_nama', jsonString(tukang['nama']));
    await prefs.setInt('tukang_id', jsonInt(tukang['id_tukang']));
    await prefs.setString(
      'tukang_status_verifikasi',
      jsonString(tukang['status_verifikasi']),
    );
  }

  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('tukang_token');
    await prefs.remove('tukang_nama');
    await prefs.remove('tukang_id');
    await prefs.remove('tukang_status_verifikasi');
  }

  static Future<Map<String, dynamic>> getOrders() async {
    final res = await http
        .get(Uri.parse('$baseUrl/tukang/orders'), headers: await _headers())
        .timeout(_networkTimeout);
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> acceptOrder(int id) async {
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/orders/$id/accept'),
          headers: await _headers(),
        )
        .timeout(_networkTimeout);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> rejectOrder(
    int id, {
    String catatan = '',
  }) async {
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/orders/$id/reject'),
          headers: await _headers(),
          body: jsonEncode({'catatan': catatan}),
        )
        .timeout(_networkTimeout);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> updateStatus(
    int id,
    String status,
  ) async {
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/orders/$id/status'),
          headers: await _headers(),
          body: jsonEncode({'status': status}),
        )
        .timeout(_networkTimeout);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> confirmCompletion(int id) async {
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/orders/$id/confirm-completion'),
          headers: await _headers(),
        )
        .timeout(_networkTimeout);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> getProfile() async {
    final res = await http
        .get(Uri.parse('$baseUrl/tukang/profile'), headers: await _headers())
        .timeout(_networkTimeout);
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> updateProfile(
    Map<String, dynamic> data,
  ) async {
    final res = await http
        .put(
          Uri.parse('$baseUrl/tukang/profile'),
          headers: await _headers(),
          body: jsonEncode(data),
        )
        .timeout(_networkTimeout);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<List<String>> getAvailableCategories() async {
    try {
      final res = await http
          .get(Uri.parse('$baseUrl/layanan'), headers: await _headers())
          .timeout(_networkTimeout);
      final data = jsonDecode(res.body);
      final items = data is List ? data : (data['data'] as List? ?? []);
      final categories = items
          .map((item) {
            if (item is Map<String, dynamic>) {
              return item['nama_layanan']?.toString() ?? '';
            }
            if (item is Map) {
              return item['nama_layanan']?.toString() ?? '';
            }
            return item.toString();
          })
          .where((item) => item.trim().isNotEmpty)
          .toList();

      return categories.isNotEmpty
          ? categories
          : List<String>.from(defaultCategories);
    } catch (_) {
      return List<String>.from(defaultCategories);
    }
  }

  static Future<Map<String, dynamic>> addPortfolio({
    String? judul,
    String? deskripsi,
    required String mediaPath,
  }) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/tukang/portfolio'),
    );
    request.headers.addAll(await _multipartHeaders());
    if (judul != null && judul.trim().isNotEmpty) {
      request.fields['judul'] = judul.trim();
    }
    if (deskripsi != null && deskripsi.trim().isNotEmpty) {
      request.fields['deskripsi'] = deskripsi.trim();
    }
    request.files.add(await http.MultipartFile.fromPath('media', mediaPath));

    final streamed = await request.send().timeout(_networkTimeout);
    final res = await http.Response.fromStream(streamed);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> deletePortfolio(int id) async {
    final res = await http
        .delete(
          Uri.parse('$baseUrl/tukang/portfolio/$id'),
          headers: await _headers(),
        )
        .timeout(_networkTimeout);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  static Future<Map<String, dynamic>> getSupportMessages() async {
    final res = await http
        .get(Uri.parse('$baseUrl/tukang/support'), headers: await _headers())
        .timeout(_networkTimeout);
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> sendSupportMessage({
    required String kategori,
    required String pesan,
  }) async {
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/support'),
          headers: await _headers(),
          body: jsonEncode({'kategori': kategori, 'pesan': pesan}),
        )
        .timeout(_networkTimeout);
    return {'statusCode': res.statusCode, ...jsonDecode(res.body)};
  }

  // ── CHAT ─────────────────────────────────────────────────────

  static Future<List<dynamic>> getChatInbox() async {
    final res = await http
        .get(Uri.parse('$baseUrl/tukang/chat'), headers: await _headers())
        .timeout(_networkTimeout);
    final data = jsonDecode(res.body);
    return (data['conversations'] as List?) ?? [];
  }

  static Future<Map<String, dynamic>> getChatMessages(int idUser) async {
    final res = await http
        .get(
          Uri.parse('$baseUrl/tukang/chat/$idUser'),
          headers: await _headers(),
        )
        .timeout(_networkTimeout);
    return jsonDecode(res.body) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> sendChatMessage(
    int idUser,
    String pesan,
  ) async {
    final res = await http
        .post(
          Uri.parse('$baseUrl/tukang/chat/$idUser'),
          headers: await _headers(),
          body: jsonEncode({'pesan': pesan}),
        )
        .timeout(_networkTimeout);
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    return {'statusCode': res.statusCode, ...data};
  }

  // ── FCM Token ─────────────────────────────────────────────────────────────

  static Future<void> saveFcmToken(String fcmToken) async {
    try {
      await http
          .post(
            Uri.parse('$baseUrl/tukang/fcm-token'),
            headers: await _headers(),
            body: jsonEncode({'token': fcmToken}),
          )
          .timeout(_networkTimeout);
    } catch (_) {}
  }

  // ── BROADCAST (Pesan dari Pusat) ─────────────────────────────

  static Future<List<dynamic>> getBroadcasts() async {
    final res = await http
        .get(Uri.parse('$baseUrl/tukang/broadcast'), headers: await _headers())
        .timeout(_networkTimeout);
    final data = jsonDecode(res.body);
    return (data['broadcasts'] as List?) ?? [];
  }
}
