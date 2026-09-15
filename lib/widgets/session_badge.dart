import 'package:flutter/widgets.dart';

import '../models/intent.dart';
import '../theme/tokens.dart';
import 'session_star.dart';

/// カード内・F・H・I3 で使う「★ + Session中 + Intent」の1行。
/// OFF の相手には緑の行を出さない。
class SessionBadge extends StatelessWidget {
  const SessionBadge({
    super.key,
    required this.isOn,
    this.intent,
    this.distanceLabel,
    this.fontSize = 14,
  });

  final bool isOn;
  final SIntent? intent;
  final String? distanceLabel;
  final double fontSize;

  @override
  Widget build(BuildContext context) {
    final i = intent;
    final d = distanceLabel;
    if (!isOn) {
      return Text(
        [if (d != null) d, if (i != null) '${i.emoji} ${i.short}'].join(' · '),
        style: TextStyle(fontSize: fontSize, color: const Color(0xB8F4F5F2)),
      );
    }
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        const SessionStar(size: 11),
        const SizedBox(width: 7),
        Flexible(
          child: Text.rich(
            TextSpan(
              text: i == null ? 'Session中' : 'Session中 · ${i.emoji} ${i.short}',
              children: d == null
                  ? null
                  : [
                      TextSpan(
                        text: ' · $d',
                        style: const TextStyle(color: Color(0x9EF4F5F2)),
                      ),
                    ],
            ),
            style: TextStyle(fontSize: fontSize, color: SColor.greenText),
          ),
        ),
      ],
    );
  }
}

/// 緑の小さなドット（Active Session の印）。星に置き換えない。
class SPresenceDot extends StatelessWidget {
  const SPresenceDot({super.key, this.size = 6});

  final double size;

  @override
  Widget build(BuildContext context) => Container(
    width: size,
    height: size,
    decoration: const BoxDecoration(color: SColor.green, shape: BoxShape.circle),
  );
}

/// 緑地のチップ（共通の興味、Verified、Intent など）。
class STintChip extends StatelessWidget {
  const STintChip(
    this.label, {
    super.key,
    this.tinted = true,
    this.radius = SRadius.chip,
    this.fontSize = 13,
  });

  final String label;
  final bool tinted;
  final double radius;
  final double fontSize;

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
    decoration: BoxDecoration(
      color: tinted ? SColor.greenTint : SColor.surface,
      borderRadius: BorderRadius.circular(radius),
    ),
    child: Text(
      label,
      style: TextStyle(
        fontSize: fontSize,
        color: tinted ? SColor.greenText : SColor.secondary,
        fontWeight: tinted ? FontWeight.w600 : FontWeight.w400,
      ),
    ),
  );
}
