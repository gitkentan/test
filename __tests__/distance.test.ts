import { distanceLabel, publicDistance } from '../src/domain/distance';

/** Distance / Location（仕様書 §10, §31「exact GPS を公開しない」）。 */
describe('distance', () => {
  it('丸めたバケットのラベルだけを返す', () => {
    expect(distanceLabel(0.4)).toBe('1km以内');
    expect(distanceLabel(1)).toBe('1km以内');
    expect(distanceLabel(1.2)).toBe('3km以内');
    expect(distanceLabel(4.9)).toBe('5km以内');
    expect(distanceLabel(9.9)).toBe('10km以内');
    expect(distanceLabel(24)).toBe('10km以上');
  });

  it('正確な距離をラベルへ出さない', () => {
    expect(distanceLabel(1.2)).not.toContain('1.2');
  });

  it('位置が不明なら距離を公開しない', () => {
    expect(publicDistance(null, { latitude: 35.6, longitude: 139.7 })).toEqual({
      distanceKm: null,
      distanceLabel: '距離は非公開',
    });
  });

  it('緯度経度から求めた距離をラベル化する', () => {
    const shibuya = { latitude: 35.6595, longitude: 139.7005 };
    const shinjuku = { latitude: 35.6896, longitude: 139.7006 };
    const result = publicDistance(shibuya, shinjuku);

    expect(result.distanceKm).toBeGreaterThan(3);
    expect(result.distanceKm).toBeLessThan(4);
    expect(result.distanceLabel).toBe('5km以内');
  });
});
