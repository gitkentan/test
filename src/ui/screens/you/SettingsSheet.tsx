import React, { useCallback, useEffect, useState } from 'react';
import { Alert, Linking, Pressable, StyleSheet, Text, View } from 'react-native';
import type { Block, User } from '../../../domain/types';
import { useApp } from '../../../state/AppContext';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { ChevronRightIcon } from '../../components/icons';
import { Photo } from '../../components/Photo';
import { Sheet } from '../../components/Sheet';

/**
 * Settings / Privacy / Blocked users / Logout / Delete account（仕様書 §17）。
 * 細かく画面を分けず、Sheet の中で内部 view を切り替える。
 */
const TERMS_URL = 'https://session.app/terms';
const PRIVACY_URL = 'https://session.app/privacy';

type SettingsView = 'root' | 'blocked';

interface Props {
  visible: boolean;
  onClose: () => void;
}

export function SettingsSheet({ visible, onClose }: Props) {
  const { services, auth, signOut, deleteAccount } = useApp();
  const [view, setView] = useState<SettingsView>('root');
  const [blocked, setBlocked] = useState<{ user: User; block: Block }[]>([]);

  const loadBlocked = useCallback(async () => {
    if (!auth) return;
    setBlocked(await services.safety.listBlocked(auth.userId));
  }, [services, auth]);

  useEffect(() => {
    if (!visible) setView('root');
  }, [visible]);

  useEffect(() => {
    if (view === 'blocked') void loadBlocked();
  }, [view, loadBlocked]);

  const confirmDelete = () => {
    Alert.alert(
      'アカウントを削除しますか？',
      'プロフィール、Session、メッセージがすべて削除されます。この操作は取り消せません。',
      [
        { text: 'キャンセル', style: 'cancel' },
        { text: '削除する', style: 'destructive', onPress: () => void deleteAccount() },
      ],
    );
  };

  if (view === 'blocked') {
    return (
      <Sheet
        visible={visible}
        onClose={onClose}
        title="ブロックしたユーザー"
        subtitle="ブロックを解除すると、またお互いに表示されるようになります。"
        scrollable
      >
        {blocked.length === 0 ? (
          <Text style={styles.empty}>ブロックしているユーザーはいません。</Text>
        ) : (
          <View style={styles.blockedList}>
            {blocked.map(({ user }) => (
              <View key={user.id} style={styles.blockedRow}>
                <Photo photo={user.photos[0] ?? null} name={user.name} style={styles.blockedAvatar} />
                <Text style={styles.blockedName}>{user.name}</Text>
                <Pressable
                  accessibilityRole="button"
                  onPress={() => {
                    if (!auth) return;
                    void services.safety.unblock(auth.userId, user.id).then(loadBlocked);
                  }}
                  style={({ pressed }) => [styles.unblock, pressed && styles.pressed]}
                >
                  <Text style={styles.unblockLabel}>解除</Text>
                </Pressable>
              </View>
            ))}
          </View>
        )}
        <View style={styles.footer}>
          <Button label="戻る" variant="ghost" onPress={() => setView('root')} />
        </View>
      </Sheet>
    );
  }

  return (
    <Sheet visible={visible} onClose={onClose} title="設定" scrollable>
      <View style={styles.group}>
        <Row label="ブロックしたユーザー" onPress={() => setView('blocked')} />
        <Row label="利用規約" onPress={() => void Linking.openURL(TERMS_URL)} />
        <Row label="プライバシーポリシー" onPress={() => void Linking.openURL(PRIVACY_URL)} />
      </View>

      <View style={styles.actions}>
        <Button label="ログアウト" variant="secondary" onPress={() => void signOut()} />
        <Button label="アカウントを削除" variant="danger" onPress={confirmDelete} />
      </View>
    </Sheet>
  );
}

function Row({ label, onPress }: { label: string; onPress: () => void }) {
  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      style={({ pressed }) => [styles.row, pressed && styles.pressed]}
    >
      <Text style={styles.rowLabel}>{label}</Text>
      <ChevronRightIcon />
    </Pressable>
  );
}

const styles = StyleSheet.create({
  group: {
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    overflow: 'hidden',
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: colors.hairline,
  },
  rowLabel: { ...typography.body, color: colors.white },
  pressed: { opacity: 0.7 },
  actions: { gap: spacing.sm, marginTop: spacing.xl },
  empty: { ...typography.body, color: colors.textSecondary },
  blockedList: { gap: spacing.md },
  blockedRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
  blockedAvatar: { width: 44, height: 44, borderRadius: radius.pill },
  blockedName: { ...typography.body, color: colors.white, flex: 1 },
  unblock: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.pill,
    borderWidth: StyleSheet.hairlineWidth,
    borderColor: colors.hairlineStrong,
  },
  unblockLabel: { ...typography.caption, color: colors.white },
  footer: { marginTop: spacing.xl },
});
