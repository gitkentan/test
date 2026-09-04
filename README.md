# Session β

**今を、出会いに。**

「マッチを増やす」のではなく、**「今会える人と会うまでの時間を短くする」**ためのアプリ。

```
Session ON → Intent → Swipe → Session → Chat
```

この1本のループを最短・最軽量で回すことだけを目的にしている。
機能を足す前に必ず「これは Session ON という行動を強くするか？」を確認する。NO なら β 版には入れない。

---

## 技術スタック

| | |
|---|---|
| Framework | Expo SDK 57 / React Native 0.86 / React 19 |
| 言語 | TypeScript（strict） |
| ナビゲーション | 自前の状態ベース（3タブ + Chat のみなのでライブラリを足していない） |
| Swipe | React Native 標準の `Animated` + `PanResponder` |
| 永続化 | AsyncStorage（`KeyValueStorage` ポートの裏） |
| テスト | Jest（jest-expo） |

iPhone-first。UI は日本語を基本とする。

## セットアップ

```bash
npm install
npm run ios      # iOS シミュレータ / Expo Go
npm run web      # ブラウザでのプレビュー（開発用）

npm run typecheck
npm test
```

開発環境では `src/data/fixtures/devSeed.ts` のダミーユーザー26人が投入され、
Session ON → Swipe → Session 成立 → Chat まで一人で通せる。

## アーキテクチャ

UI とサービス層を分離し、データアクセスはすべて repository インターフェース越しに行う。
β 版の実装は端末内の `SessionBackend` だが、**UI とサービス層はそれを知らない**ので、
実バックエンド接続時は `src/data/memory/repositories.ts` を HTTP 実装へ差し替えるだけで済む。

```
src/
├── config/          Product constants（§27）と環境判定
├── theme/           Session Green / Near Black のトークン（§21）
├── domain/          純粋ロジック。UI にも I/O にも依存しない
│   ├── sessionStatus.ts   Session ON/OFF の状態機械と TTL（§6, §28）
│   ├── discovery.ts       Priority ranking と radius fallback（§8, §9）
│   ├── requests.ts        Request の送信条件と期限（§11, §29）
│   ├── matching.ts        相互 Request → Session 成立（§12）
│   ├── sessions.ts        Active Session 上限と期限（§7, §30）
│   ├── distance.ts        距離バケット化。緯度経度は外へ出さない（§10）
│   └── blocks.ts / age.ts / errors.ts
├── data/
│   ├── repositories.ts    ポート定義。UI/サービスが依存するのはここだけ
│   ├── memory/backend.ts  β 版のサーバ相当。§25 の検証をすべて担う
│   ├── memory/repositories.ts  ポートの実装（差し替え対象）
│   └── fixtures/          開発環境限定の seed
├── services/        ドメイン操作 + Analytics を束ねる層
├── analytics/       §26 のイベント定義と sink
├── state/           AppContext（誰がログイン中か / Session は ON か）
├── navigation/      3タブの shell（§3, §22, §23）
└── ui/              screens と components
```

### server-side 検証（§25）

可否判定は必ず `SessionBackend` を通る。UI 側のガードは体験のためのもので、権限判断ではない。

`age_verified` / `session_on` / `session expiry` / `request expiry` /
`max active sessions` / `block relationship` / `duplicate request` /
`duplicate match` / `self request` / `user availability`

相互 Request の成立はペア単位のロックで直列化し、Session と Conversation が
二重に作られないようにしている。実バックエンドでは DB トランザクション + 一意制約へ置き換える前提。

### 主要な仕様の実装場所

| 仕様 | 実装 |
|---|---|
| Session ON / TTL 6時間 / Intent 変更で TTL リセット | `src/domain/sessionStatus.ts` |
| Active Session 上限 3件 | `src/domain/sessions.ts` |
| Discovery priority 1〜4 / 半径 fallback | `src/domain/discovery.ts` |
| 距離は「3km以内」等のバケットのみ | `src/domain/distance.ts` |
| Skip の 24時間クールダウン | `src/domain/requests.ts` |
| 相互 Request → Session 成立 | `src/domain/matching.ts` |
| Block の双方向遮断 | `src/domain/blocks.ts` + backend |
| Swipe Surface / Match Overlay | `src/ui/screens/session/` |

