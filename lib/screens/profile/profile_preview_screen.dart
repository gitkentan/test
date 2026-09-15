import 'package:flutter/widgets.dart';

import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/swipe_card.dart';
import '../s_scaffold.dart';

/// I3 プロフィールプレビュー · A と完全に同一のカードコンポーネントを自分のデータで描画する。
/// SESSION ON / Intent / 距離は現在値をそのまま表示し、OFF なら緑の行は出ない。操作は無効。
class ProfilePreviewScreen extends StatelessWidget {
  const ProfilePreviewScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final me = AppScope.of(context).me;

    return SScaffold(
      bottomSafe: false,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '相手からの見え方',
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w700,
                        letterSpacing: -0.34,
                        color: SColor.text,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text('スワイプ画面と同じ表示です', style: TextStyle(fontSize: 12, color: SColor.muted2)),
                  ],
                ),
                SCircleButton(
                  onTap: () => Navigator.of(context).maybePop(),
                  child: const SIcon(SIconData.close, size: 15, strokeWidth: 2),
                ),
              ],
            ),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(SSpace.photoInset, 14, SSpace.photoInset, 0),
              // 操作は無効。同じ SwipeCard をそのまま使う。
              child: IgnorePointer(child: SwipeCard(person: me)),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 14, SSpace.x5, 0),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 12),
              decoration: BoxDecoration(
                color: SColor.surface,
                borderRadius: BorderRadius.circular(SRadius.button),
              ),
              child: const Row(
                children: [
                  SIcon(SIconData.info, size: 17, color: SColor.muted, strokeWidth: 2),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      '自己紹介と写真2枚目以降は、タップされたときに表示されます。',
                      style: TextStyle(fontSize: 13, height: 1.5, color: SColor.secondary),
                    ),
                  ),
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 20, SSpace.x5, 20),
            child: SafeArea(
              top: false,
              child: SSecondaryButton('編集に戻る', onTap: () => Navigator.of(context).maybePop()),
            ),
          ),
        ],
      ),
    );
  }
}
