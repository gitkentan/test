import React, { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { intentShortText } from '../../../domain/intent';
import type { ConversationSummary } from '../../../domain/types';
import { useApp } from '../../../state/AppContext';
import { colors, radius, spacing, typography } from '../../../theme';
import { Photo } from '../../components/Photo';
import { SessionSymbol } from '../../components/SessionSymbol';

/**
 * メッセージタブ（仕様書 §15）。
 *
 * Active Sessions を先頭に、その後に Recent chats。Likes 専用タブは作らない。
 * 期限が終わっても Past Session として残し、チャットは削除しない。
 */
interface Props {
  onOpenChat: (conversationId: string) => void;
  /** タブが表示されるたびに引き直すためのキー。 */
  refreshKey: number;
}

export function MessagesTab({ onOpenChat, refreshKey }: Props) {
  const insets = useSafeAreaInsets();
  const { services, auth } = useApp();
  const [conversations, setConversations] = useState<ConversationSummary[]>([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    if (!auth) return;
    setLoading(true);
    try {
      setConversations(await services.chat.listConversations(auth.userId));
    } finally {
      setLoading(false);
    }
  }, [services, auth]);

  useEffect(() => {
    void load();
  }, [load, refreshKey]);

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <Text style={styles.title}>メッセージ</Text>

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator color={colors.green} />
        </View>
      ) : conversations.length === 0 ? (
        <View style={styles.center}>
          <SessionSymbol size={28} color={colors.textTertiary} />
          <Text style={styles.emptyLine}>まだSessionはありません。</Text>
          <Text style={styles.emptyLine}>Sessionをオンにして、近くの人を見てみよう。</Text>
        </View>
      ) : (
        <FlatList
          data={conversations}
          keyExtractor={(item) => item.conversationId}
          contentContainerStyle={styles.list}
          renderItem={({ item }) => <ConversationRow item={item} onPress={onOpenChat} />}
          ItemSeparatorComponent={() => <View style={styles.separator} />}
        />
      )}
    </View>
  );
}

function ConversationRow({
  item,
  onPress,
}: {
  item: ConversationSummary;
  onPress: (conversationId: string) => void;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      onPress={() => onPress(item.conversationId)}
      style={({ pressed }) => [styles.row, pressed && styles.rowPressed]}
    >
      <Photo photo={item.partnerPhoto} name={item.partnerName} style={styles.avatar} />

      <View style={styles.rowBody}>
        <View style={styles.rowTop}>
          <Text style={styles.name} numberOfLines={1}>
            {item.partnerName}
          </Text>
          {item.isActive ? (
            <View style={styles.activeBadge}>
              <SessionSymbol size={10} />
              <Text style={styles.activeText}>Active</Text>
            </View>
          ) : (
            <Text style={styles.pastText}>Past Session</Text>
          )}
        </View>

        <Text style={styles.preview} numberOfLines={1}>
          {item.lastMessagePreview ??
            (item.partnerIntent
              ? `${intentShortText(item.partnerIntent)} で会いたい`
              : 'メッセージを送ってみよう')}
        </Text>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  title: {
    ...typography.title,
    color: colors.white,
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.sm,
    paddingBottom: spacing.lg,
  },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: spacing.sm },
  emptyLine: { ...typography.body, color: colors.textSecondary, textAlign: 'center' },
  list: { paddingHorizontal: spacing.xl, paddingBottom: spacing.xxl },
  separator: { height: spacing.md },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  rowPressed: { opacity: 0.7 },
  avatar: { width: 56, height: 56, borderRadius: radius.pill },
  rowBody: { flex: 1, gap: 2 },
  rowTop: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  name: { ...typography.bodyStrong, color: colors.white, flexShrink: 1 },
  activeBadge: { flexDirection: 'row', alignItems: 'center', gap: 3 },
  activeText: { ...typography.micro, color: colors.green, letterSpacing: 0.2 },
  pastText: { ...typography.micro, color: colors.textTertiary, letterSpacing: 0.2 },
  preview: { ...typography.caption, color: colors.textSecondary },
});
