import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/user.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_sheet.dart';
import '../../widgets/s_sliders.dart';
import '../paywall/paywall_screen.dart';

/// D / D2 Filter Sheet · 距離は 1〜30km・1kmステップ。
/// 無料は 5km で止まり、左へドラッグしても値は変えずにロック案内（D2）を出す。
class FilterSheet extends StatefulWidget {
  const FilterSheet({super.key});

  @override
  State<FilterSheet> createState() => _FilterSheetState();
}

class _FilterSheetState extends State<FilterSheet> {
  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final locked = state.distanceLockHit && !state.isPlus;

    return SBottomSheet(
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.baseline,
              textBaseline: TextBaseline.alphabetic,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '絞り込み',
                  style: TextStyle(
                    fontSize: 25,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.5,
                    color: SColor.text,
                  ),
                ),
                GestureDetector(
                  onTap: () => AppScope.read(context).resetFilters(),
                  child: const Text('リセット', style: TextStyle(fontSize: 14, color: SColor.muted)),
                ),
              ],
            ),
            const SizedBox(height: 24),
            Opacity(
              opacity: locked ? 0.45 : 1,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  _RowLabel(
                    label: '年齢',
                    value: '${state.ageRange.start.round()} – ${state.ageRange.end.round()}',
                  ),
                  const SizedBox(height: 12),
                  SAgeRangeSlider(
                    value: state.ageRange,
                    onChanged: (v) => AppScope.read(context).setAgeRange(v),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            const Text('表示する相手', style: TextStyle(fontSize: 13, color: SColor.muted)),
            const SizedBox(height: 12),
            Row(
              children: [
                for (final g in Gender.values) ...[
                  Expanded(
                    child: _SegmentButton(
                      label: g.label,
                      selected: state.showMe == g,
                      onTap: () => AppScope.read(context).setShowMe(g),
                    ),
                  ),
                  if (g != Gender.values.last) const SizedBox(width: 8),
                ],
              ],
            ),
            const SizedBox(height: 24),
            _RowLabel(label: '距離', value: '${state.distanceKm} km以内', large: true),
            const SizedBox(height: 12),
            SDistanceSlider(
              km: state.distanceKm,
              floorKm: state.distanceFloor,
              lockHit: locked,
              onChanged: (km) => AppScope.read(context).setDistance(km),
            ),
            const SizedBox(height: 6),
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('1 km', style: TextStyle(fontSize: 11, color: Color(0xFF5A5F5A))),
                Text('30 km', style: TextStyle(fontSize: 11, color: Color(0xFF5A5F5A))),
              ],
            ),
            const SizedBox(height: 10),
            if (locked)
              _LockNotice(
                onTap: () {
                  Navigator.of(context).pop();
                  Navigator.of(context).push(sUpRoute(const PaywallScreen()));
                },
              )
            else
              const Text('近い人は自動で優先表示されます', style: TextStyle(fontSize: 12, color: SColor.muted)),
            const SizedBox(height: 24),
            SPrimaryButton(
              '適用',
              onTap: () {
                AppScope.read(context).resetQueue();
                Navigator.of(context).pop();
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _RowLabel extends StatelessWidget {
  const _RowLabel({required this.label, required this.value, this.large = false});

  final String label;
  final String value;
  final bool large;

  @override
  Widget build(BuildContext context) => Row(
    crossAxisAlignment: CrossAxisAlignment.baseline,
    textBaseline: TextBaseline.alphabetic,
    mainAxisAlignment: MainAxisAlignment.spaceBetween,
    children: [
      Text(label, style: const TextStyle(fontSize: 13, color: SColor.muted)),
      Text(
        value,
        style: TextStyle(
          fontSize: large ? 17 : 14,
          fontWeight: FontWeight.w700,
          color: SColor.text,
        ),
      ),
    ],
  );
}

class _SegmentButton extends StatelessWidget {
  const _SegmentButton({required this.label, required this.selected, required this.onTap});

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => GestureDetector(
    behavior: HitTestBehavior.opaque,
    onTap: onTap,
    child: Container(
      height: SSize.minTouch,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: selected ? SColor.greenTint : SColor.surface,
        borderRadius: BorderRadius.circular(14),
        border: selected ? Border.all(color: SColor.greenBorder) : null,
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 14,
          fontWeight: selected ? FontWeight.w700 : FontWeight.w600,
          color: selected ? SColor.greenText : SColor.muted,
        ),
      ),
    ),
  );
}

/// D2 · 無効化ではなくロック表示。Discovery 自体は常に機能する。
class _LockNotice extends StatelessWidget {
  const _LockNotice({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => GestureDetector(
    behavior: HitTestBehavior.opaque,
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
      decoration: BoxDecoration(
        color: SColor.surface,
        borderRadius: BorderRadius.circular(SRadius.field),
      ),
      child: const Row(
        children: [
          SIcon(SIconData.lock, size: 20, color: SColor.green, strokeWidth: 2),
          SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '5km未満の絞り込みはSession+で利用できます',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: SColor.text),
                ),
                SizedBox(height: 3),
                Text('1 km単位で範囲を指定', style: TextStyle(fontSize: 12, color: SColor.muted)),
              ],
            ),
          ),
          SizedBox(width: 10),
          Text(
            'Session+を見る',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: SColor.green),
          ),
        ],
      ),
    ),
  );
}
