import * as Location from 'expo-location';
import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import type { ProfileDraft, Repositories } from '../data/repositories';
import type { User, UserId } from '../domain/types';

/**
 * Profile / Onboarding / Location（仕様書 §17, §18, §10）。
 */
export class ProfileService {
  constructor(private readonly repos: Repositories) {}

  getUser(userId: UserId): Promise<User | null> {
    return this.repos.users.getUser(userId);
  }

  async completeOnboarding(userId: UserId, draft: ProfileDraft): Promise<User> {
    const user = await this.repos.users.createProfile(userId, draft);
    analytics.track(AnalyticsEvent.signupCompleted);
    return user;
  }

  updateProfile(userId: UserId, patch: Partial<ProfileDraft>): Promise<User> {
    return this.repos.users.updateProfile(userId, patch);
  }

  /**
   * 位置情報の取得。
   * 会える現実性を伝えるためだけに使い、正確な座標は他ユーザーへ公開しない（§10）。
   */
  async requestLocation(userId: UserId): Promise<User | null> {
    const { status } = await Location.requestForegroundPermissionsAsync();
    if (status !== Location.PermissionStatus.GRANTED) return null;

    const position = await Location.getCurrentPositionAsync({
      accuracy: Location.Accuracy.Balanced,
    });
    return this.repos.users.updateLocation(
      userId,
      position.coords.latitude,
      position.coords.longitude,
    );
  }

  touchActivity(userId: UserId): Promise<void> {
    return this.repos.users.touchActivity(userId);
  }
}
