import 'package:flutter/widgets.dart';

import '../../models/photo.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_buttons.dart';
import '../../widgets/s_photo.dart';
import '../s_scaffold.dart';

/// J7b Crop 3:4
///
/// この画面は本来 **自作しない**。`image_cropper` が内部で呼ぶ
/// iOS TOCropViewController / Android uCrop を
/// `lockAspectRatio: true` / ratio 3:4 / `hideBottomControls: true` で使う。
/// ここはプラグインを入れるまでの差し込み口で、設定値の指示書として同じ枠を描いている。
/// 完了時はクライアントで切り抜き → 長辺1440pxにリサイズ → EXIF の位置情報を破棄して
/// POST /me/photos。
class CropScreen extends StatelessWidget {
  const CropScreen({super.key, required this.photo});

  final SPhoto photo;

  @override
  Widget build(BuildContext context) {
    return SScaffold(
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 16, SSpace.x5, 0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                GestureDetector(
                  onTap: () => Navigator.of(context).pop(),
                  child: const Text('キャンセル', style: TextStyle(fontSize: 15, color: SColor.muted)),
                ),
                const Text(
                  '切り抜き',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: SColor.text),
                ),
                GestureDetector(
                  onTap: () => Navigator.of(context).pop(photo),
                  child: const Text(
                    '完了',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: SColor.green,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 26),
          Expanded(
            child: ColoredBox(
              color: const Color(0xFF050605),
              child: Center(
                child: AspectRatio(
                  aspectRatio: SPhoto.aspectRatio,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [SPhotoView(photo), const _CropGrid()],
                  ),
                ),
              ),
            ),
          ),
          const Padding(
            padding: EdgeInsets.fromLTRB(SSpace.x5, 26, SSpace.x5, 0),
            child: Column(
              children: [
                Text('ドラッグで位置調整 · ピンチで拡大', style: TextStyle(fontSize: 14, color: SColor.muted)),
                SizedBox(height: 8),
                Text(
                  'Session の写真はすべて 3:4 です。切り抜き枠の外側は保存されません。',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 12, height: 1.6, color: SColor.muted2),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(SSpace.x5, 20, SSpace.x5, 20),
            child: SPrimaryButton('この範囲で使う', onTap: () => Navigator.of(context).pop(photo)),
          ),
        ],
      ),
    );
  }
}

class _CropGrid extends StatelessWidget {
  const _CropGrid();

  @override
  Widget build(BuildContext context) => CustomPaint(painter: _CropGridPainter());
}

class _CropGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final thin = Paint()
      ..color = const Color(0x2EFFFFFF)
      ..strokeWidth = 1;
    final frame = Paint()
      ..color = const Color(0x8CFFFFFF)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1;
    final corner = Paint()
      ..color = SColor.text
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3;

    canvas.drawRect(Offset.zero & size, frame);
    for (var i = 1; i < 3; i++) {
      final x = size.width * i / 3;
      final y = size.height * i / 3;
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), thin);
      canvas.drawLine(Offset(0, y), Offset(size.width, y), thin);
    }
    const c = 22.0;
    canvas.drawPath(
      Path()
        ..moveTo(0, c)
        ..lineTo(0, 0)
        ..lineTo(c, 0),
      corner,
    );
    canvas.drawPath(
      Path()
        ..moveTo(size.width - c, 0)
        ..lineTo(size.width, 0)
        ..lineTo(size.width, c),
      corner,
    );
    canvas.drawPath(
      Path()
        ..moveTo(0, size.height - c)
        ..lineTo(0, size.height)
        ..lineTo(c, size.height),
      corner,
    );
    canvas.drawPath(
      Path()
        ..moveTo(size.width - c, size.height)
        ..lineTo(size.width, size.height)
        ..lineTo(size.width, size.height - c),
      corner,
    );
  }

  @override
  bool shouldRepaint(_CropGridPainter old) => false;
}
