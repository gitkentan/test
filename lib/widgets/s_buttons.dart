import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// 押したときだけ scale(.97)。54h / radius16 / 無効時は greenTint。
class SPressable extends StatefulWidget {
  const SPressable({super.key, required this.child, this.onTap, this.scale = 0.97});

  final Widget child;
  final VoidCallback? onTap;
  final double scale;

  @override
  State<SPressable> createState() => _SPressableState();
}

class _SPressableState extends State<SPressable> {
  bool _down = false;

  @override
  Widget build(BuildContext context) {
    final enabled = widget.onTap != null;
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTapDown: enabled ? (_) => setState(() => _down = true) : null,
      onTapUp: enabled ? (_) => setState(() => _down = false) : null,
      onTapCancel: enabled ? () => setState(() => _down = false) : null,
      onTap: widget.onTap,
      child: AnimatedScale(
        scale: _down ? widget.scale : 1,
        duration: const Duration(milliseconds: 90),
        child: widget.child,
      ),
    );
  }
}

class SPrimaryButton extends StatelessWidget {
  const SPrimaryButton(
    this.label, {
    super.key,
    this.onTap,
    this.height = SSize.primaryButtonHeight,
    this.enabled = true,
  });

  final String label;
  final VoidCallback? onTap;
  final double height;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    final on = enabled && onTap != null;
    return SPressable(
      onTap: on ? onTap : null,
      child: Container(
        height: height,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: on ? SColor.green : SColor.greenTint,
          borderRadius: BorderRadius.circular(SRadius.button),
        ),
        child: Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: on ? SColor.onGreen : SColor.disabled,
          ),
        ),
      ),
    );
  }
}

/// surface 塗りの副ボタン。破壊的操作の確認では白ボタンが主になる。
class SSecondaryButton extends StatelessWidget {
  const SSecondaryButton(
    this.label, {
    super.key,
    this.onTap,
    this.height = SSize.primaryButtonHeight,
    this.color = SColor.surface,
    this.textColor = SColor.text,
  });

  final String label;
  final VoidCallback? onTap;
  final double height;
  final Color color;
  final Color textColor;

  @override
  Widget build(BuildContext context) => SPressable(
    onTap: onTap,
    child: Container(
      height: height,
      alignment: Alignment.center,
      decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(SRadius.button)),
      child: Text(
        label,
        style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: textColor),
      ),
    ),
  );
}

/// 破壊的操作の確認シートで主ボタンになる白ボタン（M3 / L6）。
class SLightButton extends StatelessWidget {
  const SLightButton(this.label, {super.key, this.onTap});

  final String label;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) =>
      SSecondaryButton(label, onTap: onTap, color: SColor.text, textColor: SColor.bg);
}

class STextButton extends StatelessWidget {
  const STextButton(
    this.label, {
    super.key,
    this.onTap,
    this.color = SColor.muted,
    this.fontSize = 15,
  });

  final String label;
  final VoidCallback? onTap;
  final Color color;
  final double fontSize;

  @override
  Widget build(BuildContext context) => SPressable(
    onTap: onTap,
    scale: 0.99,
    child: Container(
      height: 50,
      alignment: Alignment.center,
      child: Text(
        label,
        style: TextStyle(fontSize: fontSize, fontWeight: FontWeight.w600, color: color),
      ),
    ),
  );
}

/// 丸い 34–40px のヘッダーボタン（戻る / 閉じる / 設定 / ···）。
class SCircleButton extends StatelessWidget {
  const SCircleButton({
    super.key,
    required this.child,
    this.onTap,
    this.size = 34,
    this.color = SColor.surface,
  });

  final Widget child;
  final VoidCallback? onTap;
  final double size;
  final Color color;

  @override
  Widget build(BuildContext context) => SPressable(
    onTap: onTap,
    scale: 0.92,
    child: SizedBox(
      // 最小タップ領域 44×44 は必ず確保する。
      width: SSize.minTouch,
      height: SSize.minTouch,
      child: Center(
        child: Container(
          width: size,
          height: size,
          alignment: Alignment.center,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          child: child,
        ),
      ),
    ),
  );
}
