import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/distance.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/session_star.dart';
import '../discovery/discovery_screen.dart';
import '../s_scaffold.dart';

/// J10 Location · 許可 → A ／ 拒否 → A（N4-02 を表示）。
/// 精度は丸め（1 / 3 / 5 / 10 km）。
class LocationScreen extends StatelessWidget {
  const LocationScreen({super.key});

  @override
  Widget build(BuildContext context) {
    void toDiscovery() {
      AppScope.read(context).signedIn = true;
      Navigator.of(
        context,
      ).pushAndRemoveUntil(sFadeRoute(const DiscoveryScreen()), (route) => false);
    }

    return SScaffold(
      child: Column(
        children: [
          Expanded(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 30),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const _Radar(),
                  const SizedBox(height: 22),
                  const Text(
                    '近くの人を見つけるために\n位置情報を使います',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 24,
                      height: 1.35,
                      fontWeight: FontWeight.w700,
                      letterSpacing: -0.48,
                      color: SColor.text,
                    ),
                  ),
                  const SizedBox(height: 22),
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    alignment: WrapAlignment.center,
                    children: [
                      for (final b in DistanceBand.values)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                          decoration: BoxDecoration(
                            color: SColor.surface,
                            borderRadius: BorderRadius.circular(SRadius.pill),
                          ),
                          child: Text(
                            b.label,
                            style: const TextStyle(fontSize: 12, color: SColor.muted),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 22),
                  const Text(
                    '正確な現在地は誰にも公開されません。距離は上のように丸めて表示され、'
                    '駅名や地図上の位置も出しません。',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 13, height: 1.7, color: SColor.muted),
                  ),
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 24),
            child: Column(
              children: [
                // 実装では permission_handler。拒否されても Discovery には入れる。
                SPrimaryButton('位置情報を許可', onTap: toDiscovery),
                STextButton('あとで', onTap: toDiscovery),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// 同心円のレーダー。中心の星だけがゆっくり脈打つ。
class _Radar extends StatefulWidget {
  const _Radar();

  @override
  State<_Radar> createState() => _RadarState();
}

class _RadarState extends State<_Radar> with SingleTickerProviderStateMixin {
  late final AnimationController _c;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: const Duration(milliseconds: 2200))
      ..repeat(reverse: true);
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => SizedBox(
    width: 190,
    height: 190,
    child: Stack(
      alignment: Alignment.center,
      children: [
        _ring(190, const Color(0x1A2BD873)),
        _ring(122, const Color(0x2E2BD873)),
        Container(
          width: 58,
          height: 58,
          decoration: const BoxDecoration(color: Color(0x122BD873), shape: BoxShape.circle),
        ),
        AnimatedBuilder(
          animation: _c,
          builder: (context, child) => Transform.scale(scale: 0.94 + 0.06 * _c.value, child: child),
          child: Container(
            width: 44,
            height: 44,
            alignment: Alignment.center,
            decoration: const BoxDecoration(color: Color(0xFF0D100F), shape: BoxShape.circle),
            child: const SessionStar(size: 22),
          ),
        ),
      ],
    ),
  );

  Widget _ring(double size, Color color) => Container(
    width: size,
    height: size,
    decoration: BoxDecoration(
      shape: BoxShape.circle,
      border: Border.all(color: color),
    ),
  );
}
