import {
  DEFAULT_DISCOVERY_RADIUS_KM,
  MAX_BIO_LENGTH,
  MAX_INTERESTS,
  MAX_PHOTOS,
} from '../../config/constants';
import { ageFromBirthDate, meetsMinimumAge } from '../../domain/age';
import { blockedUserIds, isBlockedBetween } from '../../domain/blocks';
import { resolveDiscoveryPool } from '../../domain/discovery';
import { DomainError } from '../../domain/errors';
import { createId, pairKey } from '../../domain/ids';
import { evaluateMutualMatch } from '../../domain/matching';
import { publicDistance } from '../../domain/distance';
import {
  checkCanSendRequest,
  excludedUserIds,
  isActiveRequest,
  settleRequest,
} from '../../domain/requests';
import { isActiveSession, partnerId, settleSession } from '../../domain/sessions';
import { offStatus, settleExpiry, turnOff, turnOn } from '../../domain/sessionStatus';
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
} from '../../domain/types';
import type { KeyValueStorage } from '../storage';
import type {
  AgeVerificationResult,
  AuthSession,
  DiscoveryResult,
  ProfileDraft,
  SendRequestOutcome,
} from '../repositories';

/**
 * Backend Rules（仕様書 §25）
 *
 * β 版のバックエンド実装。実サーバへ移すまでの間、サーバが担うべき検証を
 * ここに集約する。UI 側のガードは体験のためのものでしかなく、
 * 実際の可否判定は必ずこのクラスを通す。
 *
 * 検証項目:
 *   age_verified / session_on / session expiry / request expiry /
 *   max active sessions / block relationship / duplicate request /
 *   duplicate match / self request / user availability
 *
 * Mutual Request の成立は `withMatchLock` による直列化で idempotent にし、
 * 同時操作でも同じ Session が2件生成されないようにする。
 */

interface Database {
  version: number;
  authSession: AuthSession | null;
  credentials: Record<string, UserId>;
  users: Record<UserId, User>;
  statuses: Record<UserId, SessionStatus>;
  swipes: Swipe[];
  sessions: Session[];
  conversations: Conversation[];
  messages: Message[];
  blocks: Block[];
  reports: Report[];
}

const STORAGE_KEY = 'session.db.v1';
const DB_VERSION = 1;

function emptyDatabase(): Database {
  return {
    version: DB_VERSION,
    authSession: null,
    credentials: {},
    users: {},
    statuses: {},
    swipes: [],
    sessions: [],
    conversations: [],
    messages: [],
    blocks: [],
    reports: [],
  };
}

export type SeedFn = (db: Database, now: Millis) => void;

export interface BackendOptions {
  storage: KeyValueStorage;
  /** 開発環境限定の fixture 投入。本番ではこれを渡さない（§0）。 */
  seed?: SeedFn;
  now?: () => Millis;
}

export class SessionBackend {
  private db: Database = emptyDatabase();
  private loaded = false;
  private loading: Promise<void> | null = null;
  private writeQueue: Promise<unknown> = Promise.resolve();
  private readonly matchLocks = new Map<string, Promise<unknown>>();

  private readonly storage: KeyValueStorage;
  private readonly seed?: SeedFn;
  readonly now: () => Millis;

  constructor(options: BackendOptions) {
    this.storage = options.storage;
    this.seed = options.seed;
    this.now = options.now ?? (() => Date.now());
  }

  // ---------------------------------------------------------------- lifecycle

  async ready(): Promise<void> {
    if (this.loaded) return;
    if (!this.loading) this.loading = this.load();
    await this.loading;
  }

  private async load(): Promise<void> {
    const raw = await this.storage.getItem(STORAGE_KEY);
    if (raw) {
      try {
        const parsed = JSON.parse(raw) as Database;
        if (parsed.version === DB_VERSION) {
          this.db = { ...emptyDatabase(), ...parsed };
        }
      } catch {
        // 壊れた状態を抱え込むより、空から始めて起動を止めない。
        this.db = emptyDatabase();
      }
    }
    if (this.seed) this.seed(this.db, this.now());
    this.loaded = true;
    await this.persist();
  }

  private persist(): Promise<void> {
    // 書き込みを直列化して、並行更新で状態が壊れないようにする。
    this.writeQueue = this.writeQueue
      .then(() => this.storage.setItem(STORAGE_KEY, JSON.stringify(this.db)))
      .catch(() => undefined);
    return this.writeQueue as Promise<void>;
  }

