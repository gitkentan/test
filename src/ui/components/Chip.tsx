import React from 'react';
import { Pressable, StyleSheet, Text, View, type ViewStyle } from 'react-native';
import { colors, radius, spacing, typography } from '../../theme';

interface ChipProps {
  label: string;
  onPress?: () => void;
  /** SELECTED は Green の意味のひとつ（§21）。 */
  selected?: boolean;
  leading?: React.ReactNode;
  style?: ViewStyle;
  compact?: boolean;
}

export function Chip({ label, onPress, selected = false, leading, style, compact = false }: ChipProps) {
  const content = (
    <View
      style={[
        styles.chip,
        compact && styles.compact,
        selected && styles.selected,
        style,
      ]}
    >
      {leading}
      <Text style={[styles.label, compact && styles.compactLabel, selected && styles.selectedLabel]}>
        {label}
      </Text>
    </View>
  );

  if (!onPress) return content;
  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      style={({ pressed }) => (pressed ? styles.pressed : undefined)}
    >
      {content}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  chip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.pill,
    backgroundColor: 'rgba(255,255,255,0.08)',
    borderWidth: StyleSheet.hairlineWidth,
    borderColor: colors.hairline,
  },
  compact: {
    paddingHorizontal: spacing.sm + 2,
    paddingVertical: 4,
  },
  selected: {
    backgroundColor: colors.greenDim,
    borderColor: colors.greenBorder,
  },
  label: {
    ...typography.caption,
    color: colors.white,
  },
  compactLabel: {
    fontSize: 12,
  },
  selectedLabel: {
    color: colors.green,
    fontWeight: '600',
  },
  pressed: { opacity: 0.7 },
});
