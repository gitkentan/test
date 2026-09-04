/**
 * Session Visual Identity（仕様書 §21）
 *
 * Green は「NOW / ACTIVE / AVAILABLE / SELECTED / PRIMARY ACTION」だけに使う。
 * 画面全体での使用率はおおむね 5–10%。地の色は Near Black。
 */
export const colors = {
  /** Session Green — 主役。乱用しない。 */
  green: '#32F783',
  greenPressed: '#25D26D',
  greenDim: 'rgba(50, 247, 131, 0.16)',
  greenBorder: 'rgba(50, 247, 131, 0.36)',

  /** Near Black — アプリの地。 */
  bg: '#080B0B',
  /** 面をわずかに持ち上げるとき用。 */
  surface: '#111516',
  surfaceRaised: '#1A1F20',
  overlay: 'rgba(8, 11, 11, 0.92)',

  white: '#FFFFFF',
  /** Neutral gray は secondary text のみ（§21）。 */
  textPrimary: '#FFFFFF',
  textSecondary: '#9AA3A2',
  textTertiary: '#6B7474',

  hairline: 'rgba(255, 255, 255, 0.10)',
  hairlineStrong: 'rgba(255, 255, 255, 0.18)',

  danger: '#FF5A5A',
  scrim: 'rgba(0, 0, 0, 0.62)',
} as const;

export const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  xxl: 32,
} as const;

export const radius = {
  sm: 8,
  md: 14,
  lg: 20,
  xl: 28,
  pill: 999,
} as const;

export const typography = {
  display: { fontSize: 34, fontWeight: '700' as const, letterSpacing: -0.6 },
  title: { fontSize: 24, fontWeight: '700' as const, letterSpacing: -0.3 },
  headline: { fontSize: 19, fontWeight: '600' as const, letterSpacing: -0.2 },
  body: { fontSize: 15, fontWeight: '400' as const },
  bodyStrong: { fontSize: 15, fontWeight: '600' as const },
  caption: { fontSize: 13, fontWeight: '400' as const },
  micro: { fontSize: 11, fontWeight: '600' as const, letterSpacing: 0.8 },
} as const;

export const theme = { colors, spacing, radius, typography };
