import 'package:flutter/services.dart' show HapticFeedback;
import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/chat.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_sheet.dart';
import '../../widgets/s_toast.dart';
import '../../widgets/session_control_row.dart';
import '../../widgets/swipe_card.dart';
import '../messages/messages_screen.dart';
import '../paywall/daily_limit_sheet.dart';
import '../profile/profile_screen.dart';
import '../s_scaffold.dart';
import 'coach_mark.dart';
import 'discovery_states.dart';
import 'filter_sheet.dart';
import 'intent_sheet.dart';
import 'its_a_session_screen.dart';
import 'profile_detail_sheet.dart';

/// A / B Discovery — 唯一のルート。起動して即ここ。
/// ヘッダーは1行：wordmark / ON·Intent コントロール / Filter · Messages · You。
class DiscoveryScreen extends StatefulWidget {
  const DiscoveryScreen({super.key});

  @override
  State<DiscoveryScreen> createState() => _DiscoveryScreenState();
}

class _DiscoveryScreenState extends State<DiscoveryScreen> {
  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final person = state.current;

    if (!state.coachMarkSeen) {
      return CoachMark(onDone: () => AppScope.read(context).markCoachMarkSeen());
    }

    return SScaffold(
      bottomSafe: false,
      child: Column(
        children: [
          SessionControlRow(
            isOn: state.isOn,
            intent: state.intent,
            avatar: state.me.mainPhoto,
            hasUnread: state.unreadTotal > 0,
            dimmed: state.status == DiscoveryStatus.offline,
            onTapState: _openIntentSheet,
            onTapFilter: _openFilterSheet,
            onTapMessages: () => Navigator.of(context).push(sUpRoute(const MessagesScreen())),
            onTapProfile: () => Navigator.of(context).push(sUpRoute(const ProfileScreen())),
          ),
          // 人数は出さない。定性的な一言のみ。
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 10, SSpace.x5, 0),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Text(_hint(state), style: const TextStyle(fontSize: 12, color: SColor.muted2)),
            ),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(
                SSpace.photoInset,
                SSpace.x2,
                SSpace.photoInset,
                0,
              ),
              child: switch (state.status) {
                DiscoveryStatus.loading => const DiscoverySkeleton(),
                DiscoveryStatus.offline => const OfflinePanel(),
                DiscoveryStatus.empty => DiscoveryEmpty(
                  onNotify: () => SToast.show(context, '動きがあったら通知します'),
                  onWiden: () {
                    AppScope.read(context)
                      ..setDistance(10)
                      ..resetQueue();
                    SToast.show(context, '範囲を 10km に広げました');
                  },
                ),
                DiscoveryStatus.ready =>
                  person == null
                      ? DiscoveryEmpty(
                          onNotify: () => SToast.show(context, '動きがあったら通知します'),
                          onWiden: () => AppScope.read(context).resetQueue(),
                        )
                      : SwipeDeck(
                          key: ValueKey(person.id),
                          person: person,
                          next: state.next,
                          canRequest: state.canRequest,
                          onRequestBlocked: _onRequestBlocked,
                          onTapCard: () => _openProfileDetail(),
                          onSwipe: _onSwipe,
                        ),
              },
            ),
          ),
          const SizedBox(height: 46),
        ],
      ),
    );
  }

  String _hint(AppState state) {
    if (state.status == DiscoveryStatus.empty) return '少し範囲を広げています';
    if (state.status == DiscoveryStatus.offline) return '接続が戻ると自動で再開します';
    return state.isOn ? '近くで数人がSession中' : '近くでSession中の人がいます';
  }

  void _onSwipe(SwipeDirection direction) {
    final state = AppScope.read(context);
    if (direction == SwipeDirection.skip) {
      state.skip();
      return;
    }
    final match = state.sendRequest();
    HapticFeedback.mediumImpact();
    if (match != null) {
      Navigator.of(context).push(sFadeRoute(ItsASessionScreen(match: match)));
    } else {
      SToast.show(context, 'Session を送りました');
    }
  }

  void _onRequestBlocked() {
    final state = AppScope.read(context);
    if (state.dailyLimitReached) {
      showSSheet<void>(context, builder: (_) => const DailyLimitSheet());
      return;
    }
    // SESSION OFF のままでは Request できない。閲覧は自由。
    SToast.show(context, 'SESSION を ON にすると送れます');
    _openIntentSheet();
  }

  void _openIntentSheet() => showSSheet<void>(context, builder: (_) => const IntentSheet());

  void _openFilterSheet() => showSSheet<void>(context, builder: (_) => const FilterSheet());

  Future<void> _openProfileDetail() async {
    final state = AppScope.read(context);
    final person = state.current;
    if (person == null) return;
    final result = await showSSheet<ProfileDetailResult>(
      context,
      builder: (_) => ProfileDetailSheet(person: person),
    );
    if (!mounted || result == null) return;
    switch (result) {
      case ProfileDetailResult.request:
        if (!state.canRequest) {
          _onRequestBlocked();
          return;
        }
        final SMatch? match = state.sendRequest();
        HapticFeedback.mediumImpact();
        if (!mounted) return;
        if (match != null) {
          Navigator.of(context).push(sFadeRoute(ItsASessionScreen(match: match)));
        } else {
          SToast.show(context, 'Session を送りました');
        }
      case ProfileDetailResult.skip:
        state.skip();
      case ProfileDetailResult.blocked:
        SToast.show(context, 'ブロックしました');
    }
  }
}
