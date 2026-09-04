import React from 'react';
import Svg, { Circle, Path } from 'react-native-svg';
import { colors } from '../../theme';

/**
 * 最小限のラインアイコン。
 * アイコンフォントを足さずに済ませるため、必要なものだけを手で持つ。
 */
interface IconProps {
  size?: number;
  color?: string;
}

const stroke = {
  strokeWidth: 1.8,
  strokeLinecap: 'round' as const,
  strokeLinejoin: 'round' as const,
  fill: 'none',
};

export function MessageIcon({ size = 22, color = colors.textSecondary }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Path
        d="M21 11.5a8 8 0 0 1-8.5 8 9 9 0 0 1-3.6-.7L4 20.5l1.4-4A8 8 0 0 1 4.5 11 8 8 0 0 1 13 3.5a8 8 0 0 1 8 8Z"
        stroke={color}
        {...stroke}
      />
    </Svg>
  );
}

export function UserIcon({ size = 22, color = colors.textSecondary }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Circle cx="12" cy="8" r="4" stroke={color} {...stroke} />
      <Path d="M4.5 20.5a7.5 7.5 0 0 1 15 0" stroke={color} {...stroke} />
    </Svg>
  );
}

export function CloseIcon({ size = 22, color = colors.textSecondary }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Path d="M6 6 18 18M18 6 6 18" stroke={color} {...stroke} />
    </Svg>
  );
}

export function ChevronLeftIcon({ size = 24, color = colors.white }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Path d="M15 5 8 12l7 7" stroke={color} {...stroke} />
    </Svg>
  );
}

export function ChevronRightIcon({ size = 20, color = colors.textTertiary }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Path d="M9 5l7 7-7 7" stroke={color} {...stroke} />
    </Svg>
  );
}

export function InfoIcon({ size = 20, color = colors.white }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Circle cx="12" cy="12" r="9" stroke={color} {...stroke} />
      <Path d="M12 11v5.5M12 7.6v.1" stroke={color} {...stroke} />
    </Svg>
  );
}

export function ImageIcon({ size = 22, color = colors.textSecondary }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Path d="M4 5.5h16v13H4z" stroke={color} {...stroke} />
      <Circle cx="9" cy="10" r="1.6" stroke={color} {...stroke} />
      <Path d="M5 17l4.5-4.5 3.5 3.5 2.5-2.5L19 17" stroke={color} {...stroke} />
    </Svg>
  );
}

export function SendIcon({ size = 20, color = colors.bg }: IconProps) {
  return (
    <Svg width={size} height={size} viewBox="0 0 24 24">
      <Path d="M4 12 20 4l-3.5 16-4.5-6-8-2Z" stroke={color} strokeWidth={1.8} strokeLinejoin="round" fill="none" />
    </Svg>
  );
}
