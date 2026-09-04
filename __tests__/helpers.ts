import { SessionBackend } from '../src/data/memory/backend';
import { MemoryStorage } from '../src/data/storage';
import type { ProfileDraft } from '../src/data/repositories';
import type { UserId } from '../src/domain/types';

/**
 * テスト用のバックエンド。
 * fixture seed は渡さないので、本番と同じ「空の状態」から検証できる。
 */
export function createTestBackend(startAt = Date.UTC(2026, 0, 1, 12, 0, 0)) {
  const clock = { now: startAt };
  const backend = new SessionBackend({
    storage: new MemoryStorage(),
    now: () => clock.now,
  });
  return {
    backend,
    clock,
    advanceHours(hours: number) {
      clock.now += hours * 3_600_000;
    },
  };
}

export function draft(overrides: Partial<ProfileDraft> = {}): ProfileDraft {
  return {
    name: 'Test',
    birthDate: '1998-06-15',
    gender: 'woman',
    discoveryPreference: 'everyone',
    bio: '',
    interests: [],
    photos: [{ id: 'p1', uri: 'https://example.test/p1.jpg' }],
    ...overrides,
  };
}

/** プロフィール登録 + 年齢確認済み + 位置ありのユーザーを1人用意する。 */
export async function createVerifiedUser(
  backend: SessionBackend,
  id: UserId,
  overrides: Partial<ProfileDraft> = {},
  location = { latitude: 35.6595, longitude: 139.7005 },
) {
  await backend.createProfile(id, draft({ name: id, ...overrides }));
  await backend.applyVerificationResult({
    userId: id,
    ageVerified: true,
    verifiedAt: Date.now(),
    providerReference: `test-${id}`,
  });
  await backend.updateLocation(id, location.latitude, location.longitude);
  return id;
}
