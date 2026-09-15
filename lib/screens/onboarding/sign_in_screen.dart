import 'dart:io' show Platform;

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/session_star.dart';
import '../s_scaffold.dart';
import 'name_screen.dart';
import 'sms_code_screen.dart';

/// J2b Sign in · Apple → O ／ 電話番号 → J3。年齢確認は登録時に求めない。
/// Android は Apple を Google に差し替える。
class SignInScreen extends StatelessWidget {
  const SignInScreen({super.key});

  bool get _isAndroid => !kIsWeb && Platform.isAndroid;

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      child: Column(
        children: [
          SHeader(onBack: () => Navigator.of(context).maybePop()),
          Expanded(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: const [
                SessionStar(size: 44),
                SizedBox(height: 14),
                Text(
                  'Session',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.7,
                    color: SColor.text,
                  ),
                ),
                SizedBox(height: 14),
                Text('ログインまたは新規登録', style: TextStyle(fontSize: 14, color: SColor.muted)),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 28),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                SLightButton(
                  _isAndroid ? 'Googleで続ける' : 'Appleで続ける',
                  // POST /auth/apple · /auth/google。成功したら名前（O）へ。
                  onTap: () => Navigator.of(context).push(sUpRoute(const NameScreen())),
                ),
                const SizedBox(height: 10),
                SSecondaryButton(
                  '電話番号で続ける',
                  onTap: () => Navigator.of(context).push(sUpRoute(const SmsCodeScreen())),
                ),
                const SizedBox(height: 16),
                const Text(
                  '続けることで 利用規約 と プライバシーポリシー に同意したものとみなされます。'
                  '18歳未満の方はご利用いただけません。',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 12, height: 1.65, color: SColor.muted2),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
