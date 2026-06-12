import 'package:flutter/material.dart';

class BroadcastDetailScreen extends StatelessWidget {
  final Map<String, dynamic> broadcast;

  const BroadcastDetailScreen({super.key, required this.broadcast});

  @override
  Widget build(BuildContext context) {
    final tipe = broadcast['tipe'] ?? 'info';
    Color tipeColor = const Color(0xFF1976D2);
    IconData tipeIcon = Icons.info_outline;
    String tipeLabel = 'Info';
    if (tipe == 'warning') {
      tipeColor = Colors.orange;
      tipeIcon = Icons.warning_amber_outlined;
      tipeLabel = 'Perhatian';
    } else if (tipe == 'promo') {
      tipeColor = Colors.green;
      tipeIcon = Icons.local_offer_outlined;
      tipeLabel = 'Promo';
    }

    final createdAt = broadcast['created_at']?.toString() ?? '';
    String formattedDate = '';
    if (createdAt.isNotEmpty) {
      try {
        final dt = DateTime.parse(createdAt).toLocal();
        formattedDate =
            '${dt.day}/${dt.month}/${dt.year} ${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
      } catch (_) {}
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Pesan dari Pusat'),
        backgroundColor: const Color(0xFF1976D2),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: tipeColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(tipeIcon, color: tipeColor, size: 16),
                  const SizedBox(width: 6),
                  Text(tipeLabel,
                      style: TextStyle(
                          color: tipeColor,
                          fontWeight: FontWeight.w600,
                          fontSize: 13)),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Text(
              broadcast['judul'] ?? '',
              style: const TextStyle(
                  fontSize: 20, fontWeight: FontWeight.bold),
            ),
            if (formattedDate.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(formattedDate,
                  style:
                      TextStyle(color: Colors.grey[500], fontSize: 12)),
            ],
            const SizedBox(height: 20),
            Container(
              width: double.infinity,
              height: 1,
              color: Colors.grey[200],
            ),
            const SizedBox(height: 20),
            Text(
              broadcast['isi'] ?? '',
              style: const TextStyle(fontSize: 15, height: 1.6),
            ),
          ],
        ),
      ),
    );
  }
}
