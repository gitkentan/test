import 'package:flutter/material.dart' show showModalBottomSheet;
import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// 上2角 30 のボトムシート。ハンドル付き、下スワイプと背景タップで閉じる。
/// 二次的な画面はすべてこれ（専用ページは作らない）。
class SBottomSheet extends StatelessWidget {
  const SBottomSheet({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.fromLTRB(24, 14, 24, 40),
    this.background = SColor.sheet,
  });

  final Widget child;
  final EdgeInsets padding;
  final Color background;

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.viewInsetsOf(context).bottom;
    final safeBottom = MediaQuery.paddingOf(context).bottom;
    return Padding(
      padding: EdgeInsets.only(bottom: bottomInset),
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: background,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(SRadius.sheet)),
        ),
        child: SafeArea(
          top: false,
          bottom: false,
          child: Padding(
            padding: padding.copyWith(
              bottom: padding.bottom + (safeBottom > 0 ? safeBottom - 20 : 0).clamp(0, 24),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Center(child: SSheetHandle()),
                const SizedBox(height: 20),
                Flexible(child: child),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class SSheetHandle extends StatelessWidget {
  const SSheetHandle({super.key});

  @override
  Widget build(BuildContext context) => Container(
    width: 36,
    height: 4,
    decoration: BoxDecoration(
      color: const Color(0x33FFFFFF),
      borderRadius: BorderRadius.circular(2),
    ),
  );
}

/// showModalBottomSheet の共通設定（320ms / cubic-bezier(.2,.9,.3,1) / scrim .62）。
Future<T?> showSSheet<T>(
  BuildContext context, {
  required WidgetBuilder builder,
  bool dismissible = true,
}) {
  return showModalBottomSheet<T>(
    context: context,
    isScrollControlled: true,
    isDismissible: dismissible,
    enableDrag: dismissible,
    useRootNavigator: true,
    backgroundColor: const Color(0x00000000),
    barrierColor: SColor.scrim,
    transitionAnimationController: null,
    builder: (context) => PopScope(canPop: dismissible, child: builder(context)),
  );
}
