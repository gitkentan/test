import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { DomainError } from '../../../domain/errors';
import { intentShortText } from '../../../domain/intent';
import type { ConversationSummary, Message, ReportReason } from '../../../domain/types';
import { useApp } from '../../../state/AppContext';
import { colors, radius, spacing, typography } from '../../../theme';
import { ChevronLeftIcon, ImageIcon, SendIcon } from '../../components/icons';
import { Photo } from '../../components/Photo';
import { SessionSymbol } from '../../components/SessionSymbol';
import { Toast, useToast } from '../../components/Toast';
import { ReportSheet } from '../safety/ReportSheet';

/**
 * 1:1 Chat（仕様書 §16）。
 *
 * P0 は text / image / timestamp / report / block のみ。
 * GIF / 音声 / ビデオ / 位置共有 / AI は入れない。
 *
 * Header:
 *   Yuna
 *   ✦ Active Session · 🍸 飲み        （Active 終了後は「Past Session」）
 */
interface Props {
  conversationId: string;
  onBack: () => void;
}

export function ChatScreen({ conversationId, onBack }: Props) {
  const insets = useSafeAreaInsets();
  const { services, auth, user } = useApp();
  const { toast, showToast } = useToast();
  const listRef = useRef<FlatList<Message>>(null);

  const [summary, setSummary] = useState<ConversationSummary | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [draft, setDraft] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [reportOpen, setReportOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  const load = useCallback(async () => {
    if (!auth) return;
    try {
      const conversations = await services.repos.chat.listConversations(auth.userId);
      setSummary(conversations.find((c) => c.conversationId === conversationId) ?? null);
      setMessages(await services.chat.listMessages(conversationId, auth.userId));
    } catch (error) {
      showToast(error instanceof DomainError ? error.message : '会話を開けませんでした。');
      onBack();
    } finally {
      setLoading(false);
    }
  }, [services, auth, conversationId, showToast, onBack]);

  useEffect(() => {
    void load();
  }, [load]);

  const send = useCallback(async () => {
    const text = draft.trim();
    if (!auth || !text || sending) return;
    setSending(true);
    try {
      const message = await services.chat.sendText(
        conversationId,
        auth.userId,
        text,
        messages.length === 0,
      );
      setMessages((current) => [...current, message]);
      setDraft('');
    } catch (error) {
      showToast(error instanceof DomainError ? error.message : '送信できませんでした。');
    } finally {
      setSending(false);
    }
  }, [services, auth, conversationId, draft, messages.length, sending, showToast]);

  const sendImage = useCallback(async () => {
    if (!auth || sending) return;
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      showToast('写真へのアクセスを許可してください。');
      return;
    }
    const picked = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      quality: 0.8,
    });
    if (picked.canceled || !picked.assets[0]) return;

    setSending(true);
    try {
      const message = await services.chat.sendImage(
        conversationId,
        auth.userId,
        picked.assets[0].uri,
        messages.length === 0,
      );
      setMessages((current) => [...current, message]);
    } catch (error) {
      showToast(error instanceof DomainError ? error.message : '送信できませんでした。');
    } finally {
      setSending(false);
    }
  }, [services, auth, conversationId, messages.length, sending, showToast]);

  const handleReport = useCallback(
    async (reason: ReportReason, details: string | null) => {
      if (!auth || !summary) return;
      await services.safety.report(auth.userId, summary.partnerId, reason, details);
      setReportOpen(false);
      showToast('報告を受け付けました。');
    },
    [services, auth, summary, showToast],
  );

  const handleBlock = useCallback(async () => {
    if (!auth || !summary) return;
    await services.safety.block(auth.userId, summary.partnerId);
    onBack();
  }, [services, auth, summary, onBack]);

  const canSendFreeForm = user?.ageVerified ?? false;

  const headerStatus = useMemo(() => {
    if (!summary) return null;
    if (!summary.isActive) return <Text style={styles.headerPast}>Past Session</Text>;
    return (
      <View style={styles.headerStatusRow}>
        <SessionSymbol size={11} />
        <Text style={styles.headerActive}>
          Active Session
          {summary.partnerIntent ? ` · ${intentShortText(summary.partnerIntent)}` : ''}
        </Text>
      </View>
    );
  }, [summary]);

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <View style={styles.header}>
        <Pressable accessibilityRole="button" accessibilityLabel="戻る" onPress={onBack} hitSlop={12}>
          <ChevronLeftIcon />
        </Pressable>
        <Photo photo={summary?.partnerPhoto ?? null} name={summary?.partnerName} style={styles.headerAvatar} />
        <View style={styles.headerText}>
          <Text style={styles.headerName} numberOfLines={1}>
            {summary?.partnerName ?? ''}
          </Text>
          {headerStatus}
        </View>
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="その他"
          onPress={() => setMenuOpen((open) => !open)}
          hitSlop={12}
        >
          <Text style={styles.more}>···</Text>
        </Pressable>
      </View>

      {menuOpen ? (
        <View style={styles.menu}>
          <Pressable
            onPress={() => {
              setMenuOpen(false);
              setReportOpen(true);
            }}
            style={({ pressed }) => [styles.menuItem, pressed && styles.pressed]}
          >
            <Text style={styles.menuLabel}>報告する</Text>
          </Pressable>
          <Pressable
            onPress={() => {
              setMenuOpen(false);
              void handleBlock();
            }}
            style={({ pressed }) => [styles.menuItem, pressed && styles.pressed]}
          >
            <Text style={[styles.menuLabel, styles.menuDanger]}>ブロックする</Text>
          </Pressable>
        </View>
      ) : null}

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator color={colors.green} />
        </View>
      ) : (
        <FlatList
          ref={listRef}
          data={messages}
          keyExtractor={(item) => item.id}
          contentContainerStyle={styles.list}
          renderItem={({ item }) => (
            <MessageBubble message={item} mine={item.senderId === auth?.userId} />
          )}
          onContentSizeChange={() => listRef.current?.scrollToEnd({ animated: false })}
          ListEmptyComponent={
            <Text style={styles.emptyHint}>
              会うまでの段取りを決めよう。長い自己紹介はいりません。
            </Text>
          }
        />
      )}

      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={insets.top}
      >
        <View style={[styles.composer, { paddingBottom: insets.bottom + spacing.sm }]}>
          {canSendFreeForm ? (
            <>
              <Pressable
                accessibilityRole="button"
                accessibilityLabel="画像を送る"
                onPress={() => void sendImage()}
                hitSlop={10}
                style={styles.composerIcon}
              >
                <ImageIcon />
              </Pressable>
              <TextInput
                value={draft}
                onChangeText={setDraft}
                placeholder="メッセージを入力"
                placeholderTextColor={colors.textTertiary}
                style={styles.input}
                multiline
                maxLength={1000}
              />
              <Pressable
                accessibilityRole="button"
                accessibilityLabel="送信"
                onPress={() => void send()}
                disabled={!draft.trim() || sending}
                style={[styles.sendButton, (!draft.trim() || sending) && styles.sendDisabled]}
              >
                <SendIcon />
              </Pressable>
            </>
          ) : (
            <Text style={styles.gateNotice}>
              年齢確認が完了するとメッセージを送れます。
            </Text>
          )}
        </View>
      </KeyboardAvoidingView>

      <ReportSheet
        visible={reportOpen}
        targetName={summary?.partnerName ?? ''}
        onClose={() => setReportOpen(false)}
        onSubmit={handleReport}
      />

      <Toast message={toast} />
    </View>
  );
}

