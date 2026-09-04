/**
 * ドメインエラー。
 * サーバ側の検証結果をそのまま UI コピーへ落とせるよう、コードと日本語文言を対にする（§25）。
 */
export type DomainErrorCode =
  | 'AGE_NOT_VERIFIED'
  | 'SESSION_OFF'
  | 'SESSION_EXPIRED'
  | 'ACTIVE_SESSION_LIMIT'
  | 'BLOCKED'
  | 'DUPLICATE_REQUEST'
  | 'SELF_REQUEST'
  | 'USER_UNAVAILABLE'
  | 'REQUEST_EXPIRED'
  | 'UNDER_MINIMUM_AGE'
  | 'NOT_AUTHENTICATED'
  | 'CONVERSATION_UNAVAILABLE'
  | 'PROFILE_INCOMPLETE';

const MESSAGES: Record<DomainErrorCode, string> = {
  AGE_NOT_VERIFIED: '年齢確認が完了していません。',
  SESSION_OFF: 'Sessionをオンにすると送れます。',
  SESSION_EXPIRED: 'Sessionの有効期限が切れています。',
  ACTIVE_SESSION_LIMIT: '今動いているSessionがいっぱいです。',
  BLOCKED: 'このユーザーとはやり取りできません。',
  DUPLICATE_REQUEST: 'すでにRequestを送っています。',
  SELF_REQUEST: '自分にRequestは送れません。',
  USER_UNAVAILABLE: 'このユーザーは現在利用できません。',
  REQUEST_EXPIRED: 'Requestの有効期限が切れています。',
  UNDER_MINIMUM_AGE: '18歳以上の方のみ利用できます。',
  NOT_AUTHENTICATED: 'ログインが必要です。',
  CONVERSATION_UNAVAILABLE: 'この会話は利用できません。',
  PROFILE_INCOMPLETE: 'プロフィールの登録を完了してください。',
};

export class DomainError extends Error {
  readonly code: DomainErrorCode;

  constructor(code: DomainErrorCode, message?: string) {
    super(message ?? MESSAGES[code]);
    this.name = 'DomainError';
    this.code = code;
  }
}

export function domainErrorMessage(error: unknown): string {
  if (error instanceof DomainError) return error.message;
  return '通信に失敗しました。少し後でもう一度お試しください。';
}
