import * as WebBrowser from 'expo-web-browser';
import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import { ageVerificationProviderUrl, isDev } from '../config/env';
import type { Repositories } from '../data/repositories';
import type { User, UserId } from '../domain/types';

/**
 * Age Verification（仕様書 §19）
 *
 * 生年月日の自己入力だけを法定年齢確認の完了として扱わない。
 * 本人確認は外部の適合プロバイダへ委譲し、Session 側は結果だけを受け取る。
 * 本人確認書類の画像を Session 側で保存する設計にはしない。
 *
 *   Session → 18歳以上確認 → external provider → verification
 *           → callback / return to Session → age_verified = true
 */
export class AgeVerificationService {
  constructor(private readonly repos: Repositories) {}

  /**
   * 外部プロバイダへの遷移を開始する。
   *
   * プロバイダ URL が設定されていない場合、開発環境ではローカルで確認済みにして
   * 以降のループ（Session ON → Swipe → Chat）を試せるようにする。
   * 本番では URL 未設定を成功として扱わない。
   */
  async start(userId: UserId): Promise<User | null> {
    analytics.track(AnalyticsEvent.ageVerificationStarted);
    const { reference } = await this.repos.ageVerification.startVerification(userId);

    if (ageVerificationProviderUrl) {
      const result = await WebBrowser.openAuthSessionAsync(
        `${ageVerificationProviderUrl}?reference=${encodeURIComponent(reference)}`,
        'session://age-verification',
      );
      if (result.type !== 'success') {
        analytics.track(AnalyticsEvent.ageVerificationFailed, { type: result.type });
        return null;
      }
      // プロバイダからの callback は deep link で受け取り、applyResult() に流す。
      return null;
    }

    if (!isDev) {
      analytics.track(AnalyticsEvent.ageVerificationFailed, { reason: 'provider_not_configured' });
      return null;
    }

    return this.applyResult(userId, true, `dev-${reference}`);
  }

  /** プロバイダから返ってきた最小限の結果を反映する。 */
  async applyResult(
    userId: UserId,
    ageVerified: boolean,
    providerReference: string,
  ): Promise<User> {
    const user = await this.repos.ageVerification.applyResult({
      userId,
      ageVerified,
      verifiedAt: Date.now(),
      providerReference,
    });
    analytics.track(
      ageVerified ? AnalyticsEvent.ageVerificationCompleted : AnalyticsEvent.ageVerificationFailed,
    );
    return user;
  }
}