function MessageBubble({ message, mine }: { message: Message; mine: boolean }) {
  const time = new Date(message.createdAt).toLocaleTimeString('ja-JP', {
    hour: '2-digit',
    minute: '2-digit',
  });

  return (
    <View style={[styles.bubbleRow, mine ? styles.bubbleRowMine : styles.bubbleRowTheirs]}>
      <View style={[styles.bubble, mine ? styles.bubbleMine : styles.bubbleTheirs]}>
        {message.type === 'image' && message.imageUrl ? (
          <Photo
            photo={{ id: message.id, uri: message.imageUrl }}
            style={styles.bubbleImage}
          />
        ) : (
          <Text style={[styles.bubbleText, mine && styles.bubbleTextMine]}>{message.text}</Text>
        )}
      </View>
      <Text style={styles.timestamp}>{time}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: colors.hairline,
  },
  headerAvatar: { width: 36, height: 36, borderRadius: radius.pill },
  headerText: { flex: 1 },
  headerName: { ...typography.bodyStrong, color: colors.white },
  headerStatusRow: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  headerActive: { ...typography.micro, color: colors.green, letterSpacing: 0.2 },
  headerPast: { ...typography.micro, color: colors.textTertiary, letterSpacing: 0.2 },
  more: { color: colors.textSecondary, fontSize: 20, letterSpacing: 1 },
  menu: {
    position: 'absolute',
    right: spacing.lg,
    top: 92,
    zIndex: 10,
    backgroundColor: colors.surfaceRaised,
    borderRadius: radius.md,
    borderWidth: StyleSheet.hairlineWidth,
    borderColor: colors.hairline,
    overflow: 'hidden',
  },
  menuItem: { paddingHorizontal: spacing.lg, paddingVertical: spacing.md },
  menuLabel: { ...typography.body, color: colors.white },
  menuDanger: { color: colors.danger },
  pressed: { opacity: 0.7 },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  list: { padding: spacing.lg, gap: spacing.md, flexGrow: 1 },
  emptyHint: {
    ...typography.caption,
    color: colors.textTertiary,
    textAlign: 'center',
    marginTop: spacing.xxl,
  },
  bubbleRow: { maxWidth: '78%', gap: 2 },
  bubbleRowMine: { alignSelf: 'flex-end', alignItems: 'flex-end' },
  bubbleRowTheirs: { alignSelf: 'flex-start', alignItems: 'flex-start' },
  bubble: {
    borderRadius: radius.lg,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm + 2,
    overflow: 'hidden',
  },
  bubbleMine: { backgroundColor: colors.green },
  bubbleTheirs: { backgroundColor: colors.surfaceRaised },
  bubbleText: { ...typography.body, color: colors.white, lineHeight: 21 },
  bubbleTextMine: { color: colors.bg },
  bubbleImage: { width: 200, height: 200, borderRadius: radius.md, margin: -spacing.sm },
  timestamp: { ...typography.caption, fontSize: 11, color: colors.textTertiary },
  composer: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: spacing.sm,
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.sm,
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: colors.hairline,
  },
  composerIcon: { paddingBottom: spacing.sm },
  input: {
    flex: 1,
    maxHeight: 120,
    minHeight: 40,
    borderRadius: radius.lg,
    backgroundColor: colors.surfaceRaised,
    paddingHorizontal: spacing.md,
    paddingTop: spacing.sm,
    paddingBottom: spacing.sm,
    color: colors.white,
    ...typography.body,
  },
  sendButton: {
    width: 40,
    height: 40,
    borderRadius: radius.pill,
    backgroundColor: colors.green,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sendDisabled: { opacity: 0.35 },
  gateNotice: {
    ...typography.caption,
    color: colors.textSecondary,
    textAlign: 'center',
    flex: 1,
    paddingVertical: spacing.md,
  },
});
