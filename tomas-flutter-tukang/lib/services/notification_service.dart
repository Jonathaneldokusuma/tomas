import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'tukang_service.dart';

/// Handle FCM background messages (must be top-level function)
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Background messages handled silently
}

class NotificationService {
  static final FlutterLocalNotificationsPlugin _local =
      FlutterLocalNotificationsPlugin();

  static const _channelId = 'tomas_notifications';
  static const _channelName = 'Tomas Tukang Notifications';

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
      final notification = message.notification;
      if (notification != null) {
        showLocalNotification(
          title: notification.title ?? 'Notifikasi',
          body: notification.body ?? '',
          payload: message.data['type'],
        );
      }
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
