import { MAX_ACTIVE_SESSIONS } from '../config/constants';
import type { Millis, Session, SessionState, UserId } from './types';

/**
 * Session State（仕様書 §7, §12, §30）
 *
 *   active --active_until--> past
 *
 * Past になっても Conversation は残す。Chat 履歴は消さない。
 */

export function sessionState(session: Session, now: Millis = Date.now()): SessionState {
  return session.activeUntil > now ? 'active' : 'past';
}

export function isActiveSession(session: Session, now: Millis = Date.now()): boolean {
  return sessionState(session, now) === 'active';
}

/** 期限を反映した status を持つ Session を返す。 */
export function settleSession(session: Session, now: Millis = Date.now()): Session {
  const status = sessionState(session, now);
  return status === session.status ? session : { ...session, status };
}

export function participants(session: Session): [UserId, UserId] {
  return [session.userAId, session.userBId];
}

export function partnerId(session: Session, viewerId: UserId): UserId {
  return session.userAId === viewerId ? session.userBId : session.userAId;
}

export function involves(session: Session, userId: UserId): boolean {
  return session.userAId === userId || session.userBId === userId;
}

export function countActiveSessions(
  sessions: readonly Session[],
  userId: UserId,
  now: Millis = Date.now(),
): number {
  return sessions.filter((s) => involves(s, userId) && isActiveSession(s, now)).length;
}

/**
 * Active Session の枠が空いているか（§7）。
 * Match 収集アプリ化を防ぐための上限。期限が切れれば自然に空く。
 */
export function hasActiveSessionCapacity(
  sessions: readonly Session[],
  userId: UserId,
  now: Millis = Date.now(),
): boolean {
  return countActiveSessions(sessions, userId, now) < MAX_ACTIVE_SESSIONS;
}

/** 2人の間にすでに Active Session があるか（重複 Session の防止）。 */
export function findActiveSessionBetween(
  sessions: readonly Session[],
  a: UserId,
  b: UserId,
  now: Millis = Date.now(),
): Session | undefined {
  return sessions.find(
    (s) => involves(s, a) && involves(s, b) && isActiveSession(s, now),
  );
}

/**
 * active_until は両者の Session expiry の早い方（§12）。
 * どちらかの expiry が不明な場合は、判明している方に合わせる。
 */
export function resolveActiveUntil(
  aExpiresAt: Millis | null,
  bExpiresAt: Millis | null,
  fallback: Millis,
): Millis {
  const candidates = [aExpiresAt, bExpiresAt].filter(
    (value): value is Millis => value !== null,
  );
  if (candidates.length === 0) return fallback;
  return Math.min(...candidates);
}
