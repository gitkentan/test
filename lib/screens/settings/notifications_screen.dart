import 'package:flutter/widgets.dart';

import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_settings_group.dart';
import '../../widgets/s_toggle.dart';
import '../s_scaffold.dart';

/// L3 Notifications · 通知は3種のみ（Session成立 / メッセージ / 興味あり）。
class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);

    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(title: '通知', onBack: () => Navigator.of(context).maybePop()),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(20, 22, 20, 24),
              children: [
                SSettingsGroup(
                  rows: [
                    SSettingsRow(
                      label: 'Sessionが成立したとき',
                      trailing: SToggle(
                        value: state.notifyOnSession,
                        onChanged: (v) => AppScope.read(context).setNotifyOnSession(v),
                      ),
                    ),
                    SSettingsRow(
                      label: '新しいメッセージ',
                      trailing: SToggle(
                        value: state.notifyOnMessage,
                        onChanged: (v) => AppScope.read(context).setNotifyOnMessage(v),
                      ),
                    ),
                    SSettingsRow(
                      label: '興味を持たれたとき',
                      caption: '相手の名前は通知に含みません',
                      trailing: SToggle(
                        value: state.notifyOnInterest,
                        onChanged: (v) => AppScope.read(context).setNotifyOnInterest(v),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 6),
                  child: Text(
                    'プッシュ本文に相手の名前・写真は含めません。',
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
