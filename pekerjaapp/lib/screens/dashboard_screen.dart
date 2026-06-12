import 'dart:async';
import 'package:flutter/material.dart';
import '../services/tukang_service.dart';
import '../services/notification_service.dart';
import '../utils/json_value.dart';

// ── Color Palette ─────────────────────────────────────────────────────────────
const _kBlue = Color(0xFF2563EB);
const _kBlue2 = Color(0xFF1D4ED8);
const _kIndigo = Color(0xFF4F46E5);
const _kTeal = Color(0xFF0EA5E9);
const _kBg = Color(0xFFF0F4FF);

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});
  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  List<dynamic> _orders = [];
  bool _loading = true;
  String? _error;
  Timer? _refreshTimer;
  int _prevPendingCount = 0;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    _loadOrders(silent: false);

    // Auto-refresh every 15 seconds
    _refreshTimer = Timer.periodic(const Duration(seconds: 15), (_) {
      _loadOrders(silent: true);
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadOrders({bool silent = false}) async {
    if (!silent)
      setState(() {
        _loading = true;
        _error = null;
      });
    try {
      final res = await TukangService.getOrders();
      if (!mounted) return;
      if (res['orders'] != null) {
        final newOrders = List<dynamic>.from(res['orders']);
        final newPending = newOrders
            .where((o) => o['status'] == 'pending')
            .length;

        // Show notification if new pending order arrived
        if (silent && newPending > _prevPendingCount) {
          final diff = newPending - _prevPendingCount;
          NotificationService.showLocalNotification(
            title: 'Pesanan Baru!',
            body: 'Ada $diff pesanan baru yang menunggu konfirmasi Anda.',
          );
        }
        _prevPendingCount = newPending;
        setState(() {
          _orders = newOrders;
          _loading = false;
          _error = null;
        });
      } else {
        if (!silent)
          setState(() {
            _error = res['message'] ?? 'Gagal memuat pesanan';
            _loading = false;
          });
      }
    } catch (e) {
      if (!mounted) return;
      if (!silent)
        setState(() {
          _error = 'Tidak dapat terhubung ke server';
          _loading = false;
        });
    }
  }

  List<dynamic> get _pending =>
      _orders.where((o) => o['status'] == 'pending').toList();
  List<dynamic> get _active => _orders
      .where((o) => ['confirmed', 'in_progress'].contains(o['status']))
      .toList();
  List<dynamic> get _done =>
      _orders.where((o) => ['done', 'rejected'].contains(o['status'])).toList();

  void _goDetail(Map<String, dynamic> order) {
    Navigator.pushNamed(
      context,
      '/order-detail',
      arguments: order,
    ).then((_) => _loadOrders());
  }

  // ── Build ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      body: SafeArea(
        child: Column(
          children: [
            _buildHeader(),
            _buildStatsRow(),
            _buildTabBar(),
            Expanded(
              child: _loading
                  ? _buildSkeleton()
                  : _error != null
                  ? _buildError(_error ?? 'Gagal memuat dashboard')
                  : TabBarView(
                      controller: _tabCtrl,
                      children: [
                        _buildList(
                          _pending,
                          emptyMsg: 'Tidak ada pesanan masuk',
                          emptyIcon: Icons.inbox_outlined,
                        ),
                        _buildList(
                          _active,
                          emptyMsg: 'Tidak ada pesanan aktif',
                          emptyIcon: Icons.work_outline,
                        ),
                        _buildList(
                          _done,
                          emptyMsg: 'Belum ada pesanan selesai',
                          emptyIcon: Icons.check_circle_outline,
                        ),
                      ],
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() => Container(
    margin: const EdgeInsets.fromLTRB(16, 16, 16, 12),
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      gradient: const LinearGradient(
        colors: [_kIndigo, _kBlue2, _kBlue, _kTeal],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      ),
      borderRadius: BorderRadius.circular(24),
      boxShadow: [
        BoxShadow(
          color: _kBlue.withOpacity(0.22),
          blurRadius: 22,
          offset: const Offset(0, 12),
        ),
      ],
    ),
    child: Stack(
      children: [
        Positioned(
          top: -34,
          right: -18,
          child: _glowCircle(110, Colors.white.withOpacity(0.07)),
        ),
        Positioned(
          bottom: -44,
          left: -8,
          child: _glowCircle(150, Colors.white.withOpacity(0.06)),
        ),
        Row(
          children: [
            const CircleAvatar(
              radius: 26,
              backgroundColor: Colors.white24,
              child: Icon(
                Icons.engineering,
                color: Colors.white,
                size: 26,
              ),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Dashboard Tukang',
                    style: TextStyle(color: Colors.white70, fontSize: 12),
                  ),
                  SizedBox(height: 2),
                  Text(
                    'Selamat Datang!',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 18,
                    ),
                  ),
                ],
              ),
            ),
            _appBarBtn(Icons.refresh, () => _loadOrders(silent: false)),
            const SizedBox(width: 6),
            _appBarBtn(
              Icons.chat_bubble_outline,
              () => Navigator.pushNamed(context, '/chat'),
            ),
            const SizedBox(width: 6),
            _appBarBtn(
              Icons.person_outline,
              () => Navigator.pushNamed(
                context,
                '/profile',
              ).then((_) => _loadOrders()),
            ),
          ],
        ),
      ],
    ),
  );

  Widget _buildTabBar() => Container(
    margin: const EdgeInsets.fromLTRB(16, 0, 16, 10),
    padding: const EdgeInsets.all(4),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      boxShadow: [
        BoxShadow(color: _kBlue.withOpacity(0.06), blurRadius: 12),
      ],
    ),
    child: TabBar(
      controller: _tabCtrl,
      labelColor: _kBlue,
      unselectedLabelColor: const Color(0xFF6B7280),
      indicatorColor: _kBlue,
      indicatorWeight: 3,
      labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
      tabs: [
        Tab(text: 'Masuk (${_pending.length})'),
        Tab(text: 'Aktif (${_active.length})'),
        Tab(text: 'Selesai (${_done.length})'),
      ],
    ),
  );

  Widget _appBarBtn(IconData icon, VoidCallback onTap) => GestureDetector(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.18),
        shape: BoxShape.circle,
      ),
      child: Icon(icon, color: Colors.white, size: 20),
    ),
  );

  Widget _glowCircle(double size, Color color) => Container(
    width: size,
    height: size,
    decoration: BoxDecoration(shape: BoxShape.circle, color: color),
  );

  // ── Stats Row ─────────────────────────────────────────────────────────────
  Widget _buildStatsRow() => Container(
    color: _kBg,
    padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
    child: Row(
      children: [
        _statCard(
          'Menunggu',
          _pending.length,
          const Color(0xFFFEF3C7),
          const Color(0xFFD97706),
          Icons.schedule,
        ),
        const SizedBox(width: 10),
        _statCard(
          'Aktif',
          _active.length,
          const Color(0xFFEFF6FF),
          _kBlue,
          Icons.bolt,
        ),
        const SizedBox(width: 10),
        _statCard(
          'Selesai',
          _done.length,
          const Color(0xFFF0FDF4),
          Colors.green,
          Icons.check_circle,
        ),
      ],
    ),
  );

  Widget _statCard(
    String label,
    int count,
    Color bg,
    Color fg,
    IconData icon,
  ) => Expanded(
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: fg.withOpacity(0.12),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
        border: Border.all(color: fg.withOpacity(0.2), width: 1.2),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: bg,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: fg, size: 18),
          ),
          const SizedBox(height: 8),
          Text(
            '$count',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: fg,
            ),
          ),
          Text(
            label,
            style: const TextStyle(fontSize: 11, color: Color(0xFF6B7280)),
          ),
        ],
      ),
    ),
  );

  // ── List ──────────────────────────────────────────────────────────────────
  Widget _buildList(
    List<dynamic> orders, {
    required String emptyMsg,
    required IconData emptyIcon,
  }) {
    if (orders.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(color: _kBlue.withOpacity(0.08), blurRadius: 20),
                ],
              ),
              child: Icon(emptyIcon, size: 48, color: const Color(0xFFD1D5DB)),
            ),
            const SizedBox(height: 16),
            Text(
              emptyMsg,
              style: const TextStyle(color: Color(0xFF9CA3AF), fontSize: 14),
            ),
          ],
        ),
      );
    }
    return RefreshIndicator(
      color: _kBlue,
      onRefresh: () => _loadOrders(silent: false),
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
        itemCount: orders.length,
        itemBuilder: (_, i) => _orderCard(orders[i]),
      ),
    );
  }

  // ── Order Card ─────────────────────────────────────────────────────────────
  Widget _orderCard(Map<String, dynamic> order) {
    final status = order['status'] ?? '';
    final statusPay = order['status_payment'] ?? 'pending';
    final isPending = status == 'pending';

    return GestureDetector(
      onTap: () => _goDetail(order),
      child: Container(
        margin: const EdgeInsets.only(bottom: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 12,
              offset: const Offset(0, 3),
            ),
          ],
          border: isPending
              ? Border.all(
                  color: const Color(0xFFF59E0B).withOpacity(0.5),
                  width: 1.5,
                )
              : null,
        ),
        child: Column(
          children: [
            // Colored accent bar
            Container(
              height: 5,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: _statusGradient(status),
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                ),
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(18),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 20,
                        backgroundColor: _kBlue.withOpacity(0.1),
                        child: Text(
                          (order['user']?['name'] ?? 'P')
                              .substring(0, 1)
                              .toUpperCase(),
                          style: const TextStyle(
                            color: _kBlue,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              order['user']?['name'] ?? 'Pelanggan',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            Text(
                              order['kategori'] ??
                                  order['layanan']?['nama_layanan'] ??
                                  '-',
                              style: const TextStyle(
                                color: _kBlue,
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                      _statusBadge(status),
                    ],
                  ),
                  const Divider(height: 18, color: Color(0xFFF3F4F6)),
                  _infoRow(Icons.location_on_outlined, order['alamat'] ?? '-'),
                  _infoRow(
                    Icons.calendar_today_outlined,
                    '${order['tanggal'] ?? order['tanggal_kerja'] ?? '-'}  ${order['jam'] ?? order['jam_mulai'] ?? ''}',
                  ),
                  if (statusPay == 'uploaded') ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 7,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF3C7),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.payment,
                            size: 14,
                            color: Color(0xFFD97706),
                          ),
                          SizedBox(width: 6),
                          Text(
                            'Bukti bayar tersedia',
                            style: TextStyle(
                              fontSize: 12,
                              color: Color(0xFFD97706),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                  if (isPending) ...[
                    const SizedBox(height: 14),
                    Row(
                      children: [
                        Expanded(
                          child: _actionBtn(
                            label: 'Terima',
                            icon: Icons.check_rounded,
                            color: Colors.green,
                            onTap: () => _accept(jsonInt(order['id_order'])),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _actionBtn(
                            label: 'Tolak',
                            icon: Icons.close_rounded,
                            color: Colors.red,
                            outlined: true,
                            onTap: () =>
                                _confirmReject(jsonInt(order['id_order'])),
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  List<Color> _statusGradient(String status) {
    switch (status) {
      case 'pending':
        return [const Color(0xFFF59E0B), const Color(0xFFFBBF24)];
      case 'confirmed':
        return [_kBlue, _kTeal];
      case 'in_progress':
        return [Colors.green, const Color(0xFF34D399)];
      case 'done':
        return [Colors.grey, const Color(0xFFD1D5DB)];
      case 'rejected':
        return [Colors.red, const Color(0xFFFCA5A5)];
      default:
        return [Colors.grey, Colors.grey];
    }
  }

  Widget _actionBtn({
    required String label,
    required IconData icon,
    required Color color,
    bool outlined = false,
    required VoidCallback onTap,
  }) => GestureDetector(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.symmetric(vertical: 11),
      decoration: BoxDecoration(
        color: outlined ? Colors.transparent : color,
        border: Border.all(color: color, width: 1.5),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 16, color: outlined ? color : Colors.white),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              color: outlined ? color : Colors.white,
              fontWeight: FontWeight.bold,
              fontSize: 13,
            ),
          ),
        ],
      ),
    ),
  );

  void _accept(int id) async {
    final res = await TukangService.acceptOrder(id);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(res['message'] ?? 'Berhasil'),
        backgroundColor: Colors.green,
      ),
    );
    _loadOrders();
  }

  void _confirmReject(int id) {
    final noteCtrl = TextEditingController();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text(
          'Tolak Pesanan',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
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
            onPressed: () async {
              Navigator.pop(context);
              final res = await TukangService.rejectOrder(
                id,
                catatan: noteCtrl.text,
              );
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Ditolak'),
                  backgroundColor: Colors.red,
                ),
              );
              _loadOrders();
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

  Widget _statusBadge(String status) {
    final cfg = <String, List<dynamic>>{
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

  Widget _infoRow(IconData icon, String text) => Padding(
    padding: const EdgeInsets.only(bottom: 5),
    child: Row(
      children: [
        Icon(icon, size: 14, color: const Color(0xFF6B7280)),
        const SizedBox(width: 6),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280)),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    ),
  );

  // ── Skeleton / Error ──────────────────────────────────────────────────────
  Widget _buildSkeleton() => Center(
    child: Container(
      margin: const EdgeInsets.all(24),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(color: _kBlue.withOpacity(0.08), blurRadius: 24),
        ],
      ),
      child: const Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.engineering_rounded, color: _kBlue, size: 42),
          SizedBox(height: 16),
          CircularProgressIndicator(color: _kBlue),
          SizedBox(height: 16),
          Text(
            'Memuat dashboard tukang...',
            style: TextStyle(
              color: Color(0xFF1F2937),
              fontWeight: FontWeight.w700,
            ),
          ),
          SizedBox(height: 6),
          Text(
            'Mengambil data dari Railway',
            style: TextStyle(color: Color(0xFF6B7280), fontSize: 12),
          ),
        ],
      ),
    ),
  );

  Widget _buildError(String message) => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(color: Colors.red.withOpacity(0.1), blurRadius: 20),
            ],
          ),
          child: const Icon(
            Icons.wifi_off_rounded,
            size: 48,
            color: Colors.red,
          ),
        ),
        const SizedBox(height: 16),
        Text(message, style: const TextStyle(color: Color(0xFF6B7280), fontSize: 14)),
        const SizedBox(height: 16),
        ElevatedButton.icon(
          onPressed: () => _loadOrders(silent: false),
          icon: const Icon(Icons.refresh),
          label: const Text('Coba Lagi'),
          style: ElevatedButton.styleFrom(
            backgroundColor: _kBlue,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          ),
        ),
      ],
    ),
  );
}
