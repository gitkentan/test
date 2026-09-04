import type { Intent } from './types';

/**
 * Intent（仕様書 §5）
 *
 * 同時に選べるのは1つだけ。「全部OK」のような複数選択 UI は作らない。
 * 「何でもいい」は free_now（今ひま）で表現する。
 */
export const INTENTS: readonly Intent[] = ['drinks', 'food', 'cafe', 'free_now'] as const;

interface IntentPresentation {
  emoji: string;
  /** Intent sheet 用の見出し。 */
  label: string;
  /** ステータス行などに置く短い表記。 */
  shortLabel: string;
  /** カードに載せる一人称の表現。 */
  cardLabel: string;
}

const PRESENTATION: Record<Intent, IntentPresentation> = {
  drinks: { emoji: '🍸', label: '飲みに行く', shortLabel: '飲み', cardLabel: '飲みに行きたい' },
  food: { emoji: '🍽', label: 'ご飯', shortLabel: 'ご飯', cardLabel: 'ご飯に行きたい' },
  cafe: { emoji: '☕', label: 'カフェ', shortLabel: 'カフェ', cardLabel: 'カフェに行きたい' },
  free_now: { emoji: '🌙', label: '今ひま', shortLabel: '今ひま', cardLabel: '今ひま' },
};

export function intentPresentation(intent: Intent): IntentPresentation {
  return PRESENTATION[intent];
}

/** 例: 「🍸 飲み」 */
export function intentShortText(intent: Intent): string {
  const p = PRESENTATION[intent];
  return `${p.emoji} ${p.shortLabel}`;
}

/** 例: 「🍸 飲みに行きたい」 */
export function intentCardText(intent: Intent): string {
  const p = PRESENTATION[intent];
  return `${p.emoji} ${p.cardLabel}`;
}

export function isIntent(value: unknown): value is Intent {
  return typeof value === 'string' && (INTENTS as readonly string[]).includes(value);
}
