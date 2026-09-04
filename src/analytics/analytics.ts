import { isDev } from '../config/env';
import type { AnalyticsEventName, AnalyticsProperties } from './events';

/**
 * Analytics の送信先ポート。
 * β 版では console へ出すだけにしておき、実 SDK 接続時はここに sink を差し込む。
 */
export interface AnalyticsSink {
  track(event: AnalyticsEventName, properties?: AnalyticsProperties): void;
}

class Analytics implements AnalyticsSink {
  private sinks: AnalyticsSink[] = [];

  addSink(sink: AnalyticsSink): void {
    this.sinks.push(sink);
  }

  track(event: AnalyticsEventName, properties?: AnalyticsProperties): void {
    for (const sink of this.sinks) {
      try {
        sink.track(event, properties);
      } catch {
        // 計測の失敗でユーザー操作を止めない。
      }
    }
  }
}

export const analytics = new Analytics();

if (isDev) {
  analytics.addSink({
    track: (event, properties) => {
      // eslint-disable-next-line no-console
      console.log(`[analytics] ${event}`, properties ?? {});
    },
  });
}
