import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../s_scaffold.dart';

/// J5 Age verification · 登録時ではなく、メッセージ送信時・プロフィール・設定から。
/// 生年月日入力やチェックボックスは確認と見なさない。
/// 実装では SFSafariViewController / Custom Tabs で外部プロバイダへ。
/// 成功 → J6 ／ 失敗 → N4-07。呼び出し元（G2 / I / L1）に戻る。
class AgeVerificationScreen extends StatelessWidget {
  const AgeVerificationScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(onBack: () => Navigator.of(context).maybePop()),
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 26, 24, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 60,
                  height: 60,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: SColor.greenTint,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const SIcon(
                    SIconData.shield,
                    size: 26,
                    color: SColor.green,
                    strokeWidth: 2,
                  ),
                ),
                const SizedBox(height: 18),
                const Text(
                  '18歳以上の確認',
                  style: TextStyle(
                    fontSize: 28,
                    height: 1.25,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.56,
                    color: SColor.text,
                  ),
                ),
                const SizedBox(height: 12),
                const Text(
                  'メッセージのやり取りには年齢確認が必要です。提携の年齢確認サービスに移動します。'
                  '約1分。書類の画像は Session に保存されません。',
                  style: TextStyle(fontSize: 15, height: 1.7, color: SColor.muted),
                ),
                const SizedBox(height: 22),
                const _Step(number: 1, label: '年齢確認サービスへ移動', trailing: '外部', active: true),
                const SizedBox(height: 9),
                const _Step(number: 2, label: 'マイナンバーカード or 身分証で確認'),
                const SizedBox(height: 9),
                const _Step(number: 3, label: 'Sessionに戻る（年齢の結果のみ）'),
              ],
            ),
          ),
          const Spacer(),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 28),
            child: SPrimaryButton(
              '年齢確認へ進む ↗',
              onTap: () {
                // 実装では url_launcher の inAppBrowserView。戻りは App Links / Universal Links。
                AppScope.read(context).markAgeVerified();
                Navigator.of(context).pushReplacement(sUpRoute(const VerifiedScreen()));
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _Step extends StatelessWidget {
  const _Step({required this.number, required this.label, this.trailing, this.active = false});

  final int number;
  final String label;
  final String? trailing;
  final bool active;

  @override
  Widget build(BuildContext context) {
    final t = trailing;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 15),
      decoration: BoxDecoration(
        color: SColor.surface,
        borderRadius: BorderRadius.circular(SRadius.field),
      ),
      child: Row(
        children: [
          Container(
            width: 26,
            height: 26,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: active ? SColor.green : SColor.raised,
              shape: BoxShape.circle,
            ),
            child: Text(
              '$number',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: active ? SColor.onGreen : SColor.muted,
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(label, style: const TextStyle(fontSize: 15, color: SColor.text)),
          ),
          if (t != null) Text(t, style: const TextStyle(fontSize: 12, color: SColor.muted2)),
        ],
      ),
    );
  }
}

/// J6 Verified · 呼び出し元に戻る（G2 からならそのままメッセージ送信に戻れる）。
class VerifiedScreen extends StatelessWidget {
  const VerifiedScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final verifiedAt = AppScope.of(context).me.ageVerifiedAt;
    final stamp = verifiedAt == null
        ? ''
        : ' · ${verifiedAt.year}.${verifiedAt.month.toString().padLeft(2, '0')}'
              '.${verifiedAt.day.toString().padLeft(2, '0')}';
    return SScaffold(
      child: Column(
        children: [
          Expanded(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 34),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 88,
                    height: 88,
                    alignment: Alignment.center,
                    decoration: const BoxDecoration(
                      color: SColor.greenTint,
                      shape: BoxShape.circle,
                    ),
                    child: const SIcon(
                      SIconData.check,
                      size: 38,
                      color: SColor.green,
                      strokeWidth: 2.4,
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Text(
                    '確認できました',
                    style: TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.w700,
                      letterSpacing: -0.52,
                      color: SColor.text,
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Text(
                    '18歳以上であることを確認しました。メッセージを送れるようになりました。'
                    '受け取った情報は年齢の結果のみです。',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 15, height: 1.7, color: SColor.muted),
                  ),
                  const SizedBox(height: 18),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: SColor.greenTint,
                      borderRadius: BorderRadius.circular(SRadius.pill),
                    ),
                    child: Text(
                      '年齢確認済み$stamp',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: SColor.greenText,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 28),
            child: SPrimaryButton('続ける', onTap: () => Navigator.of(context).pop()),
          ),
        ],
      ),
    );
  }
}
