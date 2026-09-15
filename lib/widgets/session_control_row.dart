import 'package:flutter/widgets.dart';

import '../models/intent.dart';
import '../models/photo.dart';
import '../theme/tokens.dart';
import 's_icons.dart';
import 's_photo.dart';
import 'session_star.dart';

/// Discovery の1行ヘッダー：wordmark / ON·Intent コントロール / Filter · Messages · You。
/// 下部ドックは無い。ここが唯一のナビゲーション。
class SessionControlRow extends StatelessWidget {
  const SessionControlRow({
    super.key,
    required this.isOn,
    required this.intent,
    required this.avatar,
    required this.onTapState,
    required this.onTapFilter,
    required this.onTapMessages,
    required this.onTapProfile,
    this.hasUnread = false,
    this.dimmed = false,
  });

  final bool isOn;
  final SIntent? intent;
  final SPhoto avatar;
  final VoidCallback onTapState;
  final VoidCallback onTapFilter;
  final VoidCallback onTapMessages;
  final VoidCallback onTapProfile;
  final bool hasUnread;
  final bool dimmed;

  @override
  Widget build(BuildContext context) {
    return Opacity(
      opacity: dimmed ? 0.5 : 1,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(18, 12, 18, 0),
        child: Row(
          children: [
            const Text(
              'Session',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                letterSpacing: -0.36,
                color: SColor.text,
              ),
            ),
            Expanded(
              child: Center(
                child: SessionStateControl(isOn: isOn, intent: intent, onTap: onTapState),
              ),
            ),
            GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: onTapFilter,
              child: const SizedBox(
                width: 34,
                height: SSize.minTouch,
                child: Center(child: SIcon(SIconData.filter, size: 18)),
              ),
            ),
            GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: onTapMessages,
              child: SizedBox(
                width: 34,
                height: SSize.minTouch,
                child: Center(
                  child: Stack(
                    clipBehavior: Clip.none,
                    children: [
                      const SIcon(SIconData.message, size: 22),
                      if (hasUnread)
                        Positioned(
                          top: -1,
                          right: -1,
                          child: Container(
                            width: 8,
                            height: 8,
                            decoration: BoxDecoration(
                              color: SColor.green,
                              shape: BoxShape.circle,
                              border: Border.all(color: SColor.bg, width: 2),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
            GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: onTapProfile,
              child: SizedBox(
                width: 40,
                height: SSize.minTouch,
                child: Center(
                  child: ClipOval(
                    child: SizedBox(width: 30, height: 30, child: SPhotoView(avatar)),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// ON / OFF + Intent。タップで Intent シート（C）。
class SessionStateControl extends StatelessWidget {
  const SessionStateControl({
    super.key,
    required this.isOn,
    required this.intent,
    required this.onTap,
  });

  final bool isOn;
  final SIntent? intent;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final i = intent;
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: onTap,
      // 1行固定の Intent チップだけは文字サイズをクランプしてよい（レイアウト注意）。
      child: MediaQuery.withClampedTextScaling(
        maxScaleFactor: 1.0,
        child: AnimatedContainer(
          duration: SMotion.fade,
          height: SSize.controlHeight,
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: isOn ? SColor.greenTint : SColor.surface,
            borderRadius: BorderRadius.circular(SRadius.chip),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (isOn)
                const SessionStar(size: 11)
              else
                Container(
                  width: 6,
                  height: 6,
                  decoration: const BoxDecoration(color: SColor.disabled, shape: BoxShape.circle),
                ),
              const SizedBox(width: 7),
              Text(
                isOn ? 'ON' : 'OFF',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: isOn ? SColor.green : SColor.muted,
                ),
              ),
              if (i != null) ...[
                const SizedBox(width: 7),
                Container(width: 1, height: 12, color: const Color(0x1FFFFFFF)),
                const SizedBox(width: 7),
                Text(i.emoji, style: const TextStyle(fontSize: 13)),
                const SizedBox(width: 5),
                Flexible(
                  child: Text(
                    i.short,
                    maxLines: 1,
                    softWrap: false,
                    overflow: TextOverflow.fade,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: isOn ? SColor.greenText : SColor.text,
                    ),
                  ),
                ),
              ],
              const SizedBox(width: 6),
              const SIcon(SIconData.chevronDown, size: 10, color: SColor.muted, strokeWidth: 2.6),
            ],
          ),
        ),
      ),
    );
  }
}
