import 'package:flutter/widgets.dart';

import '../theme/tokens.dart';

/// 48×29 の自前トグル。Cupertino / Material のものは使わない。
class SToggle extends StatelessWidget {
  const SToggle({super.key, required this.value, required this.onChanged});

  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) => GestureDetector(
    behavior: HitTestBehavior.opaque,
    onTap: () => onChanged(!value),
    child: SizedBox(
      height: SSize.minTouch,
      width: 48,
      child: Center(
        child: AnimatedContainer(
          duration: SMotion.fade,
          width: 48,
          height: 29,
          decoration: BoxDecoration(
            color: value ? SColor.green : SColor.raised,
            borderRadius: BorderRadius.circular(15),
          ),
          child: AnimatedAlign(
            duration: SMotion.fade,
            curve: SMotion.sheetEase,
            alignment: value ? Alignment.centerRight : Alignment.centerLeft,
            child: Padding(
              padding: const EdgeInsets.all(2),
              child: Container(
                width: 25,
                height: 25,
                decoration: const BoxDecoration(color: SColor.bg, shape: BoxShape.circle),
              ),
            ),
          ),
        ),
      ),
    ),
  );
}
