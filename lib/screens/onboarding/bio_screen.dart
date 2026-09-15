import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/interest.dart';
import '../../models/user.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_sheet.dart';
import '../s_scaffold.dart';
import 'first_intent_screen.dart';

/// J8 自己紹介（200文字上限・ライブカウンタ）＋興味・関心。
/// 固定マスター24項目から最大5つ。自由入力は不可。
class BioScreen extends StatefulWidget {
  const BioScreen({super.key});

  @override
  State<BioScreen> createState() => _BioScreenState();
}

class _BioScreenState extends State<BioScreen> {
  final _controller = TextEditingController();
  final _focus = FocusNode();
  final List<String> _selected = [];

  @override
  void initState() {
    super.initState();
    _controller.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _controller.dispose();
    _focus.dispose();
    super.dispose();
  }

  void _toggle(String id) {
    setState(() {
      if (_selected.contains(id)) {
        _selected.remove(id);
      } else if (_selected.length < InterestMaster.maxSelectable) {
        _selected.add(id);
      }
    });
  }

  void _next() {
    final state = AppScope.read(context);
    state.updateBio(_controller.text);
    state.updateInterests(_selected);
    Navigator.of(context).push(sUpRoute(const FirstIntentScreen()));
  }

  @override
  Widget build(BuildContext context) {
    final length = _controller.text.characters.length;
    final over = length > SUser.bioMaxLength;
    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(onBack: () => Navigator.of(context).maybePop(), progress: 0.75),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(24, 28, 24, 24),
              children: [
                const Text(
                  '自己紹介',
                  style: TextStyle(
                    fontSize: 28,
                    height: 1.25,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.56,
                    color: SColor.text,
                  ),
                ),
                const SizedBox(height: 10),
                const Text('200文字以内で入力してください', style: TextStyle(fontSize: 13, color: SColor.muted)),
                const SizedBox(height: 12),
                BioField(controller: _controller, focusNode: _focus, over: over),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      over ? '200文字以内で入力してください' : '例：「金曜はだいたい飲んでる」',
                      style: TextStyle(fontSize: 13, color: over ? SColor.danger : SColor.muted),
                    ),
                    Text(
                      '$length / ${SUser.bioMaxLength}',
                      style: TextStyle(fontSize: 13, color: over ? SColor.danger : SColor.muted),
                    ),
                  ],
                ),
                const SizedBox(height: 22),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.baseline,
                  textBaseline: TextBaseline.alphabetic,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      '興味・関心',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        color: SColor.text,
                      ),
                    ),
                    Text(
                      '${_selected.length} / ${InterestMaster.maxSelectable}',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: SColor.green,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 11),
                // 先頭2カテゴリだけ出し、残りは「すべて見る」でピッカーシート。
                for (final c in [InterestCategory.nightlifeFood, InterestCategory.culture]) ...[
                  InterestGroup(category: c, selected: _selected, onToggle: _toggle),
                  const SizedBox(height: 7),
                ],
                GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: () => showSSheet<void>(
                    context,
                    builder: (_) => InterestPickerSheet(
                      selected: _selected,
                      onToggle: (id) {
                        _toggle(id);
                      },
                    ),
                  ),
                  child: Container(
                    height: 46,
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                    decoration: BoxDecoration(
                      color: SColor.surface,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Row(
                      children: [
                        Expanded(
                          child: Text.rich(
                            TextSpan(
                              text: 'すべて見る',
                              children: [
                                TextSpan(
                                  text: ' · 4カテゴリ 24項目',
                                  style: TextStyle(color: SColor.muted2),
                                ),
                              ],
                            ),
                            style: TextStyle(fontSize: 14, color: SColor.secondary),
                          ),
                        ),
                        Text('›', style: TextStyle(fontSize: 16, color: SColor.muted)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 20),
            child: Column(
              children: [
                SPrimaryButton('次へ', enabled: !over, onTap: _next),
                STextButton('あとで書く', onTap: _next),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// J8 / I2 で共通の自己紹介フィールド。超過時は枠を danger にして保存を止める。
class BioField extends StatelessWidget {
  const BioField({
    super.key,
    required this.controller,
    required this.focusNode,
    required this.over,
    this.minHeight = 150,
  });

  final TextEditingController controller;
  final FocusNode focusNode;
  final bool over;
  final double minHeight;

  @override
  Widget build(BuildContext context) => Container(
    constraints: BoxConstraints(minHeight: minHeight),
    padding: const EdgeInsets.fromLTRB(18, 16, 18, 16),
    decoration: BoxDecoration(
      color: SColor.surface,
      borderRadius: BorderRadius.circular(SRadius.field),
      border: Border.all(color: over ? SColor.danger : const Color(0x802BD873)),
    ),
    child: EditableText(
      controller: controller,
      focusNode: focusNode,
      maxLines: null,
      style: const TextStyle(fontSize: 16, height: 1.7, color: SColor.text),
      cursorColor: SColor.green,
      backgroundCursorColor: SColor.surface,
    ),
  );
}

class InterestGroup extends StatelessWidget {
  const InterestGroup({
    super.key,
    required this.category,
    required this.selected,
    required this.onToggle,
  });

  final InterestCategory category;
  final List<String> selected;
  final ValueChanged<String> onToggle;

  @override
  Widget build(BuildContext context) {
    final full = selected.length >= InterestMaster.maxSelectable;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          category.label,
          style: const TextStyle(
            fontSize: 11,
            letterSpacing: 0.66,
            fontWeight: FontWeight.w600,
            color: SColor.muted2,
          ),
        ),
        const SizedBox(height: 7),
        Wrap(
          spacing: 6,
          runSpacing: 6,
          children: [
            for (final i in InterestMaster.byCategory(category))
              InterestChip(
                label: i.labelJa,
                selected: selected.contains(i.id),
                // 5つ選択済みで6つ目をタップしたら未選択項目は無効化。
                disabled: full && !selected.contains(i.id),
                onTap: () => onToggle(i.id),
              ),
          ],
        ),
        const SizedBox(height: 8),
      ],
    );
  }
}

class InterestChip extends StatelessWidget {
  const InterestChip({
    super.key,
    required this.label,
    required this.selected,
    required this.onTap,
    this.disabled = false,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;
  final bool disabled;

  @override
  Widget build(BuildContext context) => GestureDetector(
    behavior: HitTestBehavior.opaque,
    onTap: disabled ? null : onTap,
    child: Opacity(
      opacity: disabled ? 0.4 : 1,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: selected ? SColor.greenTint : SColor.surface,
          borderRadius: BorderRadius.circular(11),
          border: selected ? Border.all(color: SColor.greenBorder) : null,
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
            color: selected ? SColor.greenText : SColor.secondary,
          ),
        ),
      ),
    ),
  );
}

/// 4カテゴリ全24項目のピッカー。
class InterestPickerSheet extends StatefulWidget {
  const InterestPickerSheet({super.key, required this.selected, required this.onToggle});

  final List<String> selected;
  final ValueChanged<String> onToggle;

  @override
  State<InterestPickerSheet> createState() => _InterestPickerSheetState();
}

class _InterestPickerSheetState extends State<InterestPickerSheet> {
  @override
  Widget build(BuildContext context) => SBottomSheet(
    child: SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                '興味・関心',
                style: TextStyle(
                  fontSize: 25,
                  fontWeight: FontWeight.w700,
                  letterSpacing: -0.5,
                  color: SColor.text,
                ),
              ),
              Text(
                '${widget.selected.length} / ${InterestMaster.maxSelectable}',
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: SColor.green,
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),
          for (final c in InterestCategory.values)
            InterestGroup(
              category: c,
              selected: widget.selected,
              onToggle: (id) {
                widget.onToggle(id);
                setState(() {});
              },
            ),
          const SizedBox(height: 10),
          SPrimaryButton('決定', onTap: () => Navigator.of(context).pop()),
        ],
      ),
    ),
  );
}
