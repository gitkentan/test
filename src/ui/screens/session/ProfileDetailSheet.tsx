import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { intentCardText } from '../../../domain/intent';
import type { DiscoveryCandidate } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Chip } from '../../components/Chip';
import { Photo } from '../../components/Photo';
import { Sheet } from '../../components/Sheet';
import { SessionSymbol } from '../../components/SessionSymbol';

/**
 * Profile Detail（仕様書 §14）。
 *
 * 独立した Profile ページは作らず、Swipe card から Bottom Sheet で開く。
 * 収入 / 結婚意思 / 子ども / 家事分担 / 長い相性診断は含めない。
 */
interface Props {
  candidate: DiscoveryCandidate | null;
  onClose: () => void;
  onReport: () => void;
  onBlock: () => void;
}

export function ProfileDetailSheet({ candidate, onClose, onReport, onBlock }: Props) {
  return (
    <Sheet visible={candidate !== null} onClose={onClose} scrollable tall>
      {candidate ? (
        <View style={styles.body}>
          <View style={styles.header}>
            <Text style={styles.name}>
              {candidate.name}
              {Number.isFinite(candidate.age) ? `, ${candidate.age}` : ''}
            </Text>
            {candidate.sessionOn ? (
              <View style={styles.statusRow}>
                <SessionSymbol size={12} />
                <Text style={styles.statusText}>SESSION ON</Text>
              </View>
            ) : (
              <Text style={styles.statusOff}>SESSION OFF</Text>
            )}
          </View>

          {candidate.intent ? (
            <Text style={styles.intent}>{intentCardText(candidate.intent)}</Text>
          ) : null}
          <Text style={styles.distance}>{candidate.distanceLabel}</Text>

          {candidate.bio ? <Text style={styles.bio}>{candidate.bio}</Text> : null}

          {candidate.interests.length > 0 ? (
            <View style={styles.tags}>
              {candidate.interests.map((tag) => (
                <Chip key={tag} label={tag} />
              ))}
            </View>
          ) : null}

          <View style={styles.photos}>
            {candidate.photos.map((photo) => (
              <Photo key={photo.id} photo={photo} name={candidate.name} style={styles.photo} />
            ))}
          </View>

          <View style={styles.safety}>
            <Button label="報告する" variant="ghost" onPress={onReport} />
            <Button label="ブロックする" variant="danger" onPress={onBlock} />
          </View>
        </View>
      ) : null}
    </Sheet>
  );
}

const styles = StyleSheet.create({
  body: { gap: spacing.sm, paddingBottom: spacing.xl },
  header: { gap: spacing.xs },
  name: { ...typography.title, color: colors.white },
  statusRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
  statusText: { ...typography.micro, color: colors.green },
  statusOff: { ...typography.micro, color: colors.textTertiary },
  intent: { ...typography.headline, color: colors.white, marginTop: spacing.sm },
  distance: { ...typography.caption, color: colors.textSecondary },
  bio: { ...typography.body, color: colors.textSecondary, marginTop: spacing.md, lineHeight: 22 },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm, marginTop: spacing.md },
  photos: { gap: spacing.md, marginTop: spacing.xl },
  photo: { width: '100%', aspectRatio: 3 / 4, borderRadius: radius.lg },
  safety: { gap: spacing.sm, marginTop: spacing.xl },
});
