import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../state/mock_data.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/session_badge.dart';
import '../../widgets/session_star.dart';
import '../s_scaffold.dart';
import 'sign_in_screen.dart';

/// J2 Welcome · 重なる3枚の写真（3:4）に Status チップを重ね、街の「今」を最初に見せる。
class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    void toSignIn() => Navigator.of(context).push(sUpRoute(const SignInScreen()));

    return SScaffold(
      bottomSafe: false,
      child: Stack(
        children: [
          Positioned(
            top: 40,
            left: -30,
            child: _Card(gradient: MockGradients.warmGrey, angle: -8, width: 180),
          ),
          Positioned(
            top: 110,
            right: -20,
            child: _Card(gradient: MockGradients.clay, angle: 7, width: 189),
          ),
          Positioned(
            top: 290,
            left: 70,
            child: _Card(gradient: MockGradients.moss, angle: -3, width: 168),
          ),
          const Positioned(top: 80, left: 150, child: _StatusChip(label: '🍸 今日飲める · 1 km')),
          const Positioned(top: 330, right: 30, child: _StatusChip(label: '☕ カフェ · 3 km')),
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            height: 420,
            child: IgnorePointer(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: const [Color(0x000A0B0A), SColor.bg],
                    stops: const [0, 0.45],
                  ),
                ),
              ),
            ),
          ),
          Positioned(
            left: SSpace.x5,
            right: SSpace.x5,
            bottom: 0,
            child: SafeArea(
              top: false,
              child: Padding(
                padding: const EdgeInsets.only(bottom: 22),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Align(
                      alignment: Alignment.centerLeft,
                      child: SessionLockup(starSize: 17, fontSize: 17),
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      '今、会いたい人と。',
                      style: TextStyle(
                        fontSize: 36,
                        height: 1.15,
                        fontWeight: FontWeight.w700,
                        letterSpacing: -1.08,
                        color: SColor.text,
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      '同じ気分、同じタイミングの人を見つけよう。',
                      style: TextStyle(fontSize: 15, height: 1.6, color: SColor.muted),
                    ),
                    const SizedBox(height: 22),
                    SPrimaryButton('はじめる', onTap: toSignIn),
                    const SizedBox(height: 6),
                    GestureDetector(
                      onTap: toSignIn,
                      child: const Padding(
                        padding: EdgeInsets.all(6),
                        child: Text.rich(
                          TextSpan(
                            text: 'アカウントをお持ちの方は ',
                            children: [
                              TextSpan(
                                text: 'ログイン',
                                style: TextStyle(color: SColor.text, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 14, color: SColor.muted),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _Card extends StatelessWidget {
  const _Card({required this.gradient, required this.angle, required this.width});

  final List<Color> gradient;
  final double angle;
  final double width;

  @override
  Widget build(BuildContext context) => Transform.rotate(
    angle: angle * 3.1415926535 / 180,
    child: Container(
      width: width,
      height: width * 4 / 3,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(32),
        gradient: LinearGradient(
          begin: const Alignment(-0.6, -1),
          end: const Alignment(0.4, 1),
          colors: gradient,
        ),
        boxShadow: const [
          BoxShadow(color: Color(0x80000000), blurRadius: 60, offset: Offset(0, 30)),
        ],
      ),
    ),
  );
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
    decoration: BoxDecoration(
      color: const Color(0xEB10301E),
      borderRadius: BorderRadius.circular(SRadius.pill),
      boxShadow: const [BoxShadow(color: Color(0x66000000), blurRadius: 30, offset: Offset(0, 10))],
    ),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        const SPresenceDot(size: 7),
        const SizedBox(width: 7),
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: SColor.greenText,
          ),
        ),
      ],
    ),
  );
}
