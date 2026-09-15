import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// 画面の下地。背景は常に ink、上下は SafeArea。
/// Android の画面比が 16:9〜21:9 と幅広いので、縦は必ず可変にする。
class SScaffold extends StatelessWidget {
  const SScaffold({
    super.key,
    required this.child,
    this.background = SColor.bg,
    this.bottomSafe = true,
  });

  final Widget child;
  final Color background;
  final bool bottomSafe;

  @override
  Widget build(BuildContext context) => ColoredBox(
    color: background,
    child: SafeArea(bottom: bottomSafe, child: child),
  );
}

/// 戻る / タイトルだけの簡素なヘッダー（設定配下・オンボーディング）。
class SHeader extends StatelessWidget {
  const SHeader({super.key, this.title, this.onBack, this.trailing, this.progress});

  final String? title;
  final VoidCallback? onBack;
  final Widget? trailing;

  /// オンボーディングの進捗バー（0〜1）。
  final double? progress;

  @override
  Widget build(BuildContext context) {
    final t = title;
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Row(
        children: [
          if (onBack != null) _BackCircle(onTap: onBack!) else const SizedBox(width: 0),
          if (t != null) ...[
            const SizedBox(width: 12),
            Expanded(
              child: Text(t, style: SText.heading.copyWith(color: SColor.text)),
            ),
          ],
          if (progress != null) ...[
            const SizedBox(width: 14),
            Expanded(child: _ProgressBar(value: progress!)),
          ],
          if (t == null && progress == null) const Spacer(),
          if (trailing != null) trailing!,
        ],
      ),
    );
  }
}

class _BackCircle extends StatelessWidget {
  const _BackCircle({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => GestureDetector(
    behavior: HitTestBehavior.opaque,
    onTap: onTap,
    child: Container(
      width: 36,
      height: 36,
      alignment: Alignment.center,
      decoration: const BoxDecoration(color: SColor.surface, shape: BoxShape.circle),
      child: const _BackGlyph(),
    ),
  );
}

class _BackGlyph extends StatelessWidget {
  const _BackGlyph();

  @override
  Widget build(BuildContext context) =>
      const SizedBox(width: 16, height: 16, child: CustomPaint(painter: _BackPainter()));
}

class _BackPainter extends CustomPainter {
  const _BackPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final s = size.width / 24;
    canvas.drawPath(
      Path()
        ..moveTo(14 * s, 6 * s)
        ..lineTo(8 * s, 12 * s)
        ..lineTo(14 * s, 18 * s),
      Paint()
        ..color = SColor.text
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2
        ..strokeCap = StrokeCap.round
        ..strokeJoin = StrokeJoin.round,
    );
  }

  @override
  bool shouldRepaint(_BackPainter old) => false;
}

class _ProgressBar extends StatelessWidget {
  const _ProgressBar({required this.value});

  final double value;

  @override
  Widget build(BuildContext context) => ClipRRect(
    borderRadius: BorderRadius.circular(2),
    child: SizedBox(
      height: 3,
      child: ColoredBox(
        color: SColor.surface,
        child: FractionallySizedBox(
          alignment: Alignment.centerLeft,
          widthFactor: value.clamp(0, 1),
          child: const ColoredBox(color: SColor.green),
        ),
      ),
    ),
  );
}
