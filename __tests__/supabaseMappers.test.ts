import { DomainError } from '../src/domain/errors';
import {
  mapConversationSummary,
  mapDiscoveryCandidate,
  mapMessage,
  mapSession,
  mapSessionStatus,
  mapUser,
  toDomainError,
  type DiscoveryRow,
  type UserRow,
} from '../src/data/supabase/mappers';

/**
 * DB の行から domain の型への変換（項目4・5）。
 * ここは I/O を持たないので、実バックエンドに繋がなくても検証できる。
 */

const userRow: UserRow = {
  id: 'u1',
  name: 'Yuna',
  birth_date: '1998-06-15',
  gender: 'woman',
  discovery_preference: 'everyone',
  bio: 'よろしく',
  interests: ['ワイン'],
  photos: [{ id: 'p1', uri: 'https://cdn.test/p1.jpg' }],
  latitude: 35.6595,
  longitude: 139.7005,
  age_verified: true,
  age_verified_at: '2026-01-01T00:00:00.000Z',
  age_verification_reference: 'agv_1',
  last_active_at: '2026-01-01T12:00:00.000Z',
  created_at: '2025-12-01T00:00:00.000Z',
  updated_at: '2026-01-01T12:00:00.000Z',
};

describe('user の変換', () => {
  it('snake_case を camelCase へ、timestamptz を epoch millis へ写す', () => {
    const user = mapUser(userRow);

    expect(user.birthDate).toBe('1998-06-15');
    expect(user.discoveryPreference).toBe('everyone');
    expect(user.ageVerifiedAt).toBe(Date.UTC(2026, 0, 1));
    expect(user.lastActiveAt).toBe(Date.UTC(2026, 0, 1, 12));
    expect(user.location).toEqual({ latitude: 35.6595, longitude: 139.7005 });
  });

  it('緯度経度が欠けていれば location は null', () => {
    expect(mapUser({ ...userRow, latitude: null, longitude: null }).location).toBeNull();
    expect(mapUser({ ...userRow, latitude: 35.6, longitude: null }).location).toBeNull();
  });

  it('null や壊れた photos / interests でも落ちない', () => {
    const user = mapUser({ ...userRow, photos: null, interests: null });
    expect(user.photos).toEqual([]);
    expect(user.interests).toEqual([]);

    // 形の合わない要素は捨てる（blank state を作らない）
    const messy = mapUser({ ...userRow, photos: [{ id: 'ok', uri: 'u' }, { id: 1 }, null, 'x'] });
    expect(messy.photos).toEqual([{ id: 'ok', uri: 'u' }]);
  });
});

describe('discovery の変換', () => {
  const row: DiscoveryRow = {
    user_id: 'u2',
    name: 'Mei',
    age: 23,
    photos: [{ id: 'p', uri: 'https://cdn.test/m.jpg' }],
    bio: '',
    interests: [],
    session_on: true,
    intent: 'drinks',
    distance_label: '3km以内',
    last_active_at: '2026-01-01T12:00:00.000Z',
    priority: 1,
    radius_km: 5,
    pool_is_low: false,
  };

  it('サーバが返した距離ラベルをそのまま使う', () => {
    expect(mapDiscoveryCandidate(row).distanceLabel).toBe('3km以内');
  });

  it('正確な距離は保持しない（サーバが返さない）', () => {
    // §10: 緯度経度も距離の数値も client へ渡さない。
    const candidate = mapDiscoveryCandidate(row);
    expect(candidate.distanceKm).toBeNull();
    expect(candidate).not.toHaveProperty('location');
    expect(JSON.stringify(candidate)).not.toContain('latitude');
  });
});

