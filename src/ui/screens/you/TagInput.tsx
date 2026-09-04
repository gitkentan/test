import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { MAX_INTERESTS } from '../../../config/constants';
import { colors, radius, spacing, typography } from '../../../theme';

/**
 * Interest tags（仕様書 §14, §18）。
 * 詳細な相性診断は作らない。軽いタグだけ。
 */
const SUGGESTIONS = [
  'ワイン', 'ラーメン', 'サウナ', 'カフェ巡り', '映画', '音楽',
  '写真', '旅行', '読書', 'ランニング', 'ゲーム', 'アート',
];

interface Props {
  tags: string[];
  onChange: (tags: string[]) => void;
}

export function TagInput({ tags, onChange }: Props) {
  const [draft, setDraft] = useState('');

  const toggle = (tag: string) => {
    if (tags.includes(tag)) {
      onChange(tags.filter((t) => t !== tag));
      return;
    }
    if (tags.length >= MAX_INTERESTS) return;
    onChange([...tags, tag]);
  };

  const addDraft = () => {
    const value = draft.trim();
    if (!value || tags.includes(value) || tags.length >= MAX_INTERESTS) {
      setDraft('');
      return;
    }
    onChange([...tags, value]);
    setDraft('');
  };

  return (
    <View style={styles.container}>
      <View style={styles.tags}>
        {SUGGESTIONS.map((tag) => {
          const selected = tags.includes(tag);
          return (
            <Pressable
              key={tag}
              accessibilityRole="button"
              accessibilityState={{ selected }}
              onPress={() => toggle(tag)}
              style={({ pressed }) => [
                styles.tag,
                selected && styles.tagSelected,
                pressed && styles.pressed,
              ]}
            >
              <Text style={[styles.tagLabel, selected && styles.tagLabelSelected]}>{tag}</Text>
            </Pressable>
          );
        })}
        {tags
          .filter((tag) => !SUGGESTIONS.includes(tag))
          .map((tag) => (
            <Pressable
              key={tag}
              accessibilityRole="button"
              onPress={() => toggle(tag)}
              style={[styles.tag, styles.tagSelected]}
            >
              <Text style={[styles.tagLabel, styles.tagLabelSelected]}>{tag}</Text>
            </Pressable>
          ))}
      </View>

      <TextInput
        value={draft}
        onChangeText={setDraft}
        onSubmitEditing={addDraft}
        placeholder={`自分で追加（最大${MAX_INTERESTS}件）`}
        placeholderTextColor={colors.textTertiary}
        style={styles.input}
        returnKeyType="done"
        maxLength={16}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { gap: spacing.md },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  tag: {
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderRadius: radius.pill,
    backgroundColor: colors.surfaceRaised,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  tagSelected: { backgroundColor: colors.greenDim, borderColor: colors.greenBorder },
  pressed: { opacity: 0.7 },
  tagLabel: { ...typography.caption, color: colors.white },
  tagLabelSelected: { color: colors.green, fontWeight: '600' },
  input: {
    height: 48,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    paddingHorizontal: spacing.md,
    color: colors.white,
    ...typography.body,
  },
});
