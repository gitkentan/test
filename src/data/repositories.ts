import type {
  Block,
  Conversation,
  ConversationSummary,
  DiscoveryCandidate,
  Intent,
  Message,
  Millis,
  Report,
  ReportReason,
  Session,
  SessionStatus,
  Swipe,
  User,
  UserId,
} from '../domain/types';

/**
 * Repository ports（仕様書 §0「UIとサービス層を分離し、最初は mock repository でも動く構成にする」）
 *
 * UI / service 層はこのインターフェースだけに依存する。
 * β 版では PersistentBackend（端末内）が実装を提供し、実バックエンド接続時は
 * 同じインターフェースを HTTP クライアントで実装して差し替える。
 */

export interface AuthSession {
  userId: UserId;
  email: string;
}

export interface AuthRepository {
  getCurrentSession(): Promise<AuthSession | null>;
  signUp(email: string): Promise<AuthSession>;
  signIn(email: string): Promise<AuthSession>;
  signOut(): Promise<void>;
  deleteAccount(userId: UserId): Promise<void>;
}

export interface ProfileDraft {
  name: string;
  birthDate: string;
  gender: User['gender'];
  discoveryPreference: User['discoveryPreference'];
  bio: string;
  interests: string[];
  photos: User['photos'];
}

export interface UserRepository {
  getUser(userId: UserId): Promise<User | null>;
  createProfile(userId: UserId, draft: ProfileDraft): Promise<User>;
  updateProfile(userId: UserId, patch: Partial<ProfileDraft>): Promise<User>;
  updateLocation(userId: UserId, latitude: number, longitude: number): Promise<User>;
  touchActivity(userId: UserId): Promise<void>;
}

export interface AgeVerificationResult {
  userId: UserId;
  ageVerified: boolean;
  verifiedAt: Millis;
  providerReference: string;
}

/**
 * 年齢確認は外部の適合プロバイダに委譲する（§19）。
 * Session 側は最小限の結果だけを受け取る。本人確認書類の画像は保持しない。
 */
export interface AgeVerificationRepository {
  startVerification(userId: UserId): Promise<{ redirectUrl: string | null; reference: string }>;
  applyResult(result: AgeVerificationResult): Promise<User>;
}

export interface SessionStatusRepository {
  getStatus(userId: UserId): Promise<SessionStatus>;
  turnOn(userId: UserId, intent: Intent): Promise<SessionStatus>;
  turnOff(userId: UserId): Promise<SessionStatus>;
}

export interface DiscoveryResult {
  candidates: DiscoveryCandidate[];
  radiusKm: number;
  poolIsLow: boolean;
  isEmpty: boolean;
}

export interface DiscoveryRepository {
  loadDiscovery(userId: UserId): Promise<DiscoveryResult>;
}

export type SendRequestOutcome =
  | { kind: 'requested'; swipe: Swipe }
  | { kind: 'matched'; swipe: Swipe; session: Session; conversationId: string };

export interface SwipeRepository {
  skip(senderId: UserId, receiverId: UserId): Promise<Swipe>;
  sendRequest(senderId: UserId, receiverId: UserId): Promise<SendRequestOutcome>;
  /** 自分宛の有効な Request 件数（P1 の Received Requests 表示の土台）。 */
  countIncomingRequests(userId: UserId): Promise<number>;
}

export interface SessionRepository {
  listSessions(userId: UserId): Promise<Session[]>;
  getSession(sessionId: string): Promise<Session | null>;
}

export interface ChatRepository {
  listConversations(userId: UserId): Promise<ConversationSummary[]>;
  getConversation(conversationId: string, viewerId: UserId): Promise<Conversation | null>;
  listMessages(conversationId: string, viewerId: UserId): Promise<Message[]>;
  sendText(conversationId: string, senderId: UserId, text: string): Promise<Message>;
  sendImage(conversationId: string, senderId: UserId, imageUrl: string): Promise<Message>;
}

export interface SafetyRepository {
  block(blockerId: UserId, blockedId: UserId): Promise<void>;
  unblock(blockerId: UserId, blockedId: UserId): Promise<void>;
  listBlocked(userId: UserId): Promise<{ user: User; block: Block }[]>;
  report(
    reporterId: UserId,
    reportedUserId: UserId,
    reason: ReportReason,
    details: string | null,
  ): Promise<Report>;
}

/** service 層へ渡す repository 一式。 */
export interface Repositories {
  auth: AuthRepository;
  users: UserRepository;
  ageVerification: AgeVerificationRepository;
  sessionStatus: SessionStatusRepository;
  discovery: DiscoveryRepository;
  swipes: SwipeRepository;
  sessions: SessionRepository;
  chat: ChatRepository;
  safety: SafetyRepository;
}
