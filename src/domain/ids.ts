/**
 * ID 生成。
 * 実バックエンドへ移す際はサーバ採番へ置き換える前提の薄いユーティリティ。
 */
let counter = 0;

export function createId(prefix: string): string {
  counter += 1;
  const random = Math.random().toString(36).slice(2, 10);
  return `${prefix}_${Date.now().toString(36)}${counter.toString(36)}${random}`;
}

/**
 * 2ユーザー間で一意になる決定的なキー。
 * 同時操作でも Session が2件生成されないことを保証する土台に使う（§25）。
 */
export function pairKey(a: string, b: string): string {
  return a < b ? `${a}:${b}` : `${b}:${a}`;
}
