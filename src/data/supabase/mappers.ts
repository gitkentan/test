import { DomainError, type DomainErrorCode } from '../../domain/errors';
import type {
  Conversation,
  ConversationSummary,
  DiscoveryCandidate,
  Message,
  Photo,
  Session,
  SessionStatus,
  User,
} from '../../domain/types';

/**
 * DB の行を domain の型へ写す。
 *
 * ここは I/O を持たない純粋関数なので、実バックエンドに繋がなくてもテストできる。
 * timestamptz は epoch millis へ、snake_case は camelCase へ揃える。
 */

export const toMillis = (value: string | null): number | null =>
  value === null ? null : new Date(value).getTime();

const requireMillis = (value: string): number => new Date(value).getTime();

function toPhotos(value: unknown): Photo[] {
  if (!Array.isArray(value)) return [];
  return value.flatMap((entry) => {
    if (!entry || typeof entry !== 'object') return [];
    const { id, uri } = entry as { id?: unknown; uri?: unknown };
    if (typeof id !== 'string' || typeof uri !== 'string') return [];
    return [{ id, uri }];
  });
}

function toPhoto(value: unknown): Photo | null {
  return toPhotos(value === null || value === undefined ? [] : [value])[0] ?? null;
}

export interface UserRow {
  id: string;
  name: string;
  birth_date: string;
  gender: User['gender'];
  discovery_preference: User['discoveryPreference'];
  bio: string;
  interests: string[] | null;
  photos: unknown;
  latitude: number | null;
  longitude: number | null;
  age_verified: boolean;
  age_verified_at: string | null;
  age_verification_reference: string | null;
  last_active_at: string;
  created_at: string;
  updated_at: string;
}

export function mapUser(row: UserRow): User {
  return {
    id: row.id,
    name: row.name,
    birthDate: row.birth_date,
    gender: row.gender,
    discoveryPreference: row.discovery_preference,
    bio: row.bio ?? '',
    interests: row.interests ?? [],
    photos: toPhotos(row.photos),
    location:
      row.latitude !== null && row.longitude !== null
        ? { latitude: row.latitude, longitude: row.longitude }
        : null,
    ageVerified: row.age_verified,
    ageVerifiedAt: toMillis(row.age_verified_at),
    ageVerificationReference: row.age_verification_reference,
    lastActiveAt: requireMillis(row.last_active_at),
    createdAt: requireMillis(row.created_at),
    updatedAt: requireMillis(row.updated_at),
  };
}

export interface SessionStatusRow {
  user_id: string;
  session_on: boolean;
  current_intent: SessionStatus['currentIntent'];
  started_at: string | null;
  expires_at: string | null;
  updated_at: string;
}

export function mapSessionStatus(row: SessionStatusRow): SessionStatus {
  return {
    userId: row.user_id,
    sessionOn: row.session_on,
    currentIntent: row.current_intent,
    startedAt: toMillis(row.started_at),
    expiresAt: toMillis(row.expires_at),
    updatedAt: requireMillis(row.updated_at),
  };
}

export interface DiscoveryRow {
  user_id: string;
  name: string;
  age: number;
  photos: unknown;
  bio: string;
  interests: string[] | null;
  session_on: boolean;
  intent: DiscoveryCandidate['intent'];
  distance_label: string;
  last_active_at: string;
  priority: number;
  radius_km: number;
  pool_is_low: boolean;
}

/**
 * Discovery の行。
 * サーバは緯度経度を返さないので、distanceKm は常に null になる（§10）。
 */
export function mapDiscoveryCandidate(row: DiscoveryRow): DiscoveryCandidate {
  return {
    userId: row.user_id,
    name: row.name,
    age: row.age,
    photos: toPhotos(row.photos),
    bio: row.bio ?? '',
    interests: row.interests ?? [],
    sessionOn: row.session_on,
    intent: row.intent,
    distanceLabel: row.distance_label,
    distanceKm: null,
    lastActiveAt: requireMillis(row.last_active_at),
  };
}

