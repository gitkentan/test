import 'package:flutter/widgets.dart';

import '../../models/interest.dart';
import '../../models/user.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_photo.dart';
import '../../widgets/s_sheet.dart';
import '../../widgets/s_toast.dart';
import '../onboarding/bio_screen.dart';
import '../s_scaffold.dart';

/// I2 プロフィール編集 · 名前は読み取り専用（鉛筆や変更ボタンは置かない）。
/// 自己紹介は200文字上限・ライブカウンタ・超過時はインライン警告して保存を無効にする。
class ProfileEditScreen extends StatefulWidget {
  const ProfileEditScreen({super.key});

  @override
  State<ProfileEditScreen> createState() => _ProfileEditScreenState();
}

class _ProfileEditScreenState extends State<ProfileEditScreen> {
  late final TextEditingController _bio;
  final _focus = FocusNode();
  late List<String> _interests;

  @override
  void initState() {
    super.initState();
    final me = AppScope.read(context).me;
    _bio = TextEditingController(text: me.bio)..addListener(() => setState(() {}));
    _interests = List.of(me.interests);
  }

  @override
  void dispose() {
    _bio.dispose();
    _focus.dispose();
    super.dispose();
  }

  void _toggle(String id) {
    setState(() {
      if (_interests.contains(id)) {
        _interests.remove(id);
      } else if (_interests.length < InterestMaster.maxSelectable) {
        _interests.add(id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final me = AppScope.of(context).me;
    final length = _bio.text.characters.length;
    final over = length > SUser.bioMaxLength;

    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 16, SSpace.x5, 0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                GestureDetector(
                  onTap: () => Navigator.of(context).maybePop(),
                  child: const Text('キャンセル', style: TextStyle(fontSize: 15, color: SColor.muted)),
                ),
                const Text(
                  'プロフィール編集',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: SColor.text),
                ),
                GestureDetector(
                  onTap: over
                      ? null
                      : () {
                          AppScope.read(context)
                            ..updateBio(_bio.text)
                            ..updateInterests(_interests);
                          Navigator.of(context).pop();
                          SToast.show(context, 'プロフィールを保存しました');
                        },
                  child: Text(
                    over ? '保存できません' : '保存',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: over ? SColor.disabled : SColor.green,
                    ),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(SSpace.x5, 22, SSpace.x5, 24),
              children: [
                Row(
                  children: [
                    ClipOval(
                      child: SizedBox(width: 56, height: 56, child: SPhotoView(me.mainPhoto)),
                    ),
                    const SizedBox(width: 14),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text.rich(
                          TextSpan(
                            text: me.displayName,
                            children: [
                              TextSpan(
                                text: ' ${me.age}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.w400,
                                  color: Color(0x9EF4F5F2),
                                ),
                              ),
                            ],
                          ),
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            letterSpacing: -0.4,
                            color: SColor.text,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          '名前は登録後に変更できません',
                          style: TextStyle(fontSize: 12, color: SColor.muted2),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 22),
                const Text(
                  '自己紹介',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: SColor.text),
                ),
                const SizedBox(height: 10),
                const Text('200文字以内で入力してください', style: TextStyle(fontSize: 13, color: SColor.muted)),
                const SizedBox(height: 10),
                BioField(controller: _bio, focusNode: _focus, over: over),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      over ? '200文字以内で入力してください' : '',
                      style: const TextStyle(fontSize: 13, color: SColor.danger),
                    ),
                    Text(
                      '$length / ${SUser.bioMaxLength}',
                      style: TextStyle(fontSize: 13, color: over ? SColor.danger : SColor.muted),
                    ),
                  ],
                ),
                const SizedBox(height: 22),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      '好きなこと',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        color: SColor.text,
                      ),
                    ),
                    GestureDetector(
                      onTap: () => showSSheet<void>(
                        context,
                        builder: (_) =>
                            InterestPickerSheet(selected: _interests, onToggle: _toggle),
                      ),
                      child: const Text(
                        '＋ 追加',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: SColor.green,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    for (final id in _interests)
                      InterestChip(
                        label: InterestMaster.labelOf(id),
                        selected: true,
                        onTap: () => _toggle(id),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
