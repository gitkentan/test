/**
 * Analytics events（仕様書 §26）
 * UI を増やさず、最低限のイベントだけを入れる。
 */
export const AnalyticsEvent = {
  signupStarted: 'signup_started',
  signupCompleted: 'signup_completed',

  ageVerificationStarted: 'age_verification_started',
  ageVerificationCompleted: 'age_verification_completed',
  ageVerificationFailed: 'age_verification_failed',

  sessionOn: 'session_on',
  sessionOff: 'session_off',
  sessionExpired: 'session_expired',
  intentSelected: 'intent_selected',
  sessionReactivated: 'session_reactivated',

  discoveryView: 'discovery_view',
  profileView: 'profile_view',
  swipeSkip: 'swipe_skip',
  sessionRequestSent: 'session_request_sent',
  sessionRequestExpired: 'session_request_expired',

  sessionCreated: 'session_created',
  matchOverlayViewed: 'match_overlay_viewed',

  messagesOpened: 'messages_opened',
  chatStarted: 'chat_started',
  messageSent: 'message_sent',

  reportSubmitted: 'report_submitted',
  userBlocked: 'user_blocked',

  discoveryPoolLow: 'discovery_pool_low',
  discoveryEmpty: 'discovery_empty',
} as const;

export type AnalyticsEventName = (typeof AnalyticsEvent)[keyof typeof AnalyticsEvent];

export type AnalyticsProperties = Record<string, string | number | boolean | null>;
