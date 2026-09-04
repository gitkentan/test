/**
 * fixture の開発環境限定（仕様書 §0「ハードコードした fake user を本番ロジックに混ぜない」）。
 *
 * `createDevSeed()` は `allowDevFixtures` を見て seed 関数を返すか決める。
 * 本番ビルド（__DEV__ === false）では必ず undefined を返し、seed は一切走らない。
 */

const loadSeed = () => {
  let created: (() => void) | undefined;
  jest.isolateModules(() => {
    created = require('../src/data/fixtures/devSeed').createDevSeed();
  });
  return created;
};

describe('dev fixtures', () => {
  const originalDev = (global as { __DEV__?: boolean }).__DEV__;
  const originalDisable = process.env.EXPO_PUBLIC_DISABLE_FIXTURES;

  afterEach(() => {
    (global as { __DEV__?: boolean }).__DEV__ = originalDev;
    if (originalDisable === undefined) {
      delete process.env.EXPO_PUBLIC_DISABLE_FIXTURES;
    } else {
      process.env.EXPO_PUBLIC_DISABLE_FIXTURES = originalDisable;
    }
  });

  it('開発環境では seed 関数を返す', () => {
    (global as { __DEV__?: boolean }).__DEV__ = true;
    delete process.env.EXPO_PUBLIC_DISABLE_FIXTURES;

    expect(loadSeed()).toBeInstanceOf(Function);
  });

  it('本番ビルドでは seed 関数を返さない', () => {
    (global as { __DEV__?: boolean }).__DEV__ = false;
    delete process.env.EXPO_PUBLIC_DISABLE_FIXTURES;

    expect(loadSeed()).toBeUndefined();
  });

  it('EXPO_PUBLIC_DISABLE_FIXTURES=1 なら開発環境でも投入しない', () => {
    (global as { __DEV__?: boolean }).__DEV__ = true;
    process.env.EXPO_PUBLIC_DISABLE_FIXTURES = '1';

    expect(loadSeed()).toBeUndefined();
  });

  it('seed を渡さない backend には fixture ユーザーが存在しない', async () => {
    const { createTestBackend } = require('./helpers');
    const { backend } = createTestBackend();
    await backend.ready();

    // createTestBackend は seed を渡していない = 本番と同じ空の状態
    expect(await backend.getUser('fixture_1')).toBeNull();
  });

  it('fixture ユーザーの id は fixture_ 接頭辞で識別できる', () => {
    (global as { __DEV__?: boolean }).__DEV__ = true;
    delete process.env.EXPO_PUBLIC_DISABLE_FIXTURES;

    const seed = loadSeed();
    const db = {
      version: 1,
      authSession: null,
      credentials: {},
      users: {} as Record<string, { id: string }>,
      statuses: {},
      swipes: [],
      sessions: [],
      conversations: [],
      messages: [],
      blocks: [],
      reports: [],
      verifications: [],
    };
    (seed as unknown as (db: unknown, now: number) => void)(db, Date.now());

    const ids = Object.keys(db.users);
    expect(ids.length).toBeGreaterThan(0);
    expect(ids.every((id) => id.startsWith('fixture_'))).toBe(true);
  });
});
