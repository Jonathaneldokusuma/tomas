import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  Map<String, dynamic>? _user;
  bool _loading = false;

  Map<String, dynamic>? get user => _user;
  bool get loading => _loading;
  bool get isLoggedIn => _user != null;

  Future<void> tryAutoLogin() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    if (token == null) return;
    try {
      _user = await ApiService.me();
      notifyListeners();
    } catch (_) {
      await prefs.remove('token');
    }
  }

  Future<void> login(String noHp, String password) async {
    _loading = true;
    notifyListeners();
    try {
      final res = await ApiService.login(noHp, password);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', res['token']);
      _user = res['user'] as Map<String, dynamic>;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> register(String nama, String noHp, String password) async {
    _loading = true;
    notifyListeners();
    try {
      final res = await ApiService.register(nama, noHp, password);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', res['token']);
      _user = res['user'] as Map<String, dynamic>;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    try {
      await ApiService.logout();
    } catch (_) {}
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    _user = null;
    notifyListeners();
  }

  Future<void> updateUser(Map<String, dynamic> updated) async {
    _user = updated;
    notifyListeners();
  }
}
