import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/interest.dart';
import '../../models/photo.dart';
import '../../models/user.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_photo.dart';
import '../../widgets/session_badge.dart';
import '../onboarding/age_verification_screen.dart';
import '../s_scaffold.dart';
import '../settings/settings_screen.dart';
import 'profile_edit_screen.dart';
import 'profile_preview_screen.dart';

/// I プロフィール · 未確認時のみ最上部に年齢確認バナー。
/// ⚙ → 設定。× で Discovery に戻る。👁 → I3 プレビュー。
class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final me = state.me;

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
                  'プロフィール',
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.7,
                    color: SColor.text,
                  ),
                ),
                Row(
                  children: [
                    SCircleButton(
                      onTap: () => Navigator.of(context).push(sPushRoute(const SettingsScreen())),
                      child: const SIcon(SIconData.gear, size: 16),
                    ),
                    SCircleButton(
                      onTap: () => Navigator.of(context).maybePop(),
                      child: const SIcon(SIconData.close, size: 15, strokeWidth: 2),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.only(bottom: 20),
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(SSpace.x5, 12, SSpace.x5, 0),
                  child: Center(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(SRadius.card),
                      child: SizedBox(
                        width: 282,
                        height: 376,
                        child: Stack(
                          fit: StackFit.expand,
                          children: [
                            SPhotoView(me.mainPhoto),
                            const SPhotoScrim(start: 0.58),
                            if (me.ageVerified)
                              const Positioned(
                                top: 14,
                                left: 14,
                                child: STintChip('年齢確認済み', fontSize: 11),
                              ),
                            Positioned(
                              left: 18,
                              right: 18,
                              bottom: 16,
                              child: Text.rich(
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
                                  fontSize: 30,
                                  height: 1,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: -0.75,
                                  color: SColor.text,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(SSpace.x5, 14, SSpace.x5, 0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (!me.ageVerified) ...[const _AgeBanner(), const SizedBox(height: 12)],
                      Text(
                        me.bio,
                        style: const TextStyle(fontSize: 14, height: 1.6, color: SColor.text2),
                      ),
                      const SizedBox(height: 12),
                      Wrap(
                        spacing: 7,
                        runSpacing: 7,
                        children: [
                          for (final id in me.interests)
                            STintChip(InterestMaster.labelOf(id), tinted: false),
                        ],
                      ),
                      const SizedBox(height: 12),
                      _PhotoStrip(photos: me.photos),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 20),
            child: SafeArea(
              top: false,
              child: Row(
                children: [
                  SPressable(
                    onTap: () => Navigator.of(context).push(sUpRoute(const ProfilePreviewScreen())),
                    child: Container(
                      width: 54,
                      height: 54,
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: SColor.surface,
                        borderRadius: BorderRadius.circular(SRadius.button),
                      ),
                      child: const SIcon(SIconData.eye, size: 20),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: SSecondaryButton(
                      'プロフィールを編集',
                      onTap: () => Navigator.of(context).push(sUpRoute(const ProfileEditScreen())),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _AgeBanner extends StatelessWidget {
  const _AgeBanner();

  @override
  Widget build(BuildContext context) => GestureDetector(
    behavior: HitTestBehavior.opaque,
    onTap: () => Navigator.of(context).push(sUpRoute(const AgeVerificationScreen())),
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
      decoration: BoxDecoration(
        color: SColor.greenTint,
        borderRadius: BorderRadius.circular(SRadius.field),
      ),
      child: const Row(
        children: [
          SIcon(SIconData.shield, size: 19, color: SColor.green, strokeWidth: 2),
          SizedBox(width: 13),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '年齢確認が未完了です',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: SColor.text),
                ),
                SizedBox(height: 2),
                Text('確認するとメッセージを送れます', style: TextStyle(fontSize: 12, color: SColor.greenText)),
              ],
            ),
          ),
          Text(
            '確認する ›',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: SColor.green),
          ),
        ],
      ),
    ),
  );
}

class _PhotoStrip extends StatelessWidget {
  const _PhotoStrip({required this.photos});

  final List<SPhoto> photos;

  @override
  Widget build(BuildContext context) => SizedBox(
    height: 96,
    child: ListView(
      scrollDirection: Axis.horizontal,
      children: [
        for (var i = 1; i < photos.length; i++) ...[
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: SizedBox(width: 72, height: 96, child: SPhotoView(photos[i])),
          ),
          const SizedBox(width: 8),
        ],
        if (photos.length < SUser.maxPhotos)
          SizedBox(
            width: 72,
            height: 96,
            child: DecoratedBox(
              decoration: BoxDecoration(
                color: SColor.sheet,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0x24FFFFFF)),
              ),
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    '+',
                    style: TextStyle(
                      fontSize: 19,
                      fontWeight: FontWeight.w300,
                      color: Color(0xFF5A5F5A),
                    ),
                  ),
                  Text(
                    '3:4',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF5A5F5A),
                    ),
                  ),
                ],
              ),
            ),
          ),
      ],
    ),
  );
}
