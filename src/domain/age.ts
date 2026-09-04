import { MINIMUM_AGE } from '../config/constants';
import type { Millis } from './types';

/** 生年月日（YYYY-MM-DD）から満年齢を求める。 */
export function ageFromBirthDate(birthDate: string, now: Millis = Date.now()): number {
  const birth = new Date(`${birthDate}T00:00:00Z`);
  if (Number.isNaN(birth.getTime())) return NaN;

  const today = new Date(now);
  let age = today.getUTCFullYear() - birth.getUTCFullYear();
  const monthDiff = today.getUTCMonth() - birth.getUTCMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getUTCDate() < birth.getUTCDate())) {
    age -= 1;
  }
  return age;
}

/**
 * 18歳以上か。
 *
 * これは「入力された生年月日が要件を満たすか」の判定でしかない。
 * 法定年齢確認の完了は外部プロバイダの結果（user.ageVerified）で判断する（§19）。
 */
export function meetsMinimumAge(birthDate: string, now: Millis = Date.now()): boolean {
  const age = ageFromBirthDate(birthDate, now);
  return Number.isFinite(age) && age >= MINIMUM_AGE;
}
