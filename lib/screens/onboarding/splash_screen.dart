import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/session_star.dart';
import '../discovery/discovery_screen.dart';
import '../s_scaffold.dart';
import 'welcome_screen.dart';

/// J1 Splash · 星が 0.8→1.0 にフェードイン（250ms）→ ワードマーク。
/// 1.2s 後、トークン有効 → A ／ 無効 → J2。
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late final AnimationController _c;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: const Duration(milliseconds: 250))..forward();
    Future<void>.delayed(const Duration(milliseconds: 1200)).then((_) {
      if (!mounted) return;
      final signedIn = AppScope.read(context).signedIn;
      Navigator.of(
        context,
      ).pushReplacement(sFadeRoute(signedIn ? const DiscoveryScreen() : const WelcomeScreen()));
    });
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      child: Stack(
        alignment: Alignment.center,
        children: [
          // 常時グローは使わないが、Splash だけは中央に淡いハローを敷く。
          Container(
            width: 360,
            height: 360,
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [Color(0x1F2BD873), Color(0x002BD873)],
                stops: [0.0, 0.65],
              ),
            ),
          ),
          Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              FadeTransition(
                opacity: _c,
                child: ScaleTransition(
                  scale: Tween(begin: 0.8, end: 1.0).animate(_c),
                  child: const SessionStar(size: 56),
                ),
              ),
              const SizedBox(height: 22),
              FadeTransition(
                opacity: CurvedAnimation(parent: _c, curve: const Interval(0.5, 1)),
                child: const Text(
                  'Session',
                  style: TextStyle(
                    fontSize: 40,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -1,
                    color: SColor.text,
                  ),
                ),
              ),
              const SizedBox(height: 16),
              FadeTransition(
                opacity: CurvedAnimation(parent: _c, curve: const Interval(0.6, 1)),
                child: const Text('今を、出会いに。', style: TextStyle(fontSize: 14, color: SColor.muted)),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
