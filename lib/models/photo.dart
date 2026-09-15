import 'package:flutter/widgets.dart';

/// 写真はすべて 3:4。プロトタイプの写真は CSS グラデーションのプレースホルダで、
/// 実装では実画像の読み込みに差し替える（読み込み中は同じ暗いプレースホルダを出す）。
@immutable
class SPhoto {
  const SPhoto({required this.id, required this.gradient, this.url});

  final String id;

  /// 実画像が入るまでのプレースホルダ。読み込み中の下地としても使う。
  final List<Color> gradient;

  /// 差し替え先。null ならプレースホルダのみ。
  final String? url;

  static const aspectRatio = 3 / 4;

  SPhoto copyWith({String? url}) => SPhoto(id: id, gradient: gradient, url: url ?? this.url);
}
