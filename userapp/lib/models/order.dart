import '../utils/json_value.dart';

class Order {
  final int idOrder;
  final Map<String, dynamic>? tukang;
  final Map<String, dynamic>? layanan;
  final bool hasReview;
  final String pembayaranStatus; // unpaid, pending, paid, failed, expired
  final String status; // pending, confirmed, in_progress, done, rejected

  Order({
    required this.idOrder,
    this.tukang,
    this.layanan,
    required this.hasReview,
    this.pembayaranStatus = 'unpaid',
    this.status = 'pending',
  });

  factory Order.fromJson(Map<String, dynamic> j) => Order(
    idOrder: jsonInt(j['id_order']),
    tukang: j['tukang'] as Map<String, dynamic>?,
    layanan: j['layanan'] as Map<String, dynamic>?,
    hasReview: jsonBool(j['has_review']),
    pembayaranStatus: jsonString(
      (j['pembayaran'] as Map<String, dynamic>?)?['status'],
      fallback: 'unpaid',
    ),
    status: jsonString(j['status'], fallback: 'pending'),
  );
}
