import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// N2 のスケルトン。スピナーや「読み込み中」テキストは出さない。shimmer は 1.4s。
class SSkeleton extends StatefulWidget {
  const SSkeleton({
    super.key,
    this.width,
    this.height = 12,
    this.radius = 6,
    this.color = SColor.surface,
    this.child,
  });

  final double? width;
  final double height;
  final double radius;
  final Color color;
  final Widget? child;

  @override
  State<SSkeleton> createState() => _SSkeletonState();
}

class _SSkeletonState extends State<SSkeleton> with SingleTickerProviderStateMixin {
  late final AnimationController _c;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: const Duration(milliseconds: 1400))
      ..repeat(reverse: true);
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: _c,
    builder: (context, _) => Opacity(
      opacity: 0.45 + 0.45 * _c.value,
      child: Container(
        width: widget.width,
        height: widget.child == null ? widget.height : null,
        decoration: BoxDecoration(
          color: widget.color,
          borderRadius: BorderRadius.circular(widget.radius),
        ),
        child: widget.child,
      ),
    ),
  );
}
