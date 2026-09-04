import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import { DomainError } from '../domain/errors';
import type { Intent, SessionStatus, UserId } from '../domain/types';
import type { Repositories } from '../data/repositories';

/**
 * Session ON / OFF（仕様書 §5, §6, §28）。
 * UI は Intent と userId しか知らなくてよく、TTL の扱いは repository 側に閉じる。
 */
export class SessionService {
  constructor(private readonly repos: Repositories) {}

  getStatus(userId: UserId): Promise<SessionStatus> {
    return this.repos.sessionStatus.getStatus(userId);
  }

  /**
   * Intent を選んで Session ON にする。
   * すでに ON の場合、Intent 変更として TTL を引き直す（§28）。
   */
  async turnOn(userId: UserId, intent: Intent, previous: SessionStatus): Promise<SessionStatus> {
    const status = await this.repos.sessionStatus.turnOn(userId, intent);
    analytics.track(AnalyticsEvent.intentSelected, { intent });
    if (!previous.sessionOn) {
      analytics.track(
        previous.startedAt ? AnalyticsEvent.sessionReactivated : AnalyticsEvent.sessionOn,
        { intent },
      );
    }
    return status;
  }

  async turnOff(userId: UserId): Promise<SessionStatus> {
    const status = await this.repos.sessionStatus.turnOff(userId);
    analytics.track(AnalyticsEvent.sessionOff);
    return status;
  }

  /**
   * 期限到達を反映する。UI にカウントダウンは出さないが、
   * 到達した瞬間に表示が OFF へ変わるよう定期的に呼ぶ（§6）。
   */
  async refreshExpiry(userId: UserId, previous: SessionStatus): Promise<SessionStatus> {
    const status = await this.repos.sessionStatus.getStatus(userId);
    if (previous.sessionOn && !status.sessionOn) {
      analytics.track(AnalyticsEvent.sessionExpired);
    }
    return status;
  }

  assertOn(status: SessionStatus): void {
    if (!status.sessionOn) throw new DomainError('SESSION_OFF');
  }
}