  /** 開発時のリセット用。 */
  async reset(): Promise<void> {
    this.db = emptyDatabase();
    if (this.seed) this.seed(this.db, this.now());
    this.loaded = true;
    await this.persist();
  }

  // ------------------------------------------------------------------- helpers

  private requireUser(userId: UserId): User {
    const user = this.db.users[userId];
    if (!user) throw new DomainError('USER_UNAVAILABLE');
    return user;
  }

  /** 期限を反映した Session status を返す。読み出しは必ずここを通す（§6）。 */
  private statusOf(userId: UserId): SessionStatus {
    const now = this.now();
    const current = this.db.statuses[userId] ?? offStatus(userId, now);
    const settled = settleExpiry(current, now);
    if (settled !== current) {
      this.db.statuses[userId] = settled;
      void this.persist();
    }
    return settled;
  }

  /** 期限切れの pending request を expired へ落とす（§29）。 */
  private settleSwipes(): void {
    const now = this.now();
    let changed = false;
    this.db.swipes = this.db.swipes.map((swipe) => {
      const settled = settleRequest(swipe, now);
      if (settled !== swipe) changed = true;
      return settled;
    });
    this.db.sessions = this.db.sessions.map((session) => {
      const settled = settleSession(session, now);
      if (settled !== session) changed = true;
      return settled;
    });
    if (changed) void this.persist();
  }

  private sessionsOf(userId: UserId): Session[] {
    return this.db.sessions.filter((s) => s.userAId === userId || s.userBId === userId);
  }

  private swipesFrom(userId: UserId): Swipe[] {
    return this.db.swipes.filter((s) => s.senderId === userId);
  }

  private swipesTo(userId: UserId): Swipe[] {
    return this.db.swipes.filter((s) => s.receiverId === userId);
  }

  // ---------------------------------------------------------------------- auth

  async getCurrentSession(): Promise<AuthSession | null> {
    await this.ready();
    return this.db.authSession;
  }

  async signUp(email: string): Promise<AuthSession> {
    await this.ready();
    const normalized = email.trim().toLowerCase();
    const existing = this.db.credentials[normalized];
    const userId = existing ?? createId('usr');
    this.db.credentials[normalized] = userId;
    this.db.authSession = { userId, email: normalized };
    await this.persist();
    return this.db.authSession;
  }

  async signIn(email: string): Promise<AuthSession> {
    return this.signUp(email);
  }

  async signOut(): Promise<void> {
    await this.ready();
    this.db.authSession = null;
    await this.persist();
  }

  async deleteAccount(userId: UserId): Promise<void> {
    await this.ready();
    delete this.db.users[userId];
    delete this.db.statuses[userId];
    for (const [email, id] of Object.entries(this.db.credentials)) {
      if (id === userId) delete this.db.credentials[email];
    }
    this.db.swipes = this.db.swipes.filter(
      (s) => s.senderId !== userId && s.receiverId !== userId,
    );
    const removedConversationIds = new Set(
      this.db.conversations
        .filter((c) => c.userAId === userId || c.userBId === userId)
        .map((c) => c.id),
    );
    this.db.conversations = this.db.conversations.filter((c) => !removedConversationIds.has(c.id));
    this.db.messages = this.db.messages.filter((m) => !removedConversationIds.has(m.conversationId));
    this.db.sessions = this.db.sessions.filter(
      (s) => s.userAId !== userId && s.userBId !== userId,
    );
    this.db.blocks = this.db.blocks.filter(
      (b) => b.blockerId !== userId && b.blockedId !== userId,
    );
    this.db.authSession = null;
    await this.persist();
  }

  // -------------------------------------------------------------------- users

  async getUser(userId: UserId): Promise<User | null> {
    await this.ready();
    return this.db.users[userId] ?? null;
  }

