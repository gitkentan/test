import { MIN_DISCOVERY_POOL } from '../src/config/constants';
import { rankCandidates, resolveDiscoveryPool, type RankableCandidate } from '../src/domain/discovery';

/** Acceptance Criteria — Discovery（仕様書 §8, §9, §31）。 */

const ORIGIN = { latitude: 35.6595, longitude: 139.7005 };
const now = Date.UTC(2026, 0, 1, 12, 0, 0);

/** ranking を決定的にするため、テストでは jitter を固定する。 */
const noRandom = () => 0;

function at(km: number): { latitude: number; longitude: number } {
  return { latitude: ORIGIN.latitude + km / 111, longitude: ORIGIN.longitude };
}

function candidate(
  userId: string,
  overrides: Partial<RankableCandidate> = {},
): RankableCandidate {
  return {
    userId,
    sessionOn: false,
    intent: null,
    location: at(1),
    lastActiveAt: now,
    ...overrides,
  };
}

describe('Discovery ranking', () => {
  it('Session ON かつ same intent を最優先し、Session OFF も候補に残す', () => {
    const ranked = rankCandidates(
      [
        candidate('off', { sessionOn: false }),
        candidate('on-other', { sessionOn: true, intent: 'cafe' }),
        candidate('on-same', { sessionOn: true, intent: 'drinks' }),
      ],
      { viewerLocation: ORIGIN, viewerIntent: 'drinks', radiusKm: 5, now, random: noRandom },
    );

    expect(ranked.map((r) => r.candidate.userId)).toEqual(['on-same', 'on-other', 'off']);
    expect(ranked.map((r) => r.priority)).toEqual([1, 2, 3]);
    // Session ON だけに限定して表示してはいけない（§8）。
    expect(ranked.some((r) => !r.candidate.sessionOn)).toBe(true);
  });

  it('半径外は priority 4 の fallback として後ろへ回る', () => {
    const ranked = rankCandidates(
      [
        candidate('far', { sessionOn: true, intent: 'drinks', location: at(20) }),
        candidate('near-off', { sessionOn: false, location: at(2) }),
      ],
      { viewerLocation: ORIGIN, viewerIntent: 'drinks', radiusKm: 5, now, random: noRandom },
    );

    expect(ranked[0].candidate.userId).toBe('near-off');
    expect(ranked[1].priority).toBe(4);
  });

  it('同 priority 内では距離が近い順になる', () => {
    const ranked = rankCandidates(
      [
        candidate('c', { sessionOn: true, intent: 'drinks', location: at(4) }),
        candidate('a', { sessionOn: true, intent: 'drinks', location: at(0.5) }),
        candidate('b', { sessionOn: true, intent: 'drinks', location: at(2) }),
      ],
      { viewerLocation: ORIGIN, viewerIntent: 'drinks', radiusKm: 5, now, random: noRandom },
    );

    expect(ranked.map((r) => r.candidate.userId)).toEqual(['a', 'b', 'c']);
  });

  it('同距離なら直近まで active だった人が先に出る', () => {
    const ranked = rankCandidates(
      [
        candidate('stale', { location: at(1), lastActiveAt: now - 48 * 3_600_000 }),
        candidate('fresh', { location: at(1), lastActiveAt: now }),
      ],
      { viewerLocation: ORIGIN, viewerIntent: null, radiusKm: 5, now, random: noRandom },
    );

    expect(ranked[0].candidate.userId).toBe('fresh');
  });

  it('位置が不明な相手も板から落とさない', () => {
    const ranked = rankCandidates([candidate('unknown', { location: null })], {
      viewerLocation: ORIGIN,
      viewerIntent: null,
      radiusKm: 5,
      now,
      random: noRandom,
    });

    expect(ranked).toHaveLength(1);
    expect(ranked[0].priority).toBe(3);
    expect(ranked[0].distanceKm).toBeNull();
  });
});

describe('Cold start / radius fallback', () => {
  const options = {
    viewerLocation: ORIGIN,
    viewerIntent: 'drinks' as const,
    now,
    random: noRandom,
  };

  it('pool が MIN_DISCOVERY_POOL 未満なら次の radius へ広げる', () => {
    // 5km 圏内には数人しかいないが、30km まで広げれば十分な数がいる。
    const near = Array.from({ length: 3 }, (_, i) => candidate(`near-${i}`, { location: at(2) }));
    const far = Array.from({ length: MIN_DISCOVERY_POOL }, (_, i) =>
      candidate(`far-${i}`, { location: at(25) }),
    );

    const result = resolveDiscoveryPool([...near, ...far], options);

    expect(result.radiusKm).toBe(30);
    expect(result.poolIsLow).toBe(false);
    expect(result.ranked.filter((r) => r.priority < 4).length).toBeGreaterThanOrEqual(
      MIN_DISCOVERY_POOL,
    );
  });

  it('広げても足りなければ pool が少ない状態として返す（Empty にはしない）', () => {
    const result = resolveDiscoveryPool([candidate('only', { location: at(2) })], options);

    expect(result.poolIsLow).toBe(true);
    expect(result.isEmpty).toBe(false);
    expect(result.ranked).toHaveLength(1);
  });

  it('候補が0のときだけ Empty State になる', () => {
    const result = resolveDiscoveryPool([], options);

    expect(result.isEmpty).toBe(true);
    expect(result.ranked).toHaveLength(0);
  });
});
