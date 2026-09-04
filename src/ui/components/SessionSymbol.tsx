import React from 'react';
import Svg, { Path } from 'react-native-svg';
import { colors } from '../../theme';

/**
 * Session symbol（仕様書 §21）。
 *
 * assets/ の app icon と同じ astroid 形状をベクターで再現したもの。
 * App icon / Splash / Bottom nav / Session Request / Session ON / Match overlay に使う。
 * 乱用しない。
 */

// |x|^k + |y|^k = 1 (k = 0.42) をサンプリングした固定パス。
// 生成元: tools/generate-brand-assets.js と同じ形状定義。
const PATH =
  'M50 2 C50.9 27.5 53.3 39.2 60.4 43.9 C67.4 48.6 79.8 49.5 98 50 ' +
  'C79.8 50.5 67.4 51.4 60.4 56.1 C53.3 60.8 50.9 72.5 50 98 ' +
  'C49.1 72.5 46.7 60.8 39.6 56.1 C32.6 51.4 20.2 50.5 2 50 ' +
  'C20.2 49.5 32.6 48.6 39.6 43.9 C46.7 39.2 49.1 27.5 50 2 Z';

interface Props {
  size?: number;
  color?: string;
  opacity?: number;
}

export function SessionSymbol({ size = 20, color = colors.green, opacity = 1 }: Props) {
  return (
    <Svg width={size} height={size} viewBox="0 0 100 100" opacity={opacity}>
      <Path d={PATH} fill={color} />
    </Svg>
  );
}
