import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../auth/login_screen.dart';
import 'edit_profil_screen.dart';
import '../support/support_screen.dart';

class ProfilScreen extends StatelessWidget {
  const ProfilScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final nama = user?['nama'] as String? ?? '-';
    final noHp = user?['no_hp'] as String? ?? '-';
    final email = user?['email'] as String? ?? '';
    final alamat = user?['alamat'] as String? ?? '';
    final badges = (user?['badges'] as List? ?? [])
        .whereType<Map>()
        .map((item) => Map<String, dynamic>.from(item))
        .toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF2F2F7),
      body: CustomScrollView(
        slivers: [
          // ── Collapsible Header ─────────────────────────────────────────────
          SliverAppBar(
            expandedHeight: 220,
            pinned: true,
            backgroundColor: const Color(0xFF2563EB),
            automaticallyImplyLeading: false,
            actions: [
              IconButton(
                icon: const Icon(Icons.edit_outlined, color: Colors.white),
                tooltip: 'Edit Profil',
                onPressed: () => Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const EditProfilScreen()),
                ),
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF1D4ED8), Color(0xFF2563EB)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 40),
                    // Avatar
                    Container(
                      width: 80,
                      height: 80,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: const LinearGradient(
                          colors: [Color(0xFF2563EB), Color(0xFF1D4ED8)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black26,
                            blurRadius: 12,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Center(
                        child: Text(
                          nama.isNotEmpty ? nama[0].toUpperCase() : '?',
                          style: const TextStyle(
                            fontSize: 32,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      nama,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.phone_outlined,
                          color: Colors.white60,
                          size: 13,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          noHp,
                          style: const TextStyle(
                            fontSize: 13,
                            color: Colors.white70,
                          ),
                        ),
                      ],
                    ),
                    if (alamat.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(
                            Icons.location_on_outlined,
                            color: Colors.white60,
                            size: 13,
                          ),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              alamat,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 12,
                                color: Colors.white60,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),

          SliverToBoxAdapter(
            child: Column(
              children: [
                const SizedBox(height: 20),

                // ── Info Akun ────────────────────────────────────────────────
                _Section(
                  title: 'Informasi Akun',
                  children: [
                    _InfoTile(
                      icon: Icons.person_outline,
                      label: 'Nama Lengkap',
                      value: nama,
                    ),
                    _InfoTile(
                      icon: Icons.phone_outlined,
                      label: 'No. HP',
                      value: noHp,
                    ),
                    if (email.isNotEmpty)
                      _InfoTile(
                        icon: Icons.email_outlined,
                        label: 'Email',
                        value: email,
                      ),
                    _InfoTile(
                      icon: Icons.location_on_outlined,
                      label: 'Alamat',
                      value: alamat.isNotEmpty ? alamat : 'Belum diisi',
                      valueColor: alamat.isEmpty ? Colors.grey : null,
                    ),
                  ],
                ),

                const SizedBox(height: 20),

                _Section(
                  title: 'Badge Saya',
                  children: [
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: badges.isEmpty
                          ? Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Text(
                                'Belum ada badge. Badge dari admin akan tampil di sini.',
                                style: TextStyle(
                                  color: Colors.grey.shade600,
                                  fontSize: 12,
                                  height: 1.4,
                                ),
                              ),
                            )
                          : SizedBox(
                              height: 148,
                              child: ListView.separated(
                                scrollDirection: Axis.horizontal,
                                itemCount: badges.length,
                                separatorBuilder: (_, __) =>
                                    const SizedBox(width: 12),
                                itemBuilder: (_, index) =>
                                    _badgeCard(badges[index]),
                              ),
                            ),
                    ),
                  ],
                ),

                const SizedBox(height: 20),

                _Section(
                  title: 'Pusat Bantuan',
                  children: [
                    ListTile(
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => const SupportScreen(),
                        ),
                      ),
                      leading: Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(9),
                        ),
                        child: const Icon(
                          Icons.headset_mic_outlined,
                          color: Color(0xFF1565C0),
                          size: 18,
                        ),
                      ),
                      title: const Text(
                        'Chat ke Customer Service',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Color(0xFF1A2B47),
                        ),
                      ),
                      subtitle: const Text(
                        'Laporkan bug, pertanyaan, atau hal ganjal lainnya.',
                        style: TextStyle(fontSize: 12),
                      ),
                      trailing: const Icon(
                        Icons.chevron_right,
                        color: Colors.grey,
                        size: 18,
                      ),
                      dense: true,
                    ),
                  ],
                ),

                const SizedBox(height: 20),

                // ── Menu ────────────────────────────────────────────────────
                _Section(
                  title: 'Pengaturan',
                  children: [
                    _MenuTile(
                      icon: Icons.edit_outlined,
                      label: 'Edit Profil',
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => const EditProfilScreen(),
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 20),

                // ── Logout ────────────────────────────────────────────────
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        final confirm = await showDialog<bool>(
                          context: context,
                          builder: (_) => AlertDialog(
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                            ),
                            title: const Text(
                              'Keluar',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                            content: const Text(
                              'Yakin ingin keluar dari akun?',
                            ),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.pop(context, false),
                                child: const Text('Batal'),
                              ),
                              TextButton(
                                onPressed: () => Navigator.pop(context, true),
                                child: const Text(
                                  'Keluar',
                                  style: TextStyle(color: Colors.red),
                                ),
                              ),
                            ],
                          ),
                        );
                        if (confirm == true && context.mounted) {
                          await context.read<AuthProvider>().logout();
                          if (context.mounted) {
                            Navigator.pushAndRemoveUntil(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const LoginScreen(),
                              ),
                              (_) => false,
                            );
                          }
                        }
                      },
                      icon: const Icon(
                        Icons.logout,
                        color: Colors.red,
                        size: 18,
                      ),
                      label: const Text(
                        'Keluar',
                        style: TextStyle(
                          color: Colors.red,
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: Colors.red),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 32),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _badgeCard(Map<String, dynamic> badge) {
    final imageUrl = badge['image_url']?.toString() ?? '';
    final label = badge['label']?.toString() ?? 'Badge';
    final description = badge['description']?.toString() ?? '';
    final color = badge['color']?.toString() ?? '#2563EB';
    final accent = _parseColor(color, fallback: const Color(0xFF2563EB));

    return Container(
      width: 165,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: accent.withOpacity(0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: accent.withOpacity(0.18)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(14),
              child: imageUrl.isNotEmpty
                  ? Image.network(
                      imageUrl,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Icon(
                        Icons.verified_rounded,
                        color: accent,
                      ),
                    )
                  : Icon(Icons.verified_rounded, color: accent),
            ),
          ),
          const SizedBox(height: 10),
          Text(
            label,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.bold,
              color: accent,
            ),
          ),
          if (description.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              description,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 11,
                color: accent.withOpacity(0.88),
                height: 1.35,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Color _parseColor(String? value, {required Color fallback}) {
    if (value == null || value.trim().isEmpty) return fallback;
    final normalized = value.trim().replaceAll('#', '');
    try {
      if (normalized.length == 6) {
        return Color(int.parse('FF$normalized', radix: 16));
      }
      if (normalized.length == 8) {
        return Color(int.parse(normalized, radix: 16));
      }
    } catch (_) {}
    return fallback;
  }
}

