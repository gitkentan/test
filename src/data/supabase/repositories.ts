import type { SupabaseClient } from '@supabase/supabase-js';
import { PHOTO_BUCKET } from '../../config/env';
import { DomainError } from '../../domain/errors';
import type {
  Block,
  Conversation,
  ConversationSummary,
  Message,
  Report,
  ReportReason,
  Session,
  SessionStatus,
  Swipe,
  User,
  UserId,
} from '../../domain/types';
import type {
  AgeVerificationOutcome,
  AgeVerificationRepository,
  AgeVerificationStart,
  AuthRepository,
  AuthSession,
  ChatRepository,
  DiscoveryRepository,
  DiscoveryResult,
  ProfileDraft,
  Repositories,
  SafetyRepository,
  SendRequestOutcome,
  SessionRepository,
  SessionStatusRepository,
  SignInResult,
  SwipeRepository,
  UserRepository,
} from '../repositories';
import {
  mapConversation,
  mapConversationSummary,
  mapDiscoveryCandidate,
  mapMessage,
  mapSession,
  mapSessionStatus,
  mapUser,
  toDomainError,
  type ConversationRow,
  type ConversationSummaryRow,
  type DiscoveryRow,
  type MessageRow,
  type SessionRow,
  type SessionStatusRow,
  type UserRow,
} from './mappers';

/**
 * Repository ports の Supabase 実装（項目4・5）。
 *
 * 状態を変える操作はすべて RPC を通す。table への直接 insert / update はしない。
 * §25 の検証は DB 側（RPC + 制約 + RLS）が持っており、ここは呼び出すだけ。
 *
 * client 側でガードを重ねないのは、二重に判定を持つと必ずずれるため。
 * UI が出す確認（Session OFF なら Intent シートを先に出す等）は体験のためのもので、
 * 可否そのものはサーバの返り値で決まる。
 */
