int jsonInt(dynamic value, {int fallback = 0}) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) {
    return int.tryParse(value) ?? double.tryParse(value)?.toInt() ?? fallback;
  }
  if (value is bool) return value ? 1 : 0;
  return fallback;
}

int? jsonIntOrNull(dynamic value) {
  if (value == null) return null;
  if (value is String && value.trim().isEmpty) return null;
  final parsed = jsonInt(value, fallback: -1);
  return parsed == -1 && value != -1 && value != '-1' ? null : parsed;
}

double jsonDouble(dynamic value, {double fallback = 0}) {
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value) ?? fallback;
  if (value is bool) return value ? 1 : 0;
  return fallback;
}

double? jsonDoubleOrNull(dynamic value) {
  if (value == null) return null;
  if (value is String && value.trim().isEmpty) return null;
  return jsonDouble(value);
}

bool jsonBool(dynamic value, {bool fallback = false}) {
  if (value is bool) return value;
  if (value is num) return value != 0;
  if (value is String) {
    final normalized = value.trim().toLowerCase();
    if (normalized == '1' ||
        normalized == 'true' ||
        normalized == 'yes' ||
        normalized == 'aktif' ||
        normalized == 'active') {
      return true;
    }
    if (normalized == '0' ||
        normalized == 'false' ||
        normalized == 'no' ||
        normalized == 'nonaktif' ||
        normalized == 'inactive') {
      return false;
    }
  }
  return fallback;
}

String jsonString(dynamic value, {String fallback = ''}) {
  if (value == null) return fallback;
  return value.toString();
}

String? jsonStringOrNull(dynamic value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}
