import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import type { Repositories } from '../data/repositories';
import type { ReportReason, UserId } from '../domain/types';

/**
 * Safety（仕様書 §20）。
 * Block / Report は必ず server-side でも enforce される前提で呼ぶ。
 */
export class SafetyService {
  constructor(private readonly repos: Repositories) {}

  async block(blockerId: UserId, blockedId: UserId): Promise<void> {
    await this.repos.safety.block(blockerId, blockedId);
    analytics.track(AnalyticsEvent.userBlocked);
  }

  unblock(blockerId: UserId, blockedId: UserId): Promise<void> {
    return this.repos.safety.unblock(blockerId, blockedId);
  }

  listBlocked(userId: UserId) {
    return this.repos.safety.listBlocked(userId);
  }

  async report(
    reporterId: UserId,
    reportedUserId: UserId,
    reason: ReportReason,
    details: string | null,
  ) {
    const report = await this.repos.safety.report(reporterId, reportedUserId, reason, details);
    analytics.track(AnalyticsEvent.reportSubmitted, { reason });
    return report;
  }
}