describe('session / status / message の変換', () => {
  it('session status の期限を millis へ写す', () => {
    const status = mapSessionStatus({
      user_id: 'u1',
      session_on: true,
      current_intent: 'cafe',
      started_at: '2026-01-01T12:00:00.000Z',
      expires_at: '2026-01-01T18:00:00.000Z',
      updated_at: '2026-01-01T12:00:00.000Z',
    });
    expect(status.expiresAt! - status.startedAt!).toBe(6 * 3_600_000);
  });

  it('OFF の status は intent も expiry も null', () => {
    const status = mapSessionStatus({
      user_id: 'u1',
      session_on: false,
      current_intent: null,
      started_at: null,
      expires_at: null,
      updated_at: '2026-01-01T12:00:00.000Z',
    });
    expect(status.currentIntent).toBeNull();
    expect(status.expiresAt).toBeNull();
  });

  it('session のペアと intent を写す', () => {
    const session = mapSession({
      id: 's1',
      user_a_id: 'a',
      user_b_id: 'b',
      user_a_intent: 'drinks',
      user_b_intent: 'food',
      started_at: '2026-01-01T12:00:00.000Z',
      active_until: '2026-01-01T18:00:00.000Z',
      status: 'active',
    });
    expect(session.userAIntent).toBe('drinks');
    expect(session.activeUntil).toBe(Date.UTC(2026, 0, 1, 18));
  });

  it('画像メッセージは text を持たない', () => {
    const message = mapMessage({
      id: 'm1',
      conversation_id: 'c1',
      sender_id: 'u1',
      type: 'image',
      text: null,
      image_url: 'https://cdn.test/i.jpg',
      created_at: '2026-01-01T12:00:00.000Z',
    });
    expect(message.type).toBe('image');
    expect(message.text).toBeNull();
    expect(message.imageUrl).toBe('https://cdn.test/i.jpg');
  });

  it('会話一覧は相手の写真を1枚だけ持つ', () => {
    const summary = mapConversationSummary({
      conversation_id: 'c1',
      session_id: 's1',
      partner_id: 'u2',
      partner_name: 'Yuna',
      partner_photo: { id: 'p', uri: 'https://cdn.test/y.jpg' },
      is_active: true,
      partner_intent: 'drinks',
      last_message_preview: '20時に渋谷で',
      last_message_at: '2026-01-01T12:30:00.000Z',
      active_until: '2026-01-01T18:00:00.000Z',
    });
    expect(summary.partnerPhoto).toEqual({ id: 'p', uri: 'https://cdn.test/y.jpg' });
    expect(summary.isActive).toBe(true);
  });

  it('相手の写真が無くても落ちない', () => {
    const summary = mapConversationSummary({
      conversation_id: 'c1',
      session_id: 's1',
      partner_id: 'u2',
      partner_name: 'Yuna',
      partner_photo: null,
      is_active: false,
      partner_intent: null,
      last_message_preview: null,
      last_message_at: null,
      active_until: '2026-01-01T18:00:00.000Z',
    });
    expect(summary.partnerPhoto).toBeNull();
    expect(summary.lastMessageAt).toBeNull();
  });
});

describe('RPC エラーの変換', () => {
  it('RPC が raise した業務エラーを DomainError に戻す', () => {
    for (const code of [
      'SESSION_OFF',
      'AGE_NOT_VERIFIED',
      'ACTIVE_SESSION_LIMIT',
      'DUPLICATE_REQUEST',
      'BLOCKED',
      'SELF_REQUEST',
    ] as const) {
      const error = toDomainError({ message: code });
      expect(error).toBeInstanceOf(DomainError);
      expect((error as DomainError).code).toBe(code);
    }
  });

  it('Postgres が付ける前置きが付いていても拾える', () => {
    const error = toDomainError({
      message: 'unhandled exception: SESSION_OFF (SQLSTATE P0001)',
    });
    expect((error as DomainError).code).toBe('SESSION_OFF');
  });

  it('一意制約違反は duplicate として扱う', () => {
    const error = toDomainError({
      message: 'duplicate key value violates unique constraint "swipes_one_active_request"',
    });
    expect((error as DomainError).code).toBe('DUPLICATE_REQUEST');
  });

  it('年齢の check 制約違反を拾う', () => {
    const error = toDomainError({
      message: 'new row violates check constraint "users_minimum_age"',
    });
    expect((error as DomainError).code).toBe('UNDER_MINIMUM_AGE');
  });

  it('未知のエラーは汎用メッセージにする', () => {
    const error = toDomainError({ message: 'connection reset' });
    expect(error).not.toBeInstanceOf(DomainError);
    expect(error.message).toBe('connection reset');
  });
});
