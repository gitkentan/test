import { SESSION_TTL_MS } from '../config/constants';
import type { Intent, Millis, SessionStatus, UserId } from './types';

/**
 * Core State Machine — Session status（仕様書 §6, §28）
 *
 *   OFF --select intent--> ON --TTL expires--> OFF
 *
 * ユーザーに時間を細かく設定させない。内部ルールだけで自動 OFF にする。
 * Intent 変更時は TTL をリセットし、新しい 6 時間 Session として扱う（§28）。
 */

export function offStatus(userId: UserId, now: Millis = Date.now()): SessionStatus {
  return {
    userId,
    sessionOn: false,
    currentIntent: null,
    startedAt: null,
    expiresAt: null,
    updatedAt: now,
  };
}

/** Session ON にする / Intent を変更する。どちらも TTL を now から引き直す。 */
export function turnOn(userId: UserId, intent: Intent, now: Millis = Date.now()): SessionStatus {
  return {
    userId,
    sessionOn: true,
    currentIntent: intent,
    startedAt: now,
    expiresAt: now + SESSION_TTL_MS,
    updatedAt: now,
  };
}

export function turnOff(status: SessionStatus, now: Millis = Date.now()): SessionStatus {
  return offStatus(status.userId, now);
}

export function isExpired(status: SessionStatus, now: Millis = Date.now()): boolean {
  return status.sessionOn && status.expiresAt !== null && status.expiresAt <= now;
}

/**
 * 期限に達していれば OFF へ落とした状態を返す。
 *
 * 読み出し側は必ずこれを通す。そうすることで、アプリを再起動しても
 * 期限切れの ON が復元されない（§31 「UI再起動後も状態が正しく復元される」）。
 */
export function settleExpiry(status: SessionStatus, now: Millis = Date.now()): SessionStatus {
  return isExpired(status, now) ? offStatus(status.userId, now) : status;
}

/** 期限反映後に実際に ON か。 */
export function isSessionOn(status: SessionStatus, now: Millis = Date.now()): boolean {
  return settleExpiry(status, now).sessionOn;
}
