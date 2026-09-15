import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../s_scaffold.dart';
import 'photos_screen.dart';

/// O Onboarding · 名前入力。変更不可であることを入力前に明示する
/// （確認ダイアログは追加しない）。
class NameScreen extends StatefulWidget {
  const NameScreen({super.key});

  @override
  State<NameScreen> createState() => _NameScreenState();
}

class _NameScreenState extends State<NameScreen> {
  final _controller = TextEditingController();
  final _focus = FocusNode();

  @override
  void initState() {
    super.initState();
    _controller.addListener(() => setState(() {}));
    WidgetsBinding.instance.addPostFrameCallback((_) => _focus.requestFocus());
  }

  @override
  void dispose() {
    _controller.dispose();
    _focus.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final valid = _controller.text.trim().isNotEmpty;
    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(onBack: () => Navigator.of(context).maybePop(), progress: 0.5),
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 34, 24, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'なんて呼べばいい？',
                  style: TextStyle(
                    fontSize: 28,
                    height: 1.25,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.56,
                    color: SColor.text,
                  ),
                ),
                const SizedBox(height: 28),
                Container(
                  height: 62,
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  alignment: Alignment.centerLeft,
                  decoration: BoxDecoration(
                    color: SColor.surface,
                    borderRadius: BorderRadius.circular(SRadius.field),
                    border: Border.all(color: const Color(0x802BD873)),
                  ),
                  child: EditableText(
                    controller: _controller,
                    focusNode: _focus,
                    style: const TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.w700,
                      color: SColor.text,
                    ),
                    cursorColor: SColor.green,
                    backgroundCursorColor: SColor.surface,
                    maxLines: 1,
                  ),
                ),
                const SizedBox(height: 10),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 4),
                  child: Text(
                    'この名前は登録後に変更できません',
                    style: TextStyle(fontSize: 13, color: SColor.muted),
                  ),
                ),
              ],
            ),
          ),
          const Spacer(),
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 0, 24, 20),
            child: SPrimaryButton(
              '次へ',
              enabled: valid,
              onTap: () => Navigator.of(context).push(sUpRoute(const PhotosScreen())),
            ),
          ),
        ],
      ),
    );
  }
}
