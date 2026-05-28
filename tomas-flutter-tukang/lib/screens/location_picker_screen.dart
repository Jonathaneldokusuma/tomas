import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';

const _kBlue = Color(0xFF2563EB);

class LocationPickerScreen extends StatefulWidget {
  final double? initialLat;
  final double? initialLng;

  const LocationPickerScreen({super.key, this.initialLat, this.initialLng});

  @override
  State<LocationPickerScreen> createState() => _LocationPickerScreenState();
}

class _LocationPickerScreenState extends State<LocationPickerScreen> {
  // Default: Jakarta Pusat
  LatLng _pinned = const LatLng(-6.2088, 106.8456);
  bool _locating = false;
  final MapController _mapCtrl = MapController();

  @override
  void initState() {
    super.initState();
    if (widget.initialLat != null && widget.initialLng != null) {
      _pinned = LatLng(widget.initialLat!, widget.initialLng!);
    }
  }

  Future<void> _goToCurrentLocation() async {
    setState(() => _locating = true);
    try {
      LocationPermission perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.deniedForever) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Izin lokasi ditolak. Aktifkan di pengaturan.')),
        );
        return;
      }
      final pos = await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
      final loc = LatLng(pos.latitude, pos.longitude);
      setState(() => _pinned = loc);
      _mapCtrl.move(loc, 16);
    } catch (_) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal mendapatkan lokasi GPS.')));
    } finally {
      setState(() => _locating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: _kBlue,
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text('Pilih Lokasi', style: TextStyle(fontWeight: FontWeight.bold)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, _pinned),
            child: const Text('Gunakan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15)),
          ),
        ],
      ),
      body: Stack(
        children: [
          FlutterMap(
            mapController: _mapCtrl,
            options: MapOptions(
              initialCenter: _pinned,
              initialZoom: 14,
              onTap: (_, latlng) => setState(() => _pinned = latlng),
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.tomas.tomas_flutter_tukang',
              ),
              MarkerLayer(
                markers: [
                  Marker(
                    point: _pinned,
                    width: 48,
                    height: 56,
                    child: Column(
                      children: [
                        Container(
                          width: 36, height: 36,
                          decoration: const BoxDecoration(color: _kBlue, shape: BoxShape.circle),
                          child: const Icon(Icons.construction, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 0),
                        CustomPaint(painter: _TrianglePainter(), size: const Size(12, 8)),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
          // Info bar bottom
          Positioned(
            bottom: 0, left: 0, right: 0,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 10)],
              ),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                Container(width: 36, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2))),
                const SizedBox(height: 12),
                Row(children: [
                  const Icon(Icons.location_pin, color: _kBlue, size: 18),
                  const SizedBox(width: 8),
                  Expanded(child: Text(
                    'Lat: ${_pinned.latitude.toStringAsFixed(6)},  Lng: ${_pinned.longitude.toStringAsFixed(6)}',
                    style: const TextStyle(fontSize: 13, color: Color(0xFF374151)),
                  )),
                ]),
                const SizedBox(height: 4),
                const Text('Tap pada peta untuk memindahkan pin lokasi kamu.', style: TextStyle(fontSize: 11, color: Colors.grey)),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => Navigator.pop(context, _pinned),
                    icon: const Icon(Icons.check_circle_outline, size: 18),
                    label: const Text('Gunakan Lokasi Ini', style: TextStyle(fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _kBlue, foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                  ),
                ),
              ]),
            ),
          ),
          // GPS button
          Positioned(
            top: 12, right: 12,
            child: FloatingActionButton.small(
              backgroundColor: Colors.white,
              onPressed: _locating ? null : _goToCurrentLocation,
              child: _locating
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: _kBlue))
                  : const Icon(Icons.my_location, color: _kBlue),
            ),
          ),
          // Attribution
          Positioned(
            bottom: 160, right: 6,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(color: Colors.white.withOpacity(0.8), borderRadius: BorderRadius.circular(4)),
              child: const Text('© OpenStreetMap', style: TextStyle(fontSize: 9, color: Colors.black54)),
            ),
          ),
        ],
      ),
    );
  }
}

class _TrianglePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = const Color(0xFF2563EB);
    final path = ui.Path()
      ..moveTo(0, 0)
      ..lineTo(size.width, 0)
      ..lineTo(size.width / 2, size.height)
      ..close();
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(_) => false;
}
