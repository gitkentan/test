import React, { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, Text, TextInput, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useApp } from '../../../state/AppContext';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { SessionSymbol } from '../../components/SessionSymbol';

/**
 * Authentication / Signup（仕様書 §2 P0）。
 *
 * β 版はメールアドレスだけで入れるようにし、Swipe まで最短で到達させる。
 * 実運用では OTP / OAuth へ差し替える前提で、UI は repository の裏に依存しない。
 */
export function AuthScreen() {
  const insets = useSafeAreaInsets();
  const { signIn } = useApp();
  const [email, setEmail] = useState('');
  const [busy, setBusy] = useState(false);

  const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());

  const submit = async () => {
    if (!valid) return;
    setBusy(true);
    try {
      await signIn(email.trim());
    } finally {
      setBusy(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={[styles.container, { paddingTop: insets.top, paddingBottom: insets.bottom }]}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <View style={styles.hero}>
        <SessionSymbol size={64} />
        <Text style={styles.wordmark}>Session</Text>
        <Text style={styles.tagline}>今を、出会いに。</Text>
      </View>

      <View style={styles.form}>
        <TextInput
          value={email}
          onChangeText={setEmail}
          placeholder="メールアドレス"
          placeholderTextColor={colors.textTertiary}
          style={styles.input}
          keyboardType="email-address"
          autoCapitalize="none"
          autoCorrect={false}
          autoComplete="email"
          onSubmitEditing={() => void submit()}
          returnKeyType="go"
        />
        <Button label="はじめる" onPress={() => void submit()} disabled={!valid} loading={busy} />
        <Text style={styles.legal}>
          続行すると、利用規約とプライバシーポリシーに同意したものとみなされます。{'\n'}
          Sessionは18歳以上の方のみ利用できます。
        </Text>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg, paddingHorizontal: spacing.xl },
  hero: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: spacing.md },
  wordmark: { ...typography.display, color: colors.white, marginTop: spacing.lg },
  tagline: { ...typography.body, color: colors.textSecondary },
  form: { gap: spacing.md, paddingBottom: spacing.xl },
  input: {
    height: 52,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    paddingHorizontal: spacing.lg,
    color: colors.white,
    ...typography.body,
  },
  legal: { ...typography.caption, color: colors.textTertiary, textAlign: 'center', lineHeight: 18 },
});
