import React, { useCallback, useState } from 'react';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import { colors } from '../theme';
import { useApp } from '../state/AppContext';
import { AuthScreen } from '../ui/screens/auth/AuthScreen';
import { ChatScreen } from '../ui/screens/messages/ChatScreen';
import { MessagesTab } from '../ui/screens/messages/MessagesTab';
import { OnboardingFlow } from '../ui/screens/onboarding/OnboardingFlow';
import { SessionTab } from '../ui/screens/session/SessionTab';
import { YouTab } from '../ui/screens/you/YouTab';
import { BottomNav, type TabKey } from './BottomNav';

/**
 * ルーティング（仕様書 §23, §31）。
 *
 *   App ├── Auth ├── Onboarding └── MainTabs（Session / メッセージ / You）
 *
 * Chat だけがタブの上に重なる。それ以外は Sheet で処理し、route を増やさない。
 * ナビゲーションライブラリを足さずに済む規模なので、状態だけで切り替える。
 */
export function RootNavigator() {
  const { phase } = useApp();
  const [tab, setTab] = useState<TabKey>('session');
  const [openConversationId, setOpenConversationId] = useState<string | null>(null);
  // タブへ戻るたびに会話一覧を引き直すためのキー。
  const [messagesRefreshKey, setMessagesRefreshKey] = useState(0);

  const openChat = useCallback((conversationId: string) => {
    setOpenConversationId(conversationId);
  }, []);

  const closeChat = useCallback(() => {
    setOpenConversationId(null);
    setMessagesRefreshKey((key) => key + 1);
  }, []);

  const goToMessages = useCallback(() => {
    setTab('messages');
    setMessagesRefreshKey((key) => key + 1);
  }, []);

  if (phase === 'loading') {
    return (
      <View style={styles.loading}>
        <ActivityIndicator color={colors.green} />
      </View>
    );
  }

  if (phase === 'unauthenticated') return <AuthScreen />;
  if (phase === 'onboarding') return <OnboardingFlow />;

  if (openConversationId) {
    return <ChatScreen conversationId={openConversationId} onBack={closeChat} />;
  }

  return (
    <View style={styles.container}>
      <View style={styles.body}>
        {tab === 'session' ? (
          <SessionTab onOpenChat={openChat} onOpenMessages={goToMessages} />
        ) : null}
        {tab === 'messages' ? (
          <MessagesTab onOpenChat={openChat} refreshKey={messagesRefreshKey} />
        ) : null}
        {tab === 'you' ? <YouTab /> : null}
      </View>

      <BottomNav
        active={tab}
        onChange={(next) => {
          setTab(next);
          if (next === 'messages') setMessagesRefreshKey((key) => key + 1);
        }}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  body: { flex: 1 },
  loading: {
    flex: 1,
    backgroundColor: colors.bg,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
