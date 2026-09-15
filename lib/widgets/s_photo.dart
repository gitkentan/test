import 'package:flutter/widgets.dart';

import '../models/photo.dart';
import '../theme/tokens.dart';

/// 写真は 3:4 固定。実画像が入るまではグラデーションのプレースホルダを描き、
/// 実装で `url` に差し替えたときも同じ下地をローディング表示として使う。
class SPhotoView extends StatelessWidget {
  const SPhotoView(this.photo, {super.key, this.dimmed = false, this.grayscale = false});

  final SPhoto photo;
  final bool dimmed;
  final bool grayscale;

  @override
  Widget build(BuildContext context) {
    final colors = grayscale ? photo.gradient.map(_toGrey).toList(growable: false) : photo.gradient;

    Widget child = DecoratedBox(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: const Alignment(-0.6, -1),
          end: const Alignment(0.4, 1),
          colors: colors,
        ),
      ),
      child: const SizedBox.expand(),
    );

    final url = photo.url;
    if (url != null) {
      child = Stack(
        fit: StackFit.expand,
        children: [
          child,
          Image.network(url, fit: BoxFit.cover),
        ],
      );
    }

    if (dimmed) {
      // 無料の「あなたに興味あり」はぼかし相当の減光。ぼかし切って壊れて見せない。
      child = Stack(
        fit: StackFit.expand,
        children: [
          child,
          const ColoredBox(color: Color(0xB30A0B0A)),
        ],
      );
    }
    return child;
  }

  static Color _toGrey(Color c) {
    final l = (0.299 * (c.r * 255) + 0.587 * (c.g * 255) + 0.114 * (c.b * 255)) * 0.55;
    return Color.fromARGB((c.a * 255).round(), l.round(), l.round(), l.round());
  }
}

/// 写真の下に敷く共通スクリム（名前と Intent を必ず読ませるため）。
class SPhotoScrim extends StatelessWidget {
  const SPhotoScrim({super.key, this.start = 0.52});

  final double start;

  @override
  Widget build(BuildContext context) => IgnorePointer(
    child: DecoratedBox(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: const [Color(0x00080908), Color(0x8C080908), Color(0xF2080908)],
          stops: [start, start + (1 - start) * 0.55, 1.0],
        ),
      ),
      child: const SizedBox.expand(),
    ),
  );
}

/// 3:4 の写真スロット（J7 / I / I2）。空きスロットは破線の枠。
class PhotoSlot extends StatelessWidget {
  const PhotoSlot({
    super.key,
    this.photo,
    this.main = false,
    this.radius = 20,
    this.onTap,
    this.showAddHint = false,
  });

  final SPhoto? photo;
  final bool main;
  final double radius;
  final VoidCallback? onTap;
  final bool showAddHint;

  @override
  Widget build(BuildContext context) {
    final p = photo;
    return GestureDetector(
      onTap: onTap,
      child: AspectRatio(
        aspectRatio: SPhoto.aspectRatio,
        child: ClipRRect(
          borderRadius: BorderRadius.circular(radius),
          child: p == null
              ? CustomPaint(
                  painter: _DashedBorderPainter(
                    radius: radius,
                    color: showAddHint ? const Color(0x33FFFFFF) : const Color(0x1AFFFFFF),
                  ),
                  child: ColoredBox(
                    color: SColor.sheet,
                    child: Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            '+',
                            style: TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.w300,
                              color: showAddHint ? SColor.green : SColor.disabled,
                            ),
                          ),
                          const Text(
                            '3:4',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                              color: SColor.muted2,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                )
              : Stack(
                  fit: StackFit.expand,
                  children: [
                    SPhotoView(p),
                    if (main) const Positioned(top: 8, left: 8, child: _MainBadge()),
                  ],
                ),
        ),
      ),
    );
  }
}

class _MainBadge extends StatelessWidget {
  const _MainBadge();

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
    decoration: BoxDecoration(
      color: SColor.green,
      borderRadius: BorderRadius.circular(SRadius.pill),
    ),
    child: const Text(
      'メイン',
      style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: SColor.onGreen),
    ),
  );
}

class _DashedBorderPainter extends CustomPainter {
  const _DashedBorderPainter({required this.radius, required this.color});

  final double radius;
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1;
    final rrect = RRect.fromRectAndRadius(Offset.zero & size, Radius.circular(radius));
    final path = Path()..addRRect(rrect);
    for (final metric in path.computeMetrics()) {
      var d = 0.0;
      while (d < metric.length) {
        canvas.drawPath(metric.extractPath(d, d + 5), paint);
        d += 10;
      }
    }
  }

  @override
  bool shouldRepaint(_DashedBorderPainter old) => old.color != color || old.radius != radius;
}
