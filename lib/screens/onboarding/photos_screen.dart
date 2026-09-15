import 'package:flutter/widgets.dart';

import '../../app.dart';
import '../../models/photo.dart';
import '../../state/app_state.dart';
import '../../models/user.dart';
import '../../state/mock_data.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_photo.dart';
import '../s_scaffold.dart';
import 'bio_screen.dart';
import 'crop_screen.dart';

/// J7 Photos · アスペクト比は 3:4 固定（選択後は必ず J7b で切り抜く）。
/// 最低3枚（未満は CTA 無効）・最大6枚。
class PhotosScreen extends StatefulWidget {
  const PhotosScreen({super.key});

  @override
  State<PhotosScreen> createState() => _PhotosScreenState();
}

class _PhotosScreenState extends State<PhotosScreen> {
  final List<SPhoto> _photos = [];

  static const _palette = [
    MockGradients.plum,
    MockGradients.slate,
    MockGradients.sand,
    MockGradients.moss,
    MockGradients.clay,
    MockGradients.warmGrey,
  ];

  Future<void> _add() async {
    if (_photos.length >= SUser.maxPhotos) return;
    // 実装では image_picker（OS 純正ピッカー・権限ダイアログ不要）→ image_cropper。
    final picked = SPhoto(
      id: 'p${_photos.length}',
      gradient: _palette[_photos.length % _palette.length],
    );
    final cropped = await Navigator.of(context).push<SPhoto>(sUpRoute(CropScreen(photo: picked)));
    if (cropped == null || !mounted) return;
    setState(() => _photos.add(cropped));
  }

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(onBack: () => Navigator.of(context).maybePop(), progress: 0.62),
          const Padding(
            padding: EdgeInsets.fromLTRB(24, 28, 24, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '写真、3枚から。',
                  style: TextStyle(
                    fontSize: 28,
                    height: 1.25,
                    fontWeight: FontWeight.w700,
                    letterSpacing: -0.56,
                    color: SColor.text,
                  ),
                ),
                SizedBox(height: 8),
                Text(
                  'すべて 3:4 に切り抜かれます。1枚目が最初に見られます。',
                  style: TextStyle(fontSize: 15, color: SColor.muted),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 22, 20, 0),
            child: GridView.count(
              crossAxisCount: 3,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 9,
              crossAxisSpacing: 9,
              childAspectRatio: SPhoto.aspectRatio,
              children: [
                for (var i = 0; i < SUser.maxPhotos; i++)
                  PhotoSlot(
                    photo: i < _photos.length ? _photos[i] : null,
                    main: i == 0 && _photos.isNotEmpty,
                    onTap: i <= _photos.length ? _add : null,
                    showAddHint: i == _photos.length,
                  ),
              ],
            ),
          ),
          const Spacer(),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 0, SSpace.x5, 24),
            child: Column(
              children: [
                Text(
                  '${_photos.length} / ${SUser.maxPhotos} 枚',
                  style: const TextStyle(fontSize: 13, color: SColor.muted),
                ),
                const SizedBox(height: 10),
                SPrimaryButton(
                  '次へ',
                  enabled: _photos.length >= SUser.minPhotos,
                  onTap: () {
                    AppScope.read(context).me.photos = List.of(_photos);
                    Navigator.of(context).push(sUpRoute(const BioScreen()));
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
