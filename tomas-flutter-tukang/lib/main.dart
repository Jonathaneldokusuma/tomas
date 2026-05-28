import 'package:flutter/material.dart';
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
  runApp(const TomasTukangApp());
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
      initialRoute: '/',
      routes: {
        '/': (ctx) => const SplashScreen(),
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
          return MaterialPageRoute(builder: (_) => OrderDetailScreen(order: order));
        }
        return null;
      },
    );
  }
}
