/**
 * Product Constants（仕様書 §27）
 *
 * 実データを見てから調整できるよう、コード中に散らさずここへ集約する。
 * 将来 remote config へ載せ替える場合も、参照側を変えずに済むようにしておく。
 */

/** Session ON の有効時間。到達したら自動的に OFF になる（§6）。 */
export const SESSION_TTL_HOURS = 6;

/** 同時に持てる Active Session の上限（§7）。 */
export const MAX_ACTIVE_SESSIONS = 3;

/** Discovery のデフォルト半径（km）。 */
export const DEFAULT_DISCOVERY_RADIUS_KM = 5;

/** 候補が足りないときに広げていく半径のステップ（§9）。 */
export const DISCOVERY_RADIUS_STEPS_KM = [5, 10, 30];

/** このプール数を下回る間は次の半径へ広げる（§9）。 */
export const MIN_DISCOVERY_POOL = 20;

/** Skip した相手を再表示しないクールダウン（§11）。 */
export const SKIP_COOLDOWN_HOURS = 24;

/** 利用可能な最低年齢。 */
export const MINIMUM_AGE = 18;

/** プロフィールに登録できる写真の枚数（§14）。 */
export const MIN_PHOTOS = 1;
export const MAX_PHOTOS = 6;

/** Interest tag はカードに最大3件まで表示する（§4）。 */
export const MAX_INTERESTS = 5;
export const MAX_CARD_INTERESTS = 3;

export const MAX_BIO_LENGTH = 120;

/** Session の期限判定を回す間隔（ms）。カウントダウン表示はしない（§6）。 */
export const EXPIRY_TICK_MS = 30_000;

export const MILLIS = {
  minute: 60_000,
  hour: 3_600_000,
  day: 86_400_000,
} as const;

export const SESSION_TTL_MS = SESSION_TTL_HOURS * MILLIS.hour;
export const SKIP_COOLDOWN_MS = SKIP_COOLDOWN_HOURS * MILLIS.hour;