  async createProfile(userId: UserId, draft: ProfileDraft): Promise<User> {
    await this.ready();
    const now = this.now();
    // 18歳未満は利用不可（§18）。自己申告の生年月日はここで足切りするだけで、
    // 法定年齢確認の完了は外部プロバイダの結果で判断する（§19）。
    if (!meetsMinimumAge(draft.birthDate, now)) {
      throw new DomainError('UNDER_MINIMUM_AGE');
    }
    const existing = this.db.users[userId];
    const user: User = {
      id: userId,
      name: draft.name.trim(),
      birthDate: draft.birthDate,
      gender: draft.gender,
      discoveryPreference: draft.discoveryPreference,
      bio: draft.bio.slice(0, MAX_BIO_LENGTH),
      interests: draft.interests.slice(0, MAX_INTERESTS),
      photos: draft.photos.slice(0, MAX_PHOTOS),
      location: existing?.location ?? null,
      ageVerified: existing?.ageVerified ?? false,
      ageVerifiedAt: existing?.ageVerifiedAt ?? null,
      ageVerificationReference: existing?.ageVerificationReference ?? null,
      lastActiveAt: now,
      createdAt: existing?.createdAt ?? now,
      updatedAt: now,
    };
    this.db.users[userId] = user;
    this.db.statuses[userId] = this.db.statuses[userId] ?? offStatus(userId, now);
    await this.persist();
    return user;
  }

  async updateProfile(userId: UserId, patch: Partial<ProfileDraft>): Promise<User> {
    await this.ready();
    const user = this.requireUser(userId);
    const now = this.now();
    if (patch.birthDate && !meetsMinimumAge(patch.birthDate, now)) {
      throw new DomainError('UNDER_MINIMUM_AGE');
    }
    const updated: User = {
      ...user,
      ...(patch.name !== undefined ? { name: patch.name.trim() } : {}),
      ...(patch.birthDate !== undefined ? { birthDate: patch.birthDate } : {}),
      ...(patch.gender !== undefined ? { gender: patch.gender } : {}),
      ...(patch.discoveryPreference !== undefined
        ? { discoveryPreference: patch.discoveryPreference }
        : {}),
      ...(patch.bio !== undefined ? { bio: patch.bio.slice(0, MAX_BIO_LENGTH) } : {}),
      ...(patch.interests !== undefined
        ? { interests: patch.interests.slice(0, MAX_INTERESTS) }
        : {}),
      ...(patch.photos !== undefined ? { photos: patch.photos.slice(0, MAX_PHOTOS) } : {}),
      updatedAt: now,
    };
    this.db.users[userId] = updated;
    await this.persist();
    return updated;
  }

  async updateLocation(userId: UserId, latitude: number, longitude: number): Promise<User> {
    await this.ready();
    const user = this.requireUser(userId);
    const updated: User = {
      ...user,
      location: { latitude, longitude },
      lastActiveAt: this.now(),
      updatedAt: this.now(),
    };
    this.db.users[userId] = updated;
    await this.persist();
    return updated;
  }

  async touchActivity(userId: UserId): Promise<void> {
    await this.ready();
    const user = this.db.users[userId];
    if (!user) return;
    this.db.users[userId] = { ...user, lastActiveAt: this.now() };
    await this.persist();
  }

  // --------------------------------------------------------- age verification

  async startVerification(userId: UserId): Promise<{ redirectUrl: string | null; reference: string }> {
    await this.ready();
    this.requireUser(userId);
    // 実運用では外部プロバイダのセッションを作り、その URL を返す。
    return { redirectUrl: null, reference: createId('agv') };
  }

  async applyVerificationResult(result: AgeVerificationResult): Promise<User> {
    await this.ready();
    const user = this.requireUser(result.userId);
    const updated: User = {
      ...user,
      ageVerified: result.ageVerified,
      ageVerifiedAt: result.ageVerified ? result.verifiedAt : null,
      ageVerificationReference: result.providerReference,
      updatedAt: this.now(),
    };
    this.db.users[result.userId] = updated;
    await this.persist();
    return updated;
  }

  // ----------------------------------------------------------- session status

  async getStatus(userId: UserId): Promise<SessionStatus> {
    await this.ready();
    return this.statusOf(userId);
  }

  /**
   * Session ON / Intent 変更（§5, §28）。
   * Intent 変更時も TTL をリセットし、新しい6時間 Session として扱う。
   */
  async turnSessionOn(userId: UserId, intent: Intent): Promise<SessionStatus> {
    await this.ready();
    const user = this.requireUser(userId);
    if (!user.ageVerified) throw new DomainError('AGE_NOT_VERIFIED');

    const status = turnOn(userId, intent, this.now());
    this.db.statuses[userId] = status;
    await this.persist();
    return status;
  }

  async turnSessionOff(userId: UserId): Promise<SessionStatus> {
    await this.ready();
    const status = turnOff(this.statusOf(userId), this.now());
    this.db.statuses[userId] = status;
    await this.persist();
    return status;
  }

  // ---------------------------------------------------------------- discovery

