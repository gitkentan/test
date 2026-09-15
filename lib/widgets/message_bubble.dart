import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// H のバブル。自分は緑・radius 20/20/6/20、相手は surface・左下 6。
class MessageBubble extends StatelessWidget {
  const MessageBubble({super.key, required this.body, required this.mine, this.pending = false});

  final String body;
  final bool mine;
  final bool pending;

  @override
  Widget build(BuildContext context) => Align(
    alignment: mine ? Alignment.centerRight : Alignment.centerLeft,
    child: Opacity(
      opacity: pending ? 0.55 : 1,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: MediaQuery.sizeOf(context).width * 0.78),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 11),
          decoration: BoxDecoration(
            color: mine ? SColor.green : SColor.surface,
            borderRadius: BorderRadius.only(
              topLeft: const Radius.circular(20),
              topRight: const Radius.circular(20),
              bottomLeft: Radius.circular(mine ? 20 : 6),
              bottomRight: Radius.circular(mine ? 6 : 20),
            ),
          ),
          child: Text(
            body,
            style: TextStyle(
              fontSize: 15,
              height: 1.45,
              color: mine ? SColor.onGreen : SColor.text,
            ),
          ),
        ),
      ),
    ),
  );
}

/// 相手の入力中（•••）。送信直後に 1.4s だけ出す。
class TypingBubble extends StatefulWidget {
  const TypingBubble({super.key});

  @override
  State<TypingBubble> createState() => _TypingBubbleState();
}

class _TypingBubbleState extends State<TypingBubble> with SingleTickerProviderStateMixin {
  late final AnimationController _c;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: const Duration(milliseconds: 1200))..repeat();
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Align(
    alignment: Alignment.centerLeft,
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: const BoxDecoration(
        color: SColor.surface,
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(20),
          topRight: Radius.circular(20),
          bottomLeft: Radius.circular(6),
          bottomRight: Radius.circular(20),
        ),
      ),
      child: AnimatedBuilder(
        animation: _c,
        builder: (context, _) => Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            for (var i = 0; i < 3; i++) ...[
              Opacity(
                opacity: 0.35 + 0.65 * _pulse(i),
                child: Container(
                  width: 6,
                  height: 6,
                  decoration: const BoxDecoration(color: SColor.muted, shape: BoxShape.circle),
                ),
              ),
              if (i != 2) const SizedBox(width: 5),
            ],
          ],
        ),
      ),
    ),
  );

  double _pulse(int i) {
    final t = (_c.value + i * 0.2) % 1.0;
    return t < 0.5 ? t * 2 : (1 - t) * 2;
  }
}
