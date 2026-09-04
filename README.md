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

## Discovery の表示対象ルール

`src/domain/discoveryPreference.ts` に集約している。backend 側に判定を書き足さない。

1. 表示は**相互**に成立したときだけ。片側の設定だけでは出さない。
2. `women` / `men` はその性別として登録しているユーザーだけを対象にする。
3. **non-binary ユーザーは `everyone` を選んでいる相手にだけ表示される。**
   二値の絞り込みに non-binary を割り当てると、本人が選んでいない性別として
   扱うことになるため、そうしない。non-binary ユーザー自身が「誰を見るか」は制限されない。

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
外部の適合プロバイダから返ってきた結果をサーバが確認したときにだけ true になる。
Session 側で本人確認書類の画像を保持する設計にはしていない。

### フロー

```
Session
  ↓ startVerification              サーバが試行 (reference) を採番
external compliant provider        EXPO_PUBLIC_AGE_VERIFICATION_URL へ遷移
  ↓ 本人確認
callback / return to Session       deep link: session://age-verification
  ↓ confirmVerification            サーバがプロバイダの結果を照会
age_verified = true
```

### 信頼モデル

**戻ってきた URL のクエリを client が読んで確認済みにはしない。**
リダイレクトは「ユーザーが操作を終えた」という合図でしかなく、
確認済みかどうかはサーバがプロバイダに照会した結果だけで決まる。

`AgeVerificationRepository` には client から `age_verified` を立てる経路が無い。

| メソッド | 誰が呼ぶ | できること |
|---|---|---|
| `startVerification` | client | 試行の採番と確認 URL の取得 |
| `confirmVerification` | client | 状態の**照会のみ**（`verified` / `pending` / `rejected`） |
| `applyVerificationResult` | **サーバのみ** | プロバイダの結果を取り込む（webhook 相当） |
| `devForceVerified` | 開発環境のみ | 本番ビルドでは必ず失敗する |

`age_verified = true` が必要なもの：**Session ON / Session Request 送信 / free-form chat 送信**

### プロバイダ側に必要な実装

1. `EXPO_PUBLIC_AGE_VERIFICATION_URL` に `?reference=<id>&redirect_uri=<deep link>` が付いて遷移する
2. 確認完了後、`redirect_uri` へリダイレクトして戻す
3. **結果本体は webhook / サーバ間 API でバックエンドへ渡す**（client 経由では渡さない）
4. バックエンドが `applyVerificationResult` 相当で `reference` に結果を紐づける

プロバイダの審査が非同期な場合、`confirmVerification` は `pending` を返す。
UI は「完了までしばらくお待ちください」と表示し、再試行できる。

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
npm test        # 61 tests
npm run typecheck
```

- `sessionStatus.test.ts` — TTL、Intent 変更でのリセット、再起動後に期限切れ ON が復活しないこと
- `discovery.test.ts` — priority 1〜4、Session OFF も候補に残ること、radius fallback、Empty State
- `discoveryPreference.test.ts` — 相互成立、non-binary の表示規則
- `distance.test.ts` — 距離バケット、正確な距離を出さないこと
- `requests.test.ts` — OFF/年齢未確認では送れない、duplicate、期限切れ、Skip クールダウン
- `matching.test.ts` — Session は1件だけ、active_until、上限、期限後 past
- `ageVerification.test.ts` — client が確認済みを主張できないこと、偽 reference、他人の reference
- `safetyAndChat.test.ts` — Block の双方向遮断、年齢 gate、当事者以外の遮断
- `fixtures.test.ts` — 本番ビルドで fixture が投入されないこと
- `fullLoop.test.ts` — signup → onboarding → 年齢確認 → Session ON → swipe → Session → chat

## 本番公開前に必要な設定値

| 環境変数 | 必須 | 未設定時の挙動 |
|---|---|---|
| `EXPO_PUBLIC_AGE_VERIFICATION_URL` | **必須** | 年齢確認が完了できず、Session ON / Request / Chat がすべて使えない。開発環境のみローカルで確認済みにしてループを通せる |
| `EXPO_PUBLIC_DISABLE_FIXTURES` | 任意 | `1` で開発環境でも fixture を投入しない。本番ビルドでは値に関わらず常に無効 |

`src/config/env.ts` の `findMissingProductionConfig()` が不足を検出できる。

`app.json` 側で公開前に差し替えが必要なもの：

- `ios.bundleIdentifier` / `android.package` — 現在は `app.session.beta`
- `SettingsSheet` の利用規約 / プライバシーポリシー URL — 現在は `https://session.app/...` のプレースホルダ

## 未接続箇所

| 項目 | 現状 | 公開に必要なこと |
|---|---|---|
| **バックエンド** | 端末内の `SessionBackend`。**ユーザー間の同期は行われない** | 実サーバの実装と、`src/data/memory/repositories.ts` の差し替え |
| **Session Request / Match / Chat の同期** | 同上。同じ端末内でしか成立しない | 上に同じ。相互 Request の成立はトランザクション + 一意制約が必須 |
| **画像アップロード** | ローカル URI をそのまま保持 | オブジェクトストレージと、`photos[].uri` を配信 URL にする経路 |
| **認証** | メールアドレスのみ、パスワードなし | OTP / OAuth への差し替え（`AuthRepository`） |
| **年齢確認プロバイダ** | 接続先 URL 未設定 | 上記「年齢確認」節のプロバイダ側実装 |
| **ロゴ** | `tools/generate-brand-assets.js` で生成した仮の symbol | 正式アセットへの差し替え |
| **Push 通知** | 未実装（P1） | — |

## 残リスク

**公開を止めるもの**

- **ユーザー間の同期が無い。** 現在の実装は端末内で完結しており、
  別の端末のユーザーへ Request が届かない。β 公開にはバックエンドが要る。
- **年齢確認プロバイダが未接続。** 未設定のまま本番ビルドを出すと、
  ユーザーは Session ON も Request も Chat もできない。
- **認証が実質無い。** メールアドレスを入れるだけでそのアカウントに入れてしまう。

**公開後に効いてくるもの**

- **non-binary ユーザーの母数が小さい。** 表示対象が `everyone` を選んだ層に限られる。
  本来は「どの検索結果に自分を表示するか」を本人が選べるようにすべきだが、
  β 版では設定項目を増やさない方針のため見送っている。
- **Report の受け口が無い。** 記録はされるが、運用側で確認する導線が未整備。
- **画像のモデレーションが無い。** UGC を扱う以上、通報前提の運用になる。
- **キーボード表示時のレイアウトは実機未検証。**
  `useKeyboardHeight` は iOS の `keyboardWillChangeFrame` を前提にしており、
  Web プレビューでは検証できない。実機 / シミュレータでの確認が必要。
- **Safe Area も実機未検証。** コード上は全画面が `useSafeAreaInsets` を適用しており、
  inset を注入したブラウザ検証では崩れないことを確認済み。

## 次にやること

1. バックエンドの実装と repository 差し替え（同期・画像・認証）
2. 年齢確認プロバイダの接続
3. 正式ロゴアセットへの差し替え
4. iOS シミュレータ / 実機での keyboard・Safe Area 検証
5. P1 — Push 通知 / Meet アンケート / Received Requests 表示 / 近くの Session 人数 / Session 再開ショートカット
