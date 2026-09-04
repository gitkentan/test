import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react';
import { EXPIRY_TICK_MS } from '../config/constants';
import { deviceStorage } from '../data/asyncStorage';
import type { AuthSession, ProfileDraft, SignInResult } from '../data/repositories';
import { offStatus } from '../domain/sessionStatus';
import type { Intent, SessionStatus, User } from '../domain/types';
import { analytics } from '../analytics/analytics';
import { AnalyticsEvent } from '../analytics/events';
import { createServices, type Services } from '../services';
import type { VerificationResult } from '../services/AgeVerificationService';
import type { KeyValueStorage } from '../data/storage';

/**
 * アプリ全体で共有する状態。
 *
 * 保持するのは「今ログインしているのは誰か」「プロフィールは埋まっているか」
 * 「Session は ON か」の3つだけ。それ以外は画面側で services を叩いて取る。
 */

export type AppPhase = 'loading' | 'unauthenticated' | 'onboarding' | 'ready';

interface AppContextValue {
  services: Services;
  phase: AppPhase;
  auth: AuthSession | null;
  user: User | null;
  sessionStatus: SessionStatus;
  requestSignIn(email: string): Promise<SignInResult>;
  verifyCode(email: string, code: string): Promise<void>;
  signOut(): Promise<void>;
  deleteAccount(): Promise<void>;
  completeOnboarding(draft: ProfileDraft): Promise<void>;
  refreshUser(): Promise<void>;
  turnSessionOn(intent: Intent): Promise<void>;
  turnSessionOff(): Promise<void>;
  startAgeVerification(): Promise<VerificationResult>;
  requestLocation(): Promise<boolean>;
}

const AppContext = createContext<AppContextValue | null>(null);

const ANONYMOUS = 'anonymous';

export function AppProvider({
  children,
  storage = deviceStorage,
}: {
  children: React.ReactNode;
  storage?: KeyValueStorage;
}) {
  const services = useMemo(() => createServices(storage), [storage]);

  const [phase, setPhase] = useState<AppPhase>('loading');
  const [auth, setAuth] = useState<AuthSession | null>(null);
  const [user, setUser] = useState<User | null>(null);
  const [sessionStatus, setSessionStatus] = useState<SessionStatus>(() => offStatus(ANONYMOUS));

  const statusRef = useRef(sessionStatus);
  statusRef.current = sessionStatus;

  const hydrate = useCallback(
    async (session: AuthSession | null) => {
      setAuth(session);
      if (!session) {
        setUser(null);
        setSessionStatus(offStatus(ANONYMOUS));
        setPhase('unauthenticated');
        return;
      }
      const profile = await services.profile.getUser(session.userId);
      setUser(profile);
      if (!profile) {
        setSessionStatus(offStatus(session.userId));
        setPhase('onboarding');
        return;
      }
      // 期限切れの ON が復元されないよう、必ず repository 経由で取り直す（§31）。
      setSessionStatus(await services.session.getStatus(session.userId));
      setPhase('ready');
    },
    [services],
  );

  useEffect(() => {
    let cancelled = false;
    void (async () => {
      const session = await services.repos.auth.getCurrentSession();
      if (!cancelled) await hydrate(session);
    })();
    return () => {
      cancelled = true;
    };
  }, [services, hydrate]);

  /**
   * Session の自動失効を画面へ反映する（§6）。
   * カウントダウンは出さないので、粗い間隔で十分。
   */
  useEffect(() => {
    if (phase !== 'ready' || !auth) return;
    const timer = setInterval(() => {
      void (async () => {
        const next = await services.session.refreshExpiry(auth.userId, statusRef.current);
        if (next.sessionOn !== statusRef.current.sessionOn || next.currentIntent !== statusRef.current.currentIntent) {
          setSessionStatus(next);
        }
      })();
    }, EXPIRY_TICK_MS);
    return () => clearInterval(timer);
  }, [phase, auth, services]);

  /**
   * 確認コードを送る。端末内構成ではその場でセッションが確立するので、
   * 呼び出し側は返り値を見てコード入力へ進むかどうかを決める。
   */
  const requestSignIn = useCallback(
    async (email: string): Promise<SignInResult> => {
      analytics.track(AnalyticsEvent.signupStarted);
      const result = await services.repos.auth.requestSignIn(email);
      if (result.kind === 'session') await hydrate(result.session);
      return result;
    },
    [services, hydrate],
  );

  const verifyCode = useCallback(
    async (email: string, code: string) => {
      const session = await services.repos.auth.verifyCode(email, code);
      await hydrate(session);
    },
    [services, hydrate],
  );

  const signOut = useCallback(async () => {
    await services.repos.auth.signOut();
    await hydrate(null);
  }, [services, hydrate]);

  const deleteAccount = useCallback(async () => {
    if (auth) await services.repos.auth.deleteAccount(auth.userId);
    await hydrate(null);
  }, [services, hydrate, auth]);

  const completeOnboarding = useCallback(
    async (draft: ProfileDraft) => {
      if (!auth) return;
      const profile = await services.profile.completeOnboarding(auth.userId, draft);
      setUser(profile);
      setSessionStatus(await services.session.getStatus(auth.userId));
      setPhase('ready');
    },
    [services, auth],
  );

  const refreshUser = useCallback(async () => {
    if (!auth) return;
    setUser(await services.profile.getUser(auth.userId));
  }, [services, auth]);

  const turnSessionOn = useCallback(
    async (intent: Intent) => {
      if (!auth) return;
      setSessionStatus(await services.session.turnOn(auth.userId, intent, statusRef.current));
    },
    [services, auth],
  );

  const turnSessionOff = useCallback(async () => {
    if (!auth) return;
    setSessionStatus(await services.session.turnOff(auth.userId));
  }, [services, auth]);

  const startAgeVerification = useCallback(async (): Promise<VerificationResult> => {
    if (!auth) {
      return { status: 'unavailable', user: null, message: 'ログインが必要です。' };
    }
    const result = await services.ageVerification.start(auth.userId);
    if (result.user) setUser(result.user);
    return result;
  }, [services, auth]);

  const requestLocation = useCallback(async () => {
    if (!auth) return false;
    const updated = await services.profile.requestLocation(auth.userId);
    if (updated) setUser(updated);
    return updated !== null;
  }, [services, auth]);

  const value = useMemo<AppContextValue>(
    () => ({
      services,
      phase,
      auth,
      user,
      sessionStatus,
      requestSignIn,
      verifyCode,
      signOut,
      deleteAccount,
      completeOnboarding,
      refreshUser,
      turnSessionOn,
      turnSessionOff,
      startAgeVerification,
      requestLocation,
    }),
    [
      services,
      phase,
      auth,
      user,
      sessionStatus,
      requestSignIn,
      verifyCode,
      signOut,
      deleteAccount,
      completeOnboarding,
      refreshUser,
      turnSessionOn,
      turnSessionOff,
      startAgeVerification,
      requestLocation,
    ],
  );

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp(): AppContextValue {
  const value = useContext(AppContext);
  if (!value) throw new Error('useApp must be used inside <AppProvider>');
  return value;
}

/** 認証済みユーザーが確定している画面でだけ使う。 */
export function useCurrentUser(): User {
  const { user } = useApp();
  if (!user) throw new Error('useCurrentUser requires a completed profile');
  return user;
}
