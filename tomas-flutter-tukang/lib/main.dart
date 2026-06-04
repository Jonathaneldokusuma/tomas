import 'dart:async';
import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'services/notification_service.dart';
import 'screens/splash_screen.dart';
import 'screens/login_screen.dart';
import 'screens/register_screen.dart';
import 'screens/ktp_upload_screen.dart';
import 'screens/waiting_verification_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/order_detail_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/chat/chat_list_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  FlutterError.onError = (details) {
    FlutterError.presentError(details);
    debugPrint('TOMAS_TUKANG_FLUTTER_ERROR: ${details.exceptionAsString()}');
    if (details.stack != null) {
      debugPrintStack(stackTrace: details.stack);
    }
  };
  PlatformDispatcher.instance.onError = (error, stack) {
    debugPrint('TOMAS_TUKANG_PLATFORM_ERROR: $error');
    debugPrintStack(stackTrace: stack);
    return true;
  };

  runZonedGuarded(
    () {
      debugPrint('TOMAS_TUKANG_BOOT: runApp');
      runApp(const TomasTukangApp());
      unawaited(_bootstrapNotifications());
    },
    (error, stack) {
      debugPrint('TOMAS_TUKANG_ZONE_ERROR: $error');
      debugPrintStack(stackTrace: stack);
    },
  );
}

Future<void> _bootstrapNotifications() async {
  try {
    await Firebase.initializeApp().timeout(const Duration(seconds: 8));
    await NotificationService.init().timeout(const Duration(seconds: 8));
  } catch (_) {
    // Firebase config can be incomplete during debug installs. Never block UI.
  }
}

class TomasTukangApp extends StatelessWidget {
  const TomasTukangApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Tomas Tukang',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF2563EB)),
        useMaterial3: true,
      ),
      home: const SplashScreen(),
      routes: {
        '/login': (ctx) => const LoginScreen(),
        '/register': (ctx) => const RegisterScreen(),
        '/ktp-upload': (ctx) => const KtpUploadScreen(),
        '/waiting-verification': (ctx) => const WaitingVerificationScreen(),
        '/dashboard': (ctx) => const DashboardScreen(),
        '/profile': (ctx) => const ProfileScreen(),
        '/chat': (ctx) => const TukangChatListScreen(),
      },
      onGenerateRoute: (settings) {
        if (settings.name == '/order-detail') {
          final order = settings.arguments as Map<String, dynamic>;
          return MaterialPageRoute(
            builder: (_) => OrderDetailScreen(order: order),
          );
        }
        return null;
      },
    );
  }
}