## Product Constants

実データを見てから調整できるよう `src/config/constants.ts` に集約している。

```ts
SESSION_TTL_HOURS = 6
MAX_ACTIVE_SESSIONS = 3
DEFAULT_DISCOVERY_RADIUS_KM = 5
DISCOVERY_RADIUS_STEPS_KM = [5, 10, 30]
MIN_DISCOVERY_POOL = 20
SKIP_COOLDOWN_HOURS = 24
```

## 年齢確認（§19）

**生年月日の自己入力だけを法定年齢確認の完了として扱っていない。**

Onboarding での生年月日入力は 18 歳未満の足切りにすぎず、`age_verified` は
外部の適合プロバイダから返ってきた結果でのみ true になる。
Session 側で本人確認書類の画像を保持する設計にはしていない。

`age_verified = true` が必要なもの：**Session ON / Session Request 送信 / free-form chat 送信**

プロバイダの接続先は `EXPO_PUBLIC_AGE_VERIFICATION_URL` で指定する。
未設定の場合、開発環境ではローカルで確認済みとして扱いループを試せるようにしてあるが、
**本番では未設定を成功として扱わない**（`src/services/AgeVerificationService.ts`）。

## ブランドアセット

`assets/` の icon / splash は `tools/generate-brand-assets.js` で決定的に再生成できる。
Session symbol は astroid（4方向に尖ったスパークル）で、同じ形状を
`src/ui/components/SessionSymbol.tsx` がベクターで持つ。

- Session Green `#32F783` — NOW / ACTIVE / AVAILABLE / SELECTED / PRIMARY ACTION / SESSION STARTED
- Near Black `#080B0B`
- White `#FFFFFF`

Green の使用率はおおむね 5–10%。

> **正式な Session logo アセットが支給された場合は、そちらを正として `assets/` に配置し、
> このスクリプトで上書きしないこと。**

## β 版に入れていないもの

AI / Boost / Session+ / AdMob / 有料プラン / 地図 / 店舗予約 / カレンダー / グループ募集 /
Reviews / Video call / Voice call / Audio profile / GIF / リアルタイム位置共有 /
詳細相性診断 / 複数 Intent 同時選択 / 細かい時間指定 / 高度な推薦 AI /
Face verification badge / 複数課金 tier / Likes 専用タブ / 複雑な検索条件

## テスト

Acceptance Criteria（§31）と Definition of Done（§32）を `__tests__/` で担保している。

```bash
npm test
```

- `sessionStatus.test.ts` — TTL、Intent 変更でのリセット、再起動後に期限切れ ON が復活しないこと
- `discovery.test.ts` — priority 1〜4、Session OFF も候補に残ること、radius fallback、Empty State
- `distance.test.ts` — 距離バケット、正確な距離を出さないこと
- `requests.test.ts` — OFF/年齢未確認では送れない、duplicate、期限切れ、Skip クールダウン
- `matching.test.ts` — Session は1件だけ、active_until、上限、期限後 past
- `safetyAndChat.test.ts` — Block の双方向遮断、年齢 gate、当事者以外の遮断
- `fullLoop.test.ts` — signup → onboarding → 年齢確認 → Session ON → swipe → Session → chat

## 未接続 / 次にやること

β 版として意図的に stub のままにしてある箇所：

- **認証** — メールアドレスのみ。OTP / OAuth へ差し替える（`AuthRepository`）
- **年齢確認プロバイダ** — 接続先 URL 未設定（上記）
- **バックエンド** — 端末内 `SessionBackend`。ユーザー間の実同期は未実装
- **画像アップロード** — ローカル URI をそのまま保持。ストレージ未接続
- **P1** — Push 通知 / Meet アンケート / Received Requests 表示 / 近くの Session 人数 / Session 再開ショートカット
