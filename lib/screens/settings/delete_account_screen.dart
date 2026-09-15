import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/safety.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_settings_group.dart';
import '../../widgets/s_sheet.dart';
import '../../widgets/s_toast.dart';
import '../onboarding/welcome_screen.dart';
import '../s_scaffold.dart';

/// L5 Delete · 非表示を先に提示。進む → L6（「削除」入力で有効）→ J2。
class DeleteAccountScreen extends StatefulWidget {
  const DeleteAccountScreen({super.key});

  @override
  State<DeleteAccountScreen> createState() => _DeleteAccountScreenState();
}

class _DeleteAccountScreenState extends State<DeleteAccountScreen> {
  DeleteReason? _reason;

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(title: 'アカウントを削除', onBack: () => Navigator.of(context).maybePop()),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
              children: [
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 6),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '本当に削除しますか？',
                        style: TextStyle(
                          fontSize: 22,
                          height: 1.35,
                          fontWeight: FontWeight.w700,
                          letterSpacing: -0.44,
                          color: SColor.text,
                        ),
                      ),
                      SizedBox(height: 8),
                      Text(
                        'Session、メッセージ、興味の履歴がすべて消え、復元できません。'
                        '年齢確認も再度必要になります。',
                        style: TextStyle(fontSize: 14, height: 1.7, color: SColor.muted),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),
                // 削除の前に一時的な非表示を提示する。
                GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: () {
                    AppScope.read(context).setDiscoverable(false);
                    Navigator.of(context).pop();
                    SToast.show(context, 'Discoveryから非表示にしました');
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                    decoration: BoxDecoration(
                      color: SColor.greenTint,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '代わりに一時的に非表示にする',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w600,
                                  color: SColor.text,
                                ),
                              ),
                              SizedBox(height: 3),
                              Text(
                                'Discoveryから消え、いつでも戻れます',
                                style: TextStyle(fontSize: 12, color: SColor.greenText),
                              ),
                            ],
                          ),
                        ),
                        Text(
                          '非表示 ›',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: SColor.green,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 18),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 6),
                  child: Text('理由（任意）', style: TextStyle(fontSize: 12, color: SColor.muted2)),
                ),
                const SizedBox(height: 10),
                SSettingsGroup(
                  rows: [
                    for (final r in DeleteReason.values)
                      SSettingsRow(
                        label: r.label,
                        chevron: false,
                        onTap: () => setState(() => _reason = r),
                        trailing: _Radio(selected: _reason == r),
                      ),
                  ],
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 28),
            child: SSecondaryButton(
              '削除に進む',
              textColor: SColor.muted,
              onTap: () async {
                final confirmed = await showSSheet<bool>(
                  context,
                  builder: (_) => const DeleteConfirmSheet(),
                );
                if (confirmed == true && context.mounted) {
                  // DELETE /me（30日以内は復元可、以降完全削除）
                  Navigator.of(
                    context,
                  ).pushAndRemoveUntil(sFadeRoute(const WelcomeScreen()), (route) => false);
                }
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _Radio extends StatelessWidget {
  const _Radio({required this.selected});

  final bool selected;

  @override
  Widget build(BuildContext context) => Container(
    width: 19,
    height: 19,
    alignment: Alignment.center,
    decoration: BoxDecoration(
      shape: BoxShape.circle,
      border: Border.all(color: selected ? SColor.green : const Color(0x38FFFFFF), width: 2),
    ),
    child: selected
        ? Container(
            width: 9,
            height: 9,
            decoration: const BoxDecoration(color: SColor.green, shape: BoxShape.circle),
          )
        : null,
  );
}

/// L6 最終確認 · 「やめる」が主ボタン。破壊的操作のみ danger を使う（緑は使わない）。
class DeleteConfirmSheet extends StatefulWidget {
  const DeleteConfirmSheet({super.key});

  @override
  State<DeleteConfirmSheet> createState() => _DeleteConfirmSheetState();
}

class _DeleteConfirmSheetState extends State<DeleteConfirmSheet> {
  final _controller = TextEditingController();
  final _focus = FocusNode();

  static const _keyword = '削除';

  @override
  void initState() {
    super.initState();
    _controller.addListener(() => setState(() {}));
    WidgetsBinding.instance.addPostFrameCallback((_) => _focus.requestFocus());
  }

  @override
  void dispose() {
    _controller.dispose();
    _focus.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final ok = _controller.text.trim() == _keyword;
    return SBottomSheet(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text(
            'アカウントを完全に削除',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              letterSpacing: -0.44,
              color: SColor.text,
            ),
          ),
          const SizedBox(height: 8),
          const Text.rich(
            TextSpan(
              text: 'この操作は取り消せません。確認のため ',
              children: [
                TextSpan(
                  text: _keyword,
                  style: TextStyle(color: SColor.text, fontWeight: FontWeight.w700),
                ),
                TextSpan(text: ' と入力してください。'),
              ],
            ),
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 14, height: 1.7, color: SColor.muted),
          ),
          const SizedBox(height: 18),
          Container(
            height: 52,
            alignment: Alignment.center,
            padding: const EdgeInsets.symmetric(horizontal: 18),
            decoration: BoxDecoration(
              color: SColor.surface,
              borderRadius: BorderRadius.circular(SRadius.button),
            ),
            child: EditableText(
              controller: _controller,
              focusNode: _focus,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w600, color: SColor.text),
              cursorColor: SColor.green,
              backgroundCursorColor: SColor.surface,
              maxLines: 1,
            ),
          ),
          const SizedBox(height: 18),
          Opacity(
            opacity: ok ? 1 : 0.55,
            child: SSecondaryButton(
              '削除する',
              color: SColor.dangerTint,
              textColor: SColor.danger,
              onTap: ok ? () => Navigator.of(context).pop(true) : null,
            ),
          ),
          const SizedBox(height: 9),
          SLightButton('やめる', onTap: () => Navigator.of(context).pop(false)),
        ],
      ),
    );
  }
}
