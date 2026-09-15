import 'dart:math' as math;

import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// ハンドオフの11個のアイコン。Material Icons は見た目が合わないので使わず、
/// SVG と同じ 24×24 / stroke 1.8 / 端は丸 で描き直している。
enum SIconData {
  back,
  close,
  chevronDown,
  chevronRight,
  filter,
  message,
  gear,
  eye,
  shield,
  lock,
  check,
  send,
  plus,
  arrowUp,
  wifiOff,
  info,
  card,
}

class SIcon extends StatelessWidget {
  const SIcon(
    this.icon, {
    super.key,
    this.size = 20,
    this.color = SColor.text,
    this.strokeWidth = 1.8,
  });

  final SIconData icon;
  final double size;
  final Color color;
  final double strokeWidth;

  @override
  Widget build(BuildContext context) => SizedBox(
    width: size,
    height: size,
    child: CustomPaint(painter: _IconPainter(icon, color, strokeWidth)),
  );
}

class _IconPainter extends CustomPainter {
  const _IconPainter(this.icon, this.color, this.strokeWidth);

  final SIconData icon;
  final Color color;
  final double strokeWidth;

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.shortestSide / 24.0;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    final fill = Paint()..color = color;

    Offset p(double x, double y) => Offset(x * s, y * s);
    void line(double x1, double y1, double x2, double y2) =>
        canvas.drawLine(p(x1, y1), p(x2, y2), paint);

