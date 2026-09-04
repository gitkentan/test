import React, { useState } from 'react';
import { Image, StyleSheet, Text, View, type ImageStyle, type StyleProp } from 'react-native';
import type { Photo as PhotoModel } from '../../domain/types';
import { colors, typography } from '../../theme';

/**
 * 写真表示。
 * 読み込みに失敗しても blank にならないよう、ブランド地のフォールバックを必ず出す（§32）。
 */
interface Props {
  photo: PhotoModel | null;
  name?: string;
  style?: StyleProp<ImageStyle>;
  resizeMode?: 'cover' | 'contain';
}

export function Photo({ photo, name, style, resizeMode = 'cover' }: Props) {
  const [failed, setFailed] = useState(false);

  if (!photo || failed) {
    return (
      <View style={[styles.fallback, style as StyleProp<ImageStyle>]}>
        <Text style={styles.initial}>{name?.trim().charAt(0).toUpperCase() ?? ''}</Text>
      </View>
    );
  }

  return (
    <Image
      source={{ uri: photo.uri }}
      style={style}
      resizeMode={resizeMode}
      onError={() => setFailed(true)}
      accessibilityIgnoresInvertColors
    />
  );
}

const styles = StyleSheet.create({
  fallback: {
    backgroundColor: colors.surfaceRaised,
    alignItems: 'center',
    justifyContent: 'center',
  },
  initial: {
    ...typography.display,
    color: colors.textTertiary,
  },
});
