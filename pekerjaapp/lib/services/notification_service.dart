import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'tukang_service.dart';

const _channelId = 'tomas_notifications';
const _channelName = 'Tomas Tukang Notifications';

String _remoteTitle(RemoteMessage message) {
  final title = message.notification?.title;
  if (title != null && title.trim().isNotEmpty) return title;

  for (final key in ['title', 'judul']) {
    final value = message.data[key]?.toString();
    if (value != null && value.trim().isNotEmpty) return value;
  }

  return 'Notifikasi';
}

String _remoteBody(RemoteMessage message) {
  final body = message.notification?.body;
  if (body != null && body.trim().isNotEmpty) return body;

  for (final key in ['body', 'pesan']) {
    final value = message.data[key]?.toString();
    if (value != null && value.trim().isNotEmpty) return value;
  }

  return '';
}

Future<void> _showRemoteMessageNotification(
  RemoteMessage message, {
  FlutterLocalNotificationsPlugin? plugin,
}) async {
  final local = plugin ?? FlutterLocalNotificationsPlugin();

  if (plugin == null) {
    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );
    await local.initialize(
      const InitializationSettings(android: android, iOS: ios),
    );
  }

  const channel = AndroidNotificationChannel(
    _channelId,
    _channelName,
    importance: Importance.high,
    playSound: true,
    enableVibration: true,
  );

  await local
      .resolvePlatformSpecificImplementation<
        AndroidFlutterLocalNotificationsPlugin
      >()
      ?.createNotificationChannel(channel);

  await local.show(
    DateTime.now().millisecondsSinceEpoch ~/ 1000,
    _remoteTitle(message),
    _remoteBody(message),
    NotificationDetails(
      android: AndroidNotificationDetails(
        _channelId,
        _channelName,
        importance: Importance.high,
        priority: Priority.high,
        icon: '@mipmap/ic_launcher',
      ),
      iOS: const DarwinNotificationDetails(),
    ),
  );
}

/// Handle FCM background messages (must be top-level function)
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp().timeout(const Duration(seconds: 8));
    await _showRemoteMessageNotification(message);
  } catch (_) {
    // Ignore background notification failures from incomplete Firebase config.
  }
}

class NotificationService {
  static final FlutterLocalNotificationsPlugin _local =
      FlutterLocalNotificationsPlugin();

  static Future<void> init() async {
    // --- Local notifications setup ---
    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );
    await _local.initialize(
      const InitializationSettings(android: android, iOS: ios),
    );

    const channel = AndroidNotificationChannel(
      _channelId,
      _channelName,
      importance: Importance.high,
      playSound: true,
      enableVibration: true,
    );
    await _local
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >()
        ?.createNotificationChannel(channel);
    await _local
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >()
        ?.requestNotificationsPermission();

    // --- Firebase Messaging setup ---
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // Request permission (iOS)
    await FirebaseMessaging.instance.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // Foreground messages → tampilkan local notification
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      _showRemoteMessageNotification(message, plugin: _local);
    });

    FirebaseMessaging.instance.onTokenRefresh.listen((_) {
      saveFcmTokenToServer();
    });
  }

  /// Ambil FCM token dan kirim ke backend. Panggil setelah login berhasil.
  static Future<void> saveFcmTokenToServer() async {
    try {
      final fcmToken = await FirebaseMessaging.instance.getToken();
      if (fcmToken == null) return;

      final prefs = await SharedPreferences.getInstance();

      await TukangService.saveFcmToken(fcmToken);
      await prefs.setString('last_tukang_fcm_token', fcmToken);
    } catch (_) {
      // Jangan crash kalau FCM belum dikonfigurasi
    }
  }

  static Future<void> showLocalNotification({
    required String title,
    required String body,
    String? payload,
  }) async {
    await _local.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title,
      body,
      NotificationDetails(
        android: AndroidNotificationDetails(
          _channelId,
          _channelName,
          importance: Importance.high,
          priority: Priority.high,
          icon: '@mipmap/ic_launcher',
        ),
        iOS: const DarwinNotificationDetails(),
      ),
      payload: payload,
    );
  }
}
