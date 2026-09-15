import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../s_scaffold.dart';
import 'name_screen.dart';

/// J3 SMS · 6桁で自動送信 → O。誤り時は下に「コードが違います」（枠は赤くしない）。
class SmsCodeScreen extends StatefulWidget {
  const SmsCodeScreen({super.key});

  @override
  State<SmsCodeScreen> createState() => _SmsCodeScreenState();
}

class _SmsCodeScreenState extends State<SmsCodeScreen> {
  String _code = '';
  bool _error = false;

  void _input(String digit) {
    if (_code.length >= 6) return;
    setState(() {
      _code += digit;
      _error = false;
    });
    if (_code.length == 6) _submit();
  }

  void _backspace() {
    if (_code.isEmpty) return;
    setState(() => _code = _code.substring(0, _code.length - 1));
  }

  void _submit() {
    // POST /auth/verify。プロトタイプでは常に成功させる。
    AppScope.read(context).signedIn = true;
    Future<void>.delayed(const Duration(milliseconds: 220)).then((_) {
      if (!mounted) return;
      Navigator.of(context).pushReplacement(sUpRoute(const NameScreen()));
    });
  }

  @override
  Widget build(BuildContext context) {
    final phone = AppScope.of(context).phone;
    return SScaffold(
      bottomSafe: false,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(onBack: () => Navigator.of(context).maybePop()),
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 30, 24, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'コードを入力',
                  style: TextStyle(
                    fontSize: 28,
                    height: 1.25,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.56,
                    color: SColor.text,
                  ),
                ),
                const SizedBox(height: 10),
                Text.rich(
                  TextSpan(
                    text: '$phone に送信しました。',
                    children: const [
                      TextSpan(
                        text: '変更',
                        style: TextStyle(color: SColor.text, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                  style: const TextStyle(fontSize: 15, height: 1.55, color: SColor.muted),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    for (var i = 0; i < 6; i++) ...[
                      Expanded(
                        child: _CodeBox(index: i, code: _code),
                      ),
                      if (i != 5) const SizedBox(width: 8),
                    ],
                  ],
                ),
                const SizedBox(height: 12),
                if (_error)
                  const Text('コードが違います', style: TextStyle(fontSize: 14, color: SColor.danger))
                else
                  const Text.rich(
                    TextSpan(
                      text: 'コードを再送 ',
                      children: [
                        TextSpan(
                          text: '0:42',
                          style: TextStyle(color: SColor.text, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                    style: TextStyle(fontSize: 14, color: SColor.muted),
                  ),
              ],
            ),
          ),
          const Spacer(),
          _Keypad(onDigit: _input, onBackspace: _backspace),
        ],
      ),
    );
  }
}

class _CodeBox extends StatelessWidget {
  const _CodeBox({required this.index, required this.code});

  final int index;
  final String code;

  @override
  Widget build(BuildContext context) {
    final filled = index < code.length;
    final isNext = index == code.length;
    return Container(
      height: 60,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: SColor.surface,
        borderRadius: BorderRadius.circular(SRadius.button),
        border: isNext ? Border.all(color: const Color(0x802BD873)) : null,
      ),
      child: filled
          ? Text(
              code[index],
              style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w700, color: SColor.text),
            )
          : isNext
          ? Container(width: 2, height: 26, color: SColor.green)
          : const SizedBox.shrink(),
    );
  }
}

/// 数字だけの自前キーパッド（sheet 背景・上角26・キーは raised/radius12）。
class _Keypad extends StatelessWidget {
  const _Keypad({required this.onDigit, required this.onBackspace});

  final ValueChanged<String> onDigit;
  final VoidCallback onBackspace;

  @override
  Widget build(BuildContext context) {
    Widget key(String label, {VoidCallback? onTap, bool flat = false}) => GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: onTap ?? () => onDigit(label),
      child: Container(
        height: 46,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: flat ? null : SColor.raised,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          label,
          style: TextStyle(fontSize: flat ? 20 : 24, color: SColor.text),
        ),
      ),
    );

    return Container(
      decoration: const BoxDecoration(
        color: SColor.sheet,
        borderRadius: BorderRadius.vertical(top: Radius.circular(26)),
      ),
      padding: const EdgeInsets.fromLTRB(6, 10, 6, 10),
      child: SafeArea(
        top: false,
        child: GridView.count(
          crossAxisCount: 3,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 6,
          crossAxisSpacing: 6,
          childAspectRatio: 2.6,
          children: [
            for (var i = 1; i <= 9; i++) key('$i'),
            const SizedBox.shrink(),
            key('0'),
            key('⌫', onTap: onBackspace, flat: true),
          ],
        ),
      ),
    );
  }
}
