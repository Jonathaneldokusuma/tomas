import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/tukang_service.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});
  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? _profile;
  bool _loading = true;
  bool _saving = false;
  bool _editing = false;
  String? _error;

  final _namaCtrl = TextEditingController();
  final _noHpCtrl = TextEditingController();
  final _bioCtrl = TextEditingController();
  final _kategoriCtrl = TextEditingController();
  final _tarifCtrl = TextEditingController();
  bool _statusAktif = false;

  @override
  void initState() { super.initState(); _load(); }

  @override
  void dispose() {
    _namaCtrl.dispose(); _noHpCtrl.dispose(); _bioCtrl.dispose();
    _kategoriCtrl.dispose(); _tarifCtrl.dispose();
    super.dispose();
  }

  void _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final res = await TukangService.getProfile();
      if (res['tukang'] != null) {
        final p = res['tukang'] as Map<String, dynamic>;
        setState(() {
          _profile = p;
          _namaCtrl.text = p['nama'] ?? '';
          _noHpCtrl.text = p['no_hp'] ?? '';
          _bioCtrl.text = p['bio'] ?? '';
          _kategoriCtrl.text = p['kategori'] ?? '';
          _tarifCtrl.text = (p['tarif'] ?? '').toString();
          _statusAktif = p['status_aktif'] == 1 || p['status_aktif'] == true;
          _loading = false;
        });
      } else {
        setState(() { _error = res['message'] ?? 'Gagal memuat profil'; _loading = false; });
      }
    } catch (e) {
      setState(() { _error = 'Tidak dapat terhubung ke server'; _loading = false; });
    }
  }

  void _save() async {
    setState(() => _saving = true);
    final res = await TukangService.updateProfile({
      'nama': _namaCtrl.text,
      'no_hp': _noHpCtrl.text,
      'bio': _bioCtrl.text,
      'kategori': _kategoriCtrl.text,
      'tarif': _tarifCtrl.text,
      'status_aktif': _statusAktif ? 1 : 0,
    });
    setState(() { _saving = false; _editing = false; });
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Profil diperbarui')));
    _load();
  }

  void _logout() async {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Kamu yakin ingin keluar?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await TukangService.logout();
              if (!mounted) return;
              Navigator.pushReplacementNamed(context, '/login');
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBlue, foregroundColor: Colors.white, elevation: 0,
        title: const Text('Profil Saya', style: TextStyle(fontWeight: FontWeight.bold)),
        actions: [
          if (!_loading)
            IconButton(
              icon: Icon(_editing ? Icons.close : Icons.edit_outlined),
              onPressed: () => setState(() => _editing = !_editing),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: _kBlue))
          : _error != null
              ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(Icons.wifi_off, size: 48, color: Colors.grey),
                  const SizedBox(height: 12),
                  Text(_error!, style: const TextStyle(color: Colors.grey)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _load, child: const Text('Coba Lagi')),
                ]))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(children: [
                    // Avatar
                    Container(
                      width: 80, height: 80,
                      decoration: BoxDecoration(color: _kBlue, shape: BoxShape.circle),
                      child: const Icon(Icons.person, color: Colors.white, size: 44),
                    ),
                    const SizedBox(height: 12),
                    Text(_profile?['nama'] ?? '-', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    Text('@${_profile?['username'] ?? '-'}', style: const TextStyle(fontSize: 13, color: Colors.grey)),
                    const SizedBox(height: 20),

                    // Status aktif toggle
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                      decoration: BoxDecoration(
                        color: _statusAktif ? const Color(0xFFF0FDF4) : const Color(0xFFF9FAFB),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: _statusAktif ? Colors.green.shade200 : const Color(0xFFE5E7EB)),
                      ),
                      child: Row(children: [
                        Icon(_statusAktif ? Icons.circle : Icons.circle_outlined, color: _statusAktif ? Colors.green : Colors.grey, size: 14),
                        const SizedBox(width: 10),
                        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text(_statusAktif ? 'Sedang Aktif' : 'Sedang Tidak Aktif', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _statusAktif ? Colors.green : Colors.grey)),
                          Text(_statusAktif ? 'Kamu bisa menerima pesanan' : 'Kamu tidak akan menerima pesanan', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                        ])),
                        Switch(value: _statusAktif, onChanged: (v) => setState(() => _statusAktif = v), activeColor: Colors.green),
                      ]),
                    ),
                    const SizedBox(height: 18),

                    // Form
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))]),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        _field('Nama Lengkap', _namaCtrl, Icons.person_outline),
                        const SizedBox(height: 14),
                        _field('No. HP', _noHpCtrl, Icons.phone_outlined, type: TextInputType.phone),
                        const SizedBox(height: 14),
                        _field('Kategori Pekerjaan', _kategoriCtrl, Icons.work_outline),
                        const SizedBox(height: 14),
                        _field('Tarif per Jam (Rp)', _tarifCtrl, Icons.attach_money, type: TextInputType.number),
                        const SizedBox(height: 14),
                        _label('Bio / Tentang Saya'),
                        TextField(
                          controller: _bioCtrl,
                          enabled: _editing,
                          maxLines: 3,
                          decoration: _dec('Ceritakan sedikit tentang kamu...', Icons.info_outline),
                        ),
                        if (_editing) ...[
                          const SizedBox(height: 20),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton(
                              onPressed: _saving ? null : _save,
                              style: ElevatedButton.styleFrom(backgroundColor: _kBlue, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)), elevation: 0),
                              child: _saving ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : const Text('Simpan Perubahan', style: TextStyle(fontWeight: FontWeight.bold)),
                            ),
                          ),
                        ],
                      ]),
                    ),
                    const SizedBox(height: 24),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: _logout,
                        icon: const Icon(Icons.logout, color: Colors.red),
                        label: const Text('Logout', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
                        style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14), side: const BorderSide(color: Colors.red), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                      ),
                    ),
                    const SizedBox(height: 30),
                  ]),
                ),
    );
  }

  Widget _label(String t) => Padding(padding: const EdgeInsets.only(bottom: 6), child: Text(t, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12, color: Color(0xFF374151))));

  Widget _field(String label, TextEditingController c, IconData icon, {TextInputType? type}) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      _label(label),
      TextField(controller: c, enabled: _editing, keyboardType: type, decoration: _dec('', icon)),
    ],
  );

  InputDecoration _dec(String hint, IconData icon) => InputDecoration(
    hintText: hint,
    prefixIcon: Icon(icon, color: _kBlue, size: 18),
    filled: true, fillColor: _editing ? const Color(0xFFF9FAFB) : const Color(0xFFF3F4F6),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
    disabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: _kBlue)),
    contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
  );
}
