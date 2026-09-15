import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';
import 's_icons.dart';

/// 角丸20の行グループ（L1 / L2 / L3）。行間は hairline。
class SSettingsGroup extends StatelessWidget {
  const SSettingsGroup({super.key, required this.rows});

  final List<Widget> rows;

  @override
  Widget build(BuildContext context) {
    final children = <Widget>[];
    for (var i = 0; i < rows.length; i++) {
      children.add(rows[i]);
      if (i != rows.length - 1) {
        children.add(const _Hairline());
      }
    }
    return ClipRRect(
      borderRadius: BorderRadius.circular(20),
      child: ColoredBox(
        color: SColor.surface,
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: children),
      ),
    );
  }
}

class _Hairline extends StatelessWidget {
  const _Hairline();

  @override
  Widget build(BuildContext context) =>
      const SizedBox(height: 1, child: ColoredBox(color: SColor.hairline));
}

class SSettingsRow extends StatelessWidget {
  const SSettingsRow({
    super.key,
    required this.label,
    this.caption,
    this.value,
    this.valueColor = SColor.muted,
    this.trailing,
    this.onTap,
    this.chevron = true,
    this.center = false,
    this.labelColor = SColor.text,
  });

  final String label;
  final String? caption;
  final String? value;
  final Color valueColor;
  final Widget? trailing;
  final VoidCallback? onTap;
  final bool chevron;
  final bool center;
  final Color labelColor;

  @override
  Widget build(BuildContext context) {
    final cap = caption;
    final v = value;
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: onTap,
      child: Padding(
        padding: EdgeInsets.symmetric(horizontal: 18, vertical: trailing != null ? 11 : 14),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: center ? CrossAxisAlignment.center : CrossAxisAlignment.start,
                children: [
                  Text(label, style: TextStyle(fontSize: 15, color: labelColor)),
                  if (cap != null) ...[
                    const SizedBox(height: 3),
                    Text(
                      cap,
                      style: const TextStyle(fontSize: 12, color: SColor.muted2, height: 1.4),
                    ),
                  ],
                ],
              ),
            ),
            if (v != null)
              Padding(
                padding: const EdgeInsets.only(left: 12),
                child: Text(
                  v,
                  style: TextStyle(fontSize: 13, color: valueColor, fontWeight: FontWeight.w600),
                ),
              ),
            if (trailing != null) trailing!,
            if (trailing == null && chevron) ...[
              const SizedBox(width: 8),
              const SIcon(SIconData.chevronRight, size: 14, color: SColor.muted, strokeWidth: 2),
            ],
          ],
        ),
      ),
    );
  }
}
