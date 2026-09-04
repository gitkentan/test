import React, { useEffect, useRef } from 'react';
import { Animated, Easing, Modal, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { intentShortText } from '../../../domain/intent';
import type { Intent, Photo as PhotoModel } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Photo } from '../../components/Photo';
import { SessionSymbol } from '../../components/SessionSymbol';

/**
 * Match Overlay（仕様書 §13）。
 *
 * 別ページを作らず、Full Screen Overlay で出す。
 * Session symbol と Session Green を主役にする。Tinder の "It's a Match" を視覚的にコピーしない。
 */
export interface MatchPresentation {
  partnerName: string;
  partnerPhoto: PhotoModel | null;
  myPhoto: PhotoModel | null;
  myName: string;
  myIntent: Intent | null;
  partnerIntent: Intent | null;
}

interface Props {
  match: MatchPresentation | null;
  onOpenChat: () => void;
  onKeepSwiping: () => void;
}

export function MatchOverlay({ match, onOpenChat, onKeepSwiping }: Props) {
  const insets = useSafeAreaInsets();
  const progress = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    if (!match) {
      progress.setValue(0);
      return;
    }
    Animated.timing(progress, {
      toValue: 1,
      duration: 420,
      easing: Easing.out(Easing.cubic),
      useNativeDriver: true,
    }).start();
  }, [match, progress]);

  const scale = progress.interpolate({ inputRange: [0, 1], outputRange: [0.86, 1] });

  return (
    <Modal visible={match !== null} transparent animationType="fade" onRequestClose={onKeepSwiping}>
      <View style={[styles.container, { paddingTop: insets.top, paddingBottom: insets.bottom }]}>
        {match ? (
          <>
            <Animated.View style={[styles.center, { opacity: progress, transform: [{ scale }] }]}>
              <SessionSymbol size={56} />
              <Text style={styles.headline}>{"IT'S A"}</Text>
              <Text style={styles.headlineStrong}>SESSION</Text>
              <Text style={styles.subhead}>Sessionが始まりました</Text>

              <View style={styles.photos}>
                <Photo photo={match.myPhoto} name={match.myName} style={styles.photo} />
                <Photo photo={match.partnerPhoto} name={match.partnerName} style={styles.photo} />
              </View>

              <View style={styles.intents}>
                {match.myIntent ? (
                  <Text style={styles.intent}>{intentShortText(match.myIntent)}</Text>
                ) : null}
                {match.myIntent && match.partnerIntent ? (
                  <Text style={styles.intentDivider}>·</Text>
                ) : null}
                {match.partnerIntent ? (
                  <Text style={styles.intent}>{intentShortText(match.partnerIntent)}</Text>
                ) : null}
              </View>
            </Animated.View>

            <View style={styles.actions}>
              <Button label="メッセージを送る" onPress={onOpenChat} />
              <Button label="スワイプを続ける" variant="ghost" onPress={onKeepSwiping} />
            </View>
          </>
        ) : null}
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    paddingHorizontal: spacing.xl,
    justifyContent: 'space-between',
  },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: spacing.xs },
  headline: { ...typography.title, color: colors.white, letterSpacing: 2, marginTop: spacing.xl },
  headlineStrong: {
    fontSize: 44,
    fontWeight: '700',
    color: colors.green,
    letterSpacing: 3,
  },
  subhead: { ...typography.body, color: colors.textSecondary, marginTop: spacing.sm },
  photos: { flexDirection: 'row', gap: spacing.lg, marginTop: spacing.xxl },
  photo: {
    width: 128,
    height: 168,
    borderRadius: radius.lg,
  },
  intents: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.lg, alignItems: 'center' },
  intent: { ...typography.bodyStrong, color: colors.white },
  intentDivider: { ...typography.body, color: colors.textTertiary },
  actions: { gap: spacing.sm, paddingBottom: spacing.lg },
});
