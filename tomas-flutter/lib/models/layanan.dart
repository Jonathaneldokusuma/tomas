import 'tukang.dart';
import '../utils/json_value.dart';

class Layanan {
  final int idLayanan;
  final String namaLayanan;

  Layanan({required this.idLayanan, required this.namaLayanan});

  factory Layanan.fromJson(Map<String, dynamic> j) => Layanan(
    idLayanan: jsonInt(j['id_layanan']),
    namaLayanan: jsonString(j['nama_layanan'], fallback: 'Layanan'),
  );
}

class LayananWithTukang {
  final Layanan layanan;
  final List<Tukang> tukangList;

  LayananWithTukang({required this.layanan, required this.tukangList});

  factory LayananWithTukang.fromJson(Map<String, dynamic> j) =>
      LayananWithTukang(
        layanan: Layanan.fromJson(j['layanan'] as Map<String, dynamic>),
        tukangList: (j['tukang'] as List? ?? [])
            .map((t) => Tukang.fromJson(t as Map<String, dynamic>))
            .toList(),
      );
}
