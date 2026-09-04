import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { colors, spacing, typography } from '../../../theme';
import { SessionSymbol } from '../../components/SessionSymbol';

/**
 * Empty State（仕様書 §9）。
 *
 * 「ユーザーがいません」は避ける。
 * pool が少ないだけのときは、範囲を広げている旨を伝える。
 */
interface Props {
  variant: 'low' | 'empty';
  radiusKm: number;
}

export function DiscoveryEmptyState({ variant, radiusKm }: Props) {
  const lines =
    variant === 'low'
      ? ['今は近くのSessionが少なめです。', `${radiusKm}kmまで範囲を広げています。`]
      : ['今は静かみたい。', 'また少し後で見てみよう。'];

  return (
    <View style={styles.container}>
      <SessionSymbol size={32} color={colors.textTertiary} />
      {lines.map((line) => (
        <Text key={line} style={styles.line}>
          {line}
        </Text>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.sm,
    paddingHorizontal: spacing.xl,
  },
  line: { ...typography.body, color: colors.textSecondary, textAlign: 'center' },
});
