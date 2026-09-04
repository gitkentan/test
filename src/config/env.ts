/**
 * 実行環境の切り分けと、外部サービスの接続設定。
 *
 * fixture / mock データは開発環境限定にする（仕様書 §0）。
 * 本番ロジックが fake user を掴むことがないよう、seed の可否はここだけで判断する。
 */

declare const __DEV__: boolean;

export const isDev = typeof __DEV__ !== 'undefined' ? __DEV__ : process.env.NODE_ENV !== 'production';

/**
 * 開発用の fixture ユーザーを Discovery に流し込んでよいか。
 *
 * `EXPO_PUBLIC_DISABLE_FIXTURES=1` を渡すと開発環境でも投入しない。
 * 本番ビルド（__DEV__ === false）では、この値に関わらず常に false。
 */
export const allowDevFixtures =
  isDev && process.env.EXPO_PUBLIC_DISABLE_FIXTURES !== '1';

/**
 * 年齢確認プロバイダの開始 URL（§19）。
 *
 * 本人確認は外部の適合プロバイダへ委譲する。ここには「確認セッションを開始する URL」を置く。
 * 未設定のまま本番ビルドを出すと、年齢確認が完了できず Session ON も Request も
 * 送れない状態になる。`assertProductionConfig()` で検出する。
 */
export const ageVerificationProviderUrl =
  process.env.EXPO_PUBLIC_AGE_VERIFICATION_URL ?? null;

/** 年齢確認から戻ってくる deep link のパス。 */
export const AGE_VERIFICATION_RETURN_PATH = 'age-verification';

export interface ConfigProblem {
  key: string;
  message: string;
}

/**
 * 本番公開前に埋まっていなければならない設定値を検査する。
 *
 * 起動時に呼び、開発環境では警告、本番では明示的に落とす／表示することで、
 * 「設定を入れ忘れたまま公開された」状態を作らない。
 */
export function findMissingProductionConfig(): ConfigProblem[] {
  const problems: ConfigProblem[] = [];

  if (!ageVerificationProviderUrl) {
    problems.push({
      key: 'EXPO_PUBLIC_AGE_VERIFICATION_URL',
      message:
        '年齢確認プロバイダの URL が未設定です。Session ON / Request / Chat が利用できません。',
    });
  }

  return problems;
}
