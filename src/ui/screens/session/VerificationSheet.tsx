import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { MINIMUM_AGE } from '../../../config/constants';
import { colors, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Sheet } from '../../components/Sheet';

/**
 * Verification Sheet（仕様書 §19）。
 *
 * Session ON / Request 送信 / free-form chat には age_verified = true が必要。
 * 未確認時はこの軽量なシートだけを出し、本人確認そのものは外部プロバイダへ委譲する。
 * Session native UI 内でフル本人確認システムを自作しない。
 */
interface Props {
  visible: boolean;
  onClose: () => void;
  onStart: () => void;
  busy?: boolean;
}

export function VerificationSheet({ visible, onClose, onStart, busy = false }: Props) {
  return (
    <Sheet
      visible={visible}
      onClose={onClose}
      title="年齢確認をお願いします"
      subtitle={`Sessionは${MINIMUM_AGE}歳以上の方のみ利用できます。`}
    >
      <View style={styles.body}>
        <Text style={styles.text}>
          安全のため、確認は外部の年齢確認サービスで行います。{'\n'}
          Sessionが本人確認書類の画像を受け取ることはありません。
        </Text>
        <View style={styles.actions}>
          <Button label="年齢確認へ進む" onPress={onStart} loading={busy} />
          <Button label="あとで" variant="ghost" onPress={onClose} disabled={busy} />
        </View>
      </View>
    </Sheet>
  );
}

const styles = StyleSheet.create({
  body: { gap: spacing.xl },
  text: { ...typography.body, color: colors.textSecondary, lineHeight: 22 },
  actions: { gap: spacing.sm },
});
