import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:latlong2/latlong.dart';
import '../services/notification_service.dart';
import '../services/tukang_service.dart';
import 'location_picker_screen.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _namaCtrl = TextEditingController();
  final _usernameCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _noHpCtrl = TextEditingController();
  final _noKtpCtrl = TextEditingController();
  final _alamatCtrl = TextEditingController();
  bool _loading = false;
  bool _showPass = false;
  String? _error;
  LatLng? _pickedLocation;

  @override
  void dispose() {
    _namaCtrl.dispose();
    _usernameCtrl.dispose();
    _passwordCtrl.dispose();
    _noHpCtrl.dispose();
    _noKtpCtrl.dispose();
    _alamatCtrl.dispose();
    super.dispose();
  }

  void _pickLocation() async {
    final result = await Navigator.push<LatLng>(
      context,
      MaterialPageRoute(
        builder: (_) => LocationPickerScreen(
          initialLat: _pickedLocation?.latitude,
          initialLng: _pickedLocation?.longitude,
        ),
      ),
    );
    if (result != null) setState(() => _pickedLocation = result);
  }

  void _register() async {
    final nama = _namaCtrl.text.trim();
    final username = _usernameCtrl.text.trim();
    final noHp = _noHpCtrl.text.trim();
    final noKtp = _noKtpCtrl.text.trim();
    final alamat = _alamatCtrl.text.trim();
    final password = _passwordCtrl.text;

    if (nama.isEmpty ||
        username.isEmpty ||
        password.isEmpty ||
        noHp.isEmpty ||
        noKtp.isEmpty ||
        alamat.isEmpty) {
      setState(() => _error = 'Semua data wajib diisi!');
      return;
    }
    if (noKtp.length != 16) {
      setState(() => _error = 'Nomor KTP harus 16 digit.');
      return;
    }
    if (noHp.length < 9) {
      setState(() => _error = 'Nomor HP tidak valid.');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await TukangService.register(
        nama: nama,
        username: username,
        noHp: noHp,
        noKtp: noKtp,
        alamat: alamat,
        password: password,
        latitude: _pickedLocation?.latitude,
        longitude: _pickedLocation?.longitude,
      );
      if (!mounted) return;
      if (res['tukang'] != null) {
        await NotificationService.saveFcmTokenToServer();
        if (!mounted) return;
        Navigator.pushReplacementNamed(context, '/ktp-upload');
      } else {
        final errors = res['errors'];
        if (errors != null) {
          final msg = (errors as Map).values
              .map((v) => (v as List).first.toString())
              .join('\n');
          setState(() => _error = msg);
        } else {
          setState(() => _error = res['message'] ?? 'Pendaftaran gagal');
        }
      }
    } catch (_) {
      if (mounted) setState(() => _error = 'Tidak dapat terhubung ke server');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              GestureDetector(
                onTap: () => Navigator.pushReplacementNamed(context, '/login'),
                child: const Row(
                  children: [
                    Icon(Icons.arrow_back_ios, size: 16, color: _kBlue),
                    Text(
                      'Kembali ke Login',
                      style: TextStyle(color: _kBlue, fontSize: 13),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              Center(
                child: Container(
                  width: 70,
                  height: 70,
                  decoration: BoxDecoration(
                    color: _kBlue,
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: const Icon(
                    Icons.construction,
                    color: Colors.white,
                    size: 38,
                  ),
                ),
              ),
              const SizedBox(height: 14),
              const Center(
                child: Text(
                  'Daftar sebagai Tukang',
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1F2937),
                  ),
                ),
              ),
              const Center(
                child: Text(
                  'Isi data diri lengkap untuk mendaftar',
                  style: TextStyle(fontSize: 13, color: Colors.grey),
                ),
              ),
              const SizedBox(height: 24),
              // Step indicator
              Row(
                children: [
                  _step('1', 'Data Diri', active: true),
                  Expanded(
                    child: Container(height: 2, color: const Color(0xFFE5E7EB)),
                  ),
                  _step('2', 'Upload KTP'),
                  Expanded(
                    child: Container(height: 2, color: const Color(0xFFE5E7EB)),
                  ),
                  _step('3', 'Verifikasi'),
                ],
              ),
              const SizedBox(height: 24),
              if (_error != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFFCA5A5)),
                  ),
                  child: Text(
                    _error!,
                    style: const TextStyle(
                      fontSize: 13,
                      color: Color(0xFFB91C1C),
                    ),
                  ),
                ),

              // ── Informasi Pribadi ─────────────────────────
              _sectionTitle('Informasi Pribadi'),
              Container(
                padding: const EdgeInsets.all(18),
                decoration: _cardDeco(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _label('Nama Lengkap *'),
                    _field(
                      _namaCtrl,
                      'Masukkan nama lengkap',
                      Icons.person_outline,
                    ),
                    const SizedBox(height: 14),
                    _label('Username *'),
                    _field(
                      _usernameCtrl,
                      'Buat username unik',
                      Icons.alternate_email,
                    ),
                    const SizedBox(height: 14),
                    _label('No. HP / WhatsApp *'),
                    _field(
                      _noHpCtrl,
                      'Contoh: 08123456789',
                      Icons.phone_outlined,
                      type: TextInputType.phone,
                      inputFormatters: _digitsOnly,
                    ),
                    const SizedBox(height: 14),
                    _label('Nomor KTP (NIK) *'),
                    _field(
                      _noKtpCtrl,
                      '16 digit nomor KTP',
                      Icons.credit_card_outlined,
                      type: TextInputType.number,
                      inputFormatters: _nikFormatters,
                    ),
                    const SizedBox(height: 14),
                    _label('Password *'),
                    TextField(
                      controller: _passwordCtrl,
                      obscureText: !_showPass,
                      decoration: _dec('Min. 6 karakter', Icons.lock_outline)
                          .copyWith(
                            suffixIcon: IconButton(
                              icon: Icon(
                                _showPass
                                    ? Icons.visibility_off
                                    : Icons.visibility,
                                color: Colors.grey,
                                size: 20,
                              ),
                              onPressed: () =>
                                  setState(() => _showPass = !_showPass),
                            ),
                          ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // ── Alamat & Lokasi ───────────────────────────
              _sectionTitle('Alamat & Lokasi'),
              Container(
                padding: const EdgeInsets.all(18),
                decoration: _cardDeco(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _label('Alamat Lengkap *'),
                    TextField(
                      controller: _alamatCtrl,
                      maxLines: 3,
                      decoration: _dec(
                        'Jl. Contoh No. 1, Kota, Provinsi',
                        Icons.home_outlined,
                      ),
                    ),
                    const SizedBox(height: 16),
                    _label('Lokasi di Peta (opsional)'),
                    const SizedBox(height: 6),
                    GestureDetector(
                      onTap: _pickLocation,
                      child: Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: _pickedLocation != null
                              ? const Color(0xFFEFF6FF)
                              : const Color(0xFFF9FAFB),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: _pickedLocation != null
                                ? _kBlue
                                : const Color(0xFFE5E7EB),
                            width: _pickedLocation != null ? 1.5 : 1,
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 36,
                              height: 36,
                              decoration: BoxDecoration(
                                color: _pickedLocation != null
                                    ? _kBlue
                                    : const Color(0xFFE5E7EB),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Icon(
                                Icons.map_outlined,
                                color: _pickedLocation != null
                                    ? Colors.white
                                    : Colors.grey,
                                size: 20,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    _pickedLocation != null
                                        ? 'Lokasi Dipilih ✓'
                                        : 'Pilih Lokasi di Peta',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w600,
                                      fontSize: 13,
                                      color: _pickedLocation != null
                                          ? _kBlue
                                          : const Color(0xFF374151),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    _pickedLocation != null
                                        ? '${_pickedLocation!.latitude.toStringAsFixed(5)}, ${_pickedLocation!.longitude.toStringAsFixed(5)}'
                                        : 'Memudahkan pelanggan menemukan area kerja kamu',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: Colors.grey,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const Icon(
                              Icons.arrow_forward_ios,
                              size: 14,
                              color: Colors.grey,
                            ),
                          ],
                        ),
                      ),
                    ),
                    if (_pickedLocation != null) ...[
                      const SizedBox(height: 8),
                      GestureDetector(
                        onTap: () => setState(() => _pickedLocation = null),
                        child: const Row(
                          children: [
                            Icon(Icons.close, size: 14, color: Colors.grey),
                            SizedBox(width: 4),
                            Text(
                              'Hapus lokasi',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _loading ? null : _register,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _kBlue,
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: const Color(0xFFBFDBFE),
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 0,
                  ),
                  child: _loading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              'Lanjut Upload KTP',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            SizedBox(width: 8),
                            Icon(Icons.arrow_forward, size: 18),
                          ],
                        ),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _sectionTitle(String t) => Padding(
    padding: const EdgeInsets.only(bottom: 8),
    child: Text(
      t,
      style: const TextStyle(
        fontWeight: FontWeight.bold,
        fontSize: 14,
        color: Color(0xFF374151),
      ),
    ),
  );

  BoxDecoration _cardDeco() => BoxDecoration(
    color: Colors.white,
    borderRadius: BorderRadius.circular(14),
    boxShadow: [
      BoxShadow(
        color: Colors.black.withOpacity(0.05),
        blurRadius: 8,
        offset: const Offset(0, 2),
      ),
    ],
  );

  Widget _label(String t) => Padding(
    padding: const EdgeInsets.only(bottom: 6),
    child: Text(
      t,
      style: const TextStyle(
        fontWeight: FontWeight.w600,
        fontSize: 12,
        color: Color(0xFF374151),
      ),
    ),
  );

  List<TextInputFormatter> get _digitsOnly => [
    FilteringTextInputFormatter.digitsOnly,
  ];

  List<TextInputFormatter> get _nikFormatters => [
    FilteringTextInputFormatter.digitsOnly,
    LengthLimitingTextInputFormatter(16),
  ];

  Widget _field(
    TextEditingController c,
    String hint,
    IconData icon, {
    TextInputType? type,
    List<TextInputFormatter>? inputFormatters,
  }) => TextField(
    controller: c,
    keyboardType: type,
    inputFormatters: inputFormatters,
    decoration: _dec(hint, icon),
  );

  InputDecoration _dec(String hint, IconData icon) => InputDecoration(
    hintText: hint,
    hintStyle: const TextStyle(color: Colors.grey, fontSize: 13),
    prefixIcon: Icon(icon, color: _kBlue, size: 20),
    filled: true,
    fillColor: const Color(0xFFF9FAFB),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: const BorderSide(color: _kBlue),
    ),
    contentPadding: const EdgeInsets.symmetric(vertical: 13, horizontal: 12),
  );

  Widget _step(
    String num,
    String label, {
    bool active = false,
    bool done = false,
  }) => Column(
    children: [
      Container(
        width: 32,
        height: 32,
        decoration: BoxDecoration(
          color: done
              ? Colors.green
              : (active ? _kBlue : const Color(0xFFE5E7EB)),
          shape: BoxShape.circle,
        ),
        child: Center(
          child: done
              ? const Icon(Icons.check, color: Colors.white, size: 16)
              : Text(
                  num,
                  style: TextStyle(
                    color: active ? Colors.white : Colors.grey,
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
        ),
      ),
      const SizedBox(height: 4),
      Text(
        label,
        style: TextStyle(
          fontSize: 10,
          color: active ? _kBlue : (done ? Colors.green : Colors.grey),
        ),
      ),
    ],
  );
}
