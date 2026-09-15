import 'package:flutter/widgets.dart';

import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_sheet.dart';

/// N4-01〜09 · 全てボトムシート。1画面1主ボタン。赤は使わずコピーで伝える。
/// 09（強制更新）だけは閉じられない。
enum SErrorKind {
  network,
  location,
  expired,
  unavailable,
  payment,
  upload,
  age,
  sendFailed,
  forceUpdate,
}

class SErrorSheet extends StatelessWidget {
  const SErrorSheet({super.key, required this.kind, this.onPrimary, this.onSecondary});

  final SErrorKind kind;
  final VoidCallback? onPrimary;
  final VoidCallback? onSecondary;

  static bool dismissible(SErrorKind kind) => kind != SErrorKind.forceUpdate;

  ({String title, String body, String primary, String? secondary, SIconData icon, bool light})
  get _spec => switch (kind) {
    SErrorKind.network => (
      title: '接続できません',
      body: 'ネットワークを確認して、もう一度お試しください。',
      primary: '再試行',
      secondary: null,
      icon: SIconData.wifiOff,
      light: false,
    ),
    SErrorKind.location => (
      title: '位置情報が使えません',
      body: '近くの人を見つけるには位置情報が必要です。正確な位置は公開されません。',
      primary: '設定を開く',
      secondary: null,
      icon: SIconData.lock,
      light: false,
    ),
    SErrorKind.expired => (
      title: 'ログインが切れました',
      body: '安全のため再ログインしてください。',
      primary: 'ログイン',
      secondary: null,
      icon: SIconData.shield,
      light: false,
    ),
    SErrorKind.unavailable => (
      title: 'このプロフィールは表示できません',
      body: '相手が退会・非表示にしたか、ブロックされています。',
      primary: '戻る',
      secondary: null,
      icon: SIconData.info,
      light: true,
    ),
    SErrorKind.payment => (
      title: '決済が完了しませんでした',
      body: '請求はされていません。支払い方法を確認してください。',
      primary: 'もう一度',
      secondary: 'あとで',
      icon: SIconData.card,
      light: false,
    ),
    SErrorKind.upload => (
      title: '写真をアップロードできませんでした',
      body: 'ファイルサイズが大きいか、接続が不安定です。',
      primary: '再アップロード',
      secondary: '別の写真を選ぶ',
      icon: SIconData.plus,
      light: false,
    ),
    SErrorKind.age => (
      title: '年齢を確認できませんでした',
      body: '別の書類でもう一度お試しください。確認できるまでご利用いただけません。',
      primary: 'もう一度 ↗',
      secondary: null,
      icon: SIconData.shield,
      light: false,
    ),
    SErrorKind.sendFailed => (
      title: 'メッセージを送れませんでした',
      body: 'Sessionが解除された可能性があります。',
      primary: 'メッセージに戻る',
      secondary: null,
      icon: SIconData.message,
      light: true,
    ),
    SErrorKind.forceUpdate => (
      title: 'アプリを更新してください',
      body: 'このバージョンはサポートが終了しました。',
      primary: 'App Storeを開く',
      secondary: null,
      icon: SIconData.arrowUp,
      light: false,
    ),
  };

  @override
  Widget build(BuildContext context) {
    final s = _spec;
    final secondary = s.secondary;
    return SBottomSheet(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              width: 50,
              height: 50,
              alignment: Alignment.center,
              decoration: const BoxDecoration(color: SColor.surface, shape: BoxShape.circle),
              child: SIcon(s.icon, size: 22, color: SColor.muted),
            ),
          ),
          const SizedBox(height: 14),
          Text(
            s.title,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: SColor.text),
          ),
          const SizedBox(height: 10),
          Text(
            s.body,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 13, height: 1.6, color: SColor.muted),
          ),
          const SizedBox(height: 20),
          if (s.light)
            SSecondaryButton(
              s.primary,
              height: 48,
              color: SColor.raised,
              onTap: onPrimary ?? () => Navigator.of(context).pop(),
            )
          else
            SPrimaryButton(
              s.primary,
              height: 48,
              onTap: onPrimary ?? () => Navigator.of(context).pop(),
            ),
          if (secondary != null)
            STextButton(secondary, onTap: onSecondary ?? () => Navigator.of(context).pop()),
        ],
      ),
    );
  }
}

/// N4 を出すヘルパー。強制更新だけは閉じられない。
Future<void> showSError(BuildContext context, SErrorKind kind, {VoidCallback? onPrimary}) =>
    showSSheet<void>(
      context,
      dismissible: SErrorSheet.dismissible(kind),
      builder: (_) => SErrorSheet(kind: kind, onPrimary: onPrimary),
    );
