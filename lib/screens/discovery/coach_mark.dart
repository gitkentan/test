import 'package:flutter/widgets.dart';

import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/session_star.dart';
import '../s_scaffold.dart';

/// B2 初回のみのコーチマーク。以降 Discovery に説明テキストは一切表示しない。
class CoachMark extends StatelessWidget {
  const CoachMark({super.key, required this.onDone});

  final VoidCallback onDone;

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Opacity(
            opacity: 0.5,
            child: Padding(
              padding: EdgeInsets.fromLTRB(20, 14, 20, 0),
              child: Align(
                alignment: Alignment.centerLeft,
                child: SessionLockup(starSize: 17, fontSize: 19),
              ),
            ),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(SSpace.photoInset, 40, SSpace.photoInset, 0),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(SRadius.photo),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    const DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment(-0.6, -1),
                          end: Alignment(0.4, 1),
                          colors: [Color(0xFF3C403C), Color(0xFF22252A), Color(0xFF141614)],
                        ),
                      ),
                    ),
                    const ColoredBox(color: Color(0xB8080908)),
                    Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            SIcon(SIconData.back, size: 26, color: SColor.muted),
                            SizedBox(width: 14),
                            Text(
                              '左へ · スキップ',
                              style: TextStyle(
                                fontSize: 19,
                                fontWeight: FontWeight.w600,
                                color: SColor.muted,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 34),
                        Container(width: 52, height: 1, color: const Color(0x24FFFFFF)),
                        const SizedBox(height: 34),
                        const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              '右へ · Session',
                              style: TextStyle(
                                fontSize: 19,
                                fontWeight: FontWeight.w700,
                                color: SColor.green,
                              ),
                            ),
                            SizedBox(width: 14),
                            SIcon(SIconData.chevronRight, size: 26, color: SColor.green),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 22, 24, 24),
            child: SLightButton('はじめる', onTap: onDone),
          ),
        ],
      ),
    );
  }
}
