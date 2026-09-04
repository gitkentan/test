import React, { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { domainErrorMessage } from '../../../domain/errors';
import { useApp } from '../../../state/AppContext';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { SessionSymbol } from '../../components/SessionSymbol';
import { Toast, useToast } from '../../components/Toast';

/**
 * Authentication / Signup（仕様書 §2 P0）。
 *
 * パスワードは持たず、メールに送った確認コードで入る。
 * 端末内バックエンド構成ではコード入力を挟まず、そのまま Onboarding へ進む。
 */
export function AuthScreen() {
  const insets = useSafeAreaInsets();
  const { requestSignIn, verifyCode } = useApp();
  const { toast, showToast } = useToast();

  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [awaitingCode, setAwaitingCode] = useState(false);
  const [busy, setBusy] = useState(false);

  const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
  const codeValid = /^\d{6}$/.test(code.trim());

  const submitEmail = async () => {
    if (!emailValid || busy) return;
    setBusy(true);
    try {
      const result = await requestSignIn(email.trim());
      if (result.kind === 'code_sent') {
        setAwaitingCode(true);
        showToast('確認コードをメールに送りました。');
      }
    } catch (error) {
      showToast(domainErrorMessage(error));
    } finally {
      setBusy(false);
    }
  };

  const submitCode = async () => {
    if (!codeValid || busy) return;
    setBusy(true);
    try {
      await verifyCode(email.trim(), code.trim());
    } catch (error) {
      showToast(domainErrorMessage(error));
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
        {awaitingCode ? (
          <>
            <Text style={styles.sentTo}>{email.trim()} に送った6桁のコードを入力してください。</Text>
            <TextInput
              value={code}
              onChangeText={(value) => setCode(value.replace(/\D/g, '').slice(0, 6))}
              placeholder="000000"
              placeholderTextColor={colors.textTertiary}
              style={[styles.input, styles.codeInput]}
              keyboardType="number-pad"
              textContentType="oneTimeCode"
              autoComplete="one-time-code"
              autoFocus
              onSubmitEditing={() => void submitCode()}
            />
            <Button label="続ける" onPress={() => void submitCode()} disabled={!codeValid} loading={busy} />
            <Pressable
              accessibilityRole="button"
              onPress={() => {
                setAwaitingCode(false);
                setCode('');
              }}
              style={({ pressed }) => [styles.back, pressed && styles.pressed]}
            >
              <Text style={styles.backLabel}>メールアドレスを変更する</Text>
            </Pressable>
          </>
        ) : (
          <>
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
              onSubmitEditing={() => void submitEmail()}
              returnKeyType="go"
            />
            <Button label="はじめる" onPress={() => void submitEmail()} disabled={!emailValid} loading={busy} />
            <Text style={styles.legal}>
              続行すると、利用規約とプライバシーポリシーに同意したものとみなされます。{'\n'}
              Sessionは18歳以上の方のみ利用できます。
            </Text>
          </>
        )}
      </View>

      <Toast message={toast} />
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg, paddingHorizontal: spacing.xl },
  hero: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: spacing.md },
  wordmark: { ...typography.display, color: colors.white, marginTop: spacing.lg },
  tagline: { ...typography.body, color: colors.textSecondary },
  form: { gap: spacing.md, paddingBottom: spacing.xl },
  sentTo: { ...typography.caption, color: colors.textSecondary, textAlign: 'center' },
  input: {
    height: 52,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    paddingHorizontal: spacing.lg,
    color: colors.white,
    ...typography.body,
  },
  codeInput: { textAlign: 'center', letterSpacing: 8, fontSize: 22 },
  legal: { ...typography.caption, color: colors.textTertiary, textAlign: 'center', lineHeight: 18 },
  back: { alignSelf: 'center', padding: spacing.sm },
  backLabel: { ...typography.caption, color: colors.textSecondary },
  pressed: { opacity: 0.7 },
});
