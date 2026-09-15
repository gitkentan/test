import 'package:flutter/widgets.dart';

import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_skeleton.dart';
import '../../widgets/session_star.dart';

/// N1 Discovery empty · ヘッダーとコントロールは常に残す（壊れていないことを示す）。
class DiscoveryEmpty extends StatelessWidget {
  const DiscoveryEmpty({super.key, required this.onNotify, required this.onWiden});

  final VoidCallback onNotify;
  final VoidCallback onWiden;

  @override
  Widget build(BuildContext context) => Container(
    decoration: BoxDecoration(
      color: SColor.sheet,
      borderRadius: BorderRadius.circular(SRadius.photo),
    ),
    padding: const EdgeInsets.symmetric(horizontal: 34),
    child: Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const _EmptyAvatars(),
        const SizedBox(height: 18),
        const Text(
          '今は静かみたい。',
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.44,
            color: SColor.text,
          ),
        ),
        const SizedBox(height: 18),
        const Text(
          'この辺りは 20:00 頃から動き始めることが多いです。'
          'SESSION ON のままにしておくと、近くの人から見つけてもらえます。',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 14, height: 1.7, color: SColor.muted),
        ),
        const SizedBox(height: 22),
        SPrimaryButton('動きがあったら通知', height: 50, onTap: onNotify),
        const SizedBox(height: 9),
        SSecondaryButton('範囲を広げる', height: 50, onTap: onWiden),
      ],
    ),
  );
}

class _EmptyAvatars extends StatelessWidget {
  const _EmptyAvatars();

  @override
  Widget build(BuildContext context) => SizedBox(
    height: 44,
    child: Stack(
      children: [
        for (var i = 0; i < 3; i++)
          Positioned(
            left: i * 31.0,
            child: Container(
              width: 44,
              height: 44,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: SColor.surface,
                shape: BoxShape.circle,
                border: Border.all(color: SColor.sheet, width: 3),
              ),
              child: i == 2 ? const SessionStar(size: 16, color: Color(0x662BD873)) : null,
            ),
          ),
      ],
    ),
  );
}

/// N2 Loading · スケルトンのみ。スピナーや「読み込み中」テキストは出さない。
class DiscoverySkeleton extends StatelessWidget {
  const DiscoverySkeleton({super.key});

  @override
  Widget build(BuildContext context) => ClipRRect(
    borderRadius: BorderRadius.circular(SRadius.photo),
    child: ColoredBox(
      color: SColor.sheet,
      child: Stack(
        children: [
          Positioned(
            top: 14,
            left: 14,
            right: 14,
            child: Row(
              children: [
                for (var i = 0; i < 3; i++) ...[
                  Expanded(
                    child: SSkeleton(
                      height: 3,
                      radius: 2,
                      color: i == 0 ? SColor.raised : SColor.surface,
                    ),
                  ),
                  if (i != 2) const SizedBox(width: 4),
                ],
              ],
            ),
          ),
          const Positioned(
            left: 22,
            right: 22,
            bottom: 24,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SSkeleton(width: 160, height: 30, radius: 8),
                SizedBox(height: 10),
                SSkeleton(width: 220, height: 14, radius: 7),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}

/// N3 Offline · Reachability で自動復帰。SESSION の状態はそのまま維持される。
class OfflinePanel extends StatelessWidget {
  const OfflinePanel({super.key, this.onRetry});

  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 26),
    child: Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          width: 80,
          height: 80,
          alignment: Alignment.center,
          decoration: const BoxDecoration(color: SColor.surface, shape: BoxShape.circle),
          child: const SIcon(SIconData.wifiOff, size: 34, color: SColor.muted),
        ),
        const SizedBox(height: 18),
        const Text(
          'オフラインです',
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.44,
            color: SColor.text,
          ),
        ),
        const SizedBox(height: 18),
        const Text(
          '接続が戻ると自動で再開します。SESSION ON / OFF の状態はそのまま維持されます。',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 14, height: 1.7, color: SColor.muted),
        ),
        const SizedBox(height: 18),
        SizedBox(width: 140, child: SSecondaryButton('再試行', height: 50, onTap: onRetry)),
      ],
    ),
  );
}
