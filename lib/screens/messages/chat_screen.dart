import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/chat.dart';
import '../../models/intent.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/message_bubble.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_photo.dart';
import '../../widgets/s_sheet.dart';
import '../../widgets/s_toast.dart';
import '../../widgets/session_badge.dart';
import '../onboarding/age_verification_screen.dart';
import '../s_scaffold.dart';
import '../safety/safety_sheets.dart';
import 'age_required_sheet.dart';

/// H Chat · 会話が始まったら定型文は消える（初回のみキーボード上に控えめに出す）。
class ChatScreen extends StatefulWidget {
  const ChatScreen({super.key, required this.match});

  final SMatch match;

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final _controller = TextEditingController();
  final _focus = FocusNode();
  final _scroll = ScrollController();
  bool _typing = false;

  static const _shortcuts = ['何時ごろ？', 'どの辺？', '今からでもOK'];

  @override
  void initState() {
    super.initState();
    _controller.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _controller.dispose();
    _focus.dispose();
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _send([String? preset]) async {
    final body = (preset ?? _controller.text).trim();
    if (body.isEmpty) return;
    final state = AppScope.read(context);

    // POST /messages だけが年齢確認を要求する（403 → G2）。
    if (!state.canSendMessage) {
      final verify = await showSSheet<bool>(
        context,
        builder: (_) => AgeRequiredSheet(person: widget.match.person),
      );
      if (verify != true || !mounted) return;
      await Navigator.of(context).push(sUpRoute(const AgeVerificationScreen()));
      if (!mounted || !AppScope.read(context).canSendMessage) return;
    }

    state.sendMessage(widget.match, body);
    _controller.clear();
    setState(() => _typing = true);
    _jumpToEnd();

    // プロトタイプでは 1.4s の入力中インジケータのあとに定型返信。
    await Future<void>.delayed(const Duration(milliseconds: 1400));
    if (!mounted) return;
    setState(() => _typing = false);
    AppScope.read(context).receiveMessage(widget.match, 'いいね、それで！');
    _jumpToEnd();
  }

  void _jumpToEnd() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scroll.hasClients) return;
      _scroll.jumpTo(_scroll.position.maxScrollExtent);
    });
  }

  @override
  Widget build(BuildContext context) {
    final match = widget.match;
    AppScope.of(context); // メッセージ追加で再描画する
    final showShortcuts = match.messages.where((m) => m.mine).isEmpty;

    return SScaffold(
      bottomSafe: false,
      child: Column(
        children: [
          _ChatHeader(match: match, onMenu: _openMenu),
          Expanded(
            child: ListView(
              controller: _scroll,
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
              children: [
                Center(
                  child: Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: Text(
                      '${_hhmm(match.createdAt)} に Session',
                      style: const TextStyle(fontSize: 11, color: SColor.muted2),
                    ),
                  ),
                ),
                for (final m in match.messages) ...[
                  MessageBubble(body: m.body, mine: m.mine, pending: m.pending),
                  const SizedBox(height: 8),
                ],
                if (_typing) const TypingBubble(),
              ],
            ),
          ),
          if (showShortcuts)
            // 初回だけ、キーボードの上に控えめに出す。横に溢れる端末幅ではスクロールさせる。
            SizedBox(
              height: 46,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                children: [
                  for (final s in _shortcuts) ...[
                    GestureDetector(
                      onTap: () => _send(s),
                      child: Container(
                        alignment: Alignment.center,
                        padding: const EdgeInsets.symmetric(horizontal: 13),
                        decoration: BoxDecoration(
                          color: SColor.surface,
                          borderRadius: BorderRadius.circular(SRadius.pill),
                        ),
                        child: Text(
                          s,
                          style: const TextStyle(fontSize: 13, color: SColor.secondary),
                        ),
                      ),
                    ),
                    if (s != _shortcuts.last) const SizedBox(width: 8),
                  ],
                ],
              ),
            ),
          _Composer(controller: _controller, focusNode: _focus, onSend: _send),
        ],
      ),
    );
  }

  Future<void> _openMenu() async {
    final result = await showSSheet<PersonMenuResult>(
      context,
      builder: (_) => PersonMenuSheet(person: widget.match.person, showUnmatch: true),
    );
    if (!mounted || result == null) return;
    switch (result) {
      case PersonMenuResult.profile:
        break;
      case PersonMenuResult.unmatched:
        AppScope.read(context).unmatch(widget.match);
        Navigator.of(context).pop();
        SToast.show(context, 'Session を解除しました');
      case PersonMenuResult.blocked:
        Navigator.of(context).pop();
        SToast.show(context, 'ブロックしました');
      case PersonMenuResult.reported:
        Navigator.of(context).pop();
    }
  }

  static String _hhmm(DateTime t) =>
      '${t.hour.toString().padLeft(2, '0')}:${t.minute.toString().padLeft(2, '0')}';
}

