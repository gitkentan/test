import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_settings_group.dart';
import '../../widgets/s_toast.dart';
import '../onboarding/age_verification_screen.dart';
import '../paywall/paywall_screen.dart';
import '../s_scaffold.dart';
import 'blocked_users_screen.dart';
import 'delete_account_screen.dart';
import 'notifications_screen.dart';
import 'privacy_screen.dart';

/// L1 Settings · I の ⚙ から。ルートには出さない。
/// β ではこの7項目のみ（言語・テーマ・課金履歴などは持たない）。
class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final me = state.me;

    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(title: '設定', onBack: () => Navigator.of(context).maybePop()),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(20, 22, 20, 24),
              children: [
                SSettingsGroup(
                  rows: [
                    SSettingsRow(
                      label: 'Session+',
                      value: state.isPlus ? '加入中 ›' : '無料プラン ›',
                      chevron: false,
                      onTap: () => Navigator.of(context).push(sUpRoute(const PaywallScreen())),
                    ),
                    SSettingsRow(label: '電話番号', value: '+81 90 •••• 5678', chevron: false),
                    SSettingsRow(
                      label: '年齢確認',
                      value: me.ageVerified ? '確認済み' : '未確認 · 確認する ›',
                      valueColor: me.ageVerified ? SColor.muted : SColor.green,
                      chevron: false,
                      onTap: me.ageVerified
                          ? null
                          : () =>
                                Navigator.of(context).push(sUpRoute(const AgeVerificationScreen())),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                SSettingsGroup(
                  rows: [
                    SSettingsRow(
                      label: '通知',
                      onTap: () =>
                          Navigator.of(context).push(sPushRoute(const NotificationsScreen())),
                    ),
                    SSettingsRow(
                      label: 'プライバシー',
                      onTap: () => Navigator.of(context).push(sPushRoute(const PrivacyScreen())),
                    ),
                    SSettingsRow(
                      label: 'ブロックしたユーザー',
                      value: '${state.blocked.length}',
                      onTap: () =>
                          Navigator.of(context).push(sPushRoute(const BlockedUsersScreen())),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                // 安全ガイド / ヘルプ / 規約は WebView。
                SSettingsGroup(
                  rows: [
                    SSettingsRow(label: '安全ガイド', onTap: () => SToast.show(context, '安全ガイドを開きます')),
                    SSettingsRow(
                      label: 'ヘルプ・お問い合わせ',
                      onTap: () => SToast.show(context, 'ヘルプを開きます'),
                    ),
                    SSettingsRow(
                      label: '利用規約・プライバシーポリシー',
                      onTap: () => SToast.show(context, '利用規約を開きます'),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                SSettingsGroup(
                  rows: [
                    SSettingsRow(
                      label: 'ログアウト',
                      center: true,
                      chevron: false,
                      onTap: () => AppScope.read(context).signOut(),
                    ),
                    // 削除は灰色・最下部。
                    SSettingsRow(
                      label: 'アカウントを削除',
                      center: true,
                      chevron: false,
                      labelColor: SColor.muted,
                      onTap: () =>
                          Navigator.of(context).push(sPushRoute(const DeleteAccountScreen())),
                    ),
                  ],
                ),
                const SizedBox(height: 18),
                const Center(
                  child: Text(
                    'Session β 1.0.0 (412)',
                    style: TextStyle(fontSize: 11, color: SColor.disabled),
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
