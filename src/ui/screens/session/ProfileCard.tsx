import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { MAX_CARD_INTERESTS } from '../../../config/constants';
import { intentCardText } from '../../../domain/intent';
import type { DiscoveryCandidate } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { Chip } from '../../components/Chip';
import { InfoIcon } from '../../components/icons';
import { Photo } from '../../components/Photo';
import { SessionSymbol } from '../../components/SessionSymbol';

/**
 * Swipe card（仕様書 §4）。
 *
 * 人物写真を画面の中心に大きく。載せるのは first name / age / approximate distance /
 * Session status / current Intent / short bio か interest tags 3件まで。
 * Tap photo で次の写真、info tap で Profile Detail Sheet。
 */
interface Props {
  candidate: DiscoveryCandidate;
  onOpenDetail: () => void;
  /** 一番上のカードだけがタップに反応する。 */
  interactive: boolean;
}

export function ProfileCard({ candidate, onOpenDetail, interactive }: Props) {
  const [photoIndex, setPhotoIndex] = useState(0);
  const photos = candidate.photos;
  const current = photos[Math.min(photoIndex, Math.max(photos.length - 1, 0))] ?? null;

  const nextPhoto = () => {
    if (photos.length <= 1) return;
    setPhotoIndex((index) => (index + 1) % photos.length);
  };

  const tags = candidate.interests.slice(0, MAX_CARD_INTERESTS);

  return (
    <View style={styles.card}>
      <Pressable
        style={StyleSheet.absoluteFill}
        onPress={interactive ? nextPhoto : undefined}
        accessibilityLabel="次の写真"
        disabled={!interactive}
      >
        <Photo photo={current} name={candidate.name} style={styles.photo} />
      </Pressable>

      {photos.length > 1 ? (
        <View style={styles.progress} pointerEvents="none">
          {photos.map((photo, index) => (
            <View
              key={photo.id}
              style={[styles.progressBar, index === photoIndex && styles.progressBarActive]}
            />
          ))}
        </View>
      ) : null}

      <LinearGradient
        colors={['transparent', 'rgba(8,11,11,0.55)', 'rgba(8,11,11,0.96)']}
        locations={[0.4, 0.68, 1]}
        style={styles.scrim}
        pointerEvents="none"
      />

      <View style={styles.info} pointerEvents="box-none">
        {candidate.sessionOn ? (
          <View style={styles.statusRow}>
            <SessionSymbol size={13} />
            <Text style={styles.statusText}>SESSION ON</Text>
          </View>
        ) : null}

        <View style={styles.nameRow}>
          <Text style={styles.name} numberOfLines={1}>
            {candidate.name}
          </Text>
          <Text style={styles.age}>{Number.isFinite(candidate.age) ? candidate.age : ''}</Text>
          <Pressable
            accessibilityRole="button"
            accessibilityLabel={`${candidate.name}のプロフィールを見る`}
            onPress={onOpenDetail}
            disabled={!interactive}
            hitSlop={12}
            style={styles.infoButton}
          >
            <InfoIcon size={22} />
          </Pressable>
        </View>

        {candidate.intent ? (
          <Text style={styles.intent}>{intentCardText(candidate.intent)}</Text>
        ) : null}

        <Text style={styles.distance}>{candidate.distanceLabel}</Text>

        {candidate.bio ? (
          <Text style={styles.bio} numberOfLines={2}>
            {candidate.bio}
          </Text>
        ) : tags.length > 0 ? (
          <View style={styles.tags}>
            {tags.map((tag) => (
              <Chip key={tag} label={tag} compact />
            ))}
          </View>
        ) : null}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    flex: 1,
    borderRadius: radius.xl,
    overflow: 'hidden',
    backgroundColor: colors.surface,
  },
  photo: { flex: 1, width: '100%' },
  scrim: { position: 'absolute', left: 0, right: 0, bottom: 0, height: '58%' },
  progress: {
    position: 'absolute',
    top: spacing.md,
    left: spacing.md,
    right: spacing.md,
    flexDirection: 'row',
    gap: 4,
  },
  progressBar: {
    flex: 1,
    height: 3,
    borderRadius: radius.pill,
    backgroundColor: 'rgba(255,255,255,0.28)',
  },
  progressBarActive: { backgroundColor: colors.white },
  info: {
    position: 'absolute',
    left: spacing.xl,
    right: spacing.xl,
    bottom: spacing.xl,
    gap: spacing.xs,
  },
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    marginBottom: spacing.xs,
  },
  statusText: { ...typography.micro, color: colors.green },
  nameRow: { flexDirection: 'row', alignItems: 'flex-end', gap: spacing.sm },
  name: { ...typography.display, color: colors.white, flexShrink: 1 },
  age: { ...typography.title, color: colors.white, opacity: 0.85, marginBottom: 2 },
  infoButton: { marginLeft: 'auto', marginBottom: 6, opacity: 0.85 },
  intent: { ...typography.headline, color: colors.white },
  distance: { ...typography.caption, color: colors.textSecondary },
  bio: { ...typography.body, color: colors.textSecondary, marginTop: spacing.xs },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.xs, marginTop: spacing.sm },
});
