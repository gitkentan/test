import * as Linking from 'expo-linking';
import * as WebBrowser from 'expo-web-browser';
import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import {
  AGE_VERIFICATION_RETURN_PATH,
  ageVerificationProviderUrl,
  isDev,
} from '../config/env';
import type { Repositories } from '../data/repositories';
import type { User, UserId } from '../domain/types';

/**
 * Age Verification（仕様書 §19）
 *
 * 生年月日の自己入力を法定年齢確認の完了として扱わない。
 * 本人確認は外部の適合プロバイダへ委譲し、Session 側は結果だけを受け取る。
 * 本人確認書類の画像を Session 側で保存する設計にはしない。
 *
 *   Session
 *     ↓ startVerification（サーバが試行を採番）
 *   external compliant age-verification provider
 *     ↓ 本人確認
 *   callback / return to Session（deep link で戻る）
 *     ↓ confirmVerification（サーバがプロバイダの結果を照会）
 *   age_verified = true
 *
 * ## 信頼モデル
 *
 * **戻ってきた URL のクエリを client が読んで確認済みにはしない。**
 * リダイレクトは「ユーザーが操作を終えた」という合図でしかなく、
 * 確認済みかどうかはサーバがプロバイダに照会した結果だけで決まる。
 * client 側にはその値を書き換える経路がない（`AgeVerificationRepository` 参照）。
 */

export type VerificationStatus = 'verified' | 'pending' | 'rejected' | 'cancelled' | 'unavailable';

export interface VerificationResult {
  status: VerificationStatus;
  user: User | null;
  /** UI にそのまま出せる説明。 */
  message: string | null;
}

const MESSAGES: Record<Exclude<VerificationStatus, 'verified'>, string> = {
  pending: '確認手続きを受け付けました。完了までしばらくお待ちください。',
  rejected: '年齢確認が完了しませんでした。もう一度お試しください。',
  cancelled: '年齢確認が途中で終了しました。',
  unavailable: '年齢確認を利用できません。時間をおいてお試しください。',
};

export class AgeVerificationService {
  constructor(private readonly repos: Repositories) {}

  /** この端末へ戻ってくる deep link。開発ビルドでは exp:// 形式になる。 */
  private returnUrl(): string {
    return Linking.createURL(AGE_VERIFICATION_RETURN_PATH);
  }

  /**
   * プロバイダの確認セッション URL を組み立てる。
   *
   * バックエンドが URL を返した場合はそれを優先する（プロバイダ側で
   * セッションを作る実装ではこちらになる）。返らない場合は設定値から組み立てる。
   */
  private providerUrl(backendUrl: string | null, reference: string): string | null {
    if (backendUrl) return backendUrl;
    if (!ageVerificationProviderUrl) return null;

    const url = new URL(ageVerificationProviderUrl);
    url.searchParams.set('reference', reference);
    url.searchParams.set('redirect_uri', this.returnUrl());
    return url.toString();
  }

  /**
   * 年齢確認を開始し、戻ってきたらサーバへ結果を照会する。
   *
   * プロバイダ URL が未設定の場合、開発環境でだけローカルで確認済みにして
   * コアループ（Session ON → Swipe → Chat）を試せるようにする。
   * 本番では未設定を成功として扱わず、`unavailable` を返す。
   */
  async start(userId: UserId): Promise<VerificationResult> {
    analytics.track(AnalyticsEvent.ageVerificationStarted);

    let start: Awaited<ReturnType<Repositories['ageVerification']['startVerification']>>;
    try {
      start = await this.repos.ageVerification.startVerification(userId);
    } catch {
      return this.failure('unavailable');
    }

    const url = this.providerUrl(start.redirectUrl, start.reference);

    if (!url) {
      // プロバイダ未接続。開発環境のみ、ループ確認のための近道を通す。
      if (!isDev) {
        analytics.track(AnalyticsEvent.ageVerificationFailed, {
          reason: 'provider_not_configured',
        });
        return this.failure('unavailable');
      }
      const user = await this.repos.ageVerification.devForceVerified(userId);
      analytics.track(AnalyticsEvent.ageVerificationCompleted, { dev: true });
      return { status: 'verified', user, message: null };
    }

    const session = await WebBrowser.openAuthSessionAsync(url, this.returnUrl());

    // 「戻ってきた」だけでは確認済みにしない。必ずサーバへ照会する。
    if (session.type !== 'success') {
      analytics.track(AnalyticsEvent.ageVerificationFailed, { type: session.type });
      return this.failure('cancelled');
    }

    return this.confirm(userId, start.reference);
  }

  /**
   * サーバへ結果を照会して確定させる。
   * プロバイダの審査が非同期な場合は pending が返るので、UI 側で再試行できる。
   */
  async confirm(userId: UserId, reference: string): Promise<VerificationResult> {
    const outcome = await this.repos.ageVerification.confirmVerification(userId, reference);

    switch (outcome.status) {
      case 'verified':
        analytics.track(AnalyticsEvent.ageVerificationCompleted);
        return { status: 'verified', user: outcome.user, message: null };
      case 'pending':
        return this.failure('pending');
      case 'rejected':
        analytics.track(AnalyticsEvent.ageVerificationFailed, {
          reason: outcome.reason ?? 'rejected',
        });
        return this.failure('rejected');
    }
  }

  private failure(status: Exclude<VerificationStatus, 'verified'>): VerificationResult {
    return { status, user: null, message: MESSAGES[status] };
  }
}
