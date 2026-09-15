import 'package:flutter/widgets.dart';

import '../models/distance.dart';
import '../models/intent.dart';
import '../models/user.dart';
import '../theme/tokens.dart';
import 's_photo.dart';
import 'session_badge.dart';

/// A / B / B2 / N1 / N2 / I3 / Q1 で共有するカード。
/// I3（自分から見たプレビュー）も必ずこれを使う — 別実装にしない。
class SwipeCard extends StatelessWidget {
  const SwipeCard({
    super.key,
    required this.person,
    this.photoIndex = 0,
    this.showProgress = true,
    this.onTapPhoto,
    this.onNextPhoto,
    this.onPreviousPhoto,
  });

  final SUser person;
  final int photoIndex;
  final bool showProgress;
  final VoidCallback? onTapPhoto;
  final VoidCallback? onNextPhoto;
  final VoidCallback? onPreviousPhoto;

  @override
  Widget build(BuildContext context) {
    final photo = person.photos[photoIndex.clamp(0, person.photos.length - 1)];
    return ClipRRect(
      borderRadius: BorderRadius.circular(SRadius.photo),
      child: ColoredBox(
        color: const Color(0xFF17191A),
        child: Stack(
          fit: StackFit.expand,
          children: [
            SPhotoView(photo),
            const SPhotoScrim(),
            if (showProgress && person.photos.length > 1)
              Positioned(
                top: 14,
                left: 14,
                right: 14,
                child: _PhotoProgress(count: person.photos.length, index: photoIndex),
              ),
            // 写真の送りは端タップ。常設の説明テキストやボタンは置かない。
            Positioned.fill(
              child: Row(
                children: [
                  Expanded(
                    flex: 1,
                    child: GestureDetector(
                      behavior: HitTestBehavior.translucent,
                      onTap: onPreviousPhoto ?? onTapPhoto,
                    ),
                  ),
                  Expanded(
                    flex: 2,
                    child: GestureDetector(
                      behavior: HitTestBehavior.translucent,
                      onTap: onTapPhoto,
                    ),
                  ),
                  Expanded(
                    flex: 1,
                    child: GestureDetector(
                      behavior: HitTestBehavior.translucent,
                      onTap: onNextPhoto ?? onTapPhoto,
                    ),
                  ),
                ],
              ),
            ),
            Positioned(
              left: 22,
              right: 22,
              bottom: 24,
              child: IgnorePointer(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text.rich(
                      TextSpan(
                        text: person.displayName,
                        children: [
                          TextSpan(
                            text: ' ${person.age}',
                            style: const TextStyle(
                              fontWeight: FontWeight.w400,
                              color: Color(0x9EF4F5F2),
                            ),
                          ),
                        ],
                      ),
                      style: SText.display.copyWith(color: SColor.text),
                    ),
                    const SizedBox(height: 7),
                    SessionBadge(
                      isOn: person.isOn,
                      intent: person.intent,
                      distanceLabel: person.distance.label,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PhotoProgress extends StatelessWidget {
  const _PhotoProgress({required this.count, required this.index});

  final int count;
  final int index;

  @override
  Widget build(BuildContext context) => Row(
    children: [
      for (var i = 0; i < count; i++) ...[
        Expanded(
          child: Container(
            height: 3,
            decoration: BoxDecoration(
              color: i == index ? SColor.text : const Color(0x4DFFFFFF),
              borderRadius: BorderRadius.circular(2),
            ),
          ),
        ),
        if (i != count - 1) const SizedBox(width: 4),
      ],
    ],
  );
}

/// スワイプの向き。
enum SwipeDirection { skip, request }

/// カードのドラッグ。外部パッケージは挙動を合わせにくいので自前で実装する。
/// しきい値は画面幅の30%、離したら 330ms で飛ばす。
class SwipeDeck extends StatefulWidget {
  const SwipeDeck({
    super.key,
    required this.person,
    required this.next,
    required this.onSwipe,
    required this.onTapCard,
    this.canRequest = true,
    this.onRequestBlocked,
  });

  final SUser person;
  final SUser? next;
  final ValueChanged<SwipeDirection> onSwipe;
  final VoidCallback onTapCard;

  /// SESSION OFF・1日の上限に達した場合は右スワイプを成立させない。
  final bool canRequest;
  final VoidCallback? onRequestBlocked;

  @override
  State<SwipeDeck> createState() => _SwipeDeckState();
}

class _SwipeDeckState extends State<SwipeDeck> with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  Offset _drag = Offset.zero;
  Offset _from = Offset.zero;
  Offset _to = Offset.zero;
  SwipeDirection? _flyingTo;
  int _photoIndex = 0;

  @override
  void didUpdateWidget(SwipeDeck oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.person.id != widget.person.id) _photoIndex = 0;
  }

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: SMotion.cardSnap)
      ..addListener(_onTick);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onTick() {
    final t = SMotion.cardEase.transform(_controller.value);
    setState(() => _drag = Offset.lerp(_from, _to, t)!);
    if (_controller.isCompleted) {
      final dir = _flyingTo;
      _flyingTo = null;
      _drag = Offset.zero;
      _controller.value = 0;
      if (dir != null) widget.onSwipe(dir);
    }
  }

  void _animateTo(Offset target, {SwipeDirection? then}) {
    _from = _drag;
    _to = target;
    _flyingTo = then;
    _controller.forward(from: 0);
  }

  void _onEnd(double width) {
    final threshold = width * 0.30;
    if (_drag.dx >= threshold) {
      if (!widget.canRequest) {
        widget.onRequestBlocked?.call();
        _animateTo(Offset.zero);
        return;
      }
      _animateTo(Offset(width * 1.5, _drag.dy), then: SwipeDirection.request);
    } else if (_drag.dx <= -threshold) {
      _animateTo(Offset(-width * 1.5, _drag.dy), then: SwipeDirection.skip);
    } else {
      _animateTo(Offset.zero);
    }
  }

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = constraints.maxWidth;
        final progress = (_drag.dx.abs() / (width * 0.30)).clamp(0.0, 1.0);
        final right = _drag.dx > 0;
        final next = widget.next;

        return GestureDetector(
          behavior: HitTestBehavior.opaque,
          onPanUpdate: (d) {
            if (_controller.isAnimating) return;
            setState(() => _drag += d.delta);
          },
          onPanEnd: (_) => _onEnd(width),
          child: Stack(
            children: [
              if (next != null)
                Positioned.fill(
                  child: Opacity(
                    opacity: 0.6,
                    child: Transform.translate(
                      offset: const Offset(0, 10),
                      child: Transform.scale(
                        scale: 0.95,
                        child: SwipeCard(person: next, showProgress: false),
                      ),
                    ),
                  ),
                ),
              Positioned.fill(
                child: Transform.translate(
                  offset: _drag,
                  child: Transform.rotate(
                    // CSS の rotate(dx/18deg) と同じ傾き。
                    angle: (_drag.dx / 18) * 3.1415926535 / 180,
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        SwipeCard(
                          person: widget.person,
                          photoIndex: _photoIndex,
                          onTapPhoto: widget.onTapCard,
                          onNextPhoto: () => setState(() {
                            if (_photoIndex < widget.person.photos.length - 1) _photoIndex++;
                          }),
                          onPreviousPhoto: () => setState(() {
                            if (_photoIndex > 0) _photoIndex--;
                          }),
                        ),
                        if (progress > 0.02)
                          IgnorePointer(
                            child: _DragFeedback(
                              progress: progress,
                              right: right,
                              intentLabel: widget.person.intent?.label,
                              intentEmoji: widget.person.intent?.emoji,
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _DragFeedback extends StatelessWidget {
  const _DragFeedback({
    required this.progress,
    required this.right,
    this.intentLabel,
    this.intentEmoji,
  });

  final double progress;
  final bool right;
  final String? intentLabel;
  final String? intentEmoji;

  @override
  Widget build(BuildContext context) {
    final label = right
        ? '${intentEmoji ?? '✦'} ${intentLabel == null ? 'Session を送る' : '一緒に${intentLabel!}'}'
        : 'スキップ';
    return Stack(
      fit: StackFit.expand,
      children: [
        if (right)
          DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.centerRight,
                end: Alignment.centerLeft,
                colors: [
                  SColor.green.withValues(alpha: 0.22 * progress),
                  const Color(0x00000000),
                ],
              ),
              borderRadius: BorderRadius.circular(SRadius.photo),
              border: Border.all(color: SColor.green.withValues(alpha: 0.9 * progress), width: 2),
            ),
          ),
        Align(
          alignment: right ? const Alignment(-0.65, -0.72) : const Alignment(0.65, -0.72),
          child: Opacity(
            opacity: progress,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: right ? SColor.green : SColor.surface,
                borderRadius: BorderRadius.circular(SRadius.pill),
              ),
              child: Text(
                label,
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: right ? SColor.onGreen : SColor.text,
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
