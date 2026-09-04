import {
  DISCOVERY_RADIUS_STEPS_KM,
  MIN_DISCOVERY_POOL,
} from '../config/constants';
import { distanceKm } from './distance';
import type { Coordinates, Intent, Millis, UserId } from './types';

/**
 * Discovery Ranking / Cold Start（仕様書 §8, §9）
 *
 * 最重要は「板を空にしないこと」。
 * Session ON の人だけに限定して表示してはいけない。通常ユーザーも表示対象にする。
 *
 *   Priority 1: Session ON + Same Intent + 半径内
 *   Priority 2: Session ON + Different Intent + 半径内
 *   Priority 3: Session OFF + 半径内
 *   Priority 4: 半径を広げた fallback
 *
 * 高度な ML recommendation は作らない。
 */

export interface RankableCandidate {
  userId: UserId;
  sessionOn: boolean;
  intent: Intent | null;
  location: Coordinates | null;
  lastActiveAt: Millis;
}

export type DiscoveryPriority = 1 | 2 | 3 | 4;

export interface RankedCandidate<T extends RankableCandidate> {
  candidate: T;
  priority: DiscoveryPriority;
  distanceKm: number | null;
}

export interface RankOptions {
  viewerLocation: Coordinates | null;
  viewerIntent: Intent | null;
  /** この半径（km）までを「preferred radius 内」として扱う。 */
  radiusKm: number;
  now?: Millis;
  /**
   * 同 priority 内の light randomization に使うシード。
   * 省略時は Math.random。テストでは固定シードを渡す。
   */
  random?: () => number;
}

function priorityOf(
  candidate: RankableCandidate,
  viewerIntent: Intent | null,
  withinRadius: boolean,
): DiscoveryPriority {
  if (!withinRadius) return 4;
  if (!candidate.sessionOn) return 3;
  if (viewerIntent !== null && candidate.intent === viewerIntent) return 1;
  return 2;
}

/**
 * 同 priority 内の並び。
 *   1. distance
 *   2. recent activity
 *   3. light randomization
 * 程度のシンプルな ranking でよい（§8）。
 */
const RANDOM_WEIGHT = 0.35;

function sortKey(
  ranked: RankedCandidate<RankableCandidate>,
  now: Millis,
  jitter: number,
): number {
  // 距離不明は最遠として扱い、板から落とさずに後ろへ回す。
  const distanceScore = ranked.distanceKm ?? 100;
  const hoursSinceActive = Math.max(0, (now - ranked.candidate.lastActiveAt) / 3_600_000);
  const recencyScore = Math.min(hoursSinceActive, 72) / 24;
  return distanceScore + recencyScore + jitter * RANDOM_WEIGHT;
}

export function rankCandidates<T extends RankableCandidate>(
  candidates: readonly T[],
  options: RankOptions,
): RankedCandidate<T>[] {
  const now = options.now ?? Date.now();
  const random = options.random ?? Math.random;

  const ranked = candidates.map((candidate) => {
    const km =
      options.viewerLocation && candidate.location
        ? distanceKm(options.viewerLocation, candidate.location)
        : null;
    // 距離不明なユーザーは半径内とみなす。位置未許可の相手を板から消さないため。
    const withinRadius = km === null ? true : km <= options.radiusKm;
    return {
      candidate,
      priority: priorityOf(candidate, options.viewerIntent, withinRadius),
      distanceKm: km,
      jitter: random(),
    };
  });

  return ranked
    .sort((a, b) => {
      if (a.priority !== b.priority) return a.priority - b.priority;
      return sortKey(a, now, a.jitter) - sortKey(b, now, b.jitter);
    })
    .map(({ candidate, priority, distanceKm: km }) => ({
      candidate,
      priority,
      distanceKm: km,
    }));
}

export interface RadiusResolution<T extends RankableCandidate> {
  ranked: RankedCandidate<T>[];
  /** 実際に採用した半径（km）。 */
  radiusKm: number;
  /** 半径を広げてもプールが足りなかったか。UI の文言を分ける材料にする（§9）。 */
  poolIsLow: boolean;
  isEmpty: boolean;
}

/**
 * 候補が MIN_DISCOVERY_POOL 未満なら次の radius へ広げる（§9）。
 * それでも足りない場合のみ Empty State を出す。
 */
export function resolveDiscoveryPool<T extends RankableCandidate>(
  candidates: readonly T[],
  options: Omit<RankOptions, 'radiusKm'> & { radiusSteps?: readonly number[] },
): RadiusResolution<T> {
  const steps = options.radiusSteps ?? DISCOVERY_RADIUS_STEPS_KM;
  let lastResult: RankedCandidate<T>[] = [];
  let lastRadius = steps[steps.length - 1] ?? 0;

  for (const radiusKm of steps) {
    const ranked = rankCandidates(candidates, { ...options, radiusKm });
    const withinRadius = ranked.filter((r) => r.priority < 4);
    lastResult = ranked;
    lastRadius = radiusKm;
    if (withinRadius.length >= MIN_DISCOVERY_POOL) {
      return { ranked, radiusKm, poolIsLow: false, isEmpty: ranked.length === 0 };
    }
  }

  return {
    ranked: lastResult,
    radiusKm: lastRadius,
    poolIsLow: lastResult.length > 0,
    isEmpty: lastResult.length === 0,
  };
}
