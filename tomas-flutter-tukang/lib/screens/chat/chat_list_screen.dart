import 'package:flutter/material.dart';
import '../../services/tukang_service.dart';
import '../../utils/json_value.dart';
import 'chat_detail_screen.dart';
import 'broadcast_screen.dart';

class TukangChatListScreen extends StatefulWidget {
  const TukangChatListScreen({super.key});

  @override
  State<TukangChatListScreen> createState() => _TukangChatListScreenState();
}

class _TukangChatListScreenState extends State<TukangChatListScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  List<dynamic> _conversations = [];
  List<dynamic> _broadcasts = [];
  bool _loadingChat = true;
  bool _loadingBroadcast = true;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _loadChat();
    _loadBroadcast();
  }

  @override
  void dispose() {
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
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabCtrl,
        children: [_buildChatTab(), _buildBroadcastTab()],
      ),
    );
  }

  Widget _buildChatTab() {
    if (_loadingChat) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_conversations.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.chat_bubble_outline, size: 64, color: Colors.grey[300]),
            const SizedBox(height: 12),
            Text(
              'Belum ada percakapan',
              style: TextStyle(color: Colors.grey[500], fontSize: 16),
            ),
          ],
        ),
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
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.campaign_outlined, size: 64, color: Colors.grey[300]),
            const SizedBox(height: 12),
            Text(
              'Belum ada pesan dari pusat',
              style: TextStyle(color: Colors.grey[500], fontSize: 16),
            ),
          ],
        ),
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
