import { createId } from './ids';
import { findActiveSessionBetween, hasActiveSessionCapacity, resolveActiveUntil } from './sessions';
import { isActiveRequest } from './requests';
import type { DomainErrorCode } from './errors';
import type { Millis, Session, SessionStatus, Swipe, UserId } from './types';
import { settleExpiry } from './sessionStatus';

/**
 * Mutual Request → IT'S A SESSION（仕様書 §12, §25）
 *
 * A → B に active request があり、B → A も有効期限内に request した場合に Session が1件成立する。
 * 同時操作でも同じ Session が2件生成されないよう、呼び出し側はこの判定と作成を
 * 単一のトランザクション / idempotent な処理の中で行うこと。
 */

export interface MutualMatchInput {
  /** これから作られる（あるいは今作られた）Request。 */
  outgoing: Swipe;
  /** 相手から自分への Swipe 一覧。 */
  incomingSwipes: readonly Swipe[];
  senderStatus: SessionStatus;
  receiverStatus: SessionStatus;
  sessions: readonly Session[];
  now?: Millis;
}

export type MutualMatchResult =
  | { kind: 'no_match' }
  | { kind: 'already_matched'; session: Session }
  | { kind: 'blocked'; reason: DomainErrorCode }
  | { kind: 'matched'; session: Session; matchedRequestIds: string[] };

export function evaluateMutualMatch(input: MutualMatchInput): MutualMatchResult {
  const now = input.now ?? Date.now();
  const { outgoing } = input;

  // 期限切れ Request から Session を作らない（§29）。
  if (!isActiveRequest(outgoing, now)) return { kind: 'no_match' };

  const reciprocal = input.incomingSwipes.find(
    (swipe) => swipe.senderId === outgoing.receiverId && isActiveRequest(swipe, now),
  );
  if (!reciprocal) return { kind: 'no_match' };

  // 重複 Session がないことを確認（§12 手順1）。
  const existing = findActiveSessionBetween(
    input.sessions,
    outgoing.senderId,
    outgoing.receiverId,
    now,
  );
  if (existing) return { kind: 'already_matched', session: existing };

  // どちらかの枠が埋まっていれば成立させない（§7）。
  for (const userId of [outgoing.senderId, outgoing.receiverId] as UserId[]) {
    if (!hasActiveSessionCapacity(input.sessions, userId, now)) {
      return { kind: 'blocked', reason: 'ACTIVE_SESSION_LIMIT' };
    }
  }

  const senderStatus = settleExpiry(input.senderStatus, now);
  const receiverStatus = settleExpiry(input.receiverStatus, now);

  const session: Session = {
    id: createId('ses'),
    userAId: outgoing.senderId,
    userBId: outgoing.receiverId,
    userAIntent: outgoing.senderIntent ?? senderStatus.currentIntent,
    userBIntent: reciprocal.senderIntent ?? receiverStatus.currentIntent,
    startedAt: now,
    activeUntil: resolveActiveUntil(
      senderStatus.expiresAt ?? outgoing.expiresAt,
      receiverStatus.expiresAt ?? reciprocal.expiresAt,
      now,
    ),
    status: 'active',
  };

  return { kind: 'matched', session, matchedRequestIds: [outgoing.id, reciprocal.id] };
}
