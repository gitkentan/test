import 'dart:ui' show ImageFilter;

import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/chat.dart';
import '../../models/intent.dart';
import '../../state/app_state.dart';
import '../../state/mock_data.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_photo.dart';
import '../../widgets/session_badge.dart';
import '../paywall/paywall_screen.dart';
import '../s_scaffold.dart';
import 'chat_screen.dart';

/// F Messages · People タブを廃止し、興味あり＋課金導線を先頭に統合。
/// Online 表示は無し。緑ドットは Active Session のみ。
class MessagesScreen extends StatelessWidget {
  const MessagesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final active = state.activeSessions;
    final recent = state.recentMessages;
    final empty = active.isEmpty && recent.isEmpty;

    return SScaffold(
      bottomSafe: false,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 16, SSpace.x5, 0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'メッセージ',
                  style: TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.65,
                    color: SColor.text,
                  ),
                ),
                SCircleButton(
                  onTap: () => Navigator.of(context).maybePop(),
                  child: const SIcon(SIconData.close, size: 15, strokeWidth: 2),
                ),
              ],
            ),
          ),
          Expanded(
            child: empty
                ? const _MessagesEmpty()
                : ListView(
                    padding: const EdgeInsets.only(bottom: 30),
                    children: [
                      // 「興味あり」が0件のときはブロックごと出さない。
                      if (state.interestedCount > 0 && !state.isPlus)
                        _InterestedBlock(count: state.interestedCount),
                      if (active.isNotEmpty) ...[
                        const _SectionLabel('ACTIVE SESSIONS'),
                        for (final m in active) _MatchRow(match: m),
                      ],
                      if (recent.isNotEmpty) ...[
                        const _SectionLabel('最近のメッセージ'),
                        for (final m in recent) _MatchRow(match: m, faded: true),
                      ],
                    ],
                  ),
          ),
        ],
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel(this.label);

  final String label;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.fromLTRB(SSpace.x5, 26, SSpace.x5, 10),
    child: Text(
      label,
      style: const TextStyle(
        fontSize: 12,
        letterSpacing: 0.72,
        fontWeight: FontWeight.w600,
        color: SColor.muted2,
      ),
    ),
  );
}

/// 無料は写真をぼかし、Intent と経過時間だけ読ませる。人数は概数。
class _InterestedBlock extends StatelessWidget {
  const _InterestedBlock({required this.count});

  final int count;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.fromLTRB(SSpace.x5, 26, SSpace.x5, 0),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.baseline,
          textBaseline: TextBaseline.alphabetic,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'あなたに興味あり',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: SColor.text),
            ),
            Text('$count', style: const TextStyle(fontSize: 14, color: SColor.muted)),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 132,
          child: Row(
            children: [
              for (var i = 0; i < 3; i++) ...[
                Expanded(
                  child: _InterestedTile(index: i, overflowCount: i == 2 ? count - 2 : null),
                ),
                if (i != 2) const SizedBox(width: 9),
              ],
            ],
          ),
        ),
        const SizedBox(height: 12),
        SPrimaryButton(
          '誰か見る · Session+',
          height: 48,
          onTap: () => Navigator.of(context).push(sUpRoute(const PaywallScreen())),
        ),
      ],
    ),
  );
}

class _InterestedTile extends StatelessWidget {
  const _InterestedTile({required this.index, this.overflowCount});

  final int index;
  final int? overflowCount;

  static const _gradients = [MockGradients.warmGrey, MockGradients.clay, MockGradients.moss];

  @override
  Widget build(BuildContext context) {
    final over = overflowCount;
    final intent = index < MockData.interestedIntents.length
        ? MockData.interestedIntents[index]
        : SIntent.cafe;
    return ClipRRect(
      borderRadius: BorderRadius.circular(SRadius.field),
      child: Stack(
        fit: StackFit.expand,
        children: [
          DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: const Alignment(-0.6, -1),
                end: const Alignment(0.4, 1),
                colors: _gradients[index % _gradients.length],
              ),
            ),
          ),
          // ぼかし＋減光。名前は出さない（Session+ で外れる）。
          const _BlurVeil(sigma: 14),
          if (over != null)
            Center(
              child: Text(
                '+$over',
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: SColor.text,
                ),
              ),
            )
          else
            Positioned(
              left: 10,
              bottom: 10,
              child: Text(
                '${intent.emoji} ${intent.short}',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: SColor.text2,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _MatchRow extends StatelessWidget {
  const _MatchRow({required this.match, this.faded = false});

  final SMatch match;
  final bool faded;

  @override
  Widget build(BuildContext context) {
    final last = match.last;
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: () {
        AppScope.read(context).markRead(match);
        Navigator.of(context).push(sUpRoute(ChatScreen(match: match)));
      },
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: SSpace.x5, vertical: 11),
        child: Row(
          children: [
            Opacity(
              opacity: faded ? 0.6 : 1,
              child: ClipOval(
                child: SizedBox(width: 52, height: 52, child: SPhotoView(match.person.mainPhoto)),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Flexible(
                        child: Text(
                          match.person.displayName,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: faded ? FontWeight.w600 : FontWeight.w700,
                            color: faded ? SColor.secondary : SColor.text,
                          ),
                        ),
                      ),
                      if (match.active) ...[
                        const SizedBox(width: 7),
                        const SPresenceDot(),
                        const SizedBox(width: 7),
                        Flexible(
                          child: Text(
                            '${match.intent.emoji} ${match.intent.short}',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 12, color: SColor.greenText),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    last?.body ?? '',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(fontSize: 14, color: faded ? SColor.muted2 : SColor.secondary),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(_elapsed(match), style: const TextStyle(fontSize: 12, color: SColor.muted2)),
                if (match.unread > 0) ...[
                  const SizedBox(height: 5),
                  Container(
                    constraints: const BoxConstraints(minWidth: 19),
                    height: 19,
                    alignment: Alignment.center,
                    padding: const EdgeInsets.symmetric(horizontal: 5),
                    decoration: BoxDecoration(
                      color: SColor.green,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '${match.unread}',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: SColor.onGreen,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _elapsed(SMatch match) {
    final at = match.last?.createdAt ?? match.createdAt;
    final d = DateTime.now().difference(at);
    if (d.inMinutes < 60) return '${d.inMinutes}分';
    if (d.inHours < 24) return '${d.inHours}時間';
    const days = ['月', '火', '水', '木', '金', '土', '日'];
    return days[at.weekday - 1];
  }
}

/// N5 Messages empty
class _MessagesEmpty extends StatelessWidget {
  const _MessagesEmpty();

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 34),
    child: Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Text(
          'まだSessionはありません。',
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.44,
            color: SColor.text,
          ),
        ),
        const SizedBox(height: 16),
        const Text(
          'お互いに Session を送るとここに表示されます。スワイプして始めよう。',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 14, height: 1.7, color: SColor.muted),
        ),
        const SizedBox(height: 22),
        SizedBox(
          width: 200,
          child: SSecondaryButton(
            'スワイプに戻る',
            height: 50,
            onTap: () => Navigator.of(context).maybePop(),
          ),
        ),
      ],
    ),
  );
}

/// 興味ありタイルのぼかし。Session+ を買うと 500ms かけて外れる。
class _BlurVeil extends StatelessWidget {
  const _BlurVeil({required this.sigma});

  final double sigma;

  @override
  Widget build(BuildContext context) => BackdropFilter(
    filter: ImageFilter.blur(sigmaX: sigma, sigmaY: sigma),
    child: const ColoredBox(color: Color(0x4D0A0B0A)),
  );
}
