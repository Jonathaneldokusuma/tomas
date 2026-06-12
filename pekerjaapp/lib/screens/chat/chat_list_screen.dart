import 'package:flutter/material.dart';

import '../../services/tukang_service.dart';
import '../../utils/json_value.dart';
import 'broadcast_screen.dart';
import 'chat_detail_screen.dart';

class TukangChatListScreen extends StatefulWidget {
  final int initialTabIndex;

  const TukangChatListScreen({super.key, this.initialTabIndex = 0});

  @override
  State<TukangChatListScreen> createState() => _TukangChatListScreenState();
}

class _TukangChatListScreenState extends State<TukangChatListScreen>
    with SingleTickerProviderStateMixin {
  static const List<Map<String, String>> _supportCategories = [
    {'value': 'bantuan', 'label': 'Bantuan'},
    {'value': 'laporan', 'label': 'Laporan'},
    {'value': 'bug', 'label': 'Bug / Error'},
    {'value': 'saran', 'label': 'Saran'},
    {'value': 'lainnya', 'label': 'Lainnya'},
  ];

  late TabController _tabCtrl;
  final TextEditingController _supportCtrl = TextEditingController();

  List<dynamic> _conversations = [];
  List<dynamic> _broadcasts = [];
  List<dynamic> _supportMessages = [];

  bool _loadingChat = true;
  bool _loadingBroadcast = true;
  bool _loadingSupport = true;
  bool _sendingSupport = false;
  String _supportCategory = 'bantuan';

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(
      length: 3,
      vsync: this,
      initialIndex: widget.initialTabIndex.clamp(0, 2).toInt(),
    );
    _loadChat();
    _loadBroadcast();
    _loadSupport();
  }

  @override
  void dispose() {
    _supportCtrl.dispose();
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadChat() async {
    setState(() => _loadingChat = true);
    try {
      final data = await TukangService.getChatInbox();
      setState(() {
        _conversations = data;
        _loadingChat = false;
      });
    } catch (_) {
      setState(() => _loadingChat = false);
    }
  }

  Future<void> _loadBroadcast() async {
    setState(() => _loadingBroadcast = true);
    try {
      final data = await TukangService.getBroadcasts();
      setState(() {
        _broadcasts = data;
        _loadingBroadcast = false;
      });
    } catch (_) {
      setState(() => _loadingBroadcast = false);
    }
  }

  Future<void> _loadSupport() async {
    setState(() => _loadingSupport = true);
    try {
      final data = await TukangService.getSupportMessages();
      setState(() {
        _supportMessages = (data['messages'] as List?) ?? [];
        _loadingSupport = false;
      });
    } catch (_) {
      setState(() => _loadingSupport = false);
    }
  }

  Future<void> _sendSupport() async {
    final pesan = _supportCtrl.text.trim();
    if (pesan.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tuliskan pesan dulu.')),
      );
      return;
    }

    final messenger = ScaffoldMessenger.of(context);
    setState(() => _sendingSupport = true);
    try {
      final res = await TukangService.sendSupportMessage(
        kategori: _supportCategory,
        pesan: pesan,
      );
      if (!mounted) return;
      if ((res['statusCode'] ?? 0) >= 200 && (res['statusCode'] ?? 0) < 300) {
        _supportCtrl.clear();
        await _loadSupport();
        messenger.showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? 'Pesan terkirim ke pusat'),
          ),
        );
      } else {
        messenger.showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Gagal mengirim pesan')),
        );
      }
    } catch (_) {
      if (!mounted) return;
      messenger.showSnackBar(
        const SnackBar(content: Text('Gagal terhubung ke pusat bantuan')),
      );
    } finally {
      if (mounted) setState(() => _sendingSupport = false);
    }
  }

  String _supportLabel(String value) {
    return _supportCategories.firstWhere(
      (item) => item['value'] == value,
      orElse: () => _supportCategories.first,
    )['label']!;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pesan'),
        backgroundColor: const Color(0xFF1976D2),
        foregroundColor: Colors.white,
        bottom: TabBar(
          controller: _tabCtrl,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
          tabs: const [
            Tab(icon: Icon(Icons.chat_bubble_outline), text: 'Chat Pelanggan'),
            Tab(icon: Icon(Icons.campaign_outlined), text: 'Pesan Pusat'),
            Tab(icon: Icon(Icons.headset_mic_outlined), text: 'Pusat Bantuan'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        children: [
          _buildChatTab(),
          _buildBroadcastTab(),
          _buildSupportTab(),
        ],
      ),
    );
  }

  Widget _buildChatTab() {
    if (_loadingChat) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_conversations.isEmpty) {
      return _emptyState(
        icon: Icons.chat_bubble_outline,
        title: 'Belum ada percakapan',
        subtitle: 'Saat pelanggan menghubungi kamu, daftar chat akan muncul di sini.',
      );
    }
    return RefreshIndicator(
      onRefresh: _loadChat,
      child: ListView.separated(
        itemCount: _conversations.length,
        separatorBuilder: (_, __) => const Divider(height: 1),
        itemBuilder: (ctx, i) {
          final conv = _conversations[i];
          final user = conv['user'] as Map<String, dynamic>?;
          final name = user?['name'] ?? 'Pelanggan';
          final lastMsg = conv['last_message'] ?? '';
          final unread = jsonInt(conv['unread']);
          final lastTime = conv['last_time'] != null
              ? _formatTime(conv['last_time'].toString())
              : '';

          return ListTile(
            leading: CircleAvatar(
              backgroundColor: const Color(0xFF1976D2),
              child: Text(
                name.isNotEmpty ? name[0].toUpperCase() : 'U',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            title: Text(
              name,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
            subtitle: Text(
              lastMsg,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(color: Colors.grey[600]),
            ),
            trailing: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                if (lastTime.isNotEmpty)
                  Text(
                    lastTime,
                    style: TextStyle(fontSize: 11, color: Colors.grey[500]),
                  ),
                if (unread > 0) ...[
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 6,
                      vertical: 2,
                    ),
                    decoration: const BoxDecoration(
                      color: Color(0xFF1976D2),
                      borderRadius: BorderRadius.all(Radius.circular(10)),
                    ),
                    child: Text(
                      '$unread',
                      style: const TextStyle(color: Colors.white, fontSize: 11),
                    ),
                  ),
                ],
              ],
            ),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => ChatDetailScreen(
                  idUser: jsonInt(user?['id_user']),
                  namaUser: name,
                ),
              ),
            ).then((_) => _loadChat()),
          );
        },
      ),
    );
  }

  Widget _buildBroadcastTab() {
    if (_loadingBroadcast) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_broadcasts.isEmpty) {
      return _emptyState(
        icon: Icons.campaign_outlined,
        title: 'Belum ada pesan dari pusat',
        subtitle: 'Pengumuman resmi dari admin akan tampil di tab ini.',
      );
    }
    return RefreshIndicator(
      onRefresh: _loadBroadcast,
      child: ListView.builder(
        padding: const EdgeInsets.all(12),
        itemCount: _broadcasts.length,
        itemBuilder: (ctx, i) {
          final msg = _broadcasts[i];
          final tipe = msg['tipe'] ?? 'info';
          Color tipeColor = const Color(0xFF1976D2);
          IconData tipeIcon = Icons.info_outline;
          if (tipe == 'warning') {
            tipeColor = Colors.orange;
            tipeIcon = Icons.warning_amber_outlined;
          } else if (tipe == 'promo') {
            tipeColor = Colors.green;
            tipeIcon = Icons.local_offer_outlined;
          }

          return Card(
            margin: const EdgeInsets.only(bottom: 10),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: BorderSide(color: tipeColor.withOpacity(0.3)),
            ),
            child: InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => BroadcastDetailScreen(broadcast: msg),
                ),
              ),
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: tipeColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(tipeIcon, color: tipeColor, size: 22),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            msg['judul'] ?? '',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            msg['isi'] ?? '',
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            _formatTime(msg['created_at']?.toString() ?? ''),
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey[400],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildSupportTab() {
    if (_loadingSupport) {
      return const Center(child: CircularProgressIndicator());
    }

    return RefreshIndicator(
      onRefresh: _loadSupport,
      child: ListView(
        padding: const EdgeInsets.all(12),
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF2563EB), Color(0xFF4F46E5)],
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(Icons.headset_mic_outlined, color: Colors.white),
                SizedBox(height: 10),
                Text(
                  'Pusat Bantuan',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 6),
                Text(
                  'Kirim laporan, bug, atau pertanyaan langsung ke customer service pusat.',
                  style: TextStyle(color: Colors.white70, height: 1.4),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Card(
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(color: Colors.grey.shade200),
            ),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Buat Laporan Baru',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    value: _supportCategory,
                    decoration: InputDecoration(
                      labelText: 'Kategori',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    items: _supportCategories
                        .map(
                          (item) => DropdownMenuItem<String>(
                            value: item['value'],
                            child: Text(item['label'] ?? ''),
                          ),
                        )
                        .toList(),
                    onChanged: (value) {
                      if (value == null) return;
                      setState(() => _supportCategory = value);
                    },
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _supportCtrl,
                    maxLines: 4,
                    decoration: InputDecoration(
                      hintText: 'Tulis pesan kamu ke pusat di sini...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _sendingSupport ? null : _sendSupport,
                      icon: _sendingSupport
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.send_rounded),
                      label: Text(
                        _sendingSupport ? 'Mengirim...' : 'Kirim ke Pusat',
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2563EB),
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
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Riwayat Pesan',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
              ),
              TextButton.icon(
                onPressed: _loadSupport,
                icon: const Icon(Icons.refresh, size: 18),
                label: const Text('Muat ulang'),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (_supportMessages.isEmpty)
            _emptyState(
              icon: Icons.inbox_outlined,
              title: 'Belum ada tiket bantuan',
              subtitle: 'Pesan yang kamu kirim ke pusat akan muncul di sini.',
            )
          else
            ..._supportMessages.map((msg) => _supportBubble(msg)),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _supportBubble(dynamic raw) {
    final msg = raw as Map<String, dynamic>;
    final isFromTukang = jsonBool(msg['dari_tukang']);
    final kategori = _supportLabel(msg['kategori']?.toString() ?? 'bantuan');
    final createdAt = msg['created_at']?.toString() ?? '';

    return Align(
      alignment: isFromTukang ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(12),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.82,
        ),
        decoration: BoxDecoration(
          color: isFromTukang ? const Color(0xFFDCEEFB) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isFromTukang ? const Color(0xFFBFDBFE) : Colors.grey.shade200,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment:
              isFromTukang ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  isFromTukang ? Icons.person : Icons.support_agent,
                  size: 16,
                  color: isFromTukang ? const Color(0xFF2563EB) : Colors.grey[700],
                ),
                const SizedBox(width: 6),
                Text(
                  isFromTukang ? 'Kamu' : 'Pusat',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: isFromTukang ? const Color(0xFF2563EB) : Colors.grey[800],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.8),
                    borderRadius: BorderRadius.circular(999),
                  ),
                  child: Text(
                    kategori,
                    style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              msg['pesan']?.toString() ?? '',
              style: const TextStyle(fontSize: 14, height: 1.5),
            ),
            if (createdAt.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                _formatTime(createdAt),
                style: TextStyle(fontSize: 11, color: Colors.grey[500]),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _emptyState({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 24),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        children: [
          Icon(icon, size: 52, color: Colors.grey[300]),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            subtitle,
            style: TextStyle(color: Colors.grey[600], height: 1.5),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  String _formatTime(String raw) {
    if (raw.isEmpty) return '';
    try {
      final dt = DateTime.parse(raw).toLocal();
      final now = DateTime.now();
      final diff = now.difference(dt);
      if (diff.inDays == 0) {
        return '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
      } else if (diff.inDays == 1) {
        return 'Kemarin';
      } else {
        return '${dt.day}/${dt.month}/${dt.year}';
      }
    } catch (_) {
      return '';
    }
  }
}
