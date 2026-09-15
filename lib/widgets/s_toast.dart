import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// 上から 250ms でスライドし、1.8s で自動的に消える。
class SToast {
  static OverlayEntry? _entry;

  static void show(BuildContext context, String message) {
    _entry?.remove();
    final overlay = Overlay.of(context, rootOverlay: true);
    final entry = OverlayEntry(builder: (context) => _ToastView(message: message));
    _entry = entry;
    overlay.insert(entry);
    Future<void>.delayed(const Duration(milliseconds: 1800)).then((_) {
      if (_entry == entry) {
        entry.remove();
        _entry = null;
      }
    });
  }
}

class _ToastView extends StatefulWidget {
  const _ToastView({required this.message});

  final String message;

  @override
  State<_ToastView> createState() => _ToastViewState();
}

class _ToastViewState extends State<_ToastView> with SingleTickerProviderStateMixin {
  late final AnimationController _c;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: const Duration(milliseconds: 250))..forward();
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final top = MediaQuery.paddingOf(context).top;
    return Positioned(
      top: top + 8,
      left: SSpace.x5,
      right: SSpace.x5,
      child: FadeTransition(
        opacity: _c,
        child: SlideTransition(
          position: Tween(
            begin: const Offset(0, -0.4),
            end: Offset.zero,
          ).animate(CurvedAnimation(parent: _c, curve: SMotion.sheetEase)),
          child: Center(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
              decoration: BoxDecoration(
                color: SColor.raised,
                borderRadius: BorderRadius.circular(SRadius.button),
                boxShadow: const [
                  BoxShadow(color: Color(0x80000000), blurRadius: 30, offset: Offset(0, 12)),
                ],
              ),
              child: Text(
                widget.message,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: SColor.text,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
