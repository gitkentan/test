import { SKIP_COOLDOWN_MS } from '../config/constants';
import type { DomainErrorCode } from './errors';
import type { Millis, Swipe, SessionStatus, UserId } from './types';
import { hasActiveSessionCapacity } from './sessions';
import type { Session } from './types';
import { settleExpiry } from './sessionStatus';

/**
 * Swipe State / Request State（仕様書 §11, §25, §29）
 *
 *   pending ├── matched
 *           ├── expired
 *           ├── cancelled
 *           └── blocked
 *
 * 期限切れ Request から Session を作らない。
 */

export function isSkip(swipe: Swipe): boolean {
  return swipe.type === 'skip';
}

export function isRequest(swipe: Swipe): boolean {
  return swipe.type === 'session_request';
}

/** 有効期限内で、まだ pending の Request か。 */
export function isActiveRequest(swipe: Swipe, now: Millis = Date.now()): boolean {
  if (!isRequest(swipe) || swipe.status !== 'pending') return false;
  return swipe.expiresAt === null || swipe.expiresAt > now;
}

/** 期限に達した pending Request を expired へ落とす。 */
export function settleRequest(swipe: Swipe, now: Millis = Date.now()): Swipe {
  if (isRequest(swipe) && swipe.status === 'pending' && swipe.expiresAt !== null && swipe.expiresAt <= now) {
    return { ...swipe, status: 'expired' };
  }
  return swipe;
}

/** Skip のクールダウン中（既定 24 時間）は同じ相手を再表示しない（§11）。 */
export function isSkipCoolingDown(swipe: Swipe, now: Millis = Date.now()): boolean {
  return isSkip(swipe) && now - swipe.createdAt < SKIP_COOLDOWN_MS;
}

export interface RequestGuardInput {
  senderId: UserId;
  receiverId: UserId;
  senderVerified: boolean;
  senderStatus: SessionStatus;
  /** 送信者の全 Swipe。重複判定に使う。 */
  senderSwipes: readonly Swipe[];
  sessions: readonly Session[];
  blockedBetween: boolean;
  receiverExists: boolean;
  now?: Millis;
}

/**
 * Session Request を作ってよいか（§11 の条件と §25 の検証項目）。
 *
 * client のガードと server の検証を同じ関数で共有し、判定がずれないようにする。
 * 呼び出し側が client であっても、必ず server 側でもこれを通す。
 */
export function checkCanSendRequest(input: RequestGuardInput): DomainErrorCode | null {
  const now = input.now ?? Date.now();

  if (input.senderId === input.receiverId) return 'SELF_REQUEST';
  if (!input.receiverExists) return 'USER_UNAVAILABLE';
  if (input.blockedBetween) return 'BLOCKED';
  if (!input.senderVerified) return 'AGE_NOT_VERIFIED';

  const status = settleExpiry(input.senderStatus, now);
  if (!status.sessionOn) return 'SESSION_OFF';

  if (!hasActiveSessionCapacity(input.sessions, input.senderId, now)) {
    return 'ACTIVE_SESSION_LIMIT';
  }

  const duplicate = input.senderSwipes.some(
    (swipe) =>
      swipe.receiverId === input.receiverId && isActiveRequest(swipe, now),
  );
  if (duplicate) return 'DUPLICATE_REQUEST';

  return null;
}

/**
 * Discovery から外すべき相手か。
 * Skip のクールダウン中と、有効な Request 送信済みの相手は出さない。
 */
export function excludedUserIds(
  swipes: readonly Swipe[],
  now: Millis = Date.now(),
): Set<UserId> {
  const excluded = new Set<UserId>();
  for (const swipe of swipes) {
    if (isSkipCoolingDown(swipe, now) || isActiveRequest(swipe, now) || swipe.status === 'matched') {
      excluded.add(swipe.receiverId);
    }
  }
  return excluded;
}
