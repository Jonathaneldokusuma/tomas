class Tukang {
  final int idTukang;
  final String nama;
  final String? kategori;
  final String? lokasi;
  final String? bio;
  final bool statusAktif;
  final String? fotoUrl;
  final double rating;

  Tukang({
    required this.idTukang,
    required this.nama,
    this.kategori,
    this.lokasi,
    this.bio,
    required this.statusAktif,
    this.fotoUrl,
    this.rating = 4.7,
  });

  factory Tukang.fromJson(Map<String, dynamic> j) => Tukang(
        idTukang: j['id_tukang'],
        nama: j['nama'],
        kategori: j['kategori'],
        lokasi: j['lokasi'],
        bio: j['bio'],
        statusAktif: j['status_aktif'] == true || j['status_aktif'] == 1,
        fotoUrl: j['foto_url'],
        rating: (j['rating'] ?? 4.7).toDouble(),
      );
}
