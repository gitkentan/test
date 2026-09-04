/**
 * 実行環境の切り分け。
 *
 * fixture / mock データは開発環境限定にする（仕様書 §0）。
 * 本番ロジックが fake user を掴むことがないよう、seed の可否はここだけで判断する。
 */

declare const __DEV__: boolean;

export const isDev = typeof __DEV__ !== 'undefined' ? __DEV__ : process.env.NODE_ENV !== 'production';

/** 開発用の fixture ユーザーを Discovery に流し込んでよいか。 */
export const allowDevFixtures = isDev;

/**
 * 年齢確認は外部の適合プロバイダに委譲する（§19）。
 * β 版では実プロバイダ未接続のため、開発環境のみスタブ画面へ遷移させる。
 */
export const ageVerificationProviderUrl = process.env.EXPO_PUBLIC_AGE_VERIFICATION_URL ?? null;
