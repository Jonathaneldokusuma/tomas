import '../utils/json_value.dart';

class Order {
  final int idOrder;
  final Map<String, dynamic>? tukang;
  final Map<String, dynamic>? layanan;
  final bool hasReview;
  final String pembayaranStatus; // unpaid, pending, paid, failed, expired
  final String status; // pending, confirmed, in_progress, done, rejected
  final String completionStatus; // waiting_completion, waiting_user, waiting_tukang, both_completed
  final String difficultyLevel;
  final double depositFee;

  Order({
    required this.idOrder,
    this.tukang,
    this.layanan,
    required this.hasReview,
    this.pembayaranStatus = 'unpaid',
    this.status = 'pending',
    this.completionStatus = 'waiting_completion',
    this.difficultyLevel = 'medium',
    this.depositFee = 0,
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
    completionStatus: jsonString(j['completion_status'], fallback: 'waiting_completion'),
    difficultyLevel: jsonString(j['difficulty_level'], fallback: 'medium'),
    depositFee: jsonDouble(j['deposit_fee']),
  );
}
