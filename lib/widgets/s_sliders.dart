import 'package:flutter/widgets.dart';

import '../models/user.dart';
import '../theme/tokens.dart';
import 's_icons.dart';

/// 年齢の2ハンドルスライダー（D）。トラック3px、つまみ26pxの白丸。
class SAgeRangeSlider extends StatelessWidget {
  const SAgeRangeSlider({
    super.key,
    required this.value,
    required this.onChanged,
    this.min = 18,
    this.max = 60,
    this.enabled = true,
  });

  final SRange value;
  final ValueChanged<SRange> onChanged;
  final double min;
  final double max;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, c) {
        final w = c.maxWidth;
        double toPx(double v) => (v - min) / (max - min) * w;
        double toValue(double px) => (px / w) * (max - min) + min;

        void dragStart(double dx) {
          final v = toValue(dx).clamp(min, value.end - 1);
          onChanged(value.copyWith(start: v.roundToDouble()));
        }

        void dragEnd(double dx) {
          final v = toValue(dx).clamp(value.start + 1, max);
          onChanged(value.copyWith(end: v.roundToDouble()));
        }

        return SizedBox(
          height: 26,
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              Positioned(
                top: 11.5,
                left: 0,
                right: 0,
                child: Container(
                  height: 3,
                  decoration: BoxDecoration(
                    color: SColor.raised,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              Positioned(
                top: 11.5,
                left: toPx(value.start),
                width: (toPx(value.end) - toPx(value.start)).clamp(0, w),
                child: Container(height: 3, color: SColor.green),
              ),
              _Thumb(left: toPx(value.start), size: 26, onDrag: enabled ? dragStart : null),
              _Thumb(left: toPx(value.end), size: 26, onDrag: enabled ? dragEnd : null),
            ],
          ),
        );
      },
    );
  }
}

/// 距離スライダー（D / D2）。1〜30km・1kmステップ。
/// 無料は 5km で止まり、左側は点線＋🔒。無効化ではなくロック表示。
class SDistanceSlider extends StatelessWidget {
  const SDistanceSlider({
    super.key,
    required this.km,
    required this.floorKm,
    required this.onChanged,
    this.lockHit = false,
  });

  final int km;
  final int floorKm;
  final ValueChanged<int> onChanged;
  final bool lockHit;

  static const _min = 1;
  static const _max = 30;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, c) {
        final w = c.maxWidth;
        double toPx(num v) => (v - _min) / (_max - _min) * w;
        int toKm(double px) => ((px / w) * (_max - _min) + _min).round();
        final lockedWidth = toPx(floorKm);

        void drag(double dx) => onChanged(toKm(dx).clamp(_min, _max));

        return SizedBox(
          height: 28,
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              Positioned(
                top: 12.5,
                left: 0,
                right: 0,
                child: Container(
                  height: 3,
                  decoration: BoxDecoration(
                    color: SColor.raised,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              if (floorKm > _min)
                Positioned(
                  top: 12.5,
                  left: 0,
                  width: lockedWidth,
                  child: CustomPaint(
                    size: Size(lockedWidth, 3),
                    painter: const _DashedTrackPainter(),
                  ),
                ),
              if (floorKm > _min)
                Positioned(
                  top: 9,
                  left: (lockedWidth - 10).clamp(0, w),
                  child: const SIcon(
                    SIconData.lock,
                    size: 10,
                    color: Color(0xFF5A5F5A),
                    strokeWidth: 2.4,
                  ),
                ),
              if (lockHit)
                // 5km より左にドラッグしたときのゴースト位置。
                Positioned(
                  top: 0,
                  left: toPx(_min) - 14,
                  child: Container(
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: SColor.text.withValues(alpha: 0.18),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
              _Thumb(left: toPx(km), size: 28, onDrag: drag, emphasized: lockHit),
            ],
          ),
        );
      },
    );
  }
}

class _Thumb extends StatelessWidget {
  const _Thumb({
    required this.left,
    required this.size,
    required this.onDrag,
    this.emphasized = false,
  });

  final double left;
  final double size;
  final ValueChanged<double>? onDrag;
  final bool emphasized;

  @override
  Widget build(BuildContext context) => Positioned(
    left: left - size / 2,
    top: 0,
    child: GestureDetector(
      behavior: HitTestBehavior.opaque,
      // 親が値から left を再計算するので、差分をそのまま足していけばよい。
      onPanUpdate: onDrag == null ? null : (d) => onDrag!(left + d.delta.dx),
      child: SizedBox(
        width: SSize.minTouch,
        height: SSize.minTouch,
        child: Center(
          child: Container(
            width: size,
            height: size,
            decoration: BoxDecoration(
              color: SColor.text,
              shape: BoxShape.circle,
              boxShadow: [
                const BoxShadow(color: Color(0x80000000), blurRadius: 8, offset: Offset(0, 2)),
                if (emphasized)
                  BoxShadow(
                    color: SColor.text.withValues(alpha: 0.12),
                    blurRadius: 0,
                    spreadRadius: 6,
                  ),
              ],
            ),
          ),
        ),
      ),
    ),
  );
}

class _DashedTrackPainter extends CustomPainter {
  const _DashedTrackPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF3A3F3A)
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round;
    var x = 0.0;
    while (x < size.width) {
      canvas.drawLine(Offset(x, 1.5), Offset((x + 4).clamp(0, size.width), 1.5), paint);
      x += 8;
    }
  }

  @override
  bool shouldRepaint(_DashedTrackPainter old) => false;
}