  /**
   * Discovery（§8, §9, §10）
   *
   * Session ON だけに絞らない。通常ユーザーも候補に含めたうえで priority を付ける。
   * 返す距離は丸めたラベルのみで、緯度経度は決して含めない。
   */
  async loadDiscovery(userId: UserId): Promise<DiscoveryResult> {
    await this.ready();
    this.settleSwipes();

    const viewer = this.requireUser(userId);
    const now = this.now();
    const viewerStatus = this.statusOf(userId);

    const blocked = blockedUserIds(this.db.blocks, userId);
    const excluded = excludedUserIds(this.swipesFrom(userId), now);
    // すでに Session が成立している相手は板に戻さない。
    for (const session of this.sessionsOf(userId)) {
      if (isActiveSession(session, now)) excluded.add(partnerId(session, userId));
    }

    const pool = Object.values(this.db.users).filter((candidate) => {
      if (candidate.id === userId) return false;
      if (blocked.has(candidate.id)) return false;
      if (excluded.has(candidate.id)) return false;
      if (!matchesPreference(viewer, candidate)) return false;
      if (!matchesPreference(candidate, viewer)) return false;
      return true;
    });

    const rankable = pool.map((candidate) => {
      const status = this.statusOf(candidate.id);
      return {
        userId: candidate.id,
        sessionOn: status.sessionOn,
        intent: status.currentIntent,
        location: candidate.location,
        lastActiveAt: candidate.lastActiveAt,
      };
    });

    const resolution = resolveDiscoveryPool(rankable, {
      viewerLocation: viewer.location,
      viewerIntent: viewerStatus.currentIntent,
      now,
    });

    const candidates: DiscoveryCandidate[] = resolution.ranked.map((ranked) => {
      const user = this.db.users[ranked.candidate.userId];
      const distance = publicDistance(viewer.location, user.location);
      return {
        userId: user.id,
        name: user.name,
        age: ageFromBirthDate(user.birthDate, now),
        photos: user.photos,
        bio: user.bio,
        interests: user.interests,
        sessionOn: ranked.candidate.sessionOn,
        intent: ranked.candidate.intent,
        distanceLabel: distance.distanceLabel,
        distanceKm: distance.distanceKm,
        lastActiveAt: user.lastActiveAt,
      };
    });

    return {
      candidates,
      radiusKm: resolution.radiusKm ?? DEFAULT_DISCOVERY_RADIUS_KM,
      poolIsLow: resolution.poolIsLow,
      isEmpty: candidates.length === 0,
    };
  }

  // ------------------------------------------------------------------- swipes

  /** 左 Swipe = Skip。24時間は同じ相手を再表示しない（§11）。 */
  async skip(senderId: UserId, receiverId: UserId): Promise<Swipe> {
    await this.ready();
    const now = this.now();
    const swipe: Swipe = {
      id: createId('swp'),
      senderId,
      receiverId,
      type: 'skip',
      senderIntent: null,
      createdAt: now,
      expiresAt: null,
      status: 'cancelled',
    };
    this.db.swipes.push(swipe);
    await this.persist();
    return swipe;
  }

  /**
   * 右 Swipe = Session Request（§11, §12, §25）。
   *
   * 送信可否の検証、Request 作成、相互成立の判定、Session と Conversation の作成までを
   * ペア単位のロックの中で行い、同時操作でも Session が2件生成されないようにする。
   */
  async sendRequest(senderId: UserId, receiverId: UserId): Promise<SendRequestOutcome> {
    await this.ready();
    return this.withMatchLock(pairKey(senderId, receiverId), async () => {
      this.settleSwipes();
      const now = this.now();

      const sender = this.requireUser(senderId);
      const receiver = this.db.users[receiverId] ?? null;

      const failure = checkCanSendRequest({
        senderId,
        receiverId,
        senderVerified: sender.ageVerified,
        senderStatus: this.statusOf(senderId),
        senderSwipes: this.swipesFrom(senderId),
        sessions: this.db.sessions,
        blockedBetween: isBlockedBetween(this.db.blocks, senderId, receiverId),
        receiverExists: receiver !== null,
        now,
      });
      if (failure) throw new DomainError(failure);

      const senderStatus = this.statusOf(senderId);
      const swipe: Swipe = {
        id: createId('req'),
        senderId,
        receiverId,
        type: 'session_request',
        senderIntent: senderStatus.currentIntent,
        createdAt: now,
        // Request の期限は sender の session_expires_at（§11）。
        expiresAt: senderStatus.expiresAt,
        status: 'pending',
      };
      this.db.swipes.push(swipe);

      const match = evaluateMutualMatch({
        outgoing: swipe,
        incomingSwipes: this.swipesTo(senderId),
        senderStatus,
        receiverStatus: this.statusOf(receiverId),
        sessions: this.db.sessions,
        now,
      });

      if (match.kind !== 'matched') {
        await this.persist();
        return { kind: 'requested', swipe } as const;
      }

      this.db.sessions.push(match.session);
      const matched = new Set(match.matchedRequestIds);
      this.db.swipes = this.db.swipes.map((s) =>
        matched.has(s.id) ? { ...s, status: 'matched' as const } : s,
      );

      const conversation: Conversation = {
        id: createId('cnv'),
        sessionId: match.session.id,
        userAId: match.session.userAId,
        userBId: match.session.userBId,
        createdAt: now,
        lastMessageAt: null,
      };
      this.db.conversations.push(conversation);

      await this.persist();
      return {
        kind: 'matched',
        swipe: { ...swipe, status: 'matched' },
        session: match.session,
        conversationId: conversation.id,
      } as const;
    });
  }

