import type { Coordinates } from './types';

/**
 * Distance / Location（仕様書 §10）
 *
 * GPS は内部 ranking のためだけに使う。
 * 他ユーザーへ公開してよいのは丸めたラベルだけで、`1.2km` のような正確な距離は出さない。
 */

const EARTH_RADIUS_KM = 6371;

const toRadians = (degrees: number) => (degrees * Math.PI) / 180;

/** 2点間の大円距離（km）。内部利用のみ。 */
export function distanceKm(a: Coordinates, b: Coordinates): number {
  const dLat = toRadians(b.latitude - a.latitude);
  const dLon = toRadians(b.longitude - a.longitude);
  const lat1 = toRadians(a.latitude);
  const lat2 = toRadians(b.latitude);

  const h =
    Math.sin(dLat / 2) ** 2 + Math.sin(dLon / 2) ** 2 * Math.cos(lat1) * Math.cos(lat2);
  return 2 * EARTH_RADIUS_KM * Math.asin(Math.min(1, Math.sqrt(h)));
}

/** 表示に使ってよい距離バケット。これ以外の粒度で距離を出さない。 */
const BUCKETS: readonly { maxKm: number; label: string }[] = [
  { maxKm: 1, label: '1km以内' },
  { maxKm: 3, label: '3km以内' },
  { maxKm: 5, label: '5km以内' },
  { maxKm: 10, label: '10km以内' },
];

const FAR_LABEL = '10km以上';
const UNKNOWN_LABEL = '距離は非公開';

export function distanceLabel(km: number | null): string {
  if (km === null || !Number.isFinite(km)) return UNKNOWN_LABEL;
  const bucket = BUCKETS.find((b) => km <= b.maxKm);
  return bucket ? bucket.label : FAR_LABEL;
}

/**
 * 相手に渡してよい形へ落とす。
 * 緯度経度・駅名・住所・リアルタイム地図は返さない（§10）。
 */
export function publicDistance(
  viewer: Coordinates | null,
  target: Coordinates | null,
): { distanceKm: number | null; distanceLabel: string } {
  if (!viewer || !target) {
    return { distanceKm: null, distanceLabel: UNKNOWN_LABEL };
  }
  const km = distanceKm(viewer, target);
  return { distanceKm: km, distanceLabel: distanceLabel(km) };
}
