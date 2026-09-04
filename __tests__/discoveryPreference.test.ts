import {
  canSeeNonBinary,
  isMutuallyDiscoverable,
  preferenceIncludesGender,
  viewerWouldSee,
} from '../src/domain/discoveryPreference';
import type { DiscoveryPreference, Gender } from '../src/domain/types';
import { createTestBackend, createVerifiedUser } from './helpers';

const person = (gender: Gender, discoveryPreference: DiscoveryPreference) => ({
  gender,
  discoveryPreference,
});

/** Discovery の表示対象ルール（仕様書 §18）。 */
describe('discovery preference', () => {
  it('women / men はその性別だけを対象にする', () => {
    expect(preferenceIncludesGender('women', 'woman')).toBe(true);
    expect(preferenceIncludesGender('women', 'man')).toBe(false);
    expect(preferenceIncludesGender('men', 'man')).toBe(true);
    expect(preferenceIncludesGender('men', 'woman')).toBe(false);
  });

  it('everyone はすべての性別を対象にする', () => {
    for (const gender of ['woman', 'man', 'nonbinary'] as Gender[]) {
      expect(preferenceIncludesGender('everyone', gender)).toBe(true);
    }
  });

  it('non-binary は everyone を選んでいる相手にだけ表示される', () => {
    expect(canSeeNonBinary('everyone')).toBe(true);
    expect(canSeeNonBinary('women')).toBe(false);
    expect(canSeeNonBinary('men')).toBe(false);

    // 二値の絞り込みに non-binary を割り当てない。
    expect(viewerWouldSee(person('woman', 'women'), person('nonbinary', 'everyone'))).toBe(false);
    expect(viewerWouldSee(person('man', 'men'), person('nonbinary', 'everyone'))).toBe(false);
    expect(viewerWouldSee(person('man', 'everyone'), person('nonbinary', 'everyone'))).toBe(true);
  });

  it('non-binary ユーザー自身が誰を見るかは制限されない', () => {
    expect(viewerWouldSee(person('nonbinary', 'women'), person('woman', 'everyone'))).toBe(true);
    expect(viewerWouldSee(person('nonbinary', 'men'), person('man', 'everyone'))).toBe(true);
    expect(viewerWouldSee(person('nonbinary', 'everyone'), person('nonbinary', 'everyone'))).toBe(
      true,
    );
  });

  it('片側だけ条件を満たしても表示しない（相互成立が必要）', () => {
    // viewer は candidate を見たいが、candidate 側の設定に viewer が入っていない。
    const viewer = person('man', 'women');
    const candidate = person('woman', 'women');

    expect(viewerWouldSee(viewer, candidate)).toBe(true);
    expect(viewerWouldSee(candidate, viewer)).toBe(false);
    expect(isMutuallyDiscoverable(viewer, candidate)).toBe(false);
  });

  it('相互に条件を満たせば表示する', () => {
    expect(isMutuallyDiscoverable(person('man', 'women'), person('woman', 'men'))).toBe(true);
  });
});

describe('Discovery における non-binary の扱い', () => {
  it('everyone の相手には出て、women / men の相手には出ない', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'nb', { gender: 'nonbinary', discoveryPreference: 'everyone' });
    await createVerifiedUser(backend, 'open', { gender: 'man', discoveryPreference: 'everyone' });
    await createVerifiedUser(backend, 'seeksWomen', {
      gender: 'man',
      discoveryPreference: 'women',
    });

    const openView = await backend.loadDiscovery('open');
    expect(openView.candidates.map((c) => c.userId)).toContain('nb');

    const narrowView = await backend.loadDiscovery('seeksWomen');
    expect(narrowView.candidates.map((c) => c.userId)).not.toContain('nb');
  });

  it('non-binary ユーザーからは、自分を表示できる相手だけが見える', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'nb', { gender: 'nonbinary', discoveryPreference: 'women' });
    // 女性だが everyone なので相互に成立する
    await createVerifiedUser(backend, 'openWoman', {
      gender: 'woman',
      discoveryPreference: 'everyone',
    });
    // 女性だが men しか見ないので相互に成立しない
    await createVerifiedUser(backend, 'womanSeeksMen', {
      gender: 'woman',
      discoveryPreference: 'men',
    });

    const view = await backend.loadDiscovery('nb');
    expect(view.candidates.map((c) => c.userId)).toEqual(['openWoman']);
  });
});
