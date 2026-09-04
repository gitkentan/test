import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { colors, spacing, typography } from '../theme';
import { MessageIcon, UserIcon } from '../ui/components/icons';
import { SessionSymbol } from '../ui/components/SessionSymbol';

/**
 * Navigation UI（仕様書 §3, §22）。
 *
 *   ✦ Session      💬 メッセージ      ○ You
 *
 * Bottom Navigation は3つだけ。Home タブや Likes 専用タブは追加しない。
 * Inactive は gray、Active は Session Green。
 */
export type TabKey = 'session' | 'messages' | 'you';

interface Props {
  active: TabKey;
  onChange: (tab: TabKey) => void;
}

export function BottomNav({ active, onChange }: Props) {
  const insets = useSafeAreaInsets();

  return (
    <View style={[styles.bar, { paddingBottom: insets.bottom || spacing.sm }]}>
      <Tab
        label="Session"
        isActive={active === 'session'}
        onPress={() => onChange('session')}
        icon={
          <SessionSymbol
            size={22}
            color={active === 'session' ? colors.green : colors.textSecondary}
          />
        }
      />
      <Tab
        label="メッセージ"
        isActive={active === 'messages'}
        onPress={() => onChange('messages')}
        icon={
          <MessageIcon color={active === 'messages' ? colors.green : colors.textSecondary} />
        }
      />
      <Tab
        label="You"
        isActive={active === 'you'}
        onPress={() => onChange('you')}
        icon={<UserIcon color={active === 'you' ? colors.green : colors.textSecondary} />}
      />
    </View>
  );
}

function Tab({
  label,
  icon,
  isActive,
  onPress,
}: {
  label: string;
  icon: React.ReactNode;
  isActive: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      accessibilityRole="tab"
      accessibilityState={{ selected: isActive }}
      accessibilityLabel={label}
      onPress={onPress}
      style={({ pressed }) => [styles.tab, pressed && styles.pressed]}
    >
      {icon}
      <Text style={[styles.label, isActive && styles.labelActive]}>{label}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    paddingTop: spacing.sm,
    backgroundColor: colors.bg,
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: colors.hairline,
  },
  tab: { flex: 1, alignItems: 'center', gap: 3, paddingVertical: spacing.xs },
  pressed: { opacity: 0.6 },
  label: { ...typography.caption, fontSize: 11, color: colors.textSecondary },
  labelActive: { color: colors.green, fontWeight: '600' },
});
