import 'package:flutter/widgets.dart';

import '../../models/user.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_sheet.dart';

/// G2 Age required · 未確認ユーザーが H で送信しようとした瞬間に出る。
/// 閲覧・スワイプ・Session 成立は年齢確認なしで可能で、メッセージ送信のみ要確認。
class AgeRequiredSheet extends StatelessWidget {
  const AgeRequiredSheet({super.key, required this.person});

  final SUser person;

  @override
  Widget build(BuildContext context) => SBottomSheet(
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 54,
          height: 54,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: SColor.greenTint,
            borderRadius: BorderRadius.circular(18),
          ),
          child: const SIcon(SIconData.shield, size: 24, color: SColor.green, strokeWidth: 2),
        ),
        const SizedBox(height: 18),
        const Text(
          'メッセージを送るには年齢確認が必要です',
          style: TextStyle(
            fontSize: 22,
            height: 1.35,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.44,
            color: SColor.text,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          '18歳以上であることを確認すると、${person.displayName}さんにメッセージを送れます。'
          '約1分で完了し、書類の画像は Session に保存されません。',
          style: const TextStyle(fontSize: 14, height: 1.7, color: SColor.muted),
        ),
        const SizedBox(height: 18),
        SPrimaryButton('年齢確認へ進む ↗', onTap: () => Navigator.of(context).pop(true)),
        STextButton('あとで', onTap: () => Navigator.of(context).pop(false)),
      ],
    ),
  );
}
