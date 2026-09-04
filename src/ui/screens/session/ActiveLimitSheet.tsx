import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { MAX_ACTIVE_SESSIONS } from '../../../config/constants';
import { colors, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Sheet } from '../../components/Sheet';

/**
 * Active Session Limit（仕様書 §7）。
 *
 * Match 収集アプリ化を防ぐため、Active Session が上限に達したら Request 送信を止める。
 * Chat 履歴そのものは消さない。期限が終われば枠が空く。
 */
interface Props {
  visible: boolean;
  onClose: () => void;
  onOpenMessages: () => void;
}

export function ActiveLimitSheet({ visible, onClose, onOpenMessages }: Props) {
  return (
    <Sheet visible={visible} onClose={onClose}>
      <View style={styles.body}>
        <Text style={styles.headline}>
          今動いているSessionが{MAX_ACTIVE_SESSIONS}件あります。
        </Text>
        <Text style={styles.text}>まずは今のSessionを楽しもう。</Text>
        <View style={styles.actions}>
          <Button label="メッセージを見る" onPress={onOpenMessages} />
          <Button label="閉じる" variant="ghost" onPress={onClose} />
        </View>
      </View>
    </Sheet>
  );
}

const styles = StyleSheet.create({
  body: { gap: spacing.sm },
  headline: { ...typography.headline, color: colors.white },
  text: { ...typography.body, color: colors.textSecondary },
  actions: { gap: spacing.sm, marginTop: spacing.xl },
});
