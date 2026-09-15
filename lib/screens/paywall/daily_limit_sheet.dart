import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_sheet.dart';
import 'paywall_screen.dart';

/// K2 Daily limit · 21回目の Request で表示 → K1 ／ 待つ → A
/// （右スワイプは無効。Discovery の閲覧は続けられる）。
class DailyLimitSheet extends StatelessWidget {
  const DailyLimitSheet({super.key});

  @override
  Widget build(BuildContext context) => SBottomSheet(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          '今日はたくさん出会いました。',
          style: TextStyle(
            fontSize: 24,
            height: 1.35,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.48,
            color: SColor.text,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Session Request は明日 06:00 にリセットされます。続けるなら Session+。',
          style: TextStyle(fontSize: 15, height: 1.7, color: SColor.muted),
        ),
        const SizedBox(height: 20),
        const _CompareTable(),
        const SizedBox(height: 20),
        SPrimaryButton(
          'Session+ をはじめる',
          onTap: () {
            Navigator.of(context).pop();
            Navigator.of(context).push(sUpRoute(const PaywallScreen()));
          },
        ),
        STextButton('明日まで待つ', onTap: () => Navigator.of(context).pop()),
      ],
    ),
  );
}

class _CompareTable extends StatelessWidget {
  const _CompareTable();

  static const _rows = [
    ('Request / 日', '20 / 20', '無制限'),
    ('距離の絞り込み', '5 km〜', '1 km〜'),
    ('メッセージ', '無制限', '無制限'),
  ];

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 18),
    decoration: BoxDecoration(
      color: SColor.surface,
      borderRadius: BorderRadius.circular(SRadius.field),
    ),
    child: Column(
      children: [
        const _Row(cells: ('', '無料', 'Session+'), header: true),
        for (var i = 0; i < _rows.length; i++) _Row(cells: _rows[i], last: i == _rows.length - 1),
      ],
    ),
  );
}

class _Row extends StatelessWidget {
  const _Row({required this.cells, this.header = false, this.last = false});

  final (String, String, String) cells;
  final bool header;
  final bool last;

  @override
  Widget build(BuildContext context) => Container(
    height: header ? 38 : 42,
    decoration: last
        ? null
        : const BoxDecoration(
            border: Border(bottom: BorderSide(color: SColor.hairline)),
          ),
    child: Row(
      children: [
        Expanded(
          flex: 3,
          child: Text(
            cells.$1,
            style: TextStyle(
              fontSize: header ? 11 : 14,
              color: header ? SColor.muted2 : SColor.text,
            ),
          ),
        ),
        Expanded(
          flex: 2,
          child: Text(
            cells.$2,
            style: TextStyle(
              fontSize: header ? 11 : 14,
              color: header ? SColor.muted2 : SColor.muted,
            ),
          ),
        ),
        Expanded(
          flex: 2,
          child: Text(
            cells.$3,
            style: TextStyle(
              fontSize: header ? 11 : 14,
              fontWeight: header ? FontWeight.w600 : FontWeight.w400,
              color: header ? SColor.green : SColor.text,
            ),
          ),
        ),
      ],
    ),
  );
}
