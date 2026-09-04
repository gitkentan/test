import { SessionBackend } from '../src/data/memory/backend';
import { MemoryStorage } from '../src/data/storage';
import { draft } from './helpers';

/**
 * Definition of Done（仕様書 §32）。
 *
 * 新規ユーザーが signup → onboarding → age verification → Session ON → swipe
 * → mutual Session → chat まで完走できること。
 */
describe('Session core loop', () => {
  it('signup から chat まで完走できる', async () => {
    const clock = { now: Date.UTC(2026, 0, 1, 20, 0, 0) };
    const storage = new MemoryStorage();
    const backend = new SessionBackend({ storage, now: () => clock.now });

    // 1. Signup
    const auth = await backend.signUp('me@example.test');
    expect(auth.userId).toBeTruthy();

    // 2. Onboarding
    await backend.createProfile(auth.userId, draft({ name: 'Me' }));
    await backend.updateLocation(auth.userId, 35.6595, 139.7005);

    // 3. Age verification（外部プロバイダの結果を受け取る）
    await backend.applyVerificationResult({
      userId: auth.userId,
      ageVerified: true,
      verifiedAt: clock.now,
      providerReference: 'provider-ref-1',
    });

    // 相手を用意する
    await backend.createProfile('partner', draft({ name: 'Yuna' }));
    await backend.updateLocation('partner', 35.6605, 139.7015);
    await backend.applyVerificationResult({
      userId: 'partner',
      ageVerified: true,
      verifiedAt: clock.now,
      providerReference: 'provider-ref-2',
    });
    await backend.turnSessionOn('partner', 'drinks');

    // 4. Session ON
    const status = await backend.turnSessionOn(auth.userId, 'drinks');
    expect(status.sessionOn).toBe(true);

    // 5. Swipe（Discovery に相手が出る。距離は丸めたラベルのみ）
    const discovery = await backend.loadDiscovery(auth.userId);
    expect(discovery.candidates.map((c) => c.userId)).toContain('partner');
    const candidate = discovery.candidates.find((c) => c.userId === 'partner')!;
    expect(candidate.distanceLabel).toBe('1km以内');
    expect(candidate).not.toHaveProperty('location');

    // 6. Mutual Request → Session
    await backend.sendRequest('partner', auth.userId);
    const outcome = await backend.sendRequest(auth.userId, 'partner');
    expect(outcome.kind).toBe('matched');
    if (outcome.kind !== 'matched') throw new Error('unreachable');

    // 7. Chat
    await backend.sendText(outcome.conversationId, auth.userId, '今から飲みに行けますか？');
    const messages = await backend.listMessages(outcome.conversationId, auth.userId);
    expect(messages).toHaveLength(1);

    // 8. Session の自動失効
    clock.now += 7 * 3_600_000;
    const expired = await backend.getStatus(auth.userId);
    expect(expired.sessionOn).toBe(false);
    expect(expired.currentIntent).toBeNull();

    // 9. Chat は残る
    expect(await backend.listMessages(outcome.conversationId, auth.userId)).toHaveLength(1);
  });

  it('アプリを再起動しても状態が正しく復元され、期限切れの ON は復活しない', async () => {
    const clock = { now: Date.UTC(2026, 0, 1, 20, 0, 0) };
    const storage = new MemoryStorage();

    const first = new SessionBackend({ storage, now: () => clock.now });
    const auth = await first.signUp('me@example.test');
    await first.createProfile(auth.userId, draft({ name: 'Me' }));
    await first.applyVerificationResult({
      userId: auth.userId,
      ageVerified: true,
      verifiedAt: clock.now,
      providerReference: 'ref',
    });
    await first.turnSessionOn(auth.userId, 'cafe');

    // 同じ storage から起動し直す = アプリ再起動
    const restarted = new SessionBackend({ storage, now: () => clock.now });
    expect(await restarted.getCurrentSession()).toEqual(auth);
    const restored = await restarted.getStatus(auth.userId);
    expect(restored.sessionOn).toBe(true);
    expect(restored.currentIntent).toBe('cafe');

    // TTL を過ぎてから起動し直すと OFF に戻っている
    clock.now += 7 * 3_600_000;
    const afterExpiry = new SessionBackend({ storage, now: () => clock.now });
    const expired = await afterExpiry.getStatus(auth.userId);
    expect(expired.sessionOn).toBe(false);
    expect(expired.currentIntent).toBeNull();
  });
});
