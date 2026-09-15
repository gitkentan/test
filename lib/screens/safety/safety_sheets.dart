import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/distance.dart';
import '../../models/intent.dart';
import '../../models/safety.dart';
import '../../models/user.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_photo.dart';
import '../../widgets/s_settings_group.dart';
import '../../widgets/s_sheet.dart';
import 'report_done_screen.dart';

enum PersonMenuResult { profile, unmatched, blocked, reported }

/// M1 Chat / Detail メニュー · 解除 → 確認 → F ／ ブロック → M3 ／ 通報 → M2。
/// 赤い文字は使わない。破壊的な意図は次のシートで確認する。
class PersonMenuSheet extends StatelessWidget {
  const PersonMenuSheet({super.key, required this.person, this.showUnmatch = true});

  final SUser person;
  final bool showUnmatch;

  @override
  Widget build(BuildContext context) {
    Widget row(String label, VoidCallback onTap) => GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: onTap,
      child: Container(
        height: 54,
        padding: const EdgeInsets.symmetric(horizontal: 18),
        decoration: BoxDecoration(
          color: SColor.surface,
          borderRadius: BorderRadius.circular(SRadius.field),
        ),
        child: Row(
          children: [
            Expanded(
              child: Text(
                label,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: SColor.text,
                ),
              ),
            ),
            const SIcon(SIconData.chevronRight, size: 14, color: SColor.muted, strokeWidth: 2),
          ],
        ),
      ),
    );

    return SBottomSheet(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              ClipOval(child: SizedBox(width: 46, height: 46, child: SPhotoView(person.mainPhoto))),
              const SizedBox(width: 14),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    person.displayName,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: SColor.text,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    [
                      person.distance.label,
                      if (person.intent != null) '${person.intent!.emoji} ${person.intent!.short}',
                    ].join(' · '),
                    style: const TextStyle(fontSize: 12, color: SColor.muted2),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),
          row('プロフィールを見る', () => Navigator.of(context).pop(PersonMenuResult.profile)),
          const SizedBox(height: 9),
          if (showUnmatch) ...[
            row('Sessionを解除', () => Navigator.of(context).pop(PersonMenuResult.unmatched)),
            const SizedBox(height: 9),
          ],
          row('ブロック', () async {
            final blocked = await showSSheet<bool>(
              context,
              builder: (_) => BlockSheet(person: person),
            );
            if (blocked == true && context.mounted) {
              AppScope.read(context).block(person);
              Navigator.of(context).pop(PersonMenuResult.blocked);
            }
          }),
          const SizedBox(height: 9),
          row('通報する', () async {
            final reason = await showSSheet<ReportReason>(
              context,
              builder: (_) => ReportSheet(person: person),
            );
            if (reason != null && context.mounted) {
              AppScope.read(context).block(person);
              await Navigator.of(context).push(sUpRoute(ReportDoneScreen(person: person)));
              if (context.mounted) Navigator.of(context).pop(PersonMenuResult.reported);
            }
          }),
          const SizedBox(height: 16),
          SSecondaryButton('キャンセル', color: SColor.raised, onTap: () => Navigator.of(context).pop()),
        ],
      ),
    );
  }
}

/// M2 Report · 理由 → 送信 → M4。相手には通知されない。
class ReportSheet extends StatefulWidget {
  const ReportSheet({super.key, required this.person});

  final SUser person;

  @override
  State<ReportSheet> createState() => _ReportSheetState();
}

class _ReportSheetState extends State<ReportSheet> {
  bool _alsoBlock = true;

  @override
  Widget build(BuildContext context) => SBottomSheet(
    child: SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            '${widget.person.displayName}さんを通報',
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              letterSpacing: -0.44,
              color: SColor.text,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            '相手には通知されません。運営のみが内容を確認します。',
            style: TextStyle(fontSize: 13, height: 1.6, color: SColor.muted),
          ),
          const SizedBox(height: 16),
          SSettingsGroup(
            rows: [
              for (final r in ReportReason.values)
                SSettingsRow(label: r.label, onTap: () => Navigator.of(context).pop(r)),
            ],
          ),
          const SizedBox(height: 16),
          GestureDetector(
            behavior: HitTestBehavior.opaque,
            onTap: () => setState(() => _alsoBlock = !_alsoBlock),
            child: Row(
              children: [
                Container(
                  width: 21,
                  height: 21,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: _alsoBlock ? SColor.green : SColor.raised,
                    borderRadius: BorderRadius.circular(7),
                  ),
                  child: _alsoBlock
                      ? const SIcon(
                          SIconData.check,
                          size: 13,
                          color: SColor.onGreen,
                          strokeWidth: 3,
                        )
                      : null,
                ),
                const SizedBox(width: 12),
                const Text('同時にブロックする', style: TextStyle(fontSize: 14, color: SColor.text)),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}

/// M3 Block · 実行 → 呼び出し元（A / F）＋Toast。主ボタンは白、赤い塗りは使わない。
class BlockSheet extends StatelessWidget {
  const BlockSheet({super.key, required this.person});

  final SUser person;

  @override
  Widget build(BuildContext context) => SBottomSheet(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Center(
          child: ClipOval(
            child: SizedBox(
              width: 68,
              height: 68,
              child: SPhotoView(person.mainPhoto, grayscale: true),
            ),
          ),
        ),
        const SizedBox(height: 16),
        Text(
          '${person.displayName}さんをブロックしますか？',
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 21,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.42,
            color: SColor.text,
          ),
        ),
        const SizedBox(height: 16),
        const Text(
          '相手には通知されません。お互いに表示されなくなり、Sessionとメッセージも削除されます。',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 14, height: 1.7, color: SColor.muted),
        ),
        const SizedBox(height: 20),
        SLightButton('ブロック', onTap: () => Navigator.of(context).pop(true)),
        const SizedBox(height: 9),
        SSecondaryButton('キャンセル', onTap: () => Navigator.of(context).pop(false)),
      ],
    ),
  );
}
