import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class KtpUploadScreen extends StatefulWidget {
  const KtpUploadScreen({super.key});
  @override
  State<KtpUploadScreen> createState() => _KtpUploadScreenState();
}

class _KtpUploadScreenState extends State<KtpUploadScreen> {
  File? _ktpImage;
  File? _selfieImage;
  bool _loading = false;

  Future<void> _pickImage(bool isKtp, {bool camera = false}) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(
      source: camera ? ImageSource.camera : ImageSource.gallery,
      imageQuality: 80,
    );
    if (picked != null) {
      setState(() {
        if (isKtp) {
          _ktpImage = File(picked.path);
        } else {
          _selfieImage = File(picked.path);
        }
      });
    }
  }

  void _showOptions(bool isKtp) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.camera_alt, color: _kBlue),
              title: const Text('Ambil Foto'),
              onTap: () {
                Navigator.pop(context);
                _pickImage(isKtp, camera: true);
              },
            ),
            ListTile(
              leading: const Icon(Icons.photo_library, color: _kBlue),
              title: const Text('Pilih dari Galeri'),
              onTap: () {
                Navigator.pop(context);
                _pickImage(isKtp);
              },
            ),
          ],
        ),
      ),
    );
  }

  void _submit() async {
    if (_ktpImage == null || _selfieImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Wajib upload foto KTP & selfie!')),
      );
      return;
    }
    setState(() => _loading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('tukang_token') ?? '';
      if (token.isEmpty) {
        throw Exception(
          'Sesi pendaftaran habis. Silakan daftar atau login ulang.',
        );
      }

      final req = http.MultipartRequest(
        'POST',
        Uri.parse('${AppConfig.apiBaseUrl}/tukang/upload-ktp'),
      );
      req.headers['X-Tukang-Token'] = token;
      req.files.add(
        await http.MultipartFile.fromPath('foto_ktp', _ktpImage!.path),
      );
      req.files.add(
        await http.MultipartFile.fromPath('foto_selfie', _selfieImage!.path),
      );

      final streamed = await req.send();
      final response = await http.Response.fromStream(streamed);
      Map<String, dynamic> data = {};
      if (response.body.isNotEmpty) {
        final decoded = jsonDecode(response.body);
        if (decoded is Map<String, dynamic>) data = decoded;
      }

      if (response.statusCode < 200 || response.statusCode >= 300) {
        final message =
            data['message']?.toString() ??
            _firstValidationError(data['errors']) ??
            'Upload KTP gagal. Coba lagi.';
        throw Exception(message);
      }

      await prefs.setString('tukang_status_verifikasi', 'pending');
      if (!mounted) return;
      Navigator.pushReplacementNamed(context, '/waiting-verification');
    } catch (e) {
      if (!mounted) return;
      final message = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  String? _firstValidationError(dynamic errors) {
    if (errors is! Map || errors.isEmpty) return null;
    final first = errors.values.first;
    if (first is List && first.isNotEmpty) return first.first.toString();
    return first?.toString();
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
              Row(
                children: [
                  _step('1', 'Data Diri', done: true),
                  Expanded(child: Container(height: 2, color: _kBlue)),
                  _step('2', 'Upload KTP', active: true),
                  Expanded(
                    child: Container(height: 2, color: const Color(0xFFE5E7EB)),
                  ),
                  _step('3', 'Verifikasi'),
                ],
              ),
              const SizedBox(height: 28),
              const Text(
                'Upload Dokumen',
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1F2937),
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Foto harus jelas agar proses verifikasi cepat.',
                style: TextStyle(fontSize: 13, color: Colors.grey),
              ),
              const SizedBox(height: 24),
              _uploadCard(
                'Foto KTP',
                'Pastikan nomor KTP dan wajah jelas',
                Icons.credit_card,
                _ktpImage,
                () => _showOptions(true),
              ),
              const SizedBox(height: 16),
              _uploadCard(
                'Selfie dengan KTP',
                'Pegang KTP di depan wajah kamu',
                Icons.face,
                _selfieImage,
                () => _showOptions(false),
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: const Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.info_outline, color: _kBlue, size: 18),
                    SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Verifikasi biasanya 1×24 jam. Foto tidak blur & tidak terpotong.',
                        style: TextStyle(
                          fontSize: 12,
                          color: Color(0xFF1D4ED8),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed:
                      (_loading || _ktpImage == null || _selfieImage == null)
                      ? null
                      : _submit,
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
                              'Kirim untuk Verifikasi',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            SizedBox(width: 8),
                            Icon(Icons.send, size: 18),
                          ],
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _uploadCard(
    String label,
    String subtitle,
    IconData icon,
    File? image,
    VoidCallback onTap,
  ) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: image != null ? _kBlue : const Color(0xFFE5E7EB),
            width: image != null ? 2 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: image == null
            ? Padding(
                padding: const EdgeInsets.symmetric(
                  vertical: 28,
                  horizontal: 20,
                ),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(icon, color: _kBlue, size: 26),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            label,
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            subtitle,
                            style: const TextStyle(
                              fontSize: 12,
                              color: Colors.grey,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const Icon(Icons.add_a_photo_outlined, color: _kBlue),
                  ],
                ),
              )
            : ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: Stack(
                  children: [
                    Image.file(
                      image,
                      height: 170,
                      width: double.infinity,
                      fit: BoxFit.cover,
                    ),
                    Positioned(
                      bottom: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: _kBlue,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.edit, color: Colors.white, size: 12),
                            SizedBox(width: 4),
                            Text(
                              'Ganti',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        width: 28,
                        height: 28,
                        decoration: const BoxDecoration(
                          color: Colors.green,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.check,
                          color: Colors.white,
                          size: 16,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }

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
