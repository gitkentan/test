import type { DiscoveryPreference, Gender, User } from './types';

/**
 * Discovery の表示対象ルール（仕様書 §18, §24）。
 *
 * これまで backend 内の switch に埋もれていた判定をここへ出し、
 * とくに non-binary ユーザーの扱いを暗黙の default ではなく明示的な規則にする。
 *
 * ## 規則
 *
 * 1. 表示は**相互**に成立したときだけ。
 *    viewer が candidate を見たいだけでは足りず、candidate 側の設定でも
 *    viewer が対象に入っている必要がある。片側だけの一方通行にしない。
 *
 * 2. `women` / `men` は、その性別として登録しているユーザーだけを対象にする。
 *
 * 3. **non-binary ユーザーは `everyone` を選んでいる相手にだけ表示される。**
 *    `women` / `men` という二値の絞り込みに non-binary を割り当てると、
 *    本人が選んでいない性別として扱うことになるため、そうしない。
 *    non-binary ユーザー自身は `women` / `men` / `everyone` のいずれも選べ、
 *    「誰を見るか」は制限されない。
 *
 * この規則の帰結として、non-binary ユーザーから見える母数は
 * `everyone` を選んでいる層に限られる。β 版では仕様どおり設定項目を増やさないが、
 * 本来は「どの検索結果に自分を表示するか」を本人が選べるようにするのが望ましい。
 * README の「残リスク」に記載している。
 */

/** viewer の設定から見て、その性別が表示対象に入るか。 */
export function preferenceIncludesGender(
  preference: DiscoveryPreference,
  gender: Gender,
): boolean {
  switch (preference) {
    case 'everyone':
      return true;
    case 'women':
      return gender === 'woman';
    case 'men':
      return gender === 'man';
  }
}

/** non-binary ユーザーを表示できるのは `everyone` を選んでいる相手だけ。 */
export function canSeeNonBinary(preference: DiscoveryPreference): boolean {
  return preference === 'everyone';
}

type GenderedUser = Pick<User, 'gender' | 'discoveryPreference'>;

/** viewer の設定だけを見た片側判定。 */
export function viewerWouldSee(viewer: GenderedUser, candidate: GenderedUser): boolean {
  return preferenceIncludesGender(viewer.discoveryPreference, candidate.gender);
}

/**
 * Discovery に出してよいか。相互に成立したときだけ true。
 * backend / ranking の双方はこの関数だけを見る。
 */
export function isMutuallyDiscoverable(viewer: GenderedUser, candidate: GenderedUser): boolean {
  return viewerWouldSee(viewer, candidate) && viewerWouldSee(candidate, viewer);
}