export function createSupabaseRepositories(supabase: SupabaseClient): Repositories {
  /** RPC を呼び、業務エラーを DomainError へ変換する。 */
  async function rpc<T>(fn: string, args?: Record<string, unknown>): Promise<T> {
    const { data, error } = await supabase.rpc(fn, args ?? {});
    if (error) throw toDomainError(error);
    return data as T;
  }

  async function currentUserId(): Promise<UserId> {
    const { data } = await supabase.auth.getUser();
    if (!data.user) throw new DomainError('NOT_AUTHENTICATED');
    return data.user.id;
  }

  const auth: AuthRepository = {
    async getCurrentSession(): Promise<AuthSession | null> {
      const { data } = await supabase.auth.getSession();
      const user = data.session?.user;
      if (!user) return null;
      return { userId: user.id, email: user.email ?? '' };
    },

    /**
     * 確認コードをメールで送る。パスワードは持たない。
     * 未登録のアドレスでもそのままサインアップになる。
     */
    async requestSignIn(email: string): Promise<SignInResult> {
      const { error } = await supabase.auth.signInWithOtp({
        email: email.trim().toLowerCase(),
        options: { shouldCreateUser: true },
      });
      if (error) throw toDomainError(error);
      return { kind: 'code_sent' };
    },

    async verifyCode(email: string, code: string): Promise<AuthSession> {
      const { data, error } = await supabase.auth.verifyOtp({
        email: email.trim().toLowerCase(),
        token: code.trim(),
        type: 'email',
      });
      if (error) throw toDomainError(error);
      const user = data.user;
      if (!user) throw new DomainError('NOT_AUTHENTICATED');
      return { userId: user.id, email: user.email ?? '' };
    },

    async signOut(): Promise<void> {
      await supabase.auth.signOut();
    },

    async deleteAccount(): Promise<void> {
      // 削除は service role が要る操作なので Edge Function に委譲する。
      const { error } = await supabase.functions.invoke('delete-account');
      if (error) throw toDomainError(error);
      await supabase.auth.signOut();
    },
  };

  const users: UserRepository = {
    async getUser(userId: UserId): Promise<User | null> {
      // RLS により自分の行しか返らない。
      const { data, error } = await supabase
        .from('users')
        .select('*')
        .eq('id', userId)
        .maybeSingle();
      if (error) throw toDomainError(error);
      return data ? mapUser(data as UserRow) : null;
    },

    async createProfile(userId: UserId, draft: ProfileDraft): Promise<User> {
      const { data, error } = await supabase
        .from('users')
        .upsert({
          id: userId,
          name: draft.name.trim(),
          birth_date: draft.birthDate,
          gender: draft.gender,
          discovery_preference: draft.discoveryPreference,
          bio: draft.bio,
          interests: draft.interests,
          photos: draft.photos,
        })
        .select()
        .single();
      if (error) throw toDomainError(error);
      return mapUser(data as UserRow);
    },

    async updateProfile(userId: UserId, patch: Partial<ProfileDraft>): Promise<User> {
      const row: Record<string, unknown> = { updated_at: new Date().toISOString() };
      if (patch.name !== undefined) row.name = patch.name.trim();
      if (patch.birthDate !== undefined) row.birth_date = patch.birthDate;
      if (patch.gender !== undefined) row.gender = patch.gender;
      if (patch.discoveryPreference !== undefined) {
        row.discovery_preference = patch.discoveryPreference;
      }
      if (patch.bio !== undefined) row.bio = patch.bio;
      if (patch.interests !== undefined) row.interests = patch.interests;
      if (patch.photos !== undefined) row.photos = patch.photos;

      const { data, error } = await supabase
        .from('users')
        .update(row)
        .eq('id', userId)
        .select()
        .single();
      if (error) throw toDomainError(error);
      return mapUser(data as UserRow);
    },

    async updateLocation(userId: UserId, latitude: number, longitude: number): Promise<User> {
      const { data, error } = await supabase
        .from('users')
        .update({
          latitude,
          longitude,
          last_active_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        })
        .eq('id', userId)
        .select()
        .single();
      if (error) throw toDomainError(error);
      return mapUser(data as UserRow);
    },

    async touchActivity(userId: UserId): Promise<void> {
      await supabase
        .from('users')
        .update({ last_active_at: new Date().toISOString() })
        .eq('id', userId);
    },
  };

  const ageVerification: AgeVerificationRepository = {
    async startVerification(): Promise<AgeVerificationStart> {
      const row = await rpc<{ reference: string }>('start_age_verification');
      // 確認 URL は client 側で設定値から組み立てる（AgeVerificationService）。
      return { redirectUrl: null, reference: row.reference };
    },

    async confirmVerification(userId, reference): Promise<AgeVerificationOutcome> {
      const result = await rpc<
        | { status: 'verified'; user: UserRow }
        | { status: 'pending' }
        | { status: 'rejected'; reason: string | null }
      >('confirm_age_verification', { p_reference: reference });

      if (result.status === 'verified') {
        return { status: 'verified', user: mapUser(result.user) };
      }
      if (result.status === 'rejected') {
        return { status: 'rejected', reason: result.reason ?? null };
      }
      return { status: 'pending' };
    },

    async devForceVerified(): Promise<User> {
      // 実バックエンドでは近道を用意しない。プロバイダを通すしかない。
      throw new DomainError(
        'AGE_NOT_VERIFIED',
        '年齢確認プロバイダを設定してください。',
      );
    },
  };

  const sessionStatus: SessionStatusRepository = {
    async getStatus(userId: UserId): Promise<SessionStatus> {
      const { data, error } = await supabase
        .from('session_statuses')
        .select('*')
        .eq('user_id', userId)
        .maybeSingle();
      if (error) throw toDomainError(error);
      if (!data) {
        return {
          userId,
          sessionOn: false,
          currentIntent: null,
          startedAt: null,
          expiresAt: null,
          updatedAt: Date.now(),
        };
      }
      const status = mapSessionStatus(data as SessionStatusRow);
      // サーバの settle_expiry が走る前に読んだ場合に備え、client 側でも期限を反映する。
      if (status.sessionOn && status.expiresAt !== null && status.expiresAt <= Date.now()) {
        return { ...status, sessionOn: false, currentIntent: null, startedAt: null, expiresAt: null };
      }
      return status;
    },

    async turnOn(_userId, intent): Promise<SessionStatus> {
      const row = await rpc<SessionStatusRow>('session_turn_on', { p_intent: intent });
      return mapSessionStatus(row);
    },

    async turnOff(): Promise<SessionStatus> {
      const row = await rpc<SessionStatusRow>('session_turn_off');
      return mapSessionStatus(row);
    },
  };

  const discovery: DiscoveryRepository = {
    async loadDiscovery(): Promise<DiscoveryResult> {
      const rows = await rpc<DiscoveryRow[]>('discovery_feed');
      const list = rows ?? [];
      return {
        candidates: list.map(mapDiscoveryCandidate),
        radiusKm: list[0]?.radius_km ?? 5,
        poolIsLow: list[0]?.pool_is_low ?? list.length === 0,
        isEmpty: list.length === 0,
      };
    },
  };

  const swipes: SwipeRepository = {
    async skip(_senderId, receiverId): Promise<Swipe> {
      const row = await rpc<{
        id: string;
        sender_id: string;
        receiver_id: string;
        created_at: string;
      }>('swipe_skip', { p_receiver: receiverId });
      return {
        id: row.id,
        senderId: row.sender_id,
        receiverId: row.receiver_id,
        type: 'skip',
        senderIntent: null,
        createdAt: new Date(row.created_at).getTime(),
        expiresAt: null,
        status: 'cancelled',
      };
    },

    /**
     * 右 Swipe。検証・Request 作成・相互成立の判定・Session 作成までを
     * サーバ側の1トランザクションで行う（§25）。
     */
    async sendRequest(_senderId, receiverId): Promise<SendRequestOutcome> {
      const result = await rpc<{
        kind: 'requested' | 'matched';
        request: {
          id: string;
          sender_id: string;
          receiver_id: string;
          sender_intent: Swipe['senderIntent'];
          created_at: string;
          expires_at: string | null;
          status: Swipe['status'];
        };
        session?: SessionRow;
        conversation_id?: string;
      }>('send_session_request', { p_receiver: receiverId });

      const swipe: Swipe = {
        id: result.request.id,
        senderId: result.request.sender_id,
        receiverId: result.request.receiver_id,
        type: 'session_request',
        senderIntent: result.request.sender_intent,
        createdAt: new Date(result.request.created_at).getTime(),
        expiresAt: result.request.expires_at
          ? new Date(result.request.expires_at).getTime()
          : null,
        status: result.kind === 'matched' ? 'matched' : result.request.status,
      };

      if (result.kind === 'matched' && result.session && result.conversation_id) {
        return {
          kind: 'matched',
          swipe,
          session: mapSession(result.session),
          conversationId: result.conversation_id,
        };
      }
      return { kind: 'requested', swipe };
    },

    countIncomingRequests(): Promise<number> {
      return rpc<number>('incoming_request_count');
    },
  };

  const sessions: SessionRepository = {
    async listSessions(userId: UserId): Promise<Session[]> {
      const { data, error } = await supabase
        .from('sessions')
        .select('*')
        .or(`user_a_id.eq.${userId},user_b_id.eq.${userId}`);
      if (error) throw toDomainError(error);
      return ((data ?? []) as SessionRow[]).map(mapSession);
    },

    async getSession(sessionId: string): Promise<Session | null> {
      const { data, error } = await supabase
        .from('sessions')
        .select('*')
        .eq('id', sessionId)
        .maybeSingle();
      if (error) throw toDomainError(error);
      return data ? mapSession(data as SessionRow) : null;
    },
  };

  const chat: ChatRepository = {
    async listConversations(): Promise<ConversationSummary[]> {
      const rows = await rpc<ConversationSummaryRow[]>('conversation_list');
      return (rows ?? []).map(mapConversationSummary);
    },

    async getConversation(conversationId: string): Promise<Conversation | null> {
      const { data, error } = await supabase
        .from('conversations')
        .select('*')
        .eq('id', conversationId)
        .maybeSingle();
      if (error) throw toDomainError(error);
      return data ? mapConversation(data as ConversationRow) : null;
    },

    async listMessages(conversationId: string): Promise<Message[]> {
      const { data, error } = await supabase
        .from('messages')
        .select('*')
        .eq('conversation_id', conversationId)
        .order('created_at', { ascending: true });
      if (error) throw toDomainError(error);
      return ((data ?? []) as MessageRow[]).map(mapMessage);
    },

    async sendText(conversationId, _senderId, text): Promise<Message> {
      const row = await rpc<MessageRow>('send_message', {
        p_conversation: conversationId,
        p_type: 'text',
        p_text: text.trim(),
        p_image_url: null,
      });
      return mapMessage(row);
    },

    async sendImage(conversationId, _senderId, imageUrl): Promise<Message> {
      const row = await rpc<MessageRow>('send_message', {
        p_conversation: conversationId,
        p_type: 'image',
        p_text: null,
        p_image_url: imageUrl,
      });
      return mapMessage(row);
    },
  };

  const safety: SafetyRepository = {
    async block(_blockerId, blockedId): Promise<void> {
      await rpc<void>('block_user', { p_blocked: blockedId });
    },

    async unblock(_blockerId, blockedId): Promise<void> {
      await rpc<void>('unblock_user', { p_blocked: blockedId });
    },

    async listBlocked(): Promise<{ user: User; block: Block }[]> {
      const rows = await rpc<
        { user_id: string; name: string; photo: unknown; created_at: string }[]
      >('blocked_list');
      const me = await currentUserId();
      // 一覧に必要なのは名前と写真だけ。他の列はサーバが返さない。
      return (rows ?? []).map((row) => ({
        user: {
          id: row.user_id,
          name: row.name,
          birthDate: '',
          gender: 'nonbinary',
          discoveryPreference: 'everyone',
          bio: '',
          interests: [],
          photos: row.photo ? [row.photo as { id: string; uri: string }] : [],
          location: null,
          ageVerified: false,
          ageVerifiedAt: null,
          ageVerificationReference: null,
          lastActiveAt: 0,
          createdAt: 0,
          updatedAt: 0,
        },
        block: {
          blockerId: me,
          blockedId: row.user_id,
          createdAt: new Date(row.created_at).getTime(),
        },
      }));
    },

    async report(reporterId, reportedUserId, reason: ReportReason, details): Promise<Report> {
      const { data, error } = await supabase
        .from('reports')
        .insert({
          reporter_id: reporterId,
          reported_user_id: reportedUserId,
          reason,
          details,
        })
        .select()
        .single();
      if (error) throw toDomainError(error);
      const row = data as {
        id: string;
        reporter_id: string;
        reported_user_id: string;
        reason: ReportReason;
        details: string | null;
        created_at: string;
      };
      return {
        id: row.id,
        reporterId: row.reporter_id,
        reportedUserId: row.reported_user_id,
        reason: row.reason,
        details: row.details,
        createdAt: new Date(row.created_at).getTime(),
      };
    },
  };

  return {
    auth,
    users,
    ageVerification,
    sessionStatus,
    discovery,
    swipes,
    sessions,
    chat,
    safety,
  };
}

/** 写真のアップロード先パス。先頭を user_id にすることで RLS が効く。 */
export function photoStoragePath(userId: UserId, fileName: string): string {
  return `${userId}/${fileName}`;
}

export { PHOTO_BUCKET };
