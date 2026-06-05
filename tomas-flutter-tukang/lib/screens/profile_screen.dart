import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/tukang_service.dart';
import '../utils/json_value.dart';
import 'chat/chat_list_screen.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _picker = ImagePicker();

  Map<String, dynamic>? _profile;
  Map<String, dynamic> _stats = {};
  List<Map<String, dynamic>> _badges = [];
  List<Map<String, dynamic>> _portfolio = [];
  List<String> _categories = [];

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
  String? _selectedKategori;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _namaCtrl.dispose();
    _noHpCtrl.dispose();
    _bioCtrl.dispose();
    _kategoriCtrl.dispose();
    _tarifCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final res = await TukangService.getProfile();
      final tukang = Map<String, dynamic>.from((res['tukang'] as Map?) ?? {});
      final stats = Map<String, dynamic>.from((res['stats'] as Map?) ?? {});
      final badges = (res['badges'] as List? ?? [])
          .whereType<Map>()
          .map((item) => Map<String, dynamic>.from(item))
          .toList();
      final portfolio = (res['portfolio'] as List? ?? [])
          .whereType<Map>()
          .map((item) => Map<String, dynamic>.from(item))
          .toList();
      final categories = (res['categories'] as List? ?? [])
          .map((item) => item.toString())
          .where((item) => item.trim().isNotEmpty)
          .toList();

      final availableCategories = categories.isNotEmpty
          ? categories
          : List<String>.from(TukangService.defaultCategories);

      final currentKategori = jsonStringOrNull(tukang['kategori']) ?? '';
      if (currentKategori.isNotEmpty &&
          !availableCategories.contains(currentKategori)) {
        availableCategories.insert(0, currentKategori);
      }

      setState(() {
        _profile = tukang;
        _stats = stats;
        _badges = badges;
        _portfolio = portfolio;
        _categories = availableCategories;
        _namaCtrl.text = jsonString(tukang['nama']);
        _noHpCtrl.text = jsonString(tukang['no_hp']);
        _bioCtrl.text = jsonString(tukang['bio']);
        _kategoriCtrl.text = currentKategori;
        _tarifCtrl.text = jsonDouble(tukang['tarif']).toStringAsFixed(0);
        _selectedKategori = currentKategori.isNotEmpty
            ? currentKategori
            : availableCategories.first;
        _statusAktif = jsonBool(tukang['status_aktif']);
        _loading = false;
      });
    } catch (_) {
      setState(() {
        _error = 'Tidak dapat terhubung ke server';
        _loading = false;
      });
    }
  }

  Future<void> _save() async {
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _saving = true);

    final kategori = _categories.isNotEmpty
        ? (_selectedKategori ?? _kategoriCtrl.text.trim())
        : _kategoriCtrl.text.trim();

    final res = await TukangService.updateProfile({
      'nama': _namaCtrl.text.trim(),
      'no_hp': _noHpCtrl.text.trim(),
      'bio': _bioCtrl.text.trim(),
      'kategori': kategori,
      'tarif': _tarifCtrl.text.trim(),
      'status_aktif': _statusAktif ? 1 : 0,
    });

    if (!mounted) return;
    setState(() => _saving = false);
    messenger.showSnackBar(
      SnackBar(content: Text(res['message'] ?? 'Profil diperbarui')),
    );
    setState(() => _editing = false);
    _load();
  }

  Future<void> _deletePortfolioItem(int id) async {
    final messenger = ScaffoldMessenger.of(context);
    final shouldDelete = await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            title: const Text('Hapus portofolio?'),
            content: const Text('Item portofolio ini akan dihapus dari profil publik.'),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx, false),
                child: const Text('Batal'),
              ),
              TextButton(
                onPressed: () => Navigator.pop(ctx, true),
                style: TextButton.styleFrom(foregroundColor: Colors.red),
                child: const Text('Hapus'),
              ),
            ],
          ),
        ) ??
        false;

    if (!shouldDelete) return;

    final res = await TukangService.deletePortfolio(id);
    if (!mounted) return;
    messenger.showSnackBar(
      SnackBar(content: Text(res['message'] ?? 'Portofolio dihapus')),
    );
    _load();
  }

  Future<void> _openPortfolioEditor() async {
    final titleCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    XFile? pickedImage;
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) {
        bool uploading = false;

        Future<void> pickImage(StateSetter setModalState) async {
          final image = await _picker.pickImage(
            source: ImageSource.gallery,
            imageQuality: 85,
          );
          if (image != null) {
            setModalState(() => pickedImage = image);
          }
        }

        return StatefulBuilder(
          builder: (ctx, setModalState) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
              ),
              padding: EdgeInsets.only(
                left: 16,
                right: 16,
                top: 16,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 16,
              ),
              child: SafeArea(
                top: false,
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Center(
                        child: Container(
                          width: 44,
                          height: 4,
                          decoration: BoxDecoration(
                            color: Colors.grey.shade300,
                            borderRadius: BorderRadius.circular(999),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'Tambah Portofolio',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Upload foto hasil kerja supaya profil kamu terlihat lebih meyakinkan.',
                        style: TextStyle(color: Colors.grey.shade600, height: 1.4),
                      ),
                      const SizedBox(height: 16),
                      GestureDetector(
                        onTap: uploading ? null : () => pickImage(setModalState),
                        child: Container(
                          height: 180,
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: Colors.grey.shade200),
                          ),
                          child: pickedImage == null
                              ? Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(
                                      Icons.add_photo_alternate_outlined,
                                      size: 42,
                                      color: Colors.grey.shade400,
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      'Pilih foto portofolio',
                                      style: TextStyle(color: Colors.grey.shade600),
                                    ),
                                  ],
                                )
                              : ClipRRect(
                                  borderRadius: BorderRadius.circular(16),
                                  child: Image.file(
                                    File(pickedImage!.path),
                                    width: double.infinity,
                                    height: 180,
                                    fit: BoxFit.cover,
                                  ),
                                ),
                        ),
                      ),
                      const SizedBox(height: 14),
                      TextField(
                        controller: titleCtrl,
                        decoration: InputDecoration(
                          labelText: 'Judul portofolio',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: descCtrl,
                        maxLines: 4,
                        decoration: InputDecoration(
                          labelText: 'Deskripsi singkat',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: uploading
                              ? null
                              : () async {
                                  if (pickedImage == null) {
                                    messenger.showSnackBar(
                                      const SnackBar(
                                        content: Text('Pilih foto portofolio dulu.'),
                                      ),
                                    );
                                    return;
                                  }

                                  setModalState(() => uploading = true);
                                  try {
                                    final res = await TukangService.addPortfolio(
                                      judul: titleCtrl.text.trim(),
                                      deskripsi: descCtrl.text.trim(),
                                      mediaPath: pickedImage!.path,
                                    );

                                    if (!mounted) return;
                                    if ((res['statusCode'] ?? 0) >= 200 &&
                                        (res['statusCode'] ?? 0) < 300) {
                                      navigator.pop();
                                      messenger.showSnackBar(
                                        SnackBar(
                                          content: Text(
                                            res['message'] ?? 'Portofolio ditambahkan',
                                          ),
                                        ),
                                      );
                                      _load();
                                    } else {
                                      setModalState(() => uploading = false);
                                      messenger.showSnackBar(
                                        SnackBar(
                                          content: Text(
                                            res['message'] ?? 'Gagal menambahkan portofolio',
                                          ),
                                        ),
                                      );
                                    }
                                  } catch (_) {
                                    if (!mounted) return;
                                    setModalState(() => uploading = false);
                                    messenger.showSnackBar(
                                      const SnackBar(
                                        content: Text('Gagal terhubung ke server'),
                                      ),
                                    );
                                  }
                                },
                          icon: uploading
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    color: Colors.white,
                                    strokeWidth: 2,
                                  ),
                                )
                              : const Icon(Icons.cloud_upload_outlined),
                          label: Text(uploading ? 'Mengunggah...' : 'Simpan Portofolio'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: _kBlue,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );

    titleCtrl.dispose();
    descCtrl.dispose();
  }

  Future<void> _openSupportCenter() async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => const TukangChatListScreen(initialTabIndex: 2),
      ),
    );
  }

  IconData _badgeIcon(String key) {
    switch (key) {
      case 'verified_partner':
        return Icons.verified_rounded;
      case 'first_job':
        return Icons.work_history_rounded;
      case 'pro_worker':
        return Icons.workspace_premium_rounded;
      case 'portfolio_pro':
        return Icons.collections_rounded;
      case 'top_rated':
        return Icons.star_rounded;
      case 'no_1_tukang':
        return Icons.emoji_events_rounded;
      default:
        return Icons.workspace_premium_rounded;
    }
  }

  Color _badgeColor(String? hex, {Color fallback = const Color(0xFF2563EB)}) {
    if (hex == null || hex.isEmpty) return fallback;
    final normalized = hex.replaceAll('#', '');
    if (normalized.length == 6) {
      return Color(int.parse('FF$normalized', radix: 16));
    }
    if (normalized.length == 8) {
      return Color(int.parse(normalized, radix: 16));
    }
    return fallback;
  }

  Widget _metricCard(String title, String value, IconData icon, Color color) {
    return Container(
      width: 118,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 18),
          ),
          const SizedBox(height: 10),
          Text(
            value,
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 2),
          Text(
            title,
            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
          ),
        ],
      ),
    );
  }

  Widget _quickActionCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: color.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(height: 10),
              Text(
                title,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              const SizedBox(height: 4),
              Text(
                subtitle,
                style: TextStyle(
                  fontSize: 11,
                  color: Colors.grey.shade600,
                  height: 1.35,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _quickActionsSection() {
    final rank = jsonInt(_stats['rank']);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionHeader(
            title: 'Akses Cepat',
            subtitle: 'Langsung ke fitur yang paling sering dipakai.',
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _quickActionCard(
                  title: 'Portofolio',
                  subtitle: '${_portfolio.length} item karya tersimpan',
                  icon: Icons.add_photo_alternate_outlined,
                  color: const Color(0xFF2563EB),
                  onTap: _openPortfolioEditor,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _quickActionCard(
                  title: 'Pusat Bantuan',
                  subtitle: 'Lapor bug atau hal ganjal',
                  icon: Icons.headset_mic_outlined,
                  color: const Color(0xFF0EA5E9),
                  onTap: _openSupportCenter,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFFFEF3C7), Color(0xFFFDE68A)],
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                const Icon(Icons.emoji_events_rounded, color: Color(0xFFB45309)),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    rank > 0
                        ? 'Emblem aktif: #$rank Tukang | ${_badges.length} badge didapat'
                        : 'Emblem akan tampil setelah order, rating, dan portofolio bertambah',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF92400E),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildField(
    String label,
    TextEditingController controller,
    IconData icon, {
    TextInputType? keyboardType,
    int maxLines = 1,
    Widget? suffix,
    String? hintText,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 12,
            color: Color(0xFF374151),
          ),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          enabled: _editing,
          keyboardType: keyboardType,
          maxLines: maxLines,
          decoration: InputDecoration(
            hintText: hintText,
            prefixIcon: Icon(icon, color: _kBlue, size: 18),
            suffixIcon: suffix,
            filled: true,
            fillColor: _editing ? const Color(0xFFF9FAFB) : const Color(0xFFF3F4F6),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
            ),
            disabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: _kBlue),
            ),
            contentPadding: const EdgeInsets.symmetric(
              vertical: 12,
              horizontal: 12,
            ),
          ),
        ),
      ],
    );
  }

  Widget _sectionHeader({
    required String title,
    required String subtitle,
    Widget? action,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 3),
              Text(
                subtitle,
                style: TextStyle(fontSize: 12, color: Colors.grey.shade600, height: 1.4),
              ),
            ],
          ),
        ),
        if (action != null) action,
      ],
    );
  }

  Widget _portfolioCard(Map<String, dynamic> item) {
    final id = jsonInt(item['id_portfolio']);
    final mediaUrl = item['media_url']?.toString() ?? '';
    final title = item['judul']?.toString().trim().isNotEmpty == true
        ? item['judul'].toString()
        : 'Portofolio';
    final desc = item['deskripsi']?.toString() ?? '';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Stack(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                AspectRatio(
                  aspectRatio: 1.1,
                  child: Container(
                    color: const Color(0xFFF8FAFC),
                    child: mediaUrl.isNotEmpty
                        ? Image.network(
                            mediaUrl,
                            fit: BoxFit.cover,
                            width: double.infinity,
                            errorBuilder: (_, __, ___) => const Center(
                              child: Icon(Icons.image_not_supported_outlined),
                            ),
                          )
                        : const Center(
                            child: Icon(Icons.image_outlined, color: Colors.grey),
                          ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        desc.isEmpty ? 'Belum ada deskripsi' : desc,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (_editing)
              Positioned(
                top: 8,
                right: 8,
                child: InkWell(
                  onTap: () => _deletePortfolioItem(id),
                  borderRadius: BorderRadius.circular(999),
                  child: Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: Colors.red.withOpacity(0.9),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.delete_outline,
                      color: Colors.white,
                      size: 16,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _badgeChip(Map<String, dynamic> badge) {
    final label = badge['label']?.toString() ?? 'Badge';
    final description = badge['description']?.toString() ?? '';
    final color = _badgeColor(badge['color']?.toString());
    final icon = _badgeIcon(badge['key']?.toString() ?? '');
    final imageUrl = badge['image_url']?.toString();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (imageUrl != null && imageUrl.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.network(
                imageUrl,
                width: 22,
                height: 22,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Icon(icon, color: color, size: 18),
              ),
            )
          else
            Icon(icon, color: color, size: 18),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                label,
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
              if (description.isNotEmpty)
                Text(
                  description,
                  style: TextStyle(color: color.withOpacity(0.82), fontSize: 10),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statusCard() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
      decoration: BoxDecoration(
        color: _statusAktif ? const Color(0xFFF0FDF4) : const Color(0xFFF9FAFB),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: _statusAktif ? Colors.green.shade200 : const Color(0xFFE5E7EB),
        ),
      ),
      child: Row(
        children: [
          Icon(
            _statusAktif ? Icons.circle : Icons.circle_outlined,
            color: _statusAktif ? Colors.green : Colors.grey,
            size: 14,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _statusAktif ? 'Sedang Aktif' : 'Sedang Tidak Aktif',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                    color: _statusAktif ? Colors.green : Colors.grey,
                  ),
                ),
                Text(
                  _statusAktif
                      ? 'Kamu bisa menerima pesanan'
                      : 'Kamu tidak akan menerima pesanan',
                  style: const TextStyle(fontSize: 11, color: Colors.grey),
                ),
              ],
            ),
          ),
          Switch(
            value: _statusAktif,
            onChanged: (v) => setState(() => _statusAktif = v),
            activeColor: Colors.green,
          ),
        ],
      ),
    );
  }

  Widget _profileCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildField('Nama Lengkap', _namaCtrl, Icons.person_outline),
          const SizedBox(height: 14),
          _buildField(
            'No. HP',
            _noHpCtrl,
            Icons.phone_outlined,
            keyboardType: TextInputType.phone,
          ),
          const SizedBox(height: 14),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Kategori Pekerjaan',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  fontSize: 12,
                  color: Color(0xFF374151),
                ),
              ),
              const SizedBox(height: 6),
              DropdownButtonFormField<String>(
                value: _selectedKategori,
                isExpanded: true,
                decoration: InputDecoration(
                  prefixIcon: const Icon(Icons.work_outline, color: _kBlue, size: 18),
                  filled: true,
                  fillColor: _editing ? const Color(0xFFF9FAFB) : const Color(0xFFF3F4F6),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
                  ),
                  disabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: _kBlue),
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    vertical: 12,
                    horizontal: 12,
                  ),
                ),
                items: _categories
                    .map(
                      (item) => DropdownMenuItem<String>(
                        value: item,
                        child: Text(
                          item,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    )
                    .toList(),
                onChanged: _editing
                    ? (value) {
                        setState(() => _selectedKategori = value);
                      }
                    : null,
              ),
              const SizedBox(height: 4),
              Text(
                _editing
                    ? 'Pilih kategori dari daftar yang tersedia.'
                    : 'Kategori ini tampil di profil dan hasil pencarian pelanggan.',
                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
              ),
            ],
          ),
          const SizedBox(height: 14),
          _buildField(
            'Tarif per Jam (Rp)',
            _tarifCtrl,
            Icons.attach_money,
            keyboardType: TextInputType.number,
          ),
          const SizedBox(height: 14),
          _buildField(
            'Bio / Tentang Saya',
            _bioCtrl,
            Icons.info_outline,
            maxLines: 4,
            hintText: 'Ceritakan sedikit tentang kamu...',
          ),
          if (_editing) ...[
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _kBlue,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 0,
                ),
                child: _saving
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Text(
                        'Simpan Perubahan',
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _portfolioSection() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionHeader(
            title: 'Portofolio',
            subtitle: 'Tampilkan hasil kerja terbaik supaya pelanggan lebih percaya.',
            action: TextButton.icon(
              onPressed: _openPortfolioEditor,
              icon: const Icon(Icons.add_photo_alternate_outlined, size: 18),
              label: const Text('Tambah'),
            ),
          ),
          const SizedBox(height: 14),
          if (_portfolio.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                children: [
                  Icon(Icons.photo_library_outlined, size: 44, color: Colors.grey.shade400),
                  const SizedBox(height: 8),
                  Text(
                    'Belum ada portofolio',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: Colors.grey.shade700,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Tambahkan foto hasil kerja supaya profil kamu lebih menarik.',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            )
          else
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _portfolio.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: 0.78,
              ),
              itemBuilder: (ctx, index) => _portfolioCard(_portfolio[index]),
            ),
        ],
      ),
    );
  }

  Widget _badgesSection() {
    final rank = jsonInt(_stats['rank']);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionHeader(
            title: 'Badge & Emblem',
            subtitle: 'Pencapaian yang berhasil kamu raih akan tampil di sini.',
          ),
          const SizedBox(height: 14),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: _badges.isNotEmpty
                ? _badges.map(_badgeChip).toList()
                : [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: Colors.grey.shade200),
                      ),
                      child: Text(
                        'Belum ada badge. Kerjakan pesanan, kumpulkan ulasan, dan isi portofolio untuk naik peringkat.',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                    ),
                  ),
                ],
          ),
          if (rank > 0) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFFEF3C7), Color(0xFFFDE68A)],
                ),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  const Icon(Icons.emoji_events_rounded, color: Color(0xFFB45309)),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Peringkat saat ini: #$rank Tukang',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF92400E),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _supportSection() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF1D4ED8)],
        ),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Chat ke Pusat Bantuan',
            style: TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Kalau ada yang ganjal, bug, atau ingin melapor sesuatu, buka pusat bantuan dan tulis pesannya di sana.',
            style: TextStyle(color: Colors.white70, height: 1.45),
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _openSupportCenter,
              icon: const Icon(Icons.headset_mic_outlined),
              label: const Text('Buka Pusat Bantuan'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: const Color(0xFF1D4ED8),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 0,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _headerCard() {
    final name = _profile?['nama'] ?? '-';
    final username = _profile?['username'] ?? '-';
    final rank = jsonInt(_stats['rank']);
    final avgRating = jsonDouble(_stats['avg_rating']);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF2563EB), Color(0xFF4F46E5)],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF2563EB).withOpacity(0.25),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 62,
                height: 62,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.18),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white.withOpacity(0.18)),
                ),
                child: const Icon(Icons.person, color: Colors.white, size: 32),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '@$username',
                      style: const TextStyle(color: Colors.white70, fontSize: 12),
                    ),
                    if (rank > 0) ...[
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.16),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          '#$rank Tukang',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: [
              _metricCard(
                'Order Selesai',
                jsonInt(_stats['orders_done']).toString(),
                Icons.task_alt_rounded,
                const Color(0xFFDBEAFE),
              ),
              _metricCard(
                'Rating',
                avgRating > 0 ? avgRating.toStringAsFixed(1) : '-',
                Icons.star_rounded,
                const Color(0xFFFDE68A),
              ),
              _metricCard(
                'Portofolio',
                jsonInt(_stats['portfolio_count']).toString(),
                Icons.photo_library_rounded,
                const Color(0xFFDCFCE7),
              ),
              _metricCard(
                'Peringkat',
                rank > 0 ? '#$rank' : '-',
                Icons.emoji_events_rounded,
                const Color(0xFFFFEDD5),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _logout() async {
    final navigator = Navigator.of(context);
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Kamu yakin ingin keluar?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await TukangService.logout();
              if (!mounted) return;
              navigator.pushReplacementNamed('/login');
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
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
        backgroundColor: _kBlue,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Profil Saya',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            tooltip: 'Pusat Bantuan',
            icon: const Icon(Icons.headset_mic_outlined),
            onPressed: _openSupportCenter,
          ),
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
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.wifi_off, size: 48, color: Colors.grey),
                      const SizedBox(height: 12),
                      Text(_error!, style: const TextStyle(color: Colors.grey)),
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: _load,
                        child: const Text('Coba Lagi'),
                      ),
                    ],
                  ),
                )
              : ListView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                  children: [
                    _headerCard(),
                    const SizedBox(height: 16),
                    _statusCard(),
                    const SizedBox(height: 16),
                    _quickActionsSection(),
                    const SizedBox(height: 16),
                    _profileCard(),
                    const SizedBox(height: 16),
                    _badgesSection(),
                    const SizedBox(height: 16),
                    _portfolioSection(),
                    const SizedBox(height: 16),
                    _supportSection(),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: _logout,
                        icon: const Icon(Icons.logout, color: Colors.red),
                        label: const Text(
                          'Logout',
                          style: TextStyle(
                            color: Colors.red,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          side: const BorderSide(color: Colors.red),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
    );
  }
}
