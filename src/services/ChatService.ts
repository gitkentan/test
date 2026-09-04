import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import type { Repositories } from '../data/repositories';
import type { ConversationSummary, Message, UserId } from '../domain/types';
import type { PhotoUploadService } from './PhotoUploadService';

/**
 * Messages / Chat（仕様書 §15, §16）。
 * Chat の目的は長文コミュニケーションではなく、会うまでの coordination。
 */
export class ChatService {
  constructor(
    private readonly repos: Repositories,
    private readonly photos: PhotoUploadService,
  ) {}

  async listConversations(userId: UserId): Promise<ConversationSummary[]> {
    const conversations = await this.repos.chat.listConversations(userId);
    analytics.track(AnalyticsEvent.messagesOpened, { count: conversations.length });
    return conversations;
  }

  listMessages(conversationId: string, viewerId: UserId): Promise<Message[]> {
    return this.repos.chat.listMessages(conversationId, viewerId);
  }

  async sendText(conversationId: string, senderId: UserId, text: string, isFirst: boolean) {
    const message = await this.repos.chat.sendText(conversationId, senderId, text);
    if (isFirst) analytics.track(AnalyticsEvent.chatStarted);
    analytics.track(AnalyticsEvent.messageSent, { type: 'text' });
    return message;
  }

  /** 画像はローカル URI のままでは相手が開けないので、送信前にアップロードする。 */
  async sendImage(conversationId: string, senderId: UserId, localUri: string, isFirst: boolean) {
    const uploaded = await this.photos.upload(senderId, localUri);
    const message = await this.repos.chat.sendImage(conversationId, senderId, uploaded.uri);
    if (isFirst) analytics.track(AnalyticsEvent.chatStarted);
    analytics.track(AnalyticsEvent.messageSent, { type: 'image' });
    return message;
  }
}
