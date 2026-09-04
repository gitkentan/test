import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { SESSION_TTL_HOURS } from '../../../config/constants';
import { INTENTS, intentPresentation } from '../../../domain/intent';
import type { Intent } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Sheet } from '../../components/Sheet';

/**
 * Intent Selection Sheet（仕様書 §5）。
 *
 * 同時に選べるのは1つだけ。「全部OK」のような複数選択 UI は作らない。
 * OFF 状態で右 Swipe したときもこのシートを出し、選択 → Session ON → そのまま Request を送る。
 */
interface Props {
  visible: boolean;
  currentIntent: Intent | null;
  sessionOn: boolean;
  /** 右 Swipe から開かれた場合、選択後にそのまま Request を送る旨を伝える。 */
  pendingRequestName?: string | null;
  onSelect: (intent: Intent) => void;
  onTurnOff: () => void;
  onClose: () => void;
  busy?: boolean;
}

export function IntentSheet({
  visible,
  currentIntent,
  sessionOn,
  pendingRequestName,
  onSelect,
  onTurnOff,
  onClose,
  busy = false,
}: Props) {
  const subtitle = pendingRequestName
    ? `Intentを選ぶとSessionがオンになり、${pendingRequestName}さんにRequestを送ります。`
    : `今の気分を1つだけ選んでください。${SESSION_TTL_HOURS}時間で自動的にオフになります。`;

  return (
    <Sheet visible={visible} onClose={onClose} title="今なにしたい？" subtitle={subtitle}>
      <View style={styles.list}>
        {INTENTS.map((intent) => {
          const presentation = intentPresentation(intent);
          const selected = sessionOn && currentIntent === intent;
          return (
            <Pressable
              key={intent}
              accessibilityRole="button"
              accessibilityState={{ selected }}
              disabled={busy}
              onPress={() => onSelect(intent)}
              style={({ pressed }) => [
                styles.option,
                selected && styles.optionSelected,
                pressed && styles.optionPressed,
              ]}
            >
              <Text style={styles.emoji}>{presentation.emoji}</Text>
              <Text style={[styles.label, selected && styles.labelSelected]}>
                {presentation.label}
              </Text>
              {selected ? <Text style={styles.current}>選択中</Text> : null}
            </Pressable>
          );
        })}
      </View>

      {sessionOn && !pendingRequestName ? (
        <View style={styles.footer}>
          <Button label="Sessionをオフにする" variant="ghost" onPress={onTurnOff} disabled={busy} />
        </View>
      ) : null}
    </Sheet>
  );
}

const styles = StyleSheet.create({
  list: { gap: spacing.sm },
  option: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.lg,
    borderRadius: radius.lg,
    backgroundColor: colors.surfaceRaised,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  optionSelected: {
    backgroundColor: colors.greenDim,
    borderColor: colors.greenBorder,
  },
  optionPressed: { opacity: 0.75 },
  emoji: { fontSize: 22 },
  label: { ...typography.headline, color: colors.white },
  labelSelected: { color: colors.green },
  current: { ...typography.micro, color: colors.green, marginLeft: 'auto' },
  footer: { marginTop: spacing.lg },
});
