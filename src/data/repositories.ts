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

export type SignInResult =
  /** その場でセッションが確立した（端末内バックエンド）。 */
  | { kind: 'session'; session: AuthSession }
  /** 確認コードをメールで送った。`verifyCode` へ続く（Supabase）。 */
  | { kind: 'code_sent' };

/**
 * 認証。
 *
 * メールに送った確認コードを入力してもらう二段構えにしている。
 * magic link の deep link 復帰は端末やメールアプリによって落ちやすく、
 * β 版で最初に踏む導線としては確実性を優先した。
 */
export interface AuthRepository {
  getCurrentSession(): Promise<AuthSession | null>;
  requestSignIn(email: string): Promise<SignInResult>;
  verifyCode(email: string, code: string): Promise<AuthSession>;
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

/**
 * プロバイダから受け取る最小限の結果（§19）。
 * 本人確認書類の画像は Session 側で保持しない。
 */
export interface AgeVerificationResult {
  userId: UserId;
  ageVerified: boolean;
  verifiedAt: Millis;
  providerReference: string;
}

export interface AgeVerificationStart {
  /** プロバイダの確認セッション URL。未設定なら null。 */
  redirectUrl: string | null;
  /** この確認試行を識別する参照。結果の照会に使う。 */
  reference: string;
}

export type AgeVerificationOutcome =
  /** サーバがプロバイダの結果を確認済み。user は確定した状態。 */
  | { status: 'verified'; user: User }
  /** プロバイダの審査が継続中。時間をおいて再照会する。 */
  | { status: 'pending' }
  /** 確認が完了しなかった。 */
  | { status: 'rejected'; reason: string | null };

/**
 * 年齢確認は外部の適合プロバイダに委譲する（§19）。
 *
 * 重要: **client は確認結果を主張できない。**
 * `confirmVerification` はサーバに照会するだけで、age_verified を立てる権限は
 * サーバ（プロバイダからの webhook / サーバ間 API）だけが持つ。
 * リダイレクト URL のクエリを client が読んで検証済みにする設計にはしない。
 */
export interface AgeVerificationRepository {
  startVerification(userId: UserId): Promise<AgeVerificationStart>;
  confirmVerification(userId: UserId, reference: string): Promise<AgeVerificationOutcome>;
  /**
   * 開発環境専用の近道。プロバイダ未接続でもループを通せるようにする。
   * 本番ビルドでは実装側が必ず失敗させる。
   */
  devForceVerified(userId: UserId): Promise<User>;
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
