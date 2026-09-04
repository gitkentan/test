import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { MAX_PHOTOS } from '../../../config/constants';
import { createId } from '../../../domain/ids';
import type { Photo as PhotoModel } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { CloseIcon } from '../../components/icons';
import { Photo } from '../../components/Photo';

/**
 * 写真の追加 / 削除（仕様書 §14, §18）。
 * Onboarding と Edit Profile で共有する。
 */
interface Props {
  photos: PhotoModel[];
  onChange: (photos: PhotoModel[]) => void;
  onError?: (message: string) => void;
}

export function PhotoPicker({ photos, onChange, onError }: Props) {
  const add = async () => {
    if (photos.length >= MAX_PHOTOS) return;
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      onError?.('写真へのアクセスを許可してください。');
      return;
    }
    const picked = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      quality: 0.85,
      allowsMultipleSelection: true,
      selectionLimit: MAX_PHOTOS - photos.length,
    });
    if (picked.canceled) return;
    onChange([
      ...photos,
      ...picked.assets.map((asset) => ({ id: createId('pht'), uri: asset.uri })),
    ].slice(0, MAX_PHOTOS));
  };

  const slots = Array.from({ length: MAX_PHOTOS }, (_, index) => photos[index] ?? null);

  return (
    <View style={styles.grid}>
      {slots.map((photo, index) => (
        <View key={photo?.id ?? `slot-${index}`} style={styles.slot}>
          {photo ? (
            <>
              <Photo photo={photo} style={styles.photo} />
              <Pressable
                accessibilityRole="button"
                accessibilityLabel="この写真を削除"
                onPress={() => onChange(photos.filter((p) => p.id !== photo.id))}
                style={styles.remove}
                hitSlop={8}
              >
                <CloseIcon size={14} color={colors.white} />
              </Pressable>
            </>
          ) : (
            <Pressable
              accessibilityRole="button"
              accessibilityLabel="写真を追加"
              onPress={() => void add()}
              style={({ pressed }) => [styles.empty, pressed && styles.pressed]}
            >
              <Text style={styles.plus}>+</Text>
            </Pressable>
          )}
        </View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  slot: { width: '31.5%', aspectRatio: 3 / 4 },
  photo: { width: '100%', height: '100%', borderRadius: radius.md },
  empty: {
    width: '100%',
    height: '100%',
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    borderWidth: 1,
    borderColor: colors.hairline,
    borderStyle: 'dashed',
    alignItems: 'center',
    justifyContent: 'center',
  },
  pressed: { opacity: 0.7 },
  plus: { ...typography.title, color: colors.textTertiary },
  remove: {
    position: 'absolute',
    top: 4,
    right: 4,
    width: 24,
    height: 24,
    borderRadius: radius.pill,
    backgroundColor: 'rgba(8,11,11,0.8)',
    alignItems: 'center',
    justifyContent: 'center',
  },
});
