import 'package:flutter/widgets.dart';

import '../../models/user.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../s_scaffold.dart';

/// M4 Report done → A
class ReportDoneScreen extends StatelessWidget {
  const ReportDoneScreen({super.key, required this.person});

  final SUser person;

  @override
  Widget build(BuildContext context) => SScaffold(
    child: Column(
      children: [
        Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 84,
                  height: 84,
                  alignment: Alignment.center,
                  decoration: const BoxDecoration(color: SColor.greenTint, shape: BoxShape.circle),
                  child: const SIcon(
                    SIconData.check,
                    size: 36,
                    color: SColor.green,
                    strokeWidth: 2.4,
                  ),
                ),
                const SizedBox(height: 18),
                const Text(
                  '通報を受け付けました',
                  style: TextStyle(
                    fontSize: 23,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.46,
                    color: SColor.text,
                  ),
                ),
                const SizedBox(height: 18),
                Text(
                  '${person.displayName}さんはブロックされ、今後表示されません。'
                  '運営が24時間以内に確認します。',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 15, height: 1.7, color: SColor.muted),
                ),
                const SizedBox(height: 18),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                  decoration: BoxDecoration(
                    color: SColor.surface,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '身の危険を感じる場合',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: SColor.text,
                        ),
                      ),
                      SizedBox(height: 5),
                      Text(
                        '警察（110）または 安全ガイド を確認してください。',
                        style: TextStyle(fontSize: 13, height: 1.6, color: SColor.muted),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 28),
          child: SPrimaryButton('Discoveryに戻る', onTap: () => Navigator.of(context).pop()),
        ),
      ],
    ),
  );
}
