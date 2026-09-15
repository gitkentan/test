import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/chat.dart';
import '../../models/intent.dart';
import '../../models/photo.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_photo.dart';
import '../../widgets/session_star.dart';
import '../messages/chat_screen.dart';
import '../s_scaffold.dart';

/// G IT'S A SESSION · コントラスト・写真・タイポで演出する。
/// 紙吹雪や過剰なグローは使わない。写真150ms → タイポ500ms → CTA750ms。
class ItsASessionScreen extends StatefulWidget {
  const ItsASessionScreen({super.key, required this.match});

  final SMatch match;

  @override
  State<ItsASessionScreen> createState() => _ItsASessionScreenState();
}

class _ItsASessionScreenState extends State<ItsASessionScreen> with SingleTickerProviderStateMixin {
  late final AnimationController _c;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: const Duration(milliseconds: 1000))..forward();
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  Animation<double> _at(int startMs) => CurvedAnimation(
    parent: _c,
    curve: Interval(startMs / 1000, (startMs + 250) / 1000, curve: SMotion.sheetEase),
  );

  @override
  Widget build(BuildContext context) {
    final person = widget.match.person;
    final me = AppScope.of(context).me;
    return SScaffold(
      background: SColor.bgDeep,
      child: Column(
        children: [
          Expanded(
            child: Center(
              child: FadeTransition(
                opacity: _at(150),
                child: SizedBox(
                  width: 330,
                  height: 290,
                  child: Stack(
                    children: [
                      Positioned(
                        left: 0,
                        top: 34,
                        child: _MatchCard(photo: me.mainPhoto, angle: -7),
                      ),
                      Positioned(
                        right: 0,
                        top: 0,
                        child: _MatchCard(photo: person.mainPhoto, angle: 7, ringed: true),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          FadeTransition(
            opacity: _at(500),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Column(
                children: [
                  const SessionStar(size: 30),
                  const SizedBox(height: 16),
                  const Text(
                    "IT'S A\nSESSION",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 44,
                      height: 1.05,
                      fontWeight: FontWeight.w700,
                      letterSpacing: -1.32,
                      color: SColor.text,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    '${person.displayName}も今夜、${widget.match.intent.wantSentence}。',
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 15, height: 1.65, color: SColor.muted),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 28),
          FadeTransition(
            opacity: _at(750),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
              child: Column(
                children: [
                  SPrimaryButton(
                    'メッセージを送る',
                    onTap: () => Navigator.of(
                      context,
                    ).pushReplacement(sUpRoute(ChatScreen(match: widget.match))),
                  ),
                  STextButton('スワイプを続ける', onTap: () => Navigator.of(context).pop()),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _MatchCard extends StatelessWidget {
  const _MatchCard({required this.photo, required this.angle, this.ringed = false});

  final SPhoto photo;
  final double angle;
  final bool ringed;

  @override
  Widget build(BuildContext context) => Transform.rotate(
    angle: angle * 3.1415926535 / 180,
    child: Container(
      width: 180,
      height: 240,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(26),
        boxShadow: const [
          BoxShadow(color: Color(0xA6000000), blurRadius: 70, offset: Offset(0, 30)),
        ],
        border: ringed ? Border.all(color: const Color(0x592BD873)) : null,
      ),
      child: ClipRRect(borderRadius: BorderRadius.circular(26), child: SPhotoView(photo)),
    ),
  );
}
