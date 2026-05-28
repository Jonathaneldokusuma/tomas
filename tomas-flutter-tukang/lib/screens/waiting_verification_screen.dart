import 'package:flutter/material.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class WaitingVerificationScreen extends StatelessWidget {
  const WaitingVerificationScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 40),
          child: Column(
            children: [
              Row(children: [
                _step('1', 'Data Diri', done: true),
                Expanded(child: Container(height: 2, color: _kBlue)),
                _step('2', 'Upload KTP', done: true),
                Expanded(child: Container(height: 2, color: _kBlue)),
                _step('3', 'Verifikasi', active: true),
              ]),
              const SizedBox(height: 48),
              Container(
                width: 90, height: 90,
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF9C3),
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFFFDE047), width: 3),
                ),
                child: const Icon(Icons.hourglass_top_rounded, color: Color(0xFFCA8A04), size: 48),
              ),
              const SizedBox(height: 24),
              const Text('Menunggu Verifikasi', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF1F2937))),
              const SizedBox(height: 8),
              const Text(
                'Akun kamu sedang ditinjau oleh tim admin. Biasanya proses ini selesai dalam 1×24 jam.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 14, color: Colors.grey, height: 1.5),
              ),
              const SizedBox(height: 36),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10)]),
                child: Column(children: [
                  _checklist('Data diri telah diterima', done: true),
                  const SizedBox(height: 12),
                  _checklist('Dokumen KTP telah diupload', done: true),
                  const SizedBox(height: 12),
                  _checklist('Sedang proses review oleh admin', active: true),
                  const SizedBox(height: 12),
                  _checklist('Akun aktif & siap digunakan'),
                ]),
              ),
              const Spacer(),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.pushReplacementNamed(context, '/login'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white, foregroundColor: _kBlue,
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: const BorderSide(color: _kBlue)),
                    elevation: 0,
                  ),
                  child: const Text('Kembali ke Login', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _checklist(String label, {bool done = false, bool active = false}) {
    return Row(children: [
      Container(
        width: 24, height: 24,
        decoration: BoxDecoration(
          color: done ? Colors.green : (active ? const Color(0xFFFEF9C3) : const Color(0xFFF3F4F6)),
          shape: BoxShape.circle,
          border: Border.all(color: done ? Colors.green : (active ? const Color(0xFFFDE047) : const Color(0xFFE5E7EB)), width: 1.5),
        ),
        child: done
            ? const Icon(Icons.check, color: Colors.white, size: 14)
            : (active ? const Icon(Icons.access_time, color: Color(0xFFCA8A04), size: 14) : const SizedBox()),
      ),
      const SizedBox(width: 12),
      Text(label, style: TextStyle(fontSize: 13, color: done ? const Color(0xFF374151) : (active ? const Color(0xFF374151) : Colors.grey))),
    ]);
  }

  Widget _step(String num, String label, {bool active = false, bool done = false}) => Column(children: [
    Container(
      width: 32, height: 32,
      decoration: BoxDecoration(color: done ? Colors.green : (active ? _kBlue : const Color(0xFFE5E7EB)), shape: BoxShape.circle),
      child: Center(child: done ? const Icon(Icons.check, color: Colors.white, size: 16) : Text(num, style: TextStyle(color: active ? Colors.white : Colors.grey, fontWeight: FontWeight.bold, fontSize: 14))),
    ),
    const SizedBox(height: 4),
    Text(label, style: TextStyle(fontSize: 10, color: active ? _kBlue : (done ? Colors.green : Colors.grey))),
  ]);
}
