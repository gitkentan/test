import { MAX_ACTIVE_SESSIONS } from '../src/config/constants';
import { evaluateMutualMatch } from '../src/domain/matching';
import { turnOn } from '../src/domain/sessionStatus';
import { createTestBackend, createVerifiedUser } from './helpers';

/** Acceptance Criteria — Session（仕様書 §7, §12, §25, §30, §31）。 */
describe("Mutual Request → IT'S A SESSION", () => {
  it('相互 Request で Session が1件だけ生成される', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');
    await backend.turnSessionOn('a', 'drinks');
    await backend.turnSessionOn('b', 'cafe');

    expect((await backend.sendRequest('a', 'b')).kind).toBe('requested');
    const outcome = await backend.sendRequest('b', 'a');

    expect(outcome.kind).toBe('matched');
    if (outcome.kind !== 'matched') throw new Error('unreachable');

    expect(outcome.session.userAIntent).toBe('cafe');
    expect(outcome.session.userBIntent).toBe('drinks');
    expect(await backend.listSessions('a')).toHaveLength(1);
    expect(await backend.listSessions('b')).toHaveLength(1);
  });

  it('active_until は両者の Session expiry の早い方になる', async () => {
    const { backend, advanceHours } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');

    await backend.turnSessionOn('a', 'drinks');
    advanceHours(2); // a の期限のほうが先に来る
    await backend.turnSessionOn('b', 'drinks');

    await backend.sendRequest('a', 'b');
    const outcome = await backend.sendRequest('b', 'a');
    if (outcome.kind !== 'matched') throw new Error('expected a match');

    const aStatus = await backend.getStatus('a');
    expect(outcome.session.activeUntil).toBe(aStatus.expiresAt);
  });

  it('同時に相互 Request が飛んでも Session と Conversation は1件ずつ', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');
    await backend.turnSessionOn('a', 'drinks');
    await backend.turnSessionOn('b', 'drinks');

    const [first, second] = await Promise.all([
      backend.sendRequest('a', 'b'),
      backend.sendRequest('b', 'a'),
    ]);

    expect([first, second].filter((o) => o.kind === 'matched')).toHaveLength(1);
    expect(await backend.listSessions('a')).toHaveLength(1);
    expect(await backend.listConversations('a')).toHaveLength(1);
  });

  it('すでに Session がある相手への Request では2件目を作らない', async () => {
    // evaluateMutualMatch の重複ガードを直接検証する。
    // withMatchLock は実バックエンドでこの判定を直列化するためのもので、
    // 判定そのものが冪等であることは、この単体テストで担保する。
    const { clock } = createTestBackend();
    const now = clock.now;
    const session = {
      id: 'existing',
      userAId: 'a',
      userBId: 'b',
      userAIntent: 'drinks' as const,
      userBIntent: 'drinks' as const,
      startedAt: now,
      activeUntil: now + 3_600_000,
      status: 'active' as const,
    };
    const request = (id: string, senderId: string, receiverId: string) => ({
      id,
      senderId,
      receiverId,
      type: 'session_request' as const,
      senderIntent: 'drinks' as const,
      createdAt: now,
      expiresAt: now + 3_600_000,
      status: 'pending' as const,
    });

    const result = evaluateMutualMatch({
      outgoing: request('r1', 'a', 'b'),
      incomingSwipes: [request('r2', 'b', 'a')],
      senderStatus: turnOn('a', 'drinks', now),
      receiverStatus: turnOn('b', 'drinks', now),
      sessions: [session],
      now,
    });

    expect(result.kind).toBe('already_matched');
  });

  it('Active Session が上限に達すると新しい Request を送れない', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'me');
    await backend.turnSessionOn('me', 'drinks');

    for (let i = 0; i < MAX_ACTIVE_SESSIONS; i++) {
      const partner = `p${i}`;
      await createVerifiedUser(backend, partner);
      await backend.turnSessionOn(partner, 'drinks');
      await backend.sendRequest(partner, 'me');
      const outcome = await backend.sendRequest('me', partner);
      expect(outcome.kind).toBe('matched');
    }

    await createVerifiedUser(backend, 'extra');
    await expect(backend.sendRequest('me', 'extra')).rejects.toMatchObject({
      code: 'ACTIVE_SESSION_LIMIT',
    });

    expect(await backend.listSessions('me')).toHaveLength(MAX_ACTIVE_SESSIONS);
  });

  it('Active Session の期限が切れると枠が空き、Session は past になる', async () => {
    const { backend, advanceHours } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');
    await backend.turnSessionOn('a', 'drinks');
    await backend.turnSessionOn('b', 'drinks');
    await backend.sendRequest('a', 'b');
    await backend.sendRequest('b', 'a');

    advanceHours(7);

    const [session] = await backend.listSessions('a');
    expect(session.status).toBe('past');

    // Chat 履歴は残る（§12, §30）。
    const conversations = await backend.listConversations('a');
    expect(conversations).toHaveLength(1);
    expect(conversations[0].isActive).toBe(false);
  });

  it('すでに Session 中の相手は Discovery に戻らない', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');
    await backend.turnSessionOn('a', 'drinks');
    await backend.turnSessionOn('b', 'drinks');
    await backend.sendRequest('a', 'b');
    await backend.sendRequest('b', 'a');

    expect((await backend.loadDiscovery('a')).candidates).toHaveLength(0);
  });
});
