import 'package:flutter/widgets.dart';

import '../../models/distance.dart';
import '../../models/interest.dart';
import '../../models/user.dart';
import '../../state/app_state.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_icons.dart';
import '../../widgets/s_photo.dart';
import '../../widgets/s_sheet.dart';
import '../../widgets/session_badge.dart';
import '../safety/safety_sheets.dart';

enum ProfileDetailResult { request, skip, blocked }

/// E Profile Detail · 自己紹介（最大200文字）はここだけに表示。
/// ··· → Report / Block。
class ProfileDetailSheet extends StatelessWidget {
  const ProfileDetailSheet({super.key, required this.person});

  final SUser person;

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final shared = state.me.interests.toSet();
    final media = MediaQuery.sizeOf(context);

    return SizedBox(
      height: media.height * 0.92,
      child: DecoratedBox(
        decoration: const BoxDecoration(
          color: SColor.bg,
          borderRadius: BorderRadius.vertical(top: Radius.circular(SRadius.sheet)),
        ),
        child: ClipRRect(
          borderRadius: const BorderRadius.vertical(top: Radius.circular(SRadius.sheet)),
          child: Stack(
            children: [
              ListView(
                padding: EdgeInsets.zero,
                children: [
                  SizedBox(
                    height: 430,
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        SPhotoView(person.mainPhoto),
                        const SPhotoScrim(start: 0.6),
                        Positioned(
                          top: 18,
                          left: 20,
                          right: 20,
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              SCircleButton(
                                color: const Color(0x800A0B0A),
                                size: 36,
                                onTap: () => Navigator.of(context).pop(),
                                child: const SIcon(SIconData.chevronDown, size: 16, strokeWidth: 2),
                              ),
                              SCircleButton(
                                color: const Color(0x800A0B0A),
                                size: 36,
                                onTap: () => _openMenu(context),
                                child: const Text(
                                  '···',
                                  style: TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 1,
                                    color: SColor.text,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        Positioned(
                          left: 24,
                          right: 24,
                          bottom: 18,
                          child: IgnorePointer(
                            child: Column(
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
                                  style: const TextStyle(
                                    fontSize: 36,
                                    height: 1,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: -0.9,
                                    color: SColor.text,
                                  ),
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
                  Padding(
                    padding: const EdgeInsets.fromLTRB(24, 22, 24, 140),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          person.bio,
                          style: const TextStyle(fontSize: 15, height: 1.75, color: SColor.text2),
                        ),
                        const SizedBox(height: 20),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            for (final id in person.interests)
                              STintChip(
                                shared.contains(id)
                                    ? '${InterestMaster.labelOf(id)} · 共通'
                                    : InterestMaster.labelOf(id),
                                tinted: shared.contains(id),
                              ),
                          ],
                        ),
                        const SizedBox(height: 20),
                        if (person.photos.length > 1)
                          Row(
                            children: [
                              for (var i = 1; i < person.photos.length; i++) ...[
                                Expanded(
                                  child: ClipRRect(
                                    borderRadius: BorderRadius.circular(20),
                                    child: SizedBox(
                                      height: 190,
                                      child: SPhotoView(person.photos[i]),
                                    ),
                                  ),
                                ),
                                if (i != person.photos.length - 1) const SizedBox(width: 10),
                              ],
                            ],
                          ),
                      ],
                    ),
                  ),
                ],
              ),
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: IgnorePointer(
                  ignoring: false,
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(20, 36, 20, 28),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [Color(0x000A0B0A), SColor.bg],
                        stops: [0, 0.42],
                      ),
                    ),
                    child: Row(
                      children: [
                        SPressable(
                          onTap: () => Navigator.of(context).pop(ProfileDetailResult.skip),
                          child: Container(
                            width: 54,
                            height: 54,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: SColor.surface,
                              borderRadius: BorderRadius.circular(SRadius.button),
                            ),
                            child: const SIcon(
                              SIconData.close,
                              size: 18,
                              color: SColor.muted,
                              strokeWidth: 2,
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: SPrimaryButton(
                            'Session を送る',
                            onTap: () => Navigator.of(context).pop(ProfileDetailResult.request),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _openMenu(BuildContext context) async {
    final result = await showSSheet<PersonMenuResult>(
      context,
      builder: (_) => PersonMenuSheet(person: person, showUnmatch: false),
    );
    if (result == PersonMenuResult.blocked && context.mounted) {
      Navigator.of(context).pop(ProfileDetailResult.blocked);
    }
  }
}