  async countIncomingRequests(userId: UserId): Promise<number> {
    await this.ready();
    this.settleSwipes();
    const now = this.now();
    const blocked = blockedUserIds(this.db.blocks, userId);
    return this.swipesTo(userId).filter(
      (swipe) => isActiveRequest(swipe, now) && !blocked.has(swipe.senderId),
    ).length;
  }

  /**
   * ペア単位の直列化。
   * 実バックエンドでは DB トランザクション + 一意制約に置き換える前提。
   */
  private withMatchLock<T>(key: string, task: () => Promise<T>): Promise<T> {
    const previous = this.matchLocks.get(key) ?? Promise.resolve();
    const next = previous.then(task, task);
    this.matchLocks.set(
      key,
      next.then(
        () => undefined,
        () => undefined,
      ),
    );
    return next;
  }

  // ----------------------------------------------------------------- sessions

  async listSessions(userId: UserId): Promise<Session[]> {
    await this.ready();
    this.settleSwipes();
    return this.sessionsOf(userId).map((s) => settleSession(s, this.now()));
  }

  async getSession(sessionId: string): Promise<Session | null> {
    await this.ready();
    const session = this.db.sessions.find((s) => s.id === sessionId);
    return session ? settleSession(session, this.now()) : null;
  }

  // --------------------------------------------------------------------- chat

  /**
   * 会話一覧（§15）。
   * 並びは Active Sessions が先、その後に Recent chats。
   * Past になってもチャットは削除しない。
   */
  async listConversations(userId: UserId): Promise<ConversationSummary[]> {
    await this.ready();
    this.settleSwipes();
    const now = this.now();
    const blocked = blockedUserIds(this.db.blocks, userId);

    const summaries: ConversationSummary[] = [];
    for (const conversation of this.db.conversations) {
      if (conversation.userAId !== userId && conversation.userBId !== userId) continue;
      const otherId =
        conversation.userAId === userId ? conversation.userBId : conversation.userAId;
      // Block 後は existing chat も UI 上非表示にする（§20）。
      if (blocked.has(otherId)) continue;

      const partner = this.db.users[otherId];
      if (!partner) continue;

      const session = this.db.sessions.find((s) => s.id === conversation.sessionId);
      if (!session) continue;

      const messages = this.db.messages.filter((m) => m.conversationId === conversation.id);
      const last = messages[messages.length - 1];
      const partnerIntent =
        session.userAId === otherId ? session.userAIntent : session.userBIntent;

      summaries.push({
        conversationId: conversation.id,
        sessionId: session.id,
        partnerId: otherId,
        partnerName: partner.name,
        partnerPhoto: partner.photos[0] ?? null,
        isActive: isActiveSession(session, now),
        partnerIntent,
        lastMessagePreview: last
          ? last.type === 'image'
            ? '画像を送信しました'
            : last.text
          : null,
        lastMessageAt: last?.createdAt ?? null,
        activeUntil: session.activeUntil,
      });
    }

    return summaries.sort((a, b) => {
      if (a.isActive !== b.isActive) return a.isActive ? -1 : 1;
      const aAt = a.lastMessageAt ?? a.activeUntil;
      const bAt = b.lastMessageAt ?? b.activeUntil;
      return bAt - aAt;
    });
  }

