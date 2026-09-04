/**
 * Minimum Data Model（仕様書 §24）
 * 既存 schema がある場合は migration で合わせる前提の、最小構成。
 */

/** UNIX epoch milliseconds。端末とサーバで解釈がぶれないよう数値で持つ。 */
export type Millis = number;

export type UserId = string;

/** Intent は同時に1つだけ（§5）。複数選択は仕様として作らない。 */
export type Intent = 'drinks' | 'food' | 'cafe' | 'free_now';

export type Gender = 'woman' | 'man' | 'nonbinary';

/** 誰を Discovery に出すか（§18）。 */
export type DiscoveryPreference = 'women' | 'men' | 'everyone';

export interface Coordinates {
  latitude: number;
  longitude: number;
}

export interface Photo {
  id: string;
  uri: string;
}

export interface User {
  id: UserId;
  name: string;
  /** ISO 8601 の日付（YYYY-MM-DD）。 */
  birthDate: string;
  gender: Gender;
  discoveryPreference: DiscoveryPreference;
  bio: string;
  interests: string[];
  photos: Photo[];
  /** 内部 ranking 専用。他ユーザーへ緯度経度を公開しない（§10）。 */
  location: Coordinates | null;
  /** 生年月日の自己申告だけでは true にしない（§19）。 */
  ageVerified: boolean;
  ageVerifiedAt: Millis | null;
  ageVerificationReference: string | null;
  lastActiveAt: Millis;
  createdAt: Millis;
  updatedAt: Millis;
}

/** Session ON / OFF の状態（§5, §24）。 */
export interface SessionStatus {
  userId: UserId;
  sessionOn: boolean;
  currentIntent: Intent | null;
  startedAt: Millis | null;
  expiresAt: Millis | null;
  updatedAt: Millis;
}

export type SwipeType = 'session_request' | 'skip';

export type RequestStatus = 'pending' | 'matched' | 'expired' | 'cancelled' | 'blocked';

/** 右 Swipe = Session Request、左 Swipe = Skip（§11, §29）。 */
export interface Swipe {
  id: string;
  senderId: UserId;
  receiverId: UserId;
  type: SwipeType;
  /** Request 送信時点の sender の Intent。 */
  senderIntent: Intent | null;
  createdAt: Millis;
  /** Skip には期限がない（null）。Request は sender の session_expires_at。 */
  expiresAt: Millis | null;
  status: RequestStatus;
}

export type SessionState = 'active' | 'past';

/** 相互 Request で成立する Session（§12, §30）。 */
export interface Session {
  id: string;
  userAId: UserId;
  userBId: UserId;
  userAIntent: Intent | null;
  userBIntent: Intent | null;
  startedAt: Millis;
  /** 双方の session expiry の早い方を基本とする（§12）。 */
  activeUntil: Millis;
  status: SessionState;
}

export interface Conversation {
  id: string;
  sessionId: string;
  userAId: UserId;
  userBId: UserId;
  createdAt: Millis;
  lastMessageAt: Millis | null;
}

export type MessageType = 'text' | 'image';

export interface Message {
  id: string;
  conversationId: string;
  senderId: UserId;
  type: MessageType;
  text: string | null;
  imageUrl: string | null;
  createdAt: Millis;
}

export interface Block {
  blockerId: UserId;
  blockedId: UserId;
  createdAt: Millis;
}

export type ReportReason =
  | 'inappropriate'
  | 'harassment'
  | 'impersonation'
  | 'underage'
  | 'solicitation'
  | 'other';

export interface Report {
  id: string;
  reporterId: UserId;
  reportedUserId: UserId;
  reason: ReportReason;
  details: string | null;
  createdAt: Millis;
}

/**
 * Discovery カードに載せる情報。
 * User をそのまま渡すと緯度経度が漏れるので、公開してよい形へ落としてから渡す（§10）。
 */
export interface DiscoveryCandidate {
  userId: UserId;
  name: string;
  age: number;
  photos: Photo[];
  bio: string;
  interests: string[];
  sessionOn: boolean;
  intent: Intent | null;
  /** 「3km以内」のような丸めた表現のみ。正確な距離は返さない。 */
  distanceLabel: string;
  /** ranking 用の内部値。UI には出さない。 */
  distanceKm: number | null;
  lastActiveAt: Millis;
}

/** 会話一覧の1行（§15）。 */
export interface ConversationSummary {
  conversationId: string;
  sessionId: string;
  partnerId: UserId;
  partnerName: string;
  partnerPhoto: Photo | null;
  /** Active Session なら true。期限後は Past Session 表示になる。 */
  isActive: boolean;
  partnerIntent: Intent | null;
  lastMessagePreview: string | null;
  lastMessageAt: Millis | null;
  activeUntil: Millis;
}
