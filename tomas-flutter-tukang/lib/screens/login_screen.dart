import 'package:flutter/material.dart';
import '../services/tukang_service.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  bool _loading = false;
  bool _showPass = false;
  String? _error;

  @override
  void dispose() {
    _usernameCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  void _login() async {
    if (_usernameCtrl.text.isEmpty || _passwordCtrl.text.isEmpty) {
      setState(() => _error = 'Username dan password wajib diisi!');
      return;
    }
    setState(() { _loading = true; _error = null; });
    try {
      final res = await TukangService.login(
        username: _usernameCtrl.text.trim(),
        password: _passwordCtrl.text,
      );
      if (!mounted) return;
      if (res['statusCode'] == 200) {
        Navigator.pushReplacementNamed(context, '/dashboard');
      } else if (res['statusCode'] == 403) {
        Navigator.pushReplacementNamed(context, '/waiting-verification');
      } else {
        setState(() => _error = res['message'] ?? 'Login gagal');
      }
    } catch (e) {
      setState(() => _error = 'Tidak dapat terhubung ke server');
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 40),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 24),
              Center(
                child: Container(
                  width: 80, height: 80,
                  decoration: BoxDecoration(color: _kBlue, borderRadius: BorderRadius.circular(20)),
                  child: const Icon(Icons.construction, color: Colors.white, size: 44),
                ),
              ),
              const SizedBox(height: 24),
              const Center(child: Text('Tomas Tukang', style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold, color: Color(0xFF1F2937)))),
              const Center(child: Text('Masuk ke akun pekerja kamu', style: TextStyle(fontSize: 14, color: Colors.grey))),
              const SizedBox(height: 40),
              if (_error != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(color: const Color(0xFFFEF2F2), borderRadius: BorderRadius.circular(10), border: Border.all(color: const Color(0xFFFCA5A5))),
                  child: Row(children: [
                    const Icon(Icons.error_outline, color: Color(0xFFEF4444), size: 18),
                    const SizedBox(width: 8),
                    Expanded(child: Text(_error!, style: const TextStyle(fontSize: 13, color: Color(0xFFB91C1C)))),
                  ]),
                ),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 4))],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _label('Username'),
                    _field(_usernameCtrl, 'Masukkan username', Icons.person_outline),
                    const SizedBox(height: 16),
                    _label('Password'),
                    TextField(
                      controller: _passwordCtrl,
                      obscureText: !_showPass,
                      decoration: _dec('Masukkan password', Icons.lock_outline).copyWith(
                        suffixIcon: IconButton(
                          icon: Icon(_showPass ? Icons.visibility_off : Icons.visibility, color: Colors.grey),
                          onPressed: () => setState(() => _showPass = !_showPass),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _loading ? null : _login,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: _kBlue, foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          elevation: 0,
                        ),
                        child: _loading
                            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : const Text('Masuk', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              Center(
                child: GestureDetector(
                  onTap: () => Navigator.pushNamed(context, '/register'),
                  child: RichText(
                    text: const TextSpan(
                      text: 'Belum punya akun? ',
                      style: TextStyle(color: Colors.grey, fontSize: 14),
                      children: [TextSpan(text: 'Daftar Sekarang', style: TextStyle(color: _kBlue, fontWeight: FontWeight.bold))],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _label(String t) => Padding(padding: const EdgeInsets.only(bottom: 6), child: Text(t, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)));

  Widget _field(TextEditingController c, String hint, IconData icon, {TextInputType? type}) =>
      TextField(controller: c, keyboardType: type, decoration: _dec(hint, icon));

  InputDecoration _dec(String hint, IconData icon) => InputDecoration(
    hintText: hint,
    prefixIcon: Icon(icon, color: _kBlue),
    filled: true, fillColor: const Color(0xFFF9FAFB),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: _kBlue)),
    contentPadding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
  );
}