    switch (icon) {
      case SIconData.back:
        canvas.drawPath(
          Path()
            ..moveTo(14 * s, 6 * s)
            ..lineTo(8 * s, 12 * s)
            ..lineTo(14 * s, 18 * s),
          paint,
        );
      case SIconData.chevronRight:
        canvas.drawPath(
          Path()
            ..moveTo(10 * s, 6 * s)
            ..lineTo(16 * s, 12 * s)
            ..lineTo(10 * s, 18 * s),
          paint,
        );
      case SIconData.chevronDown:
        canvas.drawPath(
          Path()
            ..moveTo(6 * s, 9 * s)
            ..lineTo(12 * s, 15 * s)
            ..lineTo(18 * s, 9 * s),
          paint,
        );
      case SIconData.close:
        line(6, 6, 18, 18);
        line(18, 6, 6, 18);
      case SIconData.filter:
        // スライダー2本 + つまみ。Discovery ヘッダーの絞り込み。
        line(3.5, 8, 20.5, 8);
        line(3.5, 16, 20.5, 16);
        canvas.drawCircle(p(9, 8), 2.6 * s, Paint()..color = SColor.bg);
        canvas.drawCircle(p(9, 8), 2.6 * s, paint);
        canvas.drawCircle(p(16, 16), 2.6 * s, Paint()..color = SColor.bg);
        canvas.drawCircle(p(16, 16), 2.6 * s, paint);
      case SIconData.message:
        canvas.drawPath(
          Path()
            ..moveTo(4 * s, 7.5 * s)
            ..cubicTo(4 * s, 5.8 * s, 5.3 * s, 4.5 * s, 7 * s, 4.5 * s)
            ..lineTo(17 * s, 4.5 * s)
            ..cubicTo(18.7 * s, 4.5 * s, 20 * s, 5.8 * s, 20 * s, 7.5 * s)
            ..lineTo(20 * s, 13.5 * s)
            ..cubicTo(20 * s, 15.2 * s, 18.7 * s, 16.5 * s, 17 * s, 16.5 * s)
            ..lineTo(9.5 * s, 16.5 * s)
            ..lineTo(4.5 * s, 20 * s)
            ..close(),
          paint,
        );
      case SIconData.gear:
        canvas.drawCircle(p(12, 12), 3 * s, paint);
        for (var i = 0; i < 8; i++) {
          final a = i * math.pi / 4;
          final c = Offset(12 * s, 12 * s);
          final from = c + Offset(6.2 * s * math.cos(a), 6.2 * s * math.sin(a));
          final to = c + Offset(8.6 * s * math.cos(a), 8.6 * s * math.sin(a));
          canvas.drawLine(from, to, paint);
        }
      case SIconData.eye:
        canvas.drawPath(
          Path()
            ..moveTo(2 * s, 12 * s)
            ..cubicTo(6 * s, 5.5 * s, 18 * s, 5.5 * s, 22 * s, 12 * s)
            ..cubicTo(18 * s, 18.5 * s, 6 * s, 18.5 * s, 2 * s, 12 * s)
            ..close(),
          paint,
        );
        canvas.drawCircle(p(12, 12), 2.6 * s, paint);
      case SIconData.shield:
        canvas.drawPath(
          Path()
            ..moveTo(12 * s, 3 * s)
            ..lineTo(20 * s, 7 * s)
            ..lineTo(20 * s, 12 * s)
            ..cubicTo(20 * s, 17 * s, 16.5 * s, 20.5 * s, 12 * s, 21.5 * s)
            ..cubicTo(7.5 * s, 20.5 * s, 4 * s, 17 * s, 4 * s, 12 * s)
            ..lineTo(4 * s, 7 * s)
            ..close(),
          paint,
        );
        canvas.drawPath(
          Path()
            ..moveTo(9 * s, 12 * s)
            ..lineTo(11 * s, 14 * s)
            ..lineTo(15 * s, 10 * s),
          paint,
        );
      case SIconData.lock:
        canvas.drawRRect(
          RRect.fromRectAndRadius(
            Rect.fromLTWH(5 * s, 10 * s, 14 * s, 10 * s),
            Radius.circular(3 * s),
          ),
          paint,
        );
        canvas.drawPath(
          Path()
            ..moveTo(8 * s, 10 * s)
            ..lineTo(8 * s, 7 * s)
            ..arcToPoint(Offset(16 * s, 7 * s), radius: Radius.circular(4 * s))
            ..lineTo(16 * s, 10 * s),
          paint,
        );
      case SIconData.check:
        canvas.drawPath(
          Path()
            ..moveTo(5 * s, 12.5 * s)
            ..lineTo(9.5 * s, 17 * s)
            ..lineTo(19 * s, 7 * s),
          paint,
        );
      case SIconData.send:
      case SIconData.arrowUp:
        canvas.drawPath(
          Path()
            ..moveTo(12 * s, 19 * s)
            ..lineTo(12 * s, 5 * s),
          paint,
        );
        canvas.drawPath(
          Path()
            ..moveTo(5 * s, 12 * s)
            ..lineTo(12 * s, 5 * s)
            ..lineTo(19 * s, 12 * s),
          paint,
        );
      case SIconData.plus:
        line(12, 5, 12, 19);
        line(5, 12, 19, 12);
      case SIconData.wifiOff:
        canvas.drawPath(
          Path()
            ..moveTo(4 * s, 9.5 * s)
            ..cubicTo(8 * s, 6 * s, 16 * s, 6 * s, 20 * s, 9.5 * s),
          paint,
        );
        canvas.drawPath(
          Path()
            ..moveTo(7.5 * s, 13.5 * s)
            ..cubicTo(10 * s, 11.2 * s, 14 * s, 11.2 * s, 16.5 * s, 13.5 * s),
          paint,
        );
        canvas.drawCircle(p(12, 18), 1.2 * s, fill);
        line(4, 4, 20, 20);
      case SIconData.info:
        canvas.drawCircle(p(12, 12), 9 * s, paint);
        line(12, 11.5, 12, 16);
        canvas.drawCircle(p(12, 8), 0.9 * s, fill);
      case SIconData.card:
        canvas.drawRRect(
          RRect.fromRectAndRadius(
            Rect.fromLTWH(3 * s, 5.5 * s, 18 * s, 13 * s),
            Radius.circular(3 * s),
          ),
          paint,
        );
        line(3, 10, 21, 10);
    }
  }

  @override
  bool shouldRepaint(_IconPainter old) =>
      old.icon != icon || old.color != color || old.strokeWidth != strokeWidth;
}
