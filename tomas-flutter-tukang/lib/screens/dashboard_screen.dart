import 'package:flutter/material.dart';
import '../services/tukang_service.dart';

const _kBlue = Color(0xFF2563EB);
const _kBg = Color(0xFFF2F2F7);

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});
  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  List<dynamic> _orders = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    _loadOrders();
  }

  @override
  void dispose() { _tabCtrl.dispose(); super.dispose(); }

  Future<void> _loadOrders() async {
    setState(() { _loading = true; _error = null; });
    try {
      final res = await TukangService.getOrders();
      if (res['orders'] != null) {
        setState(() { _orders = res['orders']; _loading = false; });
      } else {
        setState(() { _error = res['message'] ?? 'Gagal memuat pesanan'; _loading = false; });
      }
    } catch (e) {
      setState(() { _error = 'Tidak dapat terhubung ke server'; _loading = false; });
    }
  }

  List<dynamic> get _pending => _orders.where((o) => o['status'] == 'pending').toList();
  List<dynamic> get _active => _orders.where((o) => ['confirmed', 'in_progress'].contains(o['status'])).toList();
  List<dynamic> get _done => _orders.where((o) => ['done', 'rejected'].contains(o['status'])).toList();

  void _goDetail(Map<String, dynamic> order) {
    Navigator.pushNamed(context, '/order-detail', arguments: order).then((_) => _loadOrders());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBlue,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text('Dashboard', style: TextStyle(fontWeight: FontWeight.bold)),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadOrders),
          IconButton(icon: const Icon(Icons.chat_bubble_outline), onPressed: () => Navigator.pushNamed(context, '/chat')),
          IconButton(icon: const Icon(Icons.person_outline), onPressed: () => Navigator.pushNamed(context, '/profile').then((_) => _loadOrders())),
        ],
        bottom: TabBar(
          controller: _tabCtrl,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: [
            Tab(text: 'Masuk (${_pending.length})'),
            Tab(text: 'Aktif (${_active.length})'),
            Tab(text: 'Selesai (${_done.length})'),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: _kBlue))
          : _error != null
              ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  const Icon(Icons.wifi_off, size: 48, color: Colors.grey),
                  const SizedBox(height: 12),
                  Text(_error!, style: const TextStyle(color: Colors.grey)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _loadOrders, child: const Text('Coba Lagi')),
                ]))
              : TabBarView(
                  controller: _tabCtrl,
                  children: [
                    _buildList(_pending, emptyMsg: 'Tidak ada pesanan masuk'),
                    _buildList(_active, emptyMsg: 'Tidak ada pesanan aktif'),
                    _buildList(_done, emptyMsg: 'Belum ada pesanan selesai'),
                  ],
                ),
    );
  }

  Widget _buildList(List<dynamic> orders, {required String emptyMsg}) {
    if (orders.isEmpty) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.inbox, size: 56, color: Colors.grey),
        const SizedBox(height: 12),
        Text(emptyMsg, style: const TextStyle(color: Colors.grey, fontSize: 14)),
      ]));
    }
    return RefreshIndicator(
      onRefresh: _loadOrders,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: orders.length,
        itemBuilder: (_, i) => _orderCard(orders[i]),
      ),
    );
  }

  Widget _orderCard(Map<String, dynamic> order) {
    final status = order['status'] ?? '';
    final statusPay = order['status_payment'] ?? 'pending';
    return GestureDetector(
      onTap: () => _goDetail(order),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(child: Text(order['user']?['name'] ?? 'Pelanggan', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15))),
            _statusBadge(status),
          ]),
          const SizedBox(height: 6),
          Text(order['kategori'] ?? '-', style: const TextStyle(color: _kBlue, fontSize: 13, fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          _infoRow(Icons.location_on_outlined, order['alamat'] ?? '-'),
          _infoRow(Icons.calendar_today_outlined, '${order['tanggal'] ?? '-'}  ${order['jam'] ?? ''}'),
          if (statusPay == 'uploaded')
            Container(
              margin: const EdgeInsets.only(top: 8),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(color: const Color(0xFFFEF3C7), borderRadius: BorderRadius.circular(8)),
              child: const Row(mainAxisSize: MainAxisSize.min, children: [
                Icon(Icons.payment, size: 14, color: Color(0xFFD97706)),
                SizedBox(width: 6),
                Text('Bukti bayar tersedia', style: TextStyle(fontSize: 12, color: Color(0xFFD97706), fontWeight: FontWeight.w600)),
              ]),
            ),
          if (status == 'pending') ...[
            const SizedBox(height: 12),
            Row(children: [
              Expanded(child: ElevatedButton.icon(
                onPressed: () => _accept(order['id_order']),
                icon: const Icon(Icons.check, size: 16),
                label: const Text('Terima', style: TextStyle(fontSize: 13)),
                style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white, elevation: 0, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8))),
              )),
              const SizedBox(width: 10),
              Expanded(child: OutlinedButton.icon(
                onPressed: () => _confirmReject(order['id_order']),
                icon: const Icon(Icons.close, size: 16),
                label: const Text('Tolak', style: TextStyle(fontSize: 13)),
                style: OutlinedButton.styleFrom(foregroundColor: Colors.red, side: const BorderSide(color: Colors.red), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8))),
              )),
            ]),
          ],
        ]),
      ),
    );
  }

  void _accept(int id) async {
    final res = await TukangService.acceptOrder(id);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Berhasil')));
    _loadOrders();
  }

  void _confirmReject(int id) {
    final noteCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Tolak Pesanan'),
        content: TextField(controller: noteCtrl, decoration: const InputDecoration(hintText: 'Alasan penolakan (opsional)'), maxLines: 2),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              final res = await TukangService.rejectOrder(id, catatan: noteCtrl.text);
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Ditolak')));
              _loadOrders();
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            child: const Text('Tolak'),
          ),
        ],
      ),
    );
  }

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
      decoration: BoxDecoration(color: c[0] as Color, borderRadius: BorderRadius.circular(20)),
      child: Text(c[2] as String, style: TextStyle(color: c[1] as Color, fontSize: 11, fontWeight: FontWeight.bold)),
    );
  }

  Widget _infoRow(IconData icon, String text) => Padding(
    padding: const EdgeInsets.only(bottom: 4),
    child: Row(children: [
      Icon(icon, size: 14, color: Colors.grey),
      const SizedBox(width: 6),
      Expanded(child: Text(text, style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280)), maxLines: 1, overflow: TextOverflow.ellipsis)),
    ]),
  );
}
