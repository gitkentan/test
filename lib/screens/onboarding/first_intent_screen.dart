import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/intent.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/intent_pill.dart';
import '../../widgets/s_buttons.dart';
import '../s_scaffold.dart';
import 'location_screen.dart';

/// J9 First intent · C と同じコンポーネント・単一選択 → J10。
class FirstIntentScreen extends StatefulWidget {
  const FirstIntentScreen({super.key});

  @override
  State<FirstIntentScreen> createState() => _FirstIntentScreenState();
}

class _FirstIntentScreenState extends State<FirstIntentScreen> {
  SIntent? _selected = SIntent.drinks;

  @override
  Widget build(BuildContext context) {
    final s = _selected;
    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(onBack: () => Navigator.of(context).maybePop(), progress: 0.88),
          const Padding(
            padding: EdgeInsets.fromLTRB(24, 30, 24, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '今、何したい？',
                  style: TextStyle(
                    fontSize: 30,
                    height: 1.2,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.75,
                    color: SColor.text,
                  ),
                ),
                SizedBox(height: 8),
                Text(
                  'ひとつ選ぶと SESSION ON になります。いつでも変更・OFFにできます。',
                  style: TextStyle(fontSize: 15, height: 1.6, color: SColor.muted),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 24, SSpace.x5, 0),
            child: IntentPillList(selected: s, onSelect: (i) => setState(() => _selected = i)),
          ),
          const Spacer(),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 24),
            child: Column(
              children: [
                SPrimaryButton(
                  s == null ? 'SESSION ON にする' : '${s.emoji} ${s.label} で SESSION ON',
                  enabled: s != null,
                  onTap: () {
                    if (s != null) AppScope.read(context).setIntent(s);
                    Navigator.of(context).push(sUpRoute(const LocationScreen()));
                  },
                ),
                STextButton(
                  'OFFのまま始める',
                  onTap: () => Navigator.of(context).push(sUpRoute(const LocationScreen())),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
