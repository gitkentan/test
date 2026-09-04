import React, { useEffect, useRef } from 'react';
import {
  Animated,
  Easing,
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { colors, radius, spacing, typography } from '../../theme';
import { useKeyboardHeight } from '../hooks/useKeyboardHeight';

/**
 * Bottom Sheet（仕様書 §4, §17, §31）。
 *
 * Intent / Profile detail / Verification / Settings はすべてこの上に載せ、
 * 画面（route）を増やさない。
 */
interface SheetProps {
  visible: boolean;
  onClose: () => void;
  title?: string;
  subtitle?: string;
  children: React.ReactNode;
  /** 内容が長い場合にスクロールさせる。 */
  scrollable?: boolean;
  /** 画面をほぼ覆う高さで開く（Profile detail 用）。 */
  tall?: boolean;
}

export function Sheet({
  visible,
  onClose,
  title,
  subtitle,
  children,
  scrollable = false,
  tall = false,
}: SheetProps) {
  const insets = useSafeAreaInsets();
  const keyboardHeight = useKeyboardHeight();
  const progress = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(progress, {
      toValue: visible ? 1 : 0,
      duration: visible ? 240 : 180,
      easing: visible ? Easing.out(Easing.cubic) : Easing.in(Easing.cubic),
      useNativeDriver: true,
    }).start();
  }, [visible, progress]);

  const translateY = progress.interpolate({
    inputRange: [0, 1],
    outputRange: [420, 0],
  });

  const Body = scrollable ? ScrollView : View;

  return (
    <Modal visible={visible} transparent animationType="none" onRequestClose={onClose}>
      <Animated.View style={[styles.scrim, { opacity: progress }]}>
        <Pressable style={StyleSheet.absoluteFill} onPress={onClose} accessibilityLabel="閉じる" />
      </Animated.View>

      <View style={styles.anchor} pointerEvents="box-none">
        <Animated.View
          style={[
            styles.sheet,
            tall && styles.sheetTall,
            {
              // キーボードが出ている間はその分だけ持ち上げ、
              // home indicator 分の余白は足さない（キーボードが覆うため）。
              marginBottom: keyboardHeight,
              paddingBottom: keyboardHeight > 0 ? spacing.lg : insets.bottom + spacing.lg,
              transform: [{ translateY }],
            },
          ]}
        >
          <View style={styles.grabber} />
          {title ? (
            <View style={styles.header}>
              <Text style={styles.title}>{title}</Text>
              {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
            </View>
          ) : null}
          <Body
            {...(scrollable
              ? { showsVerticalScrollIndicator: false, contentContainerStyle: styles.scrollBody }
              : {})}
          >
            {children}
          </Body>
        </Animated.View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  scrim: {
    ...StyleSheet.absoluteFill,
    backgroundColor: colors.scrim,
  },
  anchor: {
    flex: 1,
    justifyContent: 'flex-end',
  },
  sheet: {
    backgroundColor: colors.surface,
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.md,
    maxHeight: '88%',
  },
  sheetTall: {
    height: '88%',
  },
  grabber: {
    alignSelf: 'center',
    width: 36,
    height: 4,
    borderRadius: radius.pill,
    backgroundColor: colors.hairlineStrong,
    marginBottom: spacing.lg,
  },
  header: {
    marginBottom: spacing.lg,
  },
  title: {
    ...typography.title,
    color: colors.textPrimary,
  },
  subtitle: {
    ...typography.body,
    color: colors.textSecondary,
    marginTop: spacing.xs,
  },
  scrollBody: {
    paddingBottom: spacing.lg,
  },
});