class _ChatHeader extends StatelessWidget {
  const _ChatHeader({required this.match, required this.onMenu});

  final SMatch match;
  final VoidCallback onMenu;

  @override
  Widget build(BuildContext context) => DecoratedBox(
    decoration: const BoxDecoration(
      border: Border(bottom: BorderSide(color: SColor.hairline)),
    ),
    child: Padding(
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 14),
      child: Row(
        children: [
          GestureDetector(
            behavior: HitTestBehavior.opaque,
            onTap: () => Navigator.of(context).maybePop(),
            child: const SizedBox(
              width: 28,
              height: SSize.minTouch,
              child: Center(child: SIcon(SIconData.back, size: 20, strokeWidth: 2)),
            ),
          ),
          ClipOval(
            child: SizedBox(width: 40, height: 40, child: SPhotoView(match.person.mainPhoto)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  match.person.displayName,
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.w700,
                    color: SColor.text,
                  ),
                ),
                const SizedBox(height: 3),
                if (match.active)
                  Row(
                    children: [
                      const SPresenceDot(),
                      const SizedBox(width: 6),
                      Flexible(
                        child: Text(
                          'Active Session · ${match.intent.emoji} ${match.intent.short}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 12, color: SColor.greenText),
                        ),
                      ),
                    ],
                  )
                else
                  Text(
                    '${match.intent.emoji} ${match.intent.short}',
                    style: const TextStyle(fontSize: 12, color: SColor.muted),
                  ),
              ],
            ),
          ),
          GestureDetector(
            behavior: HitTestBehavior.opaque,
            onTap: onMenu,
            child: const SizedBox(
              width: 32,
              height: SSize.minTouch,
              child: Center(
                child: Text(
                  '···',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1,
                    color: SColor.muted2,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    ),
  );
}

class _Composer extends StatelessWidget {
  const _Composer({required this.controller, required this.focusNode, required this.onSend});

  final TextEditingController controller;
  final FocusNode focusNode;
  final VoidCallback onSend;

  @override
  Widget build(BuildContext context) {
    final hasDraft = controller.text.trim().isNotEmpty;
    return Container(
      padding: EdgeInsets.fromLTRB(16, 14, 16, 14 + MediaQuery.viewInsetsOf(context).bottom),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            // 「＋」は写真のみ（OS 純正ピッカー）。
            const SIcon(SIconData.plus, size: 22, color: SColor.muted, strokeWidth: 2),
            const SizedBox(width: 10),
            Expanded(
              child: Container(
                constraints: const BoxConstraints(minHeight: 44),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: SColor.surface,
                  borderRadius: BorderRadius.circular(22),
                ),
                child: Stack(
                  children: [
                    if (!hasDraft)
                      const Text('メッセージ', style: TextStyle(fontSize: 15, color: SColor.muted2)),
                    EditableText(
                      controller: controller,
                      focusNode: focusNode,
                      maxLines: 4,
                      minLines: 1,
                      style: const TextStyle(fontSize: 15, color: SColor.text),
                      cursorColor: SColor.green,
                      backgroundCursorColor: SColor.surface,
                      onSubmitted: (_) => onSend(),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 10),
            GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: hasDraft ? onSend : null,
              child: Container(
                width: 40,
                height: 40,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  // 下書きがあるときだけ緑になる。
                  color: hasDraft ? SColor.green : SColor.surface,
                  shape: BoxShape.circle,
                ),
                child: SIcon(
                  SIconData.send,
                  size: 17,
                  strokeWidth: 2.6,
                  color: hasDraft ? SColor.onGreen : SColor.muted2,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
