import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// ロゴの四角星。唯一の fill アイコンで、ブランドの要素なので形は変えない。
/// 24×24 viewBox の path をそのまま写したもの。
class SessionStar extends StatelessWidget {
  const SessionStar({super.key, this.size = 18, this.color = SColor.green});

  final double size;
  final Color color;

  @override
  Widget build(BuildContext context) => SizedBox(
    width: size,
    height: size,
    child: CustomPaint(painter: _StarPainter(color)),
  );
}

class _StarPainter extends CustomPainter {
  const _StarPainter(this.color);

  final Color color;

  static Path path(double side) {
    final s = side / 24.0;
    final p = Path()..moveTo(12 * s, 1.5 * s);
    p.relativeCubicTo(0.95 * s, 5.7 * s, 3.3 * s, 8.05 * s, 9 * s, 9 * s);
    p.relativeCubicTo(-5.7 * s, 0.95 * s, -8.05 * s, 3.3 * s, -9 * s, 9 * s);
    p.relativeCubicTo(-0.95 * s, -5.7 * s, -3.3 * s, -8.05 * s, -9 * s, -9 * s);
    p.relativeCubicTo(5.7 * s, -0.95 * s, 8.05 * s, -3.3 * s, 9 * s, -9 * s);
    p.close();
    return p;
  }

  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawPath(path(size.shortestSide), Paint()..color = color);
  }

  @override
  bool shouldRepaint(_StarPainter old) => old.color != color;
}

/// 星 + ワードマーク。Splash / Welcome / Sign in / Session+ でのみ使う。
class SessionLockup extends StatelessWidget {
  const SessionLockup({
    super.key,
    this.starSize = 18,
    this.fontSize = 18,
    this.gap = 9,
    this.plus = false,
  });

  final double starSize;
  final double fontSize;
  final double gap;
  final bool plus;

  @override
  Widget build(BuildContext context) => Row(
    mainAxisSize: MainAxisSize.min,
    children: [
      SessionStar(size: starSize),
      SizedBox(width: gap),
      Text.rich(
        TextSpan(
          text: 'Session',
          children: plus
              ? const [
                  TextSpan(
                    text: '+',
                    style: TextStyle(color: SColor.green),
                  ),
                ]
              : null,
        ),
        style: TextStyle(
          fontSize: fontSize,
          fontWeight: FontWeight.w700,
          letterSpacing: -fontSize * 0.025,
          color: SColor.text,
        ),
      ),
    ],
  );
}
