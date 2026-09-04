import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { intentShortText } from '../../domain/intent';
import type { SessionStatus } from '../../domain/types';
import { colors, spacing, typography } from '../../theme';
import { SessionSymbol } from './SessionSymbol';

/**
 * Session status 行（仕様書 §4, §5）。
 *
 *   ON:  ✦ SESSION ON · 🍸 飲み
 *   OFF: SESSION OFF
 *
 * カウントダウンタイマーは出さない（§6）。
 */
interface Props {
  status: SessionStatus;
  onPress?: () => void;
  size?: 'default' | 'small';
}

export function SessionStatusLine({ status, onPress, size = 'default' }: Props) {
  const small = size === 'small';
  const body = (
    <View style={styles.row}>
      {status.sessionOn ? <SessionSymbol size={small ? 12 : 14} /> : null}
      <Text
        style={[
          styles.text,
          small && styles.textSmall,
          status.sessionOn ? styles.on : styles.off,
        ]}
      >
        {status.sessionOn ? 'SESSION ON' : 'SESSION OFF'}
      </Text>
      {status.sessionOn && status.currentIntent ? (
        <Text style={[styles.intent, small && styles.textSmall]}>
          {`· ${intentShortText(status.currentIntent)}`}
        </Text>
      ) : null}
    </View>
  );

  if (!onPress) return body;
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityLabel={status.sessionOn ? 'Intentを変更' : 'Sessionをオンにする'}
      onPress={onPress}
      style={({ pressed }) => (pressed ? styles.pressed : undefined)}
    >
      {body}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  text: { ...typography.micro },
  textSmall: { fontSize: 10 },
  on: { color: colors.green },
  off: { color: colors.textTertiary },
  intent: { ...typography.micro, color: colors.textSecondary, letterSpacing: 0 },
  pressed: { opacity: 0.6 },
});
