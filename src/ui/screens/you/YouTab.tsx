import React, { useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { ageFromBirthDate } from '../../../domain/age';
import { useApp } from '../../../state/AppContext';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Chip } from '../../components/Chip';
import { Photo } from '../../components/Photo';
import { SessionStatusLine } from '../../components/SessionStatusLine';
import { Toast, useToast } from '../../components/Toast';
import { VerificationSheet } from '../session/VerificationSheet';
import { EditProfileSheet } from './EditProfileSheet';
import { SettingsSheet } from './SettingsSheet';

/**
 * You タブ（仕様書 §17）。
 * Profile / Edit / Verification status / Settings / Privacy / Blocked / Logout / Delete。
 * 可能なものは Sheet で処理し、画面を増やさない。
 */
export function YouTab() {
  const insets = useSafeAreaInsets();
  const { services, auth, user, sessionStatus, refreshUser, startAgeVerification, requestLocation } =
    useApp();
  const { toast, showToast } = useToast();

  const [editOpen, setEditOpen] = useState(false);
  const [settingsOpen, setSettingsOpen] = useState(false);
  const [verificationOpen, setVerificationOpen] = useState(false);
  const [busy, setBusy] = useState(false);

  if (!user || !auth) return null;

  const age = ageFromBirthDate(user.birthDate);

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <Text style={styles.title}>You</Text>
          <Pressable
            accessibilityRole="button"
            accessibilityLabel="設定"
            onPress={() => setSettingsOpen(true)}
            hitSlop={12}
          >
            <Text style={styles.settingsIcon}>···</Text>
          </Pressable>
        </View>

        <View style={styles.profile}>
          <Photo photo={user.photos[0] ?? null} name={user.name} style={styles.avatar} />
          <View style={styles.profileText}>
            <Text style={styles.name}>
              {user.name}
              {Number.isFinite(age) ? `, ${age}` : ''}
            </Text>
            <SessionStatusLine status={sessionStatus} size="small" />
          </View>
        </View>

        {user.bio ? <Text style={styles.bio}>{user.bio}</Text> : null}

        {user.interests.length > 0 ? (
          <View style={styles.tags}>
            {user.interests.map((tag) => (
              <Chip key={tag} label={tag} />
            ))}
          </View>
        ) : null}

        <View style={styles.statusCard}>
          <View style={styles.statusRow}>
            <Text style={styles.statusLabel}>年齢確認</Text>
            <Text style={[styles.statusValue, user.ageVerified && styles.statusValueOk]}>
              {user.ageVerified ? '確認済み' : '未確認'}
            </Text>
          </View>
          {!user.ageVerified ? (
            <Button
              label="年齢確認をする"
              onPress={() => setVerificationOpen(true)}
              variant="secondary"
            />
          ) : null}

          <View style={styles.statusRow}>
            <Text style={styles.statusLabel}>位置情報</Text>
            <Text style={[styles.statusValue, user.location !== null && styles.statusValueOk]}>
              {user.location ? '許可済み' : '未設定'}
            </Text>
          </View>
          {!user.location ? (
            <Button
              label="位置情報を許可する"
              variant="secondary"
              onPress={() => {
                void requestLocation().then((granted) => {
                  if (!granted) showToast('位置情報が取得できませんでした。');
                });
              }}
            />
          ) : null}
          <Text style={styles.privacyNote}>
            正確な位置が他のユーザーに表示されることはありません。
          </Text>
        </View>

        <Button label="プロフィールを編集" onPress={() => setEditOpen(true)} />
      </ScrollView>

      <EditProfileSheet
        visible={editOpen}
        user={user}
        onClose={() => setEditOpen(false)}
        onSave={async (patch) => {
          await services.profile.updateProfile(auth.userId, patch);
          await refreshUser();
          showToast('プロフィールを更新しました。');
        }}
      />

      <SettingsSheet visible={settingsOpen} onClose={() => setSettingsOpen(false)} />

      <VerificationSheet
        visible={verificationOpen}
        busy={busy}
        onClose={() => setVerificationOpen(false)}
        onStart={() => {
          setBusy(true);
          void startAgeVerification()
            .then((result) => {
              setVerificationOpen(false);
              showToast(
                result.status === 'verified'
                  ? '年齢確認が完了しました。'
                  : (result.message ?? '年齢確認が完了しませんでした。'),
              );
            })
            .finally(() => setBusy(false));
        }}
      />

      <Toast message={toast} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  content: { padding: spacing.xl, gap: spacing.lg, paddingBottom: spacing.xxl },
  header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  title: { ...typography.title, color: colors.white },
  settingsIcon: { color: colors.textSecondary, fontSize: 20, letterSpacing: 1 },
  profile: { flexDirection: 'row', alignItems: 'center', gap: spacing.lg },
  avatar: { width: 84, height: 84, borderRadius: radius.pill },
  profileText: { gap: spacing.xs },
  name: { ...typography.title, color: colors.white },
  bio: { ...typography.body, color: colors.textSecondary, lineHeight: 22 },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  statusCard: {
    gap: spacing.md,
    padding: spacing.lg,
    borderRadius: radius.lg,
    backgroundColor: colors.surface,
  },
  statusRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  statusLabel: { ...typography.body, color: colors.white },
  statusValue: { ...typography.caption, color: colors.textTertiary },
  statusValueOk: { color: colors.green, fontWeight: '600' },
  privacyNote: { ...typography.caption, color: colors.textTertiary },
});
