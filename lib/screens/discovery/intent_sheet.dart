import 'package:flutter/services.dart' show HapticFeedback;
import 'package:flutter/widgets.dart';

import '../../models/intent.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/intent_pill.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_sheet.dart';
import '../../widgets/s_toast.dart';

/// C Intent Sheet · 5種・単一選択。選択すると SESSION ON。
/// 自動失効はサーバー側 TTL で、タイマーは表示しない。
class IntentSheet extends StatefulWidget {
  const IntentSheet({super.key});

  @override
  State<IntentSheet> createState() => _IntentSheetState();
}

class _IntentSheetState extends State<IntentSheet> {
  SIntent? _selected;
  bool _initialized = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_initialized) return;
    _selected = AppScope.read(context).intent;
    _initialized = true;
  }

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final s = _selected;
    return SBottomSheet(
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              '今、何したい？',
              style: TextStyle(
                fontSize: 25,
                fontWeight: FontWeight.w700,
                letterSpacing: -0.5,
                color: SColor.text,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              'ひとつ選ぶと SESSION ON になります',
              style: TextStyle(fontSize: 13, color: SColor.muted),
            ),
            const SizedBox(height: 20),
            IntentPillList(
              selected: s,
              onSelect: (i) {
                HapticFeedback.lightImpact();
                setState(() => _selected = i);
              },
            ),
            const SizedBox(height: 20),
            SPrimaryButton(
              'SESSION ON にする',
              enabled: s != null,
              onTap: () {
                if (s == null) return;
                AppScope.read(context).setIntent(s);
                Navigator.of(context).pop();
                SToast.show(context, 'SESSION を「${s.label}」で ON にしました');
              },
            ),
            const SizedBox(height: 4),
            STextButton(
              state.isOn ? 'OFFにする' : 'OFFのままにする',
              onTap: () {
                final wasOn = AppScope.read(context).isOn;
                if (wasOn) AppScope.read(context).turnOff();
                Navigator.of(context).pop();
                if (wasOn) SToast.show(context, 'SESSION を OFF にしました');
              },
            ),
          ],
        ),
      ),
    );
  }
}
