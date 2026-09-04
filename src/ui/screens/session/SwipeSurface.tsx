import React, { useCallback, useEffect, useMemo, useRef } from 'react';
import {
  Animated,
  Dimensions,
  PanResponder,
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import * as Haptics from 'expo-haptics';
import type { DiscoveryCandidate } from '../../../domain/types';
import { colors, radius, spacing, typography } from '../../../theme';
import { CloseIcon } from '../../components/icons';
import { SessionSymbol } from '../../components/SessionSymbol';
import { ProfileCard } from './ProfileCard';

/**
 * Swipe Surface（仕様書 §4, §11）。
 *
 *   Swipe left  = Skip
 *   Swipe right = Session Request
 *
 * 右アクションは「Like」ではなく Session / Request の意味を持たせる。
 * Heart は主アクションにしない。
 */

const { width: SCREEN_WIDTH } = Dimensions.get('window');
const SWIPE_THRESHOLD = SCREEN_WIDTH * 0.28;
const SWIPE_OUT_DISTANCE = SCREEN_WIDTH * 1.4;

export type SwipeDirection = 'left' | 'right';

interface Props {
  candidates: DiscoveryCandidate[];
  onSwipe: (candidate: DiscoveryCandidate, direction: SwipeDirection) => void;
  onOpenDetail: (candidate: DiscoveryCandidate) => void;
  /** 送信条件を満たしていなくても Swipe は受け付け、判定は上位で行う。 */
  disabled?: boolean;
}

export function SwipeSurface({ candidates, onSwipe, onOpenDetail, disabled = false }: Props) {
  const position = useRef(new Animated.ValueXY()).current;
  const topCandidate = candidates[0] ?? null;
  const nextCandidate = candidates[1] ?? null;

  // 現在のカードを ref で持つ。PanResponder は生成時のクロージャを掴み続けるため。
  const topRef = useRef<DiscoveryCandidate | null>(topCandidate);
  topRef.current = topCandidate;
  const disabledRef = useRef(disabled);
  disabledRef.current = disabled;
  const onSwipeRef = useRef(onSwipe);
  onSwipeRef.current = onSwipe;

  useEffect(() => {
    position.setValue({ x: 0, y: 0 });
  }, [topCandidate?.userId, position]);

  const completeSwipe = useCallback(
    (direction: SwipeDirection) => {
      const candidate = topRef.current;
      if (!candidate) return;
      void Haptics.impactAsync(
        direction === 'right'
          ? Haptics.ImpactFeedbackStyle.Medium
          : Haptics.ImpactFeedbackStyle.Light,
      );
      Animated.timing(position, {
        toValue: { x: direction === 'right' ? SWIPE_OUT_DISTANCE : -SWIPE_OUT_DISTANCE, y: 0 },
        duration: 220,
        useNativeDriver: false,
      }).start(() => {
        position.setValue({ x: 0, y: 0 });
        onSwipeRef.current(candidate, direction);
      });
    },
    [position],
  );

  const panResponder = useMemo(
    () =>
      PanResponder.create({
        onMoveShouldSetPanResponder: (_event, gesture) =>
          Math.abs(gesture.dx) > 8 && Math.abs(gesture.dx) > Math.abs(gesture.dy),
        onPanResponderMove: (_event, gesture) => {
          position.setValue({ x: gesture.dx, y: gesture.dy * 0.25 });
        },
        onPanResponderRelease: (_event, gesture) => {
          if (gesture.dx > SWIPE_THRESHOLD) {
            completeSwipe('right');
          } else if (gesture.dx < -SWIPE_THRESHOLD) {
            completeSwipe('left');
          } else {
            Animated.spring(position, {
              toValue: { x: 0, y: 0 },
              friction: 7,
              tension: 60,
              useNativeDriver: false,
            }).start();
          }
        },
        onPanResponderTerminate: () => {
          Animated.spring(position, {
            toValue: { x: 0, y: 0 },
            friction: 7,
            useNativeDriver: false,
          }).start();
        },
      }),
    [position, completeSwipe],
  );

  const rotate = position.x.interpolate({
    inputRange: [-SCREEN_WIDTH, 0, SCREEN_WIDTH],
    outputRange: ['-9deg', '0deg', '9deg'],
  });

  const requestOpacity = position.x.interpolate({
    inputRange: [0, SWIPE_THRESHOLD],
    outputRange: [0, 1],
    extrapolate: 'clamp',
  });

  const skipOpacity = position.x.interpolate({
    inputRange: [-SWIPE_THRESHOLD, 0],
    outputRange: [1, 0],
    extrapolate: 'clamp',
  });

  const nextScale = position.x.interpolate({
    inputRange: [-SWIPE_THRESHOLD, 0, SWIPE_THRESHOLD],
    outputRange: [1, 0.94, 1],
    extrapolate: 'clamp',
  });

  if (!topCandidate) return null;

  return (
    <View style={styles.container}>
      <View style={styles.deck}>
        {nextCandidate ? (
          <Animated.View
            style={[styles.cardLayer, styles.behind, { transform: [{ scale: nextScale }] }]}
            pointerEvents="none"
          >
            <ProfileCard candidate={nextCandidate} onOpenDetail={() => undefined} interactive={false} />
          </Animated.View>
        ) : null}

        <Animated.View
          key={topCandidate.userId}
          style={[
            styles.cardLayer,
            { transform: [{ translateX: position.x }, { translateY: position.y }, { rotate }] },
          ]}
          {...panResponder.panHandlers}
        >
          <ProfileCard
            candidate={topCandidate}
            onOpenDetail={() => onOpenDetail(topCandidate)}
            interactive
          />

          <Animated.View
            style={[styles.stamp, styles.stampRight, { opacity: requestOpacity }]}
            pointerEvents="none"
          >
            <SessionSymbol size={16} />
            <Text style={styles.stampRightLabel}>REQUEST</Text>
          </Animated.View>

          <Animated.View
            style={[styles.stamp, styles.stampLeft, { opacity: skipOpacity }]}
            pointerEvents="none"
          >
            <Text style={styles.stampLeftLabel}>SKIP</Text>
          </Animated.View>
        </Animated.View>
      </View>

      <View style={styles.actions}>
        <Pressable
          accessibilityRole="button"
          accessibilityLabel="スキップ"
          onPress={() => completeSwipe('left')}
          style={({ pressed }) => [styles.actionButton, pressed && styles.actionPressed]}
        >
          <CloseIcon size={26} color={colors.white} />
        </Pressable>

        <Pressable
          accessibilityRole="button"
          accessibilityLabel="Session Requestを送る"
          onPress={() => completeSwipe('right')}
          style={({ pressed }) => [
            styles.actionButton,
            styles.actionPrimary,
            pressed && styles.actionPressed,
          ]}
        >
          <SessionSymbol size={30} color={colors.bg} />
        </Pressable>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  deck: { flex: 1 },
  cardLayer: StyleSheet.absoluteFill,
  behind: { opacity: 0.6 },
  stamp: {
    position: 'absolute',
    top: spacing.xxl,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.xs,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.sm,
    borderRadius: radius.pill,
    borderWidth: 1.5,
  },
  stampRight: {
    right: spacing.xl,
    borderColor: colors.green,
    backgroundColor: 'rgba(50,247,131,0.14)',
  },
  stampLeft: {
    left: spacing.xl,
    borderColor: colors.hairlineStrong,
    backgroundColor: 'rgba(8,11,11,0.5)',
  },
  stampRightLabel: { ...typography.micro, color: colors.green },
  stampLeftLabel: { ...typography.micro, color: colors.white },
  actions: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: spacing.xl,
    paddingTop: spacing.lg,
  },
  actionButton: {
    width: 60,
    height: 60,
    borderRadius: radius.pill,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.surfaceRaised,
    borderWidth: StyleSheet.hairlineWidth,
    borderColor: colors.hairline,
  },
  actionPrimary: {
    width: 68,
    height: 68,
    backgroundColor: colors.green,
    borderColor: 'transparent',
  },
  actionPressed: { opacity: 0.75, transform: [{ scale: 0.96 }] },
});