export interface SessionRow {
  id: string;
  user_a_id: string;
  user_b_id: string;
  user_a_intent: Session['userAIntent'];
  user_b_intent: Session['userBIntent'];
  started_at: string;
  active_until: string;
  status: Session['status'];
}

export function mapSession(row: SessionRow): Session {
  return {
    id: row.id,
    userAId: row.user_a_id,
    userBId: row.user_b_id,
    userAIntent: row.user_a_intent,
    userBIntent: row.user_b_intent,
    startedAt: requireMillis(row.started_at),
    activeUntil: requireMillis(row.active_until),
    status: row.status,
  };
}

export interface ConversationRow {
  id: string;
  session_id: string;
  user_a_id: string;
  user_b_id: string;
  created_at: string;
  last_message_at: string | null;
}

export function mapConversation(row: ConversationRow): Conversation {
  return {
    id: row.id,
    sessionId: row.session_id,
    userAId: row.user_a_id,
    userBId: row.user_b_id,
    createdAt: requireMillis(row.created_at),
    lastMessageAt: toMillis(row.last_message_at),
  };
}

export interface ConversationSummaryRow {
  conversation_id: string;
  session_id: string;
  partner_id: string;
  partner_name: string;
  partner_photo: unknown;
  is_active: boolean;
  partner_intent: ConversationSummary['partnerIntent'];
  last_message_preview: string | null;
  last_message_at: string | null;
  active_until: string;
}

export function mapConversationSummary(row: ConversationSummaryRow): ConversationSummary {
  return {
    conversationId: row.conversation_id,
    sessionId: row.session_id,
    partnerId: row.partner_id,
    partnerName: row.partner_name,
    partnerPhoto: toPhoto(row.partner_photo),
    isActive: row.is_active,
    partnerIntent: row.partner_intent,
    lastMessagePreview: row.last_message_preview,
    lastMessageAt: toMillis(row.last_message_at),
    activeUntil: requireMillis(row.active_until),
  };
}

export interface MessageRow {
  id: string;
  conversation_id: string;
  sender_id: string;
  type: Message['type'];
  text: string | null;
  image_url: string | null;
  created_at: string;
}

export function mapMessage(row: MessageRow): Message {
  return {
    id: row.id,
    conversationId: row.conversation_id,
    senderId: row.sender_id,
    type: row.type,
    text: row.text,
    imageUrl: row.image_url,
    createdAt: requireMillis(row.created_at),
  };
}

/**
 * RPC が raise した業務エラーを DomainError へ戻す。
 *
 * Postgres の例外メッセージはそのままエラーコードにしてあるので、
 * 既知のコードならユーザー向けの日本語文言に落ちる。
 */
const KNOWN_CODES: DomainErrorCode[] = [
  'AGE_NOT_VERIFIED',
  'SESSION_OFF',
  'SESSION_EXPIRED',
  'ACTIVE_SESSION_LIMIT',
  'BLOCKED',
  'DUPLICATE_REQUEST',
  'SELF_REQUEST',
  'USER_UNAVAILABLE',
  'REQUEST_EXPIRED',
  'UNDER_MINIMUM_AGE',
  'NOT_AUTHENTICATED',
  'CONVERSATION_UNAVAILABLE',
  'PROFILE_INCOMPLETE',
];

export function toDomainError(error: { message?: string } | null): Error {
  const message = error?.message ?? '';
  const code = KNOWN_CODES.find((known) => message.includes(known));
  if (code) return new DomainError(code);

  // 一意制約に当たった場合も duplicate として扱う（RPC の判定と同じ結論になる）。
  if (message.includes('swipes_one_active_request')) return new DomainError('DUPLICATE_REQUEST');
  if (message.includes('sessions_one_active_pair')) return new DomainError('DUPLICATE_REQUEST');
  if (message.includes('users_minimum_age')) return new DomainError('UNDER_MINIMUM_AGE');

  return new Error(message || 'バックエンドとの通信に失敗しました。');
}