// ── Widgets ─────────────────────────────────────────────────────────────────

class _Section extends StatelessWidget {
  final String title;
  final List<Widget> children;
  const _Section({required this.title, required this.children});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
          child: Text(
            title,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: Colors.grey,
              letterSpacing: 0.5,
            ),
          ),
        ),
        Container(
          margin: const EdgeInsets.symmetric(horizontal: 16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(children: children),
        ),
      ],
    );
  }
}

class _InfoTile extends StatelessWidget {
  final IconData icon;
  final String label, value;
  final Color? valueColor;
  const _InfoTile({
    required this.icon,
    required this.label,
    required this.value,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: const Color(0xFFEFF6FF),
          borderRadius: BorderRadius.circular(9),
        ),
        child: Icon(icon, color: const Color(0xFF1565C0), size: 18),
      ),
      title: Text(
        label,
        style: const TextStyle(fontSize: 11, color: Colors.grey),
      ),
      subtitle: Text(
        value,
        style: TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.w500,
          color: valueColor ?? const Color(0xFF1A2B47),
        ),
      ),
      dense: true,
    );
  }
}

class _MenuTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  const _MenuTile({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      onTap: onTap,
      leading: Container(
        width: 36,
        height: 36,
        decoration: BoxDecoration(
          color: const Color(0xFFEFF6FF),
          borderRadius: BorderRadius.circular(9),
        ),
        child: Icon(icon, color: const Color(0xFF1565C0), size: 18),
      ),
      title: Text(
        label,
        style: const TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.w500,
          color: Color(0xFF1A2B47),
        ),
      ),
      trailing: const Icon(Icons.chevron_right, color: Colors.grey, size: 18),
      dense: true,
    );
  }
}
