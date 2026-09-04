import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, TextInput, View } from 'react-native';
import { MAX_BIO_LENGTH } from '../../../config/constants';
import type { DiscoveryPreference, Photo as PhotoModel, User } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Chip } from '../../components/Chip';
import { Sheet } from '../../components/Sheet';
import { PhotoPicker } from './PhotoPicker';
import { TagInput } from './TagInput';

/**
 * Edit Profile（仕様書 §17）。
 * 設定を細かく分割した多数の画面にせず、Sheet 1枚で完結させる。
 */
const PREFERENCES: { value: DiscoveryPreference; label: string }[] = [
  { value: 'women', label: '女性' },
  { value: 'men', label: '男性' },
  { value: 'everyone', label: 'すべて' },
];

interface Props {
  visible: boolean;
  user: User;
  onClose: () => void;
  onSave: (patch: {
    name: string;
    bio: string;
    interests: string[];
    photos: PhotoModel[];
    discoveryPreference: DiscoveryPreference;
  }) => Promise<void>;
}

export function EditProfileSheet({ visible, user, onClose, onSave }: Props) {
  const [name, setName] = useState(user.name);
  const [bio, setBio] = useState(user.bio);
  const [interests, setInterests] = useState(user.interests);
  const [photos, setPhotos] = useState(user.photos);
  const [preference, setPreference] = useState(user.discoveryPreference);
  const [busy, setBusy] = useState(false);

  // シートを開くたびに現在のプロフィールへ戻す。
  useEffect(() => {
    if (!visible) return;
    setName(user.name);
    setBio(user.bio);
    setInterests(user.interests);
    setPhotos(user.photos);
    setPreference(user.discoveryPreference);
  }, [visible, user]);

  const save = async () => {
    setBusy(true);
    try {
      await onSave({ name, bio, interests, photos, discoveryPreference: preference });
      onClose();
    } finally {
      setBusy(false);
    }
  };

  const canSave = name.trim().length > 0 && photos.length > 0;

  return (
    <Sheet visible={visible} onClose={onClose} title="プロフィールを編集" scrollable tall>
      <View style={styles.body}>
        <Field label="写真">
          <PhotoPicker photos={photos} onChange={setPhotos} />
        </Field>

        <Field label="名前">
          <TextInput
            value={name}
            onChangeText={setName}
            style={styles.input}
            placeholder="表示される名前"
            placeholderTextColor={colors.textTertiary}
            maxLength={20}
          />
        </Field>

        <Field label="ひとこと">
          <TextInput
            value={bio}
            onChangeText={setBio}
            style={[styles.input, styles.multiline]}
            placeholder="今の気分や、行きたい場所など"
            placeholderTextColor={colors.textTertiary}
            multiline
            maxLength={MAX_BIO_LENGTH}
          />
        </Field>

        <Field label="興味">
          <TagInput tags={interests} onChange={setInterests} />
        </Field>

        <Field label="表示する相手">
          <View style={styles.row}>
            {PREFERENCES.map((item) => (
              <Chip
                key={item.value}
                label={item.label}
                selected={preference === item.value}
                onPress={() => setPreference(item.value)}
              />
            ))}
          </View>
        </Field>

        <Button label="保存する" onPress={save} disabled={!canSave} loading={busy} />
      </View>
    </Sheet>
  );
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <View style={styles.field}>
      <Text style={styles.fieldLabel}>{label}</Text>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  body: { gap: spacing.xl, paddingBottom: spacing.xl },
  field: { gap: spacing.sm },
  fieldLabel: { ...typography.micro, color: colors.textTertiary },
  input: {
    minHeight: 48,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.md,
    color: colors.white,
    ...typography.body,
  },
  multiline: { minHeight: 88, textAlignVertical: 'top' },
  row: { flexDirection: 'row', gap: spacing.sm },
});
