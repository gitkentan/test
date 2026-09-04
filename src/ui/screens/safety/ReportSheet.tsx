import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import type { ReportReason } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Sheet } from '../../components/Sheet';

/**
 * Report（仕様書 §20）。
 * UGC / social app として P0 に含める。
 */
const REASONS: { value: ReportReason; label: string }[] = [
  { value: 'inappropriate', label: '不適切な内容' },
  { value: 'harassment', label: '嫌がらせ' },
  { value: 'impersonation', label: 'なりすまし' },
  { value: 'underage', label: '未成年の疑い' },
  { value: 'solicitation', label: '営業 / 勧誘' },
  { value: 'other', label: 'その他' },
];

interface Props {
  visible: boolean;
  targetName: string;
  onClose: () => void;
  onSubmit: (reason: ReportReason, details: string | null) => Promise<void> | void;
}

export function ReportSheet({ visible, targetName, onClose, onSubmit }: Props) {
  const [reason, setReason] = useState<ReportReason | null>(null);
  const [details, setDetails] = useState('');
  const [busy, setBusy] = useState(false);

  const close = () => {
    setReason(null);
    setDetails('');
    onClose();
  };

  const submit = async () => {
    if (!reason) return;
    setBusy(true);
    try {
      await onSubmit(reason, details.trim() || null);
      setReason(null);
      setDetails('');
    } finally {
      setBusy(false);
    }
  };

  return (
    <Sheet
      visible={visible}
      onClose={close}
      title="報告する"
      subtitle={`${targetName}さんについて、当てはまるものを選んでください。`}
      scrollable
    >
      <View style={styles.list}>
        {REASONS.map((item) => (
          <Pressable
            key={item.value}
            accessibilityRole="button"
            accessibilityState={{ selected: reason === item.value }}
            onPress={() => setReason(item.value)}
            style={({ pressed }) => [
              styles.option,
              reason === item.value && styles.optionSelected,
              pressed && styles.pressed,
            ]}
          >
            <Text style={[styles.label, reason === item.value && styles.labelSelected]}>
              {item.label}
            </Text>
          </Pressable>
        ))}
      </View>

      <TextInput
        value={details}
        onChangeText={setDetails}
        placeholder="詳しい状況（任意）"
        placeholderTextColor={colors.textTertiary}
        style={styles.input}
        multiline
        maxLength={400}
      />

      <View style={styles.actions}>
        <Button label="報告を送信" onPress={submit} disabled={!reason} loading={busy} />
      </View>
    </Sheet>
  );
}

const styles = StyleSheet.create({
  list: { gap: spacing.sm },
  option: {
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  optionSelected: { backgroundColor: colors.greenDim, borderColor: colors.greenBorder },
  pressed: { opacity: 0.75 },
  label: { ...typography.body, color: colors.white },
  labelSelected: { color: colors.green, fontWeight: '600' },
  input: {
    marginTop: spacing.lg,
    minHeight: 88,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    padding: spacing.md,
    color: colors.white,
    ...typography.body,
    textAlignVertical: 'top',
  },
  actions: { marginTop: spacing.lg },
});
