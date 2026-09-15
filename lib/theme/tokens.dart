import 'package:flutter/widgets.dart';

/// Session v4 design tokens.
///
/// 全画面ダーク固定。OS のライトモードには追従しない。
/// 値は「Session v4 Core」P / R セクションのハンドオフをそのまま写している。
abstract final class SColor {
  static const bg = Color(0xFF0A0B0A);
  static const bgDeep = Color(0xFF070807); // G / K1 のみ
  static const sheet = Color(0xFF151715);
  static const surface = Color(0xFF1B1E1B);
  static const raised = Color(0xFF262926);
  static const greenTint = Color(0xFF10301E);
  static const green = Color(0xFF2BD873);
  static const greenText = Color(0xFF7CFFB2);
  static const onGreen = Color(0xFF08150D);
  static const text = Color(0xFFF4F5F2);
  static const text2 = Color(0xFFD7DAD5);
  static const secondary = Color(0xFFB4B9B3);
  static const muted = Color(0xFF8B908B);
  static const muted2 = Color(0xFF7A7F7A);
  static const disabled = Color(0xFF4A504A);

  /// 破壊的操作とバリデーションのみ。CTA の塗りには使わない。
  static const danger = Color(0xFFC8735F);
  static const dangerTint = Color(0xFF2A1E1B);

  static const hairline = Color(0x0FFFFFFF);
  static const scrim = Color(0x9E060706);

  static const greenBorder = Color(0x732BD873); // rgba(43,216,115,.45)
}

abstract final class SSpace {
  static const x1 = 4.0;
  static const x2 = 8.0;
  static const x3 = 12.0;
  static const x4 = 16.0;

  /// 画面の左右標準余白
  static const x5 = 22.0;
  static const x6 = 32.0;

  /// 写真カードの外側余白
  static const photoInset = 14.0;
}

abstract final class SRadius {
  static const photo = 28.0; // スワイプカード
  static const card = 24.0;
  static const sheet = 30.0; // 上2角のみ
  static const field = 18.0;
  static const button = 16.0;
  static const chip = 12.0;
  static const pill = 999.0;
}

abstract final class SText {
  static const display = TextStyle(
    fontSize: 34,
    height: 1.0,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.85,
  );
  static const title = TextStyle(
    fontSize: 26,
    height: 1.15,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.65,
  );
  static const heading = TextStyle(
    fontSize: 22,
    height: 1.3,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.44,
  );
  static const body = TextStyle(fontSize: 15, height: 1.7, fontWeight: FontWeight.w400);
  static const label = TextStyle(fontSize: 14, height: 1.4, fontWeight: FontWeight.w600);
  static const caption = TextStyle(fontSize: 12, height: 1.55, fontWeight: FontWeight.w400);
}

abstract final class SMotion {
  static const sheet = Duration(milliseconds: 320);
  static const sheetEase = Cubic(0.2, 0.9, 0.3, 1.0);
  static const fade = Duration(milliseconds: 200);
  static const cardSnap = Duration(milliseconds: 330);
  static const cardEase = Cubic(0.4, 0.0, 0.6, 1.0);

  /// G（It's a Session）だけ順次表示：写真 150ms → タイポ 500ms → CTA 750ms
  static const matchPhotos = Duration(milliseconds: 150);
  static const matchType = Duration(milliseconds: 500);
  static const matchCta = Duration(milliseconds: 750);
}

/// 画面共通のサイズ。モックは 393×852pt 基準だが、実装は縦方向可変。
abstract final class SSize {
  static const primaryButtonHeight = 54.0;
  static const controlHeight = 34.0;
  static const minTouch = 44.0;
}
