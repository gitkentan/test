import 'package:flutter/widgets.dart';

import '../models/intent.dart';
import '../theme/tokens.dart';
import 's_icons.dart';

/// C / J9 で使う選択カプセル。単一選択のみ。
class IntentPill extends StatelessWidget {
  const IntentPill({super.key, required this.intent, required this.selected, required this.onTap});

  final SIntent intent;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => GestureDetector(
    behavior: HitTestBehavior.opaque,
    onTap: onTap,
    child: AnimatedContainer(
      duration: const Duration(milliseconds: 250),
      curve: const Cubic(0.2, 0.9, 0.3, 1.2),
      height: 58,
      padding: const EdgeInsets.symmetric(horizontal: 20),
      decoration: BoxDecoration(
        color: selected ? SColor.greenTint : SColor.surface,
        borderRadius: BorderRadius.circular(SRadius.field),
        border: selected ? Border.all(color: SColor.greenBorder) : null,
      ),
      child: Row(
        children: [
          Text(intent.emoji, style: const TextStyle(fontSize: 21)),
          const SizedBox(width: 14),
          Expanded(
            child: Text(
              intent.label,
              style: TextStyle(
                fontSize: 17,
                fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
                color: SColor.text,
              ),
            ),
          ),
          if (selected)
            const SIcon(SIconData.check, size: 17, color: SColor.green, strokeWidth: 2.6),
        ],
      ),
    ),
  );
}

/// 5種を縦に並べたリスト（C / J9 で同じものを使う）。
class IntentPillList extends StatelessWidget {
  const IntentPillList({super.key, required this.selected, required this.onSelect});

  final SIntent? selected;
  final ValueChanged<SIntent> onSelect;

  @override
  Widget build(BuildContext context) => Column(
    children: [
      for (final i in SIntent.values) ...[
        IntentPill(intent: i, selected: selected == i, onTap: () => onSelect(i)),
        if (i != SIntent.values.last) const SizedBox(height: 9),
      ],
    ],
  );
}
