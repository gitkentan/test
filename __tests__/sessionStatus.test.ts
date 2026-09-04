import { SESSION_TTL_HOURS, SESSION_TTL_MS } from '../src/config/constants';
import { isExpired, offStatus, settleExpiry, turnOn } from '../src/domain/sessionStatus';

/** Acceptance Criteria — Session ON（仕様書 §31）。 */
describe('Session ON', () => {
  const now = Date.UTC(2026, 0, 1, 12, 0, 0);

  it('ON にすると expires_at が TTL 後に設定される', () => {
    const status = turnOn('u1', 'drinks', now);
    expect(status.sessionOn).toBe(true);
    expect(status.currentIntent).toBe('drinks');
    expect(status.expiresAt).toBe(now + SESSION_TTL_MS);
    expect(SESSION_TTL_HOURS).toBe(6);
  });

  it('Intent は1つだけ持ち、変更すると TTL がリセットされる', () => {
    const first = turnOn('u1', 'drinks', now);
    const second = turnOn('u1', 'cafe', now + 3 * 3_600_000);

    expect(second.currentIntent).toBe('cafe');
    expect(second.startedAt).toBe(now + 3 * 3_600_000);
    expect(second.expiresAt).toBeGreaterThan(first.expiresAt!);
  });

  it('期限に達すると自動的に OFF になる', () => {
    const status = turnOn('u1', 'drinks', now);
    const atExpiry = now + SESSION_TTL_MS;

    expect(isExpired(status, atExpiry - 1)).toBe(false);
    expect(isExpired(status, atExpiry)).toBe(true);

    const settled = settleExpiry(status, atExpiry);
    expect(settled.sessionOn).toBe(false);
    expect(settled.currentIntent).toBeNull();
  });

  it('期限切れの状態を読み出しても ON として復元されない', () => {
    // 端末に保存された ON をアプリ再起動後に読み直す状況を再現する。
    const persisted = turnOn('u1', 'food', now);
    const restored = settleExpiry(persisted, now + SESSION_TTL_MS + 60_000);

    expect(restored).toEqual(offStatus('u1', now + SESSION_TTL_MS + 60_000));
  });
});
