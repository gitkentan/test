import 'package:flutter/widgets.dart';

import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_settings_group.dart';
import '../../widgets/s_toggle.dart';
import '../s_scaffold.dart';

/// L2 Privacy · 「Live / Online」は存在しない。位置精度は常に丸め。
class PrivacyScreen extends StatelessWidget {
  const PrivacyScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);

    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(title: 'プライバシー', onBack: () => Navigator.of(context).maybePop()),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(20, 22, 20, 24),
              children: [
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: SColor.greenTint,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SIcon(SIconData.lock, size: 20, color: SColor.green, strokeWidth: 2),
                      SizedBox(width: 12),
                      Expanded(
                        child: Text.rich(
                          TextSpan(
                            text: '正確な位置は誰にも公開されません。\n',
                            style: TextStyle(fontWeight: FontWeight.w700, color: SColor.text),
                            children: [
                              TextSpan(
                                text:
                                    '距離は 1 / 3 / 5 / 10 km に丸めて表示。'
                                    '地図・駅名・住所は一切出しません。',
                                style: TextStyle(
                                  fontWeight: FontWeight.w400,
                                  color: SColor.greenText,
                                ),
                              ),
                            ],
                          ),
                          style: TextStyle(fontSize: 13, height: 1.65),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                SSettingsGroup(
                  rows: [
                    const SSettingsRow(
                      label: '位置情報',
                      caption: '使用中のみ許可',
                      value: 'iOS設定 ›',
                      chevron: false,
                    ),
                    SSettingsRow(
                      label: 'Discoveryに表示',
                      caption: 'OFFでもSession中の相手とは話せます',
                      trailing: SToggle(
                        value: state.discoverable,
                        onChanged: (v) => AppScope.read(context).setDiscoverable(v),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 6),
                  child: Text(
                    'SESSION ON / OFF は可用性の状態で、オンライン状態ではありません。'
                    '最終接続時刻は相手に表示されません（ランキングにのみ内部使用）。',
                    style: TextStyle(fontSize: 12, height: 1.65, color: SColor.muted2),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
