import React, { useMemo, useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MAX_BIO_LENGTH, MINIMUM_AGE } from '../../../config/constants';
import { meetsMinimumAge } from '../../../domain/age';
import { DomainError } from '../../../domain/errors';
import type {
  DiscoveryPreference,
  Gender,
  Photo as PhotoModel,
} from '../../../domain/types';
import { useApp } from '../../../state/AppContext';
import { colors, radius, spacing, typography } from '../../../theme';
import { Button } from '../../components/Button';
import { Chip } from '../../components/Chip';
import { SessionSymbol } from '../../components/SessionSymbol';
import { Toast, useToast } from '../../components/Toast';
import { PhotoPicker } from '../you/PhotoPicker';
import { TagInput } from '../you/TagInput';

/**
 * Onboarding（仕様書 §18）。
 *
 * 最短で Swipe まで到達させる。1つの flow 内で内部 step を切り替え、route は増やさない。
 * 18歳未満は利用不可。ただし生年月日の自己入力は足切りであって、
 * 法定年齢確認は別途 external provider で行う（§19）。
 */
type Step =
  | 'terms'
  | 'name'
  | 'birthDate'
  | 'gender'
  | 'preference'
  | 'photos'
  | 'bio'
  | 'interests'
  | 'location';

const STEPS: Step[] = [
  'terms',
  'name',
  'birthDate',
  'gender',
  'preference',
  'photos',
  'bio',
  'interests',
  'location',
];

const GENDERS: { value: Gender; label: string }[] = [
  { value: 'woman', label: '女性' },
  { value: 'man', label: '男性' },
  { value: 'nonbinary', label: 'ノンバイナリー' },
];

const PREFERENCES: { value: DiscoveryPreference; label: string }[] = [
  { value: 'women', label: '女性' },
  { value: 'men', label: '男性' },
  { value: 'everyone', label: 'すべて' },
];

