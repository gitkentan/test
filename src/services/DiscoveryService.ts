import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import type { DiscoveryResult, Repositories, SendRequestOutcome } from '../data/repositories';
import type { UserId } from '../domain/types';

/**
 * Discovery / Swipe（仕様書 §8–§11）。
 */
export class DiscoveryService {
  constructor(private readonly repos: Repositories) {}

  async load(userId: UserId): Promise<DiscoveryResult> {
    const result = await this.repos.discovery.loadDiscovery(userId);
    analytics.track(AnalyticsEvent.discoveryView, {
      count: result.candidates.length,
      radius_km: result.radiusKm,
    });
    if (result.isEmpty) {
      analytics.track(AnalyticsEvent.discoveryEmpty);
    } else if (result.poolIsLow) {
      analytics.track(AnalyticsEvent.discoveryPoolLow, { radius_km: result.radiusKm });
    }
    return result;
  }

  async skip(userId: UserId, targetId: UserId): Promise<void> {
    await this.repos.swipes.skip(userId, targetId);
    analytics.track(AnalyticsEvent.swipeSkip);
  }

  /**
   * 右 Swipe = Session Request。
   * 相互 Request なら Session が成立して返る（§12）。
   */
  async sendRequest(userId: UserId, targetId: UserId): Promise<SendRequestOutcome> {
    const outcome = await this.repos.swipes.sendRequest(userId, targetId);
    analytics.track(AnalyticsEvent.sessionRequestSent, { matched: outcome.kind === 'matched' });
    if (outcome.kind === 'matched') {
      analytics.track(AnalyticsEvent.sessionCreated, {
        user_a_intent: outcome.session.userAIntent,
        user_b_intent: outcome.session.userBIntent,
      });
    }
    return outcome;
  }

  countIncomingRequests(userId: UserId): Promise<number> {
    return this.repos.swipes.countIncomingRequests(userId);
  }
}
