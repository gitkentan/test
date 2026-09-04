import type {
  AgeVerificationRepository,
  AuthRepository,
  ChatRepository,
  DiscoveryRepository,
  ProfileDraft,
  Repositories,
  SafetyRepository,
  SessionRepository,
  SessionStatusRepository,
  SwipeRepository,
  UserRepository,
} from '../repositories';
import type { SessionBackend } from './backend';

/**
 * Repository ports の β 版実装。
 * SessionBackend（＝サーバ相当）へ委譲するだけの薄いアダプタにしておき、
 * 実バックエンド接続時はこのファイルを HTTP 実装へ差し替えるだけで済むようにする。
 */
export function createRepositories(backend: SessionBackend): Repositories {
  const auth: AuthRepository = {
    getCurrentSession: () => backend.getCurrentSession(),
    // 端末内構成では確認コードを挟まず、その場でセッションを確立する。
    requestSignIn: async (email) => ({ kind: 'session', session: await backend.signIn(email) }),
    verifyCode: (email) => backend.signIn(email),
    signOut: () => backend.signOut(),
    deleteAccount: (userId) => backend.deleteAccount(userId),
  };

  const users: UserRepository = {
    getUser: (userId) => backend.getUser(userId),
    createProfile: (userId: string, draft: ProfileDraft) => backend.createProfile(userId, draft),
    updateProfile: (userId, patch) => backend.updateProfile(userId, patch),
    updateLocation: (userId, lat, lon) => backend.updateLocation(userId, lat, lon),
    touchActivity: (userId) => backend.touchActivity(userId),
  };

  const ageVerification: AgeVerificationRepository = {
    startVerification: (userId) => backend.startVerification(userId),
    // 照会のみ。client から age_verified を立てる経路は用意しない（§19）。
    confirmVerification: (userId, reference) => backend.confirmVerification(userId, reference),
    devForceVerified: (userId) => backend.devForceVerified(userId),
  };

  const sessionStatus: SessionStatusRepository = {
    getStatus: (userId) => backend.getStatus(userId),
    turnOn: (userId, intent) => backend.turnSessionOn(userId, intent),
    turnOff: (userId) => backend.turnSessionOff(userId),
  };

  const discovery: DiscoveryRepository = {
    loadDiscovery: (userId) => backend.loadDiscovery(userId),
  };

  const swipes: SwipeRepository = {
    skip: (senderId, receiverId) => backend.skip(senderId, receiverId),
    sendRequest: (senderId, receiverId) => backend.sendRequest(senderId, receiverId),
    countIncomingRequests: (userId) => backend.countIncomingRequests(userId),
  };

  const sessions: SessionRepository = {
    listSessions: (userId) => backend.listSessions(userId),
    getSession: (sessionId) => backend.getSession(sessionId),
  };

  const chat: ChatRepository = {
    listConversations: (userId) => backend.listConversations(userId),
    getConversation: (conversationId, viewerId) =>
      backend.getConversation(conversationId, viewerId),
    listMessages: (conversationId, viewerId) => backend.listMessages(conversationId, viewerId),
    sendText: (conversationId, senderId, text) =>
      backend.sendText(conversationId, senderId, text),
    sendImage: (conversationId, senderId, imageUrl) =>
      backend.sendImage(conversationId, senderId, imageUrl),
  };

  const safety: SafetyRepository = {
    block: (blockerId, blockedId) => backend.block(blockerId, blockedId),
    unblock: (blockerId, blockedId) => backend.unblock(blockerId, blockedId),
    listBlocked: (userId) => backend.listBlocked(userId),
    report: (reporterId, reportedUserId, reason, details) =>
      backend.report(reporterId, reportedUserId, reason, details),
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
