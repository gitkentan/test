import 'package:flutter/widgets.dart';

import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_toast.dart';
import '../../widgets/session_star.dart';
import '../s_scaffold.dart';

/// K1 Session+ paywall · StoreKit 2 / Google Play Billing 6。
/// 王冠・金色・ダイヤは使わない。
class PaywallScreen extends StatefulWidget {
  const PaywallScreen({super.key});

  @override
  State<PaywallScreen> createState() => _PaywallScreenState();
}

class _PaywallScreenState extends State<PaywallScreen> {
  int _plan = 1;

  static const _benefits = [
    '誰が興味を持ってくれたか見る',
    '距離を 1 km 単位で絞り込む',
    'Session Request 無制限',
    'Undo（直前のスキップを戻す）',
    '近くの人数を正確に表示',
  ];

  static const _plans = [
    (label: '1ヶ月', price: '¥1,980', note: null, perMonth: null),
    (label: '3ヶ月', price: '¥4,500', note: '24% OFF', perMonth: '¥1,500/月'),
    (label: '6ヶ月', price: '¥7,800', note: null, perMonth: '¥1,300/月'),
  ];

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      background: SColor.bgDeep,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                SCircleButton(
                  onTap: () => Navigator.of(context).maybePop(),
                  child: const SIcon(SIconData.close, size: 15, strokeWidth: 2),
                ),
                GestureDetector(
                  onTap: () => SToast.show(context, '購入を復元しています'),
                  child: const Text('購入を復元', style: TextStyle(fontSize: 13, color: SColor.muted)),
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(24, 28, 24, 12),
              children: [
                const SessionLockup(starSize: 30, fontSize: 30, plus: true),
                const SizedBox(height: 10),
                const Text(
                  'もっと多くの「今」に。',
                  style: TextStyle(
                    fontSize: 22,
                    height: 1.35,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.44,
                    color: SColor.text,
                  ),
                ),
                const SizedBox(height: 22),
                for (final b in _benefits) ...[
                  Row(
                    children: [
                      Container(
                        width: 22,
                        height: 22,
                        alignment: Alignment.center,
                        decoration: const BoxDecoration(
                          color: SColor.greenTint,
                          shape: BoxShape.circle,
                        ),
                        child: const SIcon(
                          SIconData.check,
                          size: 12,
                          color: SColor.green,
                          strokeWidth: 3,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(b, style: const TextStyle(fontSize: 15, color: SColor.text)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 13),
                ],
                const SizedBox(height: 12),
                for (var i = 0; i < _plans.length; i++) ...[
                  _PlanRow(
                    plan: _plans[i],
                    selected: _plan == i,
                    onTap: () => setState(() => _plan = i),
                  ),
                  const SizedBox(height: 8),
                ],
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 24),
            child: Column(
              children: [
                SPrimaryButton(
                  'Session+ をはじめる · ${_plans[_plan].price}',
                  onTap: () {
                    // 失敗時は N4-05。ここでは購入成功として扱う。
                    AppScope.read(context).purchasePlus();
                    Navigator.of(context).pop();
                    SToast.show(context, 'Session+ を開始しました');
                  },
                ),
                const SizedBox(height: 10),
                const Text(
                  '自動更新 · いつでも解約できます · 利用規約',
                  style: TextStyle(fontSize: 11, color: SColor.muted2),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

typedef _Plan = ({String label, String price, String? note, String? perMonth});

class _PlanRow extends StatelessWidget {
  const _PlanRow({required this.plan, required this.selected, required this.onTap});

  final _Plan plan;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final note = plan.note;
    final perMonth = plan.perMonth;
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        decoration: BoxDecoration(
          color: selected ? SColor.greenTint : SColor.surface,
          borderRadius: BorderRadius.circular(SRadius.field),
          border: selected ? Border.all(color: SColor.greenBorder) : null,
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    plan.label,
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
                      color: selected ? SColor.text : SColor.muted,
                    ),
                  ),
                  if (note != null) ...[
                    const SizedBox(height: 3),
                    Text(note, style: const TextStyle(fontSize: 12, color: SColor.greenText)),
                  ],
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  plan.price,
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w700,
                    color: SColor.text,
                  ),
                ),
                if (perMonth != null) ...[
                  const SizedBox(height: 3),
                  Text(perMonth, style: const TextStyle(fontSize: 12, color: SColor.muted)),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}