  async getConversation(conversationId: string, viewerId: UserId): Promise<Conversation | null> {
    await this.ready();
    const conversation = this.db.conversations.find((c) => c.id === conversationId);
    if (!conversation) return null;
    if (conversation.userAId !== viewerId && conversation.userBId !== viewerId) {
      throw new DomainError('CONVERSATION_UNAVAILABLE');
    }
    const otherId = conversation.userAId === viewerId ? conversation.userBId : conversation.userAId;
    if (isBlockedBetween(this.db.blocks, viewerId, otherId)) {
      throw new DomainError('BLOCKED');
    }
    return conversation;
  }

  async listMessages(conversationId: string, viewerId: UserId): Promise<Message[]> {
    await this.ready();
    await this.getConversation(conversationId, viewerId);
    return this.db.messages
      .filter((m) => m.conversationId === conversationId)
      .sort((a, b) => a.createdAt - b.createdAt);
  }

  /**
   * メッセージ送信（§16, §19 Gate, §20）。
   * 年齢未確認ユーザーは free-form message を送信できない。
   */
  private async createMessage(
    conversationId: string,
    senderId: UserId,
    payload: Pick<Message, 'type' | 'text' | 'imageUrl'>,
  ): Promise<Message> {
    await this.ready();
    const conversation = await this.getConversation(conversationId, senderId);
    if (!conversation) throw new DomainError('CONVERSATION_UNAVAILABLE');

    const sender = this.requireUser(senderId);
    if (!sender.ageVerified) throw new DomainError('AGE_NOT_VERIFIED');

    const now = this.now();
    const message: Message = {
      id: createId('msg'),
      conversationId,
      senderId,
      ...payload,
      createdAt: now,
    };
    this.db.messages.push(message);
    this.db.conversations = this.db.conversations.map((c) =>
      c.id === conversationId ? { ...c, lastMessageAt: now } : c,
    );
    await this.persist();
    return message;
  }

  sendText(conversationId: string, senderId: UserId, text: string): Promise<Message> {
    return this.createMessage(conversationId, senderId, {
      type: 'text',
      text: text.trim(),
      imageUrl: null,
    });
  }

  sendImage(conversationId: string, senderId: UserId, imageUrl: string): Promise<Message> {
    return this.createMessage(conversationId, senderId, {
      type: 'image',
      text: null,
      imageUrl,
    });
  }

  // ------------------------------------------------------------------- safety

  async block(blockerId: UserId, blockedId: UserId): Promise<void> {
    await this.ready();
    if (blockerId === blockedId) return;
    if (isBlockedBetween(this.db.blocks, blockerId, blockedId)) return;

    this.db.blocks.push({ blockerId, blockedId, createdAt: this.now() });
    // 進行中の Request は成立させない（§20, §29）。
    this.db.swipes = this.db.swipes.map((swipe) => {
      const between =
        (swipe.senderId === blockerId && swipe.receiverId === blockedId) ||
        (swipe.senderId === blockedId && swipe.receiverId === blockerId);
      return between && swipe.status === 'pending' ? { ...swipe, status: 'blocked' as const } : swipe;
    });
    await this.persist();
  }

  async unblock(blockerId: UserId, blockedId: UserId): Promise<void> {
    await this.ready();
    this.db.blocks = this.db.blocks.filter(
      (b) => !(b.blockerId === blockerId && b.blockedId === blockedId),
    );
    await this.persist();
  }

  async listBlocked(userId: UserId): Promise<{ user: User; block: Block }[]> {
    await this.ready();
    return this.db.blocks
      .filter((b) => b.blockerId === userId)
      .map((block) => ({ user: this.db.users[block.blockedId], block }))
      .filter((entry): entry is { user: User; block: Block } => Boolean(entry.user));
  }

  async report(
    reporterId: UserId,
    reportedUserId: UserId,
    reason: ReportReason,
    details: string | null,
  ): Promise<Report> {
    await this.ready();
    const report: Report = {
      id: createId('rpt'),
      reporterId,
      reportedUserId,
      reason,
      details,
      createdAt: this.now(),
    };
    this.db.reports.push(report);
    await this.persist();
    return report;
  }
}

/** discovery_preferences のマッチング（§18, §24）。 */
function matchesPreference(viewer: User, candidate: User): boolean {
  switch (viewer.discoveryPreference) {
    case 'everyone':
      return true;
    case 'women':
      return candidate.gender === 'woman';
    case 'men':
      return candidate.gender === 'man';
    default:
      return true;
  }
}

export type { Database };
