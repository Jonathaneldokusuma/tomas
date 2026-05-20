import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../models/tukang.dart';
import '../../models/layanan.dart';
import '../tukang/tukang_detail_screen.dart';
import '../order/order_list_screen.dart';
import '../chat/chat_list_screen.dart';
import '../favorit/favorit_screen.dart';
import '../profil/profil_screen.dart';
import '../notifikasi/notifikasi_screen.dart';

const _kBlue = Color(0xFF2563EB);
const _kBlueLt = Color(0xFF3B82F6);
const _kBg = Color(0xFFF2F2F7);

// ── Shell ────────────────────────────────────────────────────────────────────
class HomeScreen extends StatefulWidget {
  final int initialTab;
  const HomeScreen({super.key, this.initialTab = 0});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late int _tabIndex;

  @override
  void initState() {
    super.initState();
    _tabIndex = widget.initialTab;
  }

  @override
  Widget build(BuildContext context) {
    final pages = [
      const _HomeTab(),
      const ChatListScreen(),
      const OrderListScreen(),
      const FavoritScreen(),
      const ProfilScreen(),
    ];

    return Scaffold(
      backgroundColor: _kBg,
      body: IndexedStack(index: _tabIndex, children: pages),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Color(0xFFE5E7EB))),
          boxShadow: [
            BoxShadow(
              color: Color(0x0A000000),
              blurRadius: 16,
              offset: Offset(0, -2),
            ),
          ],
        ),
        child: SafeArea(
          child: SizedBox(
            height: 60,
            child: Row(
              children: [
                _NavItem(
                  icon: Icons.home_outlined,
                  activeIcon: Icons.home,
                  label: 'Beranda',
                  idx: 0,
                  cur: _tabIndex,
                  onTap: (i) => setState(() => _tabIndex = i),
                ),
                _NavItem(
                  icon: Icons.chat_bubble_outline,
                  activeIcon: Icons.chat_bubble,
                  label: 'Chat',
                  idx: 1,
                  cur: _tabIndex,
                  onTap: (i) => setState(() => _tabIndex = i),
                ),
                _NavItem(
                  icon: Icons.receipt_long_outlined,
                  activeIcon: Icons.receipt_long,
                  label: 'Riwayat',
                  idx: 2,
                  cur: _tabIndex,
                  onTap: (i) => setState(() => _tabIndex = i),
                ),
                _NavItem(
                  icon: Icons.favorite_border,
                  activeIcon: Icons.favorite,
                  label: 'Favorit',
                  idx: 3,
                  cur: _tabIndex,
                  onTap: (i) => setState(() => _tabIndex = i),
                ),
                _NavItem(
                  icon: Icons.person_outline,
                  activeIcon: Icons.person,
                  label: 'Profil',
                  idx: 4,
                  cur: _tabIndex,
                  onTap: (i) => setState(() => _tabIndex = i),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData icon, activeIcon;
  final String label;
  final int idx, cur;
  final void Function(int) onTap;
  const _NavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    required this.idx,
    required this.cur,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final on = idx == cur;
    return Expanded(
      child: InkWell(
        onTap: () => onTap(idx),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              on ? activeIcon : icon,
              color: on ? _kBlue : const Color(0xFF9CA3AF),
              size: 22,
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                color: on ? _kBlue : const Color(0xFF9CA3AF),
                fontWeight: on ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Home Tab ─────────────────────────────────────────────────────────────────
class _HomeTab extends StatefulWidget {
  const _HomeTab();
  @override
  State<_HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<_HomeTab> {
  List<dynamic> _layananList = [];
  List<dynamic> _byLayanan = [];
  bool _loading = true;
  int _unreadCount = 0;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final r = await Future.wait([
        ApiService.getLayanan(),
        ApiService.getTukangByLayanan(),
        ApiService.getUnreadCount(),
      ]);
      if (!mounted) return;
      setState(() {
        _layananList = r[0] as List;
        _byLayanan = r[1] as List;
        _unreadCount = r[2] as int;
        _loading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _search() {
    final q = _searchCtrl.text.trim();
    if (q.isEmpty) return;
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => TukangListScreen(query: q)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final nama = user?['nama'] as String? ?? 'Pengguna';
    return Scaffold(
      backgroundColor: _kBg,
      body: RefreshIndicator(
        color: _kBlue,
        onRefresh: _load,
        child: CustomScrollView(
          slivers: [
            // AppBar
            SliverAppBar(
              pinned: true,
              backgroundColor: Colors.white,
              elevation: 0,
              automaticallyImplyLeading: false,
              bottom: PreferredSize(
                preferredSize: const Size.fromHeight(1),
                child: Container(height: 1, color: const Color(0xFFE5E7EB)),
              ),
              toolbarHeight: 56,
              titleSpacing: 0,
              title: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Row(
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Row(
                          children: [
                            const Icon(
                              Icons.location_on,
                              size: 13,
                              color: _kBlue,
                            ),
                            const SizedBox(width: 3),
                            Text(
                              _shortAlamat(user?['alamat'] as String?),
                              style: const TextStyle(
                                fontSize: 11,
                                color: Color(0xFF6B7280),
                              ),
                            ),
                          ],
                        ),
                        Text(
                          'Halo, ${nama.split(' ').first}! 👋',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1F2937),
                          ),
                        ),
                      ],
                    ),
                    const Spacer(),
                    Stack(
                      children: [
                        IconButton(
                          icon: const Icon(
                            Icons.notifications_outlined,
                            color: Color(0xFF374151),
                            size: 24,
                          ),
                          onPressed: () async {
                            await Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const NotifikasiScreen(),
                              ),
                            );
                            final c = await ApiService.getUnreadCount();
                            if (mounted) setState(() => _unreadCount = c);
                          },
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(),
                        ),
                        if (_unreadCount > 0)
                          Positioned(
                            right: 0,
                            top: 0,
                            child: Container(
                              width: 14,
                              height: 14,
                              decoration: const BoxDecoration(
                                color: Colors.red,
                                shape: BoxShape.circle,
                              ),
                              child: Center(
                                child: Text(
                                  _unreadCount > 9 ? '9+' : '$_unreadCount',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 8,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

            SliverToBoxAdapter(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Search
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(30),
                        border: Border.all(color: const Color(0xFFE5E7EB)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.05),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: TextField(
                        controller: _searchCtrl,
                        onSubmitted: (_) => _search(),
                        style: const TextStyle(fontSize: 14),
                        decoration: InputDecoration(
                          hintText: 'Cari jasa yang kamu inginkan...',
                          hintStyle: const TextStyle(
                            color: Color(0xFF9CA3AF),
                            fontSize: 13,
                          ),
                          prefixIcon: const Icon(
                            Icons.search,
                            color: Color(0xFF9CA3AF),
                            size: 20,
                          ),
                          suffixIcon: IconButton(
                            icon: const Icon(
                              Icons.arrow_forward,
                              color: _kBlue,
                              size: 20,
                            ),
                            onPressed: _search,
                          ),
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 13,
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Banner
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(18),
                      child: Container(
                        width: double.infinity,
                        height: 148,
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            colors: [Color(0xFF1D4ED8), _kBlueLt],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                        ),
                        child: Stack(
                          children: [
                            Positioned(
                              right: -10,
                              top: -10,
                              child: Icon(
                                Icons.handyman,
                                size: 130,
                                color: Colors.white.withOpacity(0.08),
                              ),
                            ),
                            Padding(
                              padding: const EdgeInsets.all(20),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Tomas',
                                    style: TextStyle(
                                      color: Colors.white60,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w500,
                                      letterSpacing: 1.5,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  const Text(
                                    'Solusi Jasa\nTerlengkap!',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 19,
                                      fontWeight: FontWeight.bold,
                                      height: 1.2,
                                    ),
                                  ),
                                  const Spacer(),
                                  GestureDetector(
                                    onTap: () => Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (_) =>
                                            const TukangListScreen(),
                                      ),
                                    ),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 16,
                                        vertical: 8,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.white,
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: const Text(
                                        'Pesan Sekarang',
                                        style: TextStyle(
                                          color: _kBlue,
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),

                  // Kategori
                  if (_layananList.isNotEmpty) ...[
                    const Padding(
                      padding: EdgeInsets.fromLTRB(16, 20, 16, 10),
                      child: Text(
                        'Kategori Layanan',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1F2937),
                        ),
                      ),
                    ),
                    SizedBox(
                      height: 88,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemCount: _layananList.length,
                        itemBuilder: (_, i) {
                          final l = _layananList[i] as Map<String, dynamic>;
                          final nm = l['nama_layanan'] as String? ?? '';
                          final cfg = _layananCfg(nm);
                          return GestureDetector(
                            onTap: () => Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => TukangListScreen(layanan: nm),
                              ),
                            ),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                              ),
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Container(
                                    width: 52,
                                    height: 52,
                                    decoration: BoxDecoration(
                                      color: cfg.$1,
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                    child: Icon(
                                      cfg.$2,
                                      color: cfg.$3,
                                      size: 24,
                                    ),
                                  ),
                                  const SizedBox(height: 5),
                                  SizedBox(
                                    width: 62,
                                    child: Text(
                                      nm,
                                      textAlign: TextAlign.center,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w500,
                                        color: Color(0xFF374151),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  ],

                  // Tukang sections
                  if (_loading)
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.all(40),
                        child: CircularProgressIndicator(color: _kBlue),
                      ),
                    )
                  else if (_byLayanan.isEmpty)
                    const Center(
                      child: Padding(
                        padding: EdgeInsets.all(40),
                        child: Text(
                          'Belum ada data.',
                          style: TextStyle(color: Colors.grey),
                        ),
                      ),
                    )
                  else
                    ..._byLayanan.map((sec) {
                      final s = sec as Map<String, dynamic>;
                      final lay = Layanan.fromJson(
                        s['layanan'] as Map<String, dynamic>,
                      );
                      final lst = (s['tukang'] as List)
                          .map(
                            (t) => Tukang.fromJson(t as Map<String, dynamic>),
                          )
                          .toList();
                      return _LayananSection(layanan: lay, tukangList: lst);
                    }),

                  const SizedBox(height: 16),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _shortAlamat(String? a) {
    if (a == null || a.isEmpty) return 'Surakarta';
    final s = a.split(',').first.trim();
    return s.length > 20 ? '${s.substring(0, 20)}…' : s;
  }
}

// ── Layanan icon config ───────────────────────────────────────────────────────
(Color, IconData, Color) _layananCfg(String n) {
  final s = n.toLowerCase();
  if (s.contains('ac') || s.contains('servis'))
    return (const Color(0xFFE0F2FE), Icons.ac_unit, const Color(0xFF0284C7));
  if (s.contains('tukang') || s.contains('bangun'))
    return (const Color(0xFFFFF7ED), Icons.build, const Color(0xFFEA580C));
  if (s.contains('antar') || s.contains('jemput'))
    return (
      const Color(0xFFF0FDF4),
      Icons.two_wheeler,
      const Color(0xFF16A34A),
    );
  if (s.contains('foto'))
    return (
      const Color(0xFFF5F3FF),
      Icons.photo_camera,
      const Color(0xFF7C3AED),
    );
  if (s.contains('baby') || s.contains('anak'))
    return (const Color(0xFFFFF1F2), Icons.child_care, const Color(0xFFE11D48));
  if (s.contains('listrik'))
    return (
      const Color(0xFFFEFCE8),
      Icons.electrical_services,
      const Color(0xFFCA8A04),
    );
  if (s.contains('bersih') || s.contains('cuci'))
    return (
      const Color(0xFFF0FDFA),
      Icons.cleaning_services,
      const Color(0xFF0D9488),
    );
  if (s.contains('cat'))
    return (
      const Color(0xFFFFF1F2),
      Icons.format_paint,
      const Color(0xFFE11D48),
    );
  return (
    const Color(0xFFF8FAFC),
    Icons.home_repair_service,
    const Color(0xFF475569),
  );
}

// ── Layanan Section ───────────────────────────────────────────────────────────
class _LayananSection extends StatelessWidget {
  final Layanan layanan;
  final List<Tukang> tukangList;
  const _LayananSection({required this.layanan, required this.tukangList});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    layanan.namaLayanan,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1F2937),
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) =>
                          TukangListScreen(layanan: layanan.namaLayanan),
                    ),
                  ),
                  child: const Text(
                    'Lihat Semua',
                    style: TextStyle(
                      fontSize: 12,
                      color: _kBlue,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          ),
          SizedBox(
            height: 196,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              itemCount: tukangList.length,
              itemBuilder: (_, i) => _TukangCard(tukang: tukangList[i]),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Tukang Card ───────────────────────────────────────────────────────────────
class _TukangCard extends StatelessWidget {
  final Tukang tukang;
  const _TukangCard({required this.tukang});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => TukangDetailScreen(idTukang: tukang.idTukang),
        ),
      ),
      child: Container(
        width: 144,
        margin: const EdgeInsets.symmetric(horizontal: 6),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFE5E7EB)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(15),
              ),
              child: tukang.fotoUrl != null
                  ? Image.network(
                      tukang.fotoUrl!,
                      height: 108,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _ph(),
                    )
                  : _ph(),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tukang.nama,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1F2937),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(
                        Icons.star,
                        size: 12,
                        color: Color(0xFFF59E0B),
                      ),
                      const SizedBox(width: 3),
                      Text(
                        tukang.rating.toStringAsFixed(1),
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF1F2937),
                        ),
                      ),
                      const Spacer(),
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(
                          color: tukang.statusAktif
                              ? const Color(0xFF10B981)
                              : const Color(0xFF9CA3AF),
                          shape: BoxShape.circle,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _ph() => Container(
    height: 108,
    width: double.infinity,
    color: const Color(0xFFF1F5F9),
    child: const Icon(Icons.person, size: 40, color: Color(0xFF94A3B8)),
  );
}

// ── Tukang List Screen ────────────────────────────────────────────────────────
class TukangListScreen extends StatefulWidget {
  final String? query;
  final String? layanan;
  const TukangListScreen({super.key, this.query, this.layanan});
  @override
  State<TukangListScreen> createState() => _TukangListScreenState();
}

class _TukangListScreenState extends State<TukangListScreen> {
  List<Tukang> _all = [];
  List<Tukang> _shown = [];
  List<String> _cats = [];
  String? _activeCat;
  bool _loading = true;
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _activeCat = widget.layanan;
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final data = await ApiService.getTukang(q: widget.query);
      if (!mounted) return;
      final list = data
          .map((t) => Tukang.fromJson(t as Map<String, dynamic>))
          .toList();
      final cats =
          list
              .map((t) => t.kategori ?? '')
              .where((c) => c.isNotEmpty)
              .toSet()
              .toList()
            ..sort();
      setState(() {
        _all = list;
        _cats = cats;
        _loading = false;
        _filter();
      });
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _filter() {
    final q = _searchCtrl.text.toLowerCase();
    _shown = _all.where((t) {
      final matchCat = _activeCat == null || t.kategori == _activeCat;
      final matchQ =
          q.isEmpty ||
          t.nama.toLowerCase().contains(q) ||
          (t.kategori ?? '').toLowerCase().contains(q);
      return matchCat && matchQ;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final title =
        _activeCat ??
        (widget.query != null ? 'Hasil: "${widget.query}"' : 'Semua Tukang');
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        title: Text(
          title,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 16,
            color: Color(0xFF1F2937),
          ),
        ),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF1F2937),
        elevation: 0,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(height: 1, color: const Color(0xFFE5E7EB)),
        ),
      ),
      body: Column(
        children: [
          // Search
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: const Color(0xFFE5E7EB)),
              ),
              child: TextField(
                controller: _searchCtrl,
                onChanged: (_) => setState(_filter),
                style: const TextStyle(fontSize: 13),
                decoration: const InputDecoration(
                  hintText: 'Cari nama tukang...',
                  hintStyle: TextStyle(color: Color(0xFF9CA3AF), fontSize: 13),
                  prefixIcon: Icon(
                    Icons.search,
                    color: Color(0xFF9CA3AF),
                    size: 18,
                  ),
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.symmetric(vertical: 11),
                ),
              ),
            ),
          ),
          // Category chips
          if (_cats.isNotEmpty)
            SizedBox(
              height: 40,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                children: [
                  _chip('Semua', null),
                  ..._cats.map((c) => _chip(c, c)),
                ],
              ),
            ),
          const SizedBox(height: 4),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: _kBlue))
                : _shown.isEmpty
                ? const Center(
                    child: Text(
                      'Tidak ada tukang ditemukan.',
                      style: TextStyle(color: Colors.grey),
                    ),
                  )
                : RefreshIndicator(
                    color: _kBlue,
                    onRefresh: _load,
                    child: ListView.builder(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 8,
                      ),
                      itemCount: _shown.length,
                      itemBuilder: (_, i) => _TukangListItem(tukang: _shown[i]),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _chip(String label, String? val) {
    final active = _activeCat == val;
    return GestureDetector(
      onTap: () => setState(() {
        _activeCat = val;
        _filter();
      }),
      child: Container(
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: active ? _kBlue : Colors.white,
          border: Border.all(color: active ? _kBlue : const Color(0xFFE5E7EB)),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: active ? Colors.white : const Color(0xFF374151),
          ),
        ),
      ),
    );
  }
}

class _TukangListItem extends StatelessWidget {
  final Tukang tukang;
  const _TukangListItem({required this.tukang});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => TukangDetailScreen(idTukang: tukang.idTukang),
        ),
      ),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFF1F5F9)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: tukang.fotoUrl != null
                  ? Image.network(
                      tukang.fotoUrl!,
                      width: 60,
                      height: 60,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _ph(),
                    )
                  : _ph(),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tukang.nama,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1F2937),
                    ),
                  ),
                  const SizedBox(height: 3),
                  if (tukang.kategori != null)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        tukang.kategori!,
                        style: const TextStyle(
                          fontSize: 10,
                          color: _kBlue,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  if (tukang.lokasi != null) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(
                          Icons.location_on_outlined,
                          size: 12,
                          color: Color(0xFF9CA3AF),
                        ),
                        const SizedBox(width: 3),
                        Text(
                          tukang.lokasi!,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF9CA3AF),
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            Column(
              children: [
                const Icon(Icons.star, size: 14, color: Color(0xFFF59E0B)),
                Text(
                  tukang.rating.toStringAsFixed(1),
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1F2937),
                  ),
                ),
                const SizedBox(height: 4),
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: tukang.statusAktif
                        ? const Color(0xFF10B981)
                        : const Color(0xFF9CA3AF),
                    shape: BoxShape.circle,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _ph() => Container(
    width: 60,
    height: 60,
    color: const Color(0xFFF1F5F9),
    child: const Icon(Icons.person, color: Color(0xFF94A3B8)),
  );
}
