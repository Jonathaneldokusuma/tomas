import '../utils/json_value.dart';

class Tukang {
  final int idTukang;
  final String nama;
  final String? kategori;
  final String? lokasi;
  final String? alamat;
  final String? bio;
  final String? noHp;
  final double? tarif;
  final bool statusAktif;
  final String? fotoUrl;
  final double rating;
  final double? latitude;
  final double? longitude;

  Tukang({
    required this.idTukang,
    required this.nama,
    this.kategori,
    this.lokasi,
    this.alamat,
    this.bio,
    this.noHp,
    this.tarif,
    required this.statusAktif,
    this.fotoUrl,
    this.rating = 4.7,
    this.latitude,
    this.longitude,
  });

  factory Tukang.fromJson(Map<String, dynamic> j) => Tukang(
    idTukang: jsonInt(j['id_tukang']),
    nama: jsonString(j['nama'], fallback: 'Tukang'),
    kategori: jsonStringOrNull(j['kategori']),
    lokasi: jsonStringOrNull(j['lokasi']),
    alamat: jsonStringOrNull(j['alamat']),
    bio: jsonStringOrNull(j['bio']),
    noHp: jsonStringOrNull(j['no_hp']),
    tarif: jsonDoubleOrNull(j['tarif']),
    statusAktif: jsonBool(j['status_aktif']),
    fotoUrl: jsonStringOrNull(j['foto_url']),
    rating: jsonDouble(j['rating'], fallback: 4.7),
    latitude: jsonDoubleOrNull(j['latitude']),
    longitude: jsonDoubleOrNull(j['longitude']),
  );
}
