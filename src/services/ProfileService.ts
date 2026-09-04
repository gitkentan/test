import * as Location from 'expo-location';
import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import type { ProfileDraft, Repositories } from '../data/repositories';
import type { User, UserId } from '../domain/types';
import type { PhotoUploadService } from './PhotoUploadService';

/**
 * Profile / Onboarding / Location（仕様書 §17, §18, §10）。
 */
export class ProfileService {
  constructor(
    private readonly repos: Repositories,
    private readonly photos: PhotoUploadService,
  ) {}

  getUser(userId: UserId): Promise<User | null> {
    return this.repos.users.getUser(userId);
  }

  /**
   * Onboarding の完了。
   * ImagePicker が返すのは端末内のローカル URI なので、保存の前に配信 URL へ差し替える。
   */
  async completeOnboarding(userId: UserId, draft: ProfileDraft): Promise<User> {
    const photos = await this.photos.uploadAll(userId, draft.photos);
    const user = await this.repos.users.createProfile(userId, { ...draft, photos });
    analytics.track(AnalyticsEvent.signupCompleted);
    return user;
  }

  async updateProfile(userId: UserId, patch: Partial<ProfileDraft>): Promise<User> {
    const photos = patch.photos ? await this.photos.uploadAll(userId, patch.photos) : undefined;
    return this.repos.users.updateProfile(userId, photos ? { ...patch, photos } : patch);
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
