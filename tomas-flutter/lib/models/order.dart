class Order {
  final int idOrder;
  final Map<String, dynamic>? tukang;
  final Map<String, dynamic>? layanan;
  final bool hasReview;
  final String pembayaranStatus; // unpaid, pending, paid, failed, expired

  Order({
    required this.idOrder,
    this.tukang,
    this.layanan,
    required this.hasReview,
    this.pembayaranStatus = 'unpaid',
  });

  factory Order.fromJson(Map<String, dynamic> j) => Order(
    idOrder: j['id_order'],
    tukang: j['tukang'] as Map<String, dynamic>?,
    layanan: j['layanan'] as Map<String, dynamic>?,
    hasReview: j['has_review'] == true,
    pembayaranStatus:
        (j['pembayaran'] as Map<String, dynamic>?)?['status'] ?? 'unpaid',
  );
}
