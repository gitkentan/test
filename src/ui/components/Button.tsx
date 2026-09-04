import React from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, radius, spacing, typography } from '../../theme';

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger';

interface Props {
  label: string;
  onPress: () => void;
  variant?: Variant;
  disabled?: boolean;
  loading?: boolean;
  icon?: React.ReactNode;
  fullWidth?: boolean;
}

/**
 * Primary action だけが Session Green を持つ（§21）。
 * Green の使用率をおおむね 5–10% に保つため、画面内で primary は原則1つ。
 */
export function Button({
  label,
  onPress,
  variant = 'primary',
  disabled = false,
  loading = false,
  icon,
  fullWidth = true,
}: Props) {
  const isDisabled = disabled || loading;
  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ disabled: isDisabled }}
      onPress={onPress}
      disabled={isDisabled}
      style={({ pressed }) => [
        styles.base,
        fullWidth && styles.fullWidth,
        styles[variant],
        pressed && !isDisabled && styles[`${variant}Pressed` as const],
        isDisabled && styles.disabled,
      ]}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'primary' ? colors.bg : colors.white} />
      ) : (
        <View style={styles.content}>
          {icon}
          <Text style={[styles.label, styles[`${variant}Label` as const]]}>{label}</Text>
        </View>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    height: 52,
    borderRadius: radius.pill,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.xl,
  },
  fullWidth: { alignSelf: 'stretch' },
  content: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  label: { ...typography.bodyStrong },

  primary: { backgroundColor: colors.green },
  primaryPressed: { backgroundColor: colors.greenPressed },
  primaryLabel: { color: colors.bg },

  secondary: { backgroundColor: colors.surfaceRaised },
  secondaryPressed: { backgroundColor: colors.hairline },
  secondaryLabel: { color: colors.white },

  ghost: { backgroundColor: 'transparent' },
  ghostPressed: { backgroundColor: colors.hairline },
  ghostLabel: { color: colors.textSecondary },

  danger: { backgroundColor: 'transparent', borderWidth: 1, borderColor: colors.danger },
  dangerPressed: { backgroundColor: 'rgba(255, 90, 90, 0.12)' },
  dangerLabel: { color: colors.danger },

  disabled: { opacity: 0.4 },
});
