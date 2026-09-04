import React, { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { analytics } from '../../../analytics/analytics';
import { AnalyticsEvent } from '../../../analytics/events';
import type { DiscoveryResult } from '../../../data/repositories';
import { DomainError } from '../../../domain/errors';
import type { DiscoveryCandidate, Intent, ReportReason } from '../../../domain/types';
import { useApp } from '../../../state/AppContext';
import { colors, spacing, typography } from '../../../theme';
import { SessionStatusLine } from '../../components/SessionStatusLine';
import { SessionSymbol } from '../../components/SessionSymbol';
import { Toast, useToast } from '../../components/Toast';
import { ReportSheet } from '../safety/ReportSheet';
import { ActiveLimitSheet } from './ActiveLimitSheet';
import { DiscoveryEmptyState } from './EmptyState';
import { IntentSheet } from './IntentSheet';
import { MatchOverlay, type MatchPresentation } from './MatchOverlay';
import { ProfileDetailSheet } from './ProfileDetailSheet';
import { SwipeSurface, type SwipeDirection } from './SwipeSurface';
import { VerificationSheet } from './VerificationSheet';

/**
 * Session タブ（仕様書 §4）。
 *
 * 原則1つの Swipe Surface。別の Home / Discover / NOW ページは作らない。
 * Intent / Profile detail / Verification / Active limit はすべて Sheet で処理する。
 */
interface Props {
  onOpenChat: (conversationId: string) => void;
  onOpenMessages: () => void;
}

export function SessionTab({ onOpenChat, onOpenMessages }: Props) {
  const insets = useSafeAreaInsets();
  const { services, auth, user, sessionStatus, turnSessionOn, turnSessionOff, startAgeVerification } =
    useApp();
  const { toast, showToast } = useToast();

  const [discovery, setDiscovery] = useState<DiscoveryResult | null>(null);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  const [intentSheetOpen, setIntentSheetOpen] = useState(false);
  const [verificationOpen, setVerificationOpen] = useState(false);
  const [activeLimitOpen, setActiveLimitOpen] = useState(false);
  const [detailCandidate, setDetailCandidate] = useState<DiscoveryCandidate | null>(null);
  const [reportTarget, setReportTarget] = useState<DiscoveryCandidate | null>(null);
  const [match, setMatch] = useState<(MatchPresentation & { conversationId: string }) | null>(null);

  /**
   * OFF 状態で右 Swipe されたときに保留する相手。
   * Intent 選択 → Session ON → そのまま Request 送信、まで一気に進める（§5）。
   */
  const [pendingRequest, setPendingRequest] = useState<DiscoveryCandidate | null>(null);

  const userId = auth?.userId ?? null;

  const loadDiscovery = useCallback(async () => {
    if (!userId) return;
    setLoading(true);
    try {
      setDiscovery(await services.discovery.load(userId));
    } finally {
      setLoading(false);
    }
  }, [services, userId]);

  useEffect(() => {
    void loadDiscovery();
  }, [loadDiscovery]);

  /** 板の先頭を1枚落とす。再フェッチせずに次のカードへ進める。 */
  const dropTopCard = useCallback((candidateId: string) => {
    setDiscovery((current) =>
      current
        ? { ...current, candidates: current.candidates.filter((c) => c.userId !== candidateId) }
        : current,
    );
  }, []);

  const sendRequest = useCallback(
    async (candidate: DiscoveryCandidate) => {
      if (!userId || !user) return;
      try {
        const outcome = await services.discovery.sendRequest(userId, candidate.userId);
        dropTopCard(candidate.userId);
        if (outcome.kind === 'matched') {
          analytics.track(AnalyticsEvent.matchOverlayViewed);
          setMatch({
            conversationId: outcome.conversationId,
            partnerName: candidate.name,
            partnerPhoto: candidate.photos[0] ?? null,
            partnerIntent: candidate.intent,
            myName: user.name,
            myPhoto: user.photos[0] ?? null,
            myIntent: sessionStatus.currentIntent,
          });
        }
      } catch (error) {
        if (error instanceof DomainError && error.code === 'ACTIVE_SESSION_LIMIT') {
          setActiveLimitOpen(true);
          return;
        }
        if (error instanceof DomainError && error.code === 'AGE_NOT_VERIFIED') {
          setVerificationOpen(true);
          return;
        }
        showToast(error instanceof DomainError ? error.message : '送信できませんでした。');
      }
    },
    [services, userId, user, sessionStatus.currentIntent, dropTopCard, showToast],
  );

  const handleSwipe = useCallback(
    async (candidate: DiscoveryCandidate, direction: SwipeDirection) => {
      if (!userId) return;

      if (direction === 'left') {
        dropTopCard(candidate.userId);
        await services.discovery.skip(userId, candidate.userId);
        return;
      }

      // 年齢確認が済んでいなければ、まず Verification Sheet（§19 Gate）。
      if (!user?.ageVerified) {
        setPendingRequest(candidate);
        setVerificationOpen(true);
        return;
      }

      // OFF 状態では直接送れない。Intent を選ばせてから送る（§5）。
      if (!sessionStatus.sessionOn) {
        setPendingRequest(candidate);
        setIntentSheetOpen(true);
        return;
      }

      await sendRequest(candidate);
    },
    [services, userId, user, sessionStatus.sessionOn, dropTopCard, sendRequest],
  );

  const handleSelectIntent = useCallback(
    async (intent: Intent) => {
      setBusy(true);
      try {
        await turnSessionOn(intent);
        setIntentSheetOpen(false);
        const target = pendingRequest;
        setPendingRequest(null);
        if (target) {
          await sendRequest(target);
        } else {
          // Intent が変わると same-intent の優先度が変わるので板を引き直す（§8）。
          await loadDiscovery();
        }
      } catch (error) {
        if (error instanceof DomainError && error.code === 'AGE_NOT_VERIFIED') {
          setIntentSheetOpen(false);
          setVerificationOpen(true);
          return;
        }
        showToast(error instanceof DomainError ? error.message : 'Sessionをオンにできませんでした。');
      } finally {
        setBusy(false);
      }
    },
    [turnSessionOn, pendingRequest, sendRequest, loadDiscovery, showToast],
  );

  const handleStartVerification = useCallback(async () => {
    setBusy(true);
    try {
      const result = await startAgeVerification();
      setVerificationOpen(false);
      if (result.status !== 'verified') {
        setPendingRequest(null);
        showToast(result.message ?? '年齢確認が完了しませんでした。');
        return;
      }
      // 確認が済んだら、止めていた導線をそのまま続ける。
      if (pendingRequest) setIntentSheetOpen(true);
    } finally {
      setBusy(false);
    }
  }, [startAgeVerification, pendingRequest, showToast]);

  const handleReport = useCallback(
    async (reason: ReportReason, details: string | null) => {
      if (!userId || !reportTarget) return;
      await services.safety.report(userId, reportTarget.userId, reason, details);
      setReportTarget(null);
      setDetailCandidate(null);
      showToast('報告を受け付けました。');
    },
    [services, userId, reportTarget, showToast],
  );

  const handleBlock = useCallback(async () => {
    if (!userId || !detailCandidate) return;
    await services.safety.block(userId, detailCandidate.userId);
    dropTopCard(detailCandidate.userId);
    setDetailCandidate(null);
    showToast('ブロックしました。');
  }, [services, userId, detailCandidate, dropTopCard, showToast]);

  const candidates = discovery?.candidates ?? [];
  const showEmpty = !loading && candidates.length === 0;

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <View style={styles.header}>
        <View style={styles.wordmarkRow}>
          <SessionSymbol size={18} />
          <Text style={styles.wordmark}>Session</Text>
        </View>
        <SessionStatusLine status={sessionStatus} onPress={() => setIntentSheetOpen(true)} />
      </View>

      <View style={styles.surface}>
        {loading ? (
          <View style={styles.loading}>
            <ActivityIndicator color={colors.green} />
          </View>
        ) : showEmpty ? (
          <DiscoveryEmptyState
            variant={discovery?.poolIsLow ? 'low' : 'empty'}
            radiusKm={discovery?.radiusKm ?? 0}
          />
        ) : (
          <>
            {discovery?.poolIsLow ? (
              <Text style={styles.poolNotice}>
                今は近くのSessionが少なめです。{discovery.radiusKm}kmまで範囲を広げています。
              </Text>
            ) : null}
            <SwipeSurface
              candidates={candidates}
              onSwipe={(candidate, direction) => void handleSwipe(candidate, direction)}
              onOpenDetail={(candidate) => {
                analytics.track(AnalyticsEvent.profileView);
                setDetailCandidate(candidate);
              }}
            />
          </>
        )}
      </View>

      <IntentSheet
        visible={intentSheetOpen}
        currentIntent={sessionStatus.currentIntent}
        sessionOn={sessionStatus.sessionOn}
        pendingRequestName={pendingRequest?.name ?? null}
        busy={busy}
        onSelect={(intent) => void handleSelectIntent(intent)}
        onTurnOff={() => {
          setIntentSheetOpen(false);
          void turnSessionOff();
        }}
        onClose={() => {
          setIntentSheetOpen(false);
          setPendingRequest(null);
        }}
      />

      <VerificationSheet
        visible={verificationOpen}
        busy={busy}
        onStart={() => void handleStartVerification()}
        onClose={() => {
          setVerificationOpen(false);
          setPendingRequest(null);
        }}
      />

      <ActiveLimitSheet
        visible={activeLimitOpen}
        onClose={() => setActiveLimitOpen(false)}
        onOpenMessages={() => {
          setActiveLimitOpen(false);
          onOpenMessages();
        }}
      />

      <ProfileDetailSheet
        candidate={detailCandidate}
        onClose={() => setDetailCandidate(null)}
        onReport={() => setReportTarget(detailCandidate)}
        onBlock={() => void handleBlock()}
      />

      <ReportSheet
        visible={reportTarget !== null}
        targetName={reportTarget?.name ?? ''}
        onClose={() => setReportTarget(null)}
        onSubmit={handleReport}
      />

      <MatchOverlay
        match={match}
        onOpenChat={() => {
          const conversationId = match?.conversationId;
          setMatch(null);
          if (conversationId) onOpenChat(conversationId);
        }}
        onKeepSwiping={() => setMatch(null)}
      />

      <Toast message={toast} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  header: {
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.sm,
    paddingBottom: spacing.md,
    gap: spacing.xs,
  },
  wordmarkRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
  wordmark: { ...typography.headline, color: colors.white, letterSpacing: 0.4 },
  surface: { flex: 1, paddingHorizontal: spacing.lg, paddingBottom: spacing.md },
  loading: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  poolNotice: {
    ...typography.caption,
    color: colors.textTertiary,
    textAlign: 'center',
    paddingBottom: spacing.sm,
  },
});
