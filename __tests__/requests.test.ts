import { DomainError } from '../src/domain/errors';
import { createTestBackend, createVerifiedUser, draft } from './helpers';

/** Acceptance Criteria — Request（仕様書 §11, §25, §29, §31）。 */
describe('Session Request', () => {
  it('OFF 状態では直接送れず、ON にしてから送れる', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');

    await expect(backend.sendRequest('a', 'b')).rejects.toMatchObject({ code: 'SESSION_OFF' });

    await backend.turnSessionOn('a', 'drinks');
    const outcome = await backend.sendRequest('a', 'b');

    expect(outcome.kind).toBe('requested');
  });

  it('年齢未確認では Session ON も Request もできない', async () => {
    const { backend } = createTestBackend();
    await backend.createProfile('a', draft({ name: 'a' }));
    await createVerifiedUser(backend, 'b');

    await expect(backend.turnSessionOn('a', 'drinks')).rejects.toMatchObject({
      code: 'AGE_NOT_VERIFIED',
    });
    await expect(backend.sendRequest('a', 'b')).rejects.toBeInstanceOf(DomainError);
  });

  it('同じ相手への duplicate request を作らない', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');
    await backend.turnSessionOn('a', 'drinks');

    await backend.sendRequest('a', 'b');
    await expect(backend.sendRequest('a', 'b')).rejects.toMatchObject({
      code: 'DUPLICATE_REQUEST',
    });
  });

  it('自分自身には送れない', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await backend.turnSessionOn('a', 'drinks');

    await expect(backend.sendRequest('a', 'a')).rejects.toMatchObject({ code: 'SELF_REQUEST' });
  });

  it('存在しない相手には送れない', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await backend.turnSessionOn('a', 'drinks');

    await expect(backend.sendRequest('a', 'ghost')).rejects.toMatchObject({
      code: 'USER_UNAVAILABLE',
    });
  });

  it('期限切れの Request からは Session を作らない', async () => {
    const { backend, advanceHours } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');

    await backend.turnSessionOn('a', 'drinks');
    await backend.sendRequest('a', 'b');

    // a の Session TTL（6時間）を超えると、a の Request も expire する。
    advanceHours(7);

    await backend.turnSessionOn('b', 'drinks');
    const outcome = await backend.sendRequest('b', 'a');

    expect(outcome.kind).toBe('requested');
    expect(await backend.listSessions('a')).toHaveLength(0);
  });

  it('Skip した相手はクールダウン中は Discovery に出さず、24時間後に戻る', async () => {
    const { backend, advanceHours } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');

    await backend.skip('a', 'b');
    expect((await backend.loadDiscovery('a')).candidates).toHaveLength(0);

    advanceHours(25);
    expect((await backend.loadDiscovery('a')).candidates.map((c) => c.userId)).toEqual(['b']);
  });
});