export function OnboardingFlow() {
  const insets = useSafeAreaInsets();
  const { completeOnboarding, requestLocation } = useApp();
  const { toast, showToast } = useToast();

  const [stepIndex, setStepIndex] = useState(0);
  const [busy, setBusy] = useState(false);

  const [name, setName] = useState('');
  const [birthDate, setBirthDate] = useState('');
  const [gender, setGender] = useState<Gender | null>(null);
  const [preference, setPreference] = useState<DiscoveryPreference | null>(null);
  const [photos, setPhotos] = useState<PhotoModel[]>([]);
  const [bio, setBio] = useState('');
  const [interests, setInterests] = useState<string[]>([]);

  const step = STEPS[stepIndex];

  const canAdvance = useMemo(() => {
    switch (step) {
      case 'terms':
        return true;
      case 'name':
        return name.trim().length > 0;
      case 'birthDate':
        return meetsMinimumAge(birthDate);
      case 'gender':
        return gender !== null;
      case 'preference':
        return preference !== null;
      case 'photos':
        return photos.length > 0;
      // bio と interests は skip 可能（§18）。
      case 'bio':
      case 'interests':
      case 'location':
        return true;
      default:
        return false;
    }
  }, [step, name, birthDate, gender, preference, photos]);

  const finish = async () => {
    if (!gender || !preference) return;
    setBusy(true);
    try {
      await completeOnboarding({
        name,
        birthDate,
        gender,
        discoveryPreference: preference,
        bio,
        interests,
        photos,
      });
    } catch (error) {
      showToast(
        error instanceof DomainError ? error.message : 'プロフィールを保存できませんでした。',
      );
    } finally {
      setBusy(false);
    }
  };

  const advance = () => {
    if (stepIndex < STEPS.length - 1) {
      setStepIndex((index) => index + 1);
      return;
    }
    void finish();
  };

  const birthDateInvalid = birthDate.length === 10 && !meetsMinimumAge(birthDate);

  return (
    <KeyboardAvoidingView
      style={[styles.container, { paddingTop: insets.top }]}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <View style={styles.progress}>
        {STEPS.map((value, index) => (
          <View
            key={value}
            style={[styles.progressBar, index <= stepIndex && styles.progressBarActive]}
          />
        ))}
      </View>

      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {step === 'terms' ? (
          <Step title="Sessionへようこそ" subtitle="今を、出会いに。">
            <View style={styles.terms}>
              <SessionSymbol size={40} />
              <Text style={styles.termsText}>
                Sessionは、今会う意思がある人どうしをつなぐアプリです。{'\n\n'}
                続行すると、利用規約とプライバシーポリシーに同意したものとみなされます。{'\n'}
                Sessionは{MINIMUM_AGE}歳以上の方のみ利用できます。
              </Text>
            </View>
          </Step>
        ) : null}

        {step === 'name' ? (
          <Step title="名前を教えてください" subtitle="相手に表示される名前です。">
            <TextInput
              value={name}
              onChangeText={setName}
              placeholder="ニックネームでもかまいません"
              placeholderTextColor={colors.textTertiary}
              style={styles.input}
              maxLength={20}
              autoFocus
            />
          </Step>
        ) : null}

        {step === 'birthDate' ? (
          <Step
            title="生年月日"
            subtitle={`${MINIMUM_AGE}歳以上の方のみ利用できます。他のユーザーには年齢だけが表示されます。`}
          >
            <TextInput
              value={birthDate}
              onChangeText={setBirthDate}
              placeholder="1998-06-15"
              placeholderTextColor={colors.textTertiary}
              style={styles.input}
              keyboardType="numbers-and-punctuation"
              maxLength={10}
            />
            {birthDateInvalid ? (
              <Text style={styles.error}>{MINIMUM_AGE}歳以上の方のみ利用できます。</Text>
            ) : null}
          </Step>
        ) : null}

        {step === 'gender' ? (
          <Step title="あなたについて" subtitle="プロフィールに表示されます。">
            <View style={styles.row}>
              {GENDERS.map((item) => (
                <Chip
                  key={item.value}
                  label={item.label}
                  selected={gender === item.value}
                  onPress={() => setGender(item.value)}
                />
              ))}
            </View>
          </Step>
        ) : null}

        {step === 'preference' ? (
          <Step title="誰を表示しますか？" subtitle="あとから変更できます。">
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
          </Step>
        ) : null}

        {step === 'photos' ? (
          <Step title="写真を追加" subtitle="1枚から始められます。あとから追加できます。">
            <PhotoPicker photos={photos} onChange={setPhotos} onError={showToast} />
          </Step>
        ) : null}

        {step === 'bio' ? (
          <Step title="ひとこと" subtitle="スキップできます。">
            <TextInput
              value={bio}
              onChangeText={setBio}
              placeholder="今の気分や、行きたい場所など"
              placeholderTextColor={colors.textTertiary}
              style={[styles.input, styles.multiline]}
              multiline
              maxLength={MAX_BIO_LENGTH}
            />
          </Step>
        ) : null}

        {step === 'interests' ? (
          <Step title="興味のあること" subtitle="スキップできます。">
            <TagInput tags={interests} onChange={setInterests} />
          </Step>
        ) : null}

        {step === 'location' ? (
          <Step
            title="位置情報を許可"
            subtitle="近くでSession中の人を表示するために使います。"
          >
            <Text style={styles.note}>
              正確な位置が他のユーザーに表示されることはありません。{'\n'}
              表示されるのは「3km以内」のような大まかな距離だけです。
            </Text>
            <Button
              label="位置情報を許可する"
              variant="secondary"
              onPress={() => {
                void requestLocation().then((granted) => {
                  if (!granted) showToast('位置情報なしでも始められます。');
                });
              }}
            />
          </Step>
        ) : null}
      </ScrollView>

      <View style={[styles.footer, { paddingBottom: insets.bottom + spacing.lg }]}>
        <Button
          label={stepIndex === STEPS.length - 1 ? 'Sessionをはじめる' : '次へ'}
          onPress={advance}
          disabled={!canAdvance}
          loading={busy}
        />
        {stepIndex > 0 ? (
          <Pressable
            accessibilityRole="button"
            onPress={() => setStepIndex((index) => index - 1)}
            style={({ pressed }) => [styles.back, pressed && styles.pressed]}
          >
            <Text style={styles.backLabel}>戻る</Text>
          </Pressable>
        ) : null}
      </View>

      <Toast message={toast} />
    </KeyboardAvoidingView>
  );
}

function Step({
  title,
  subtitle,
  children,
}: {
  title: string;
  subtitle: string;
  children: React.ReactNode;
}) {
  return (
    <View style={styles.step}>
      <Text style={styles.title}>{title}</Text>
      <Text style={styles.subtitle}>{subtitle}</Text>
      <View style={styles.stepBody}>{children}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  progress: {
    flexDirection: 'row',
    gap: 4,
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.md,
  },
  progressBar: { flex: 1, height: 3, borderRadius: radius.pill, backgroundColor: colors.hairline },
  progressBarActive: { backgroundColor: colors.green },
  content: { padding: spacing.xl, flexGrow: 1 },
  step: { gap: spacing.sm },
  title: { ...typography.title, color: colors.white, marginTop: spacing.xl },
  subtitle: { ...typography.body, color: colors.textSecondary, lineHeight: 21 },
  stepBody: { marginTop: spacing.xl, gap: spacing.md },
  input: {
    minHeight: 52,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceRaised,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    color: colors.white,
    ...typography.body,
  },
  multiline: { minHeight: 96, textAlignVertical: 'top' },
  row: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
  error: { ...typography.caption, color: colors.danger },
  note: { ...typography.body, color: colors.textSecondary, lineHeight: 21 },
  terms: { alignItems: 'center', gap: spacing.xl, paddingTop: spacing.xl },
  termsText: { ...typography.body, color: colors.textSecondary, lineHeight: 22, textAlign: 'center' },
  footer: { paddingHorizontal: spacing.xl, gap: spacing.sm },
  back: { alignSelf: 'center', padding: spacing.sm },
  backLabel: { ...typography.caption, color: colors.textSecondary },
  pressed: { opacity: 0.7 },
});
