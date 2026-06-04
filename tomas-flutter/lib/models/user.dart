import '../utils/json_value.dart';

class UserModel {
  final int idUser;
  final String nama;
  final String noHp;
  final String? token;

  UserModel({
    required this.idUser,
    required this.nama,
    required this.noHp,
    this.token,
  });

  factory UserModel.fromJson(Map<String, dynamic> j) => UserModel(
    idUser: jsonInt(j['id_user']),
    nama: jsonString(j['nama']),
    noHp: jsonString(j['no_hp']),
    token: jsonStringOrNull(j['token']),
  );
}
