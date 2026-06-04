import 'package:flutter/material.dart';
import '../services/tukang_service.dart';
import '../utils/json_value.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class OrderDetailScreen extends StatefulWidget {
  final Map<String, dynamic> order;
  const OrderDetailScreen({super.key, required this.order});
  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  bool _loading = false;

  Map<String, dynamic> get order => widget.order;
  int get orderId => jsonInt(order['id_order']);

  Future<void> _do(
    Future<Map<String, dynamic>> Function() action, {
    String? successMsg,
  }) async {
    setState(() => _loading = true);
    final res = await action();
    setState(() => _loading = false);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(res['message'] ?? successMsg ?? 'Berhasil')),
    );
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final status = order['status'] ?? '';
    final statusPay = order['status_payment'] ?? 'pending';

    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBlue,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Detail Pesanan',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: _card(),
              child: Row(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(
                      Icons.construction,
                      color: _kBlue,
                      size: 28,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          order['kategori'] ?? '-',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Order #${order['id_order']}',
                          style: const TextStyle(
                            fontSize: 12,
                            color: Colors.grey,
                          ),
                        ),
                      ],
                    ),
                  ),
                  _statusBadge(status),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Info section
            Container(
              padding: const EdgeInsets.all(18),
              decoration: _card(),
              child: Column(
                children: [
                  _row(
                    Icons.person_outline,
                    'Pelanggan',
                    order['user']?['name'] ?? '-',
                  ),
                  const Divider(height: 20),
                  _row(
                    Icons.phone_outlined,
                    'No. HP',
                    order['user']?['no_hp'] ?? '-',
                  ),
                  const Divider(height: 20),
                  _row(
                    Icons.calendar_today_outlined,
                    'Tanggal',
                    '${order['tanggal'] ?? '-'}',
                  ),
                  const Divider(height: 20),
                  _row(Icons.access_time_outlined, 'Jam', order['jam'] ?? '-'),
                  const Divider(height: 20),
                  _row(
                    Icons.location_on_outlined,
                    'Alamat',
                    order['alamat'] ?? '-',
                  ),
                  if (order['keterangan'] != null &&
                      order['keterangan'].toString().isNotEmpty) ...[
                    const Divider(height: 20),
                    _row(
                      Icons.notes_outlined,
                      'Keterangan',
                      order['keterangan'],
                    ),
                  ],
                ],
              ),
            ),

            if (statusPay == 'uploaded' &&
                order['bukti_bayar_url'] != null) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(18),
                decoration: _card(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Bukti Pembayaran',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 12),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: Image.network(
                        order['bukti_bayar_url'],
                        height: 200,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 24),
            _actionButtons(status, statusPay),
          ],
        ),
      ),
    );
  }

  Widget _actionButtons(String status, String statusPay) {
    if (_loading)
      return const Center(child: CircularProgressIndicator(color: _kBlue));

    List<Widget> buttons = [];

    if (status == 'pending') {
      buttons = [
        _btn(
          'Terima Pesanan',
          Colors.green,
          Icons.check_circle_outline,
          () => _do(() => TukangService.acceptOrder(orderId)),
        ),
        const SizedBox(height: 10),
        _btnOutline(
          'Tolak Pesanan',
          Colors.red,
          Icons.cancel_outlined,
          () => _showRejectDialog(),
        ),
      ];
    } else if (status == 'confirmed') {
      buttons = [
        _btn(
          'Mulai Kerjaan',
          _kBlue,
          Icons.play_arrow_rounded,
          () => _do(() => TukangService.updateStatus(orderId, 'in_progress')),
        ),
      ];
    } else if (status == 'in_progress' && statusPay == 'uploaded') {
      buttons = [
        _btn(
          'Konfirmasi Pembayaran',
          Colors.green,
          Icons.payment,
          () => _do(() => TukangService.confirmPayment(orderId)),
        ),
        const SizedBox(height: 10),
        _btn(
          'Tandai Selesai',
          _kBlue,
          Icons.done_all,
          () => _do(() => TukangService.updateStatus(orderId, 'done')),
        ),
      ];
    } else if (status == 'in_progress') {
      buttons = [
        _btn(
          'Tandai Selesai',
          _kBlue,
          Icons.done_all,
          () => _do(() => TukangService.updateStatus(orderId, 'done')),
        ),
      ];
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: buttons,
    );
  }

  void _showRejectDialog() {
    final noteCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Tolak Pesanan'),
        content: TextField(
          controller: noteCtrl,
          decoration: const InputDecoration(
            hintText: 'Alasan penolakan (opsional)',
            border: OutlineInputBorder(),
          ),
          maxLines: 2,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _do(
                () =>
                    TukangService.rejectOrder(orderId, catatan: noteCtrl.text),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            child: const Text('Tolak'),
          ),
        ],
      ),
    );
  }

  BoxDecoration _card() => BoxDecoration(
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

  Widget _row(IconData icon, String label, String value) => Row(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Icon(icon, color: _kBlue, size: 18),
      const SizedBox(width: 12),
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(
                fontSize: 11,
                color: Colors.grey,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                color: Color(0xFF1F2937),
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    ],
  );

  Widget _btn(String label, Color color, IconData icon, VoidCallback onTap) =>
      ElevatedButton.icon(
        onPressed: onTap,
        icon: Icon(icon, size: 18),
        label: Text(
          label,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: 0,
        ),
      );

  Widget _btnOutline(
    String label,
    Color color,
    IconData icon,
    VoidCallback onTap,
  ) => OutlinedButton.icon(
    onPressed: onTap,
    icon: Icon(icon, size: 18),
    label: Text(
      label,
      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: color),
    ),
    style: OutlinedButton.styleFrom(
      side: BorderSide(color: color),
      padding: const EdgeInsets.symmetric(vertical: 14),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ),
  );

  Widget _statusBadge(String status) {
    final cfg = {
      'pending': [const Color(0xFFFEF3C7), const Color(0xFFD97706), 'Menunggu'],
      'confirmed': [const Color(0xFFEFF6FF), _kBlue, 'Dikonfirmasi'],
      'in_progress': [const Color(0xFFF0FDF4), Colors.green, 'Dikerjakan'],
      'done': [const Color(0xFFF3F4F6), Colors.grey, 'Selesai'],
      'rejected': [const Color(0xFFFEF2F2), Colors.red, 'Ditolak'],
    };
    final c = cfg[status] ?? [const Color(0xFFF3F4F6), Colors.grey, status];
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c[0] as Color,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        c[2] as String,
        style: TextStyle(
          color: c[1] as Color,
          fontSize: 11,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}
