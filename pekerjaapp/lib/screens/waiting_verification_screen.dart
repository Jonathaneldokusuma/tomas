import 'dart:async';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/tukang_service.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class WaitingVerificationScreen extends StatefulWidget {
  const WaitingVerificationScreen({super.key});

  @override
  State<WaitingVerificationScreen> createState() =>
      _WaitingVerificationScreenState();
}

class _WaitingVerificationScreenState extends State<WaitingVerificationScreen> {
  Timer? _timer;
  String _status = 'pending';
  String _message =
      'Akun kamu sedang ditinjau oleh tim admin. Biasanya proses ini selesai dalam 1×24 jam.';
  String? _reason;

  @override
  void initState() {
    super.initState();
    _bootstrap();
    _timer = Timer.periodic(const Duration(seconds: 5), (_) => _refresh());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _bootstrap() async {
    final prefs = await SharedPreferences.getInstance();
    if (!mounted) return;
    final cached = prefs.getString('tukang_status_verifikasi') ?? 'pending';
    setState(() => _status = cached);
    await _refresh();
  }

  Future<void> _refresh() async {
    try {
      final latest = await TukangService.fetchLatestVerificationStatus();
      if (!mounted || latest == null) return;
      final profile = await TukangService.getProfile();
      final tukang = profile['tukang'] as Map<String, dynamic>?;
      final reason = tukang?['rejection_reason']?.toString();

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('tukang_status_verifikasi', latest);

      if (latest != _status) {
        setState(() => _status = latest);
      }

      if (latest == 'verified') {
        await TukangService.syncProfileCache();
        if (!mounted) return;
        Navigator.pushReplacementNamed(context, '/dashboard');
        return;
      }

      if (latest == 'rejected') {
        await TukangService.logout();
        if (!mounted) return;
        Navigator.pushReplacementNamed(
          context,
          '/login',
          arguments: {
            'message':
                'Register ditolak oleh admin. ${reason?.isNotEmpty == true ? reason : 'Silakan daftar ulang atau hubungi admin.'}',
          },
        );
        return;
      }

      setState(() {
        _reason = reason?.isNotEmpty == true ? reason : null;
        _message =
            'Akun kamu sedang ditinjau oleh tim admin. Biasanya proses ini selesai dalam 1×24 jam.';
      });
    } catch (_) {}
  }

  Future<void> _cancelAndBack() async {
    await TukangService.logout();
    if (!mounted) return;
    Navigator.pushReplacementNamed(context, '/register');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 40),
          child: Column(
            children: [
              Row(
                children: [
                  _step('1', 'Data Diri', done: true),
                  Expanded(child: Container(height: 2, color: _kBlue)),
                  _step('2', 'Upload KTP', done: true),
                  Expanded(child: Container(height: 2, color: _kBlue)),
                  _step('3', 'Verifikasi', active: true),
                ],
              ),
              const SizedBox(height: 48),
              Container(
                width: 90,
                height: 90,
                decoration: BoxDecoration(
                  color: _status == 'rejected'
                      ? const Color(0xFFFEF2F2)
                      : const Color(0xFFFEF9C3),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: _status == 'rejected'
                        ? const Color(0xFFFECACA)
                        : const Color(0xFFFDE047),
                    width: 3,
                  ),
                ),
                child: Icon(
                  _status == 'rejected'
                      ? Icons.cancel_outlined
                      : Icons.hourglass_top_rounded,
                  color: _status == 'rejected'
                      ? Colors.red
                      : const Color(0xFFCA8A04),
                  size: 48,
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Menunggu Verifikasi',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1F2937),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                _message,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  color: Colors.grey,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 36),
              if (_status == 'rejected' && _reason != null) ...[
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF2F2),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFFECACA)),
                  ),
                  child: Text(
                    'Alasan penolakan: $_reason',
                    style: const TextStyle(
                      color: Color(0xFF991B1B),
                      fontSize: 13,
                      height: 1.4,
                    ),
                  ),
                ),
              ],
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 10,
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    _checklist('Data diri telah diterima', done: true),
                    const SizedBox(height: 12),
                    _checklist('Dokumen KTP telah diupload', done: true),
                    const SizedBox(height: 12),
                    _checklist('Sedang proses review oleh admin', active: true),
                    const SizedBox(height: 12),
                    _checklist('Akun aktif & siap digunakan'),
                  ],
                ),
              ),
              const Spacer(),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _cancelAndBack,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: _kBlue,
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                      side: const BorderSide(color: _kBlue),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Batalkan & Kembali',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _checklist(String label, {bool done = false, bool active = false}) {
    return Row(
      children: [
        Container(
          width: 24,
          height: 24,
          decoration: BoxDecoration(
            color: done
                ? Colors.green
                : (active ? const Color(0xFFFEF9C3) : const Color(0xFFF3F4F6)),
            shape: BoxShape.circle,
            border: Border.all(
              color: done
                  ? Colors.green
                  : (active ? const Color(0xFFFDE047) : const Color(0xFFE5E7EB)),
              width: 1.5,
            ),
          ),
          child: done
              ? const Icon(Icons.check, color: Colors.white, size: 14)
              : (active
                  ? const Icon(Icons.access_time, color: Color(0xFFCA8A04), size: 14)
                  : const SizedBox()),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            label,
            style: TextStyle(
              fontSize: 13,
              color: done
                  ? const Color(0xFF374151)
                  : (active ? const Color(0xFF374151) : Colors.grey),
            ),
          ),
        ),
      ],
    );
  }

  Widget _step(String num, String label, {bool active = false, bool done = false}) =>
      Column(
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
