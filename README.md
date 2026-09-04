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
cp .env.example .env.local     # 値は下の「本番公開前に必要な設定値」を参照
npm run ios                    # iOS シミュレータ / Expo Go
npm run web                    # ブラウザでのプレビュー（開発用）

npm run typecheck
npm test                       # 77 tests（TypeScript）
./scripts/test-sql.sh          # 29 assertions（実 Postgres に対するサーバ側ルール）
```

### バックエンド未設定でも動く

`EXPO_PUBLIC_SUPABASE_URL` / `EXPO_PUBLIC_SUPABASE_ANON_KEY` が無い場合、
端末内で完結する `SessionBackend` にフォールバックする。
開発環境では fixture ユーザー26人が投入され、
Session ON → Swipe → Session 成立 → Chat まで一人で通せる。

**この構成では他のユーザーへ Request が届かない。** β 公開には Supabase の設定が要る。

### Supabase のセットアップ

```bash
supabase link --project-ref <ref>
supabase db push               # supabase/migrations/ を適用
```

Auth は「メール + 6桁の確認コード」を使う。
Supabase ダッシュボードで Email provider を有効にし、
Confirm signup / Magic link のテンプレートに `{{ .Token }}` を含めること
（magic link ではなくコードを表示させる）。

Storage には `photos` バケットが作られる（`00000000000005_storage.sql`）。

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
│   ├── supabase/          実バックエンド実装（RPC 呼び出しと行→domain の変換）
│   ├── memory/backend.ts  バックエンド未設定時の代替。§25 の検証を同じ形で持つ
│   ├── memory/repositories.ts  ポートの実装
│   └── fixtures/          開発環境限定の seed
├── services/        ドメイン操作 + Analytics を束ねる層
├── analytics/       §26 のイベント定義と sink
├── state/           AppContext（誰がログイン中か / Session は ON か）
├── navigation/      3タブの shell（§3, §22, §23）
└── ui/              screens と components

supabase/
├── migrations/      スキーマ・RPC・RLS・Storage
└── tests/           サーバ側ルールの検証（./scripts/test-sql.sh から実行）
```

### バックエンドは2実装ある

UI とサービス層は `Repositories` ポートだけに依存しており、
接続先の違いを知らない。設定の有無で実装が切り替わる。

| | Supabase 実装 | 端末内実装 |
|---|---|---|
| 用途 | β 公開 | バックエンド未設定時の開発 |
| §25 の検証 | Postgres の RPC + 制約 + RLS | `SessionBackend` 内 |
| ユーザー間同期 | あり | **なし**（端末内で完結） |
| 画像 | Supabase Storage へアップロード | ローカル URI のまま |

### server-side 検証（§25）

可否判定は必ずサーバ側を通る。UI 側のガードは体験のためのもので、権限判断ではない。

`age_verified` / `session_on` / `session expiry` / `request expiry` /
`max active sessions` / `block relationship` / `duplicate request` /
`duplicate match` / `self request` / `user availability`

Supabase 実装では、これを RPC（`security definer`）+ 制約 + RLS で担保している。

- **client は table を直接 update しない。** 状態を変えるのは RPC だけ。
- `users` の RLS は自分の行しか select させない。
  他人のプロフィールは RPC 経由でのみ、公開してよい列だけが返る。
  **緯度経度が client へ渡る経路が存在しない**（§10）。
- `age_verified` はトリガで保護され、取り込み RPC 以外からは更新できない（§19）。

#### 相互 Request の直列化

「同時操作でも同じ Session が2件生成されないこと」（§25）を、
ペアを正規化した advisory lock と部分一意インデックスの二段で担保している。

```sql
create unique index sessions_one_active_pair
  on sessions (user_a_id, user_b_id) where (status = 'active');
```

実 Postgres に対する検証では、20ペアが同時に相互 Request を投げても
Session と Conversation はそれぞれ 20 件ちょうど、エラーなしで成立する。

ロックを外すと、20ペア中2ペアで **Session が1件も作られない**（両者の
INSERT が互いに見えないまま「相手の Request が無い」と判定される）。
重複ではなく取りこぼしが起きるため、一意インデックスだけでは足りない。

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

Session symbol は astroid（4方向に尖ったスパークル）。これが正式アセット。

- `assets/` の icon / splash は `node tools/generate-brand-assets.js` で決定的に再生成できる
- 同じ形状を `src/ui/components/SessionSymbol.tsx` がベクターで持つ
- 形を変える場合は両方を合わせて更新すること

- Session Green `#32F783` — NOW / ACTIVE / AVAILABLE / SELECTED / PRIMARY ACTION / SESSION STARTED
- Near Black `#080B0B`
- White `#FFFFFF`

Green の使用率はおおむね 5–10%。

## β 版に入れていないもの

AI / Boost / Session+ / AdMob / 有料プラン / 地図 / 店舗予約 / カレンダー / グループ募集 /
Reviews / Video call / Voice call / Audio profile / GIF / リアルタイム位置共有 /
詳細相性診断 / 複数 Intent 同時選択 / 細かい時間指定 / 高度な推薦 AI /
Face verification badge / 複数課金 tier / Likes 専用タブ / 複雑な検索条件

## テスト

Acceptance Criteria（§31）と Definition of Done（§32）を担保している。

```bash
npm test               # 77 tests — domain / 端末内バックエンド / 行の変換
./scripts/test-sql.sh  # 29 assertions — 実 Postgres に対するサーバ側ルール
npm run typecheck
```

### TypeScript（`__tests__/`）

- `sessionStatus.test.ts` — TTL、Intent 変更でのリセット、再起動後に期限切れ ON が復活しないこと
- `discovery.test.ts` — priority 1〜4、Session OFF も候補に残ること、radius fallback、Empty State
- `discoveryPreference.test.ts` — 相互成立、non-binary の表示規則
- `distance.test.ts` — 距離バケット、正確な距離を出さないこと
- `requests.test.ts` — OFF/年齢未確認では送れない、duplicate、期限切れ、Skip クールダウン
- `matching.test.ts` — Session は1件だけ、active_until、上限、期限後 past
- `ageVerification.test.ts` — client が確認済みを主張できないこと、偽 reference、他人の reference
- `safetyAndChat.test.ts` — Block の双方向遮断、年齢 gate、当事者以外の遮断
- `fixtures.test.ts` — 本番ビルドで fixture が投入されないこと
- `supabaseMappers.test.ts` — 行→domain の変換、RPC エラーの復元、距離を持ち込まないこと
- `fullLoop.test.ts` — signup → onboarding → 年齢確認 → Session ON → swipe → Session → chat

### SQL（`supabase/tests/`）

実際の Postgres 16 にスキーマと RPC を適用して実行する（Supabase 本体は不要）。
18歳未満の拒否、TTL、duplicate request、相互成立、active_until、
Block の双方向遮断、年齢確認の信頼モデル、non-binary の表示規則、
距離の丸め、Active Session 上限を検証する。

同時実行の検証（20ペアが同時に相互 Request）は `scripts/` の手順で再現できる。

## 本番公開前に必要な設定値

`.env.example` を `.env.local` にコピーして埋める。

| 環境変数 | 必須 | 未設定時の挙動 |
|---|---|---|
| `EXPO_PUBLIC_SUPABASE_URL` | **必須** | 端末内で完結し、他のユーザーへ Request が届かない |
| `EXPO_PUBLIC_SUPABASE_ANON_KEY` | **必須** | 同上 |
| `EXPO_PUBLIC_AGE_VERIFICATION_URL` | **必須** | 年齢確認が完了できず、Session ON / Request / Chat がすべて使えない |
| `EXPO_PUBLIC_DISABLE_FIXTURES` | 任意 | `1` で開発環境でも fixture を投入しない。本番ビルドでは値に関わらず常に無効 |

`src/config/env.ts` の `findMissingProductionConfig()` が不足を検出できる。

### Supabase 側

- `supabase db push` でマイグレーションを適用
- Auth: Email provider を有効化し、テンプレートに `{{ .Token }}` を入れる（6桁コード方式）
- Storage: `photos` バケット（マイグレーションが作成）
- **service role キーはアプリに置かない。** 年齢確認の取り込み
  （`apply_age_verification_result`）とアカウント削除は Edge Function 側で行う

### app.json 側

- `ios.bundleIdentifier` / `android.package` — 現在は `app.session.beta`
- `SettingsSheet` の利用規約 / プライバシーポリシー URL — 現在は `https://session.app/...` のプレースホルダ

## 未接続箇所

| 項目 | 状態 | 残っていること |
|---|---|---|
| **バックエンド** | 実装済み（Supabase） | プロジェクト作成と `db push`、環境変数の設定 |
| **Session Request / Match / Chat 同期** | 実装済み | 上に同じ。**実 Supabase インスタンスに対する疎通は未検証** |
| **画像アップロード** | 実装済み（Supabase Storage） | 同上 |
| **認証** | 実装済み（メール + 6桁コード） | Email テンプレートの設定 |
| **年齢確認プロバイダ** | client 側は実装済み | プロバイダ契約と、結果を取り込む Edge Function |
| **アカウント削除** | `delete-account` Edge Function を呼ぶだけ | Edge Function 本体が未実装 |
| **Push 通知** | 未実装（P1） | — |
| **リアルタイム更新** | 未実装 | 現在は画面を開いたときに取得。Supabase Realtime を繋ぐ余地あり |

## 残リスク

**公開を止めるもの**

- **実 Supabase インスタンスに対する疎通が未検証。**
  スキーマ・RPC・RLS はローカルの Postgres 16 で検証済みだが、
  `supabase-js` からの実際の呼び出し、Auth のコード送信、Storage への
  アップロードは通していない。最初にここを通すこと。
- **年齢確認プロバイダが未接続。** 未設定のまま本番ビルドを出すと、
  ユーザーは Session ON も Request も Chat もできない。
  結果を取り込む Edge Function も併せて必要。
- **アカウント削除が動かない。** `delete-account` Edge Function が未実装。
  削除導線は UI にあるので、公開前に実装するか導線を隠すこと。

**公開後に効いてくるもの**

- **写真バケットが public。** URL に UUID を含むので推測はできないが、
  URL を知る者は誰でも見られる。退会後もオブジェクトは残る。
  絞る場合は `public=false` + 署名付き URL へ切り替える。
- **画像のモデレーションが無い。** UGC を扱う以上、通報前提の運用になる。
- **Report の受け口が無い。** 記録はされるが、運用側で確認する導線が未整備。
- **non-binary ユーザーの母数が小さい。** 表示対象が `everyone` を選んだ層に限られる。
  本来は「どの検索結果に自分を表示するか」を本人が選べるようにすべきだが、
  β 版では設定項目を増やさない方針のため見送っている。
- **Discovery が毎回 users 全件を走査する。** 数千人規模までは持つが、
  それ以上では PostGIS などで絞り込みが要る。
- **キーボード表示時のレイアウトと Safe Area は実機未検証。**
  `useKeyboardHeight` は iOS の `keyboardWillChangeFrame` を前提にしており、
  Web プレビューでは検証できない。Safe Area は inset を注入したブラウザ検証で
  崩れないことを確認済みだが、実機での確認が要る。
- **オフライン時の扱いが素朴。** 失敗すると Toast を出すだけで、再送は行わない。

## 次にやること

1. Supabase プロジェクトを作り、`db push` して実インスタンスで疎通を確認する
2. 年齢確認プロバイダの接続と、結果を取り込む Edge Function
3. `delete-account` Edge Function
4. iOS シミュレータ / 実機での keyboard・Safe Area 検証
5. P1 — Push 通知 / Meet アンケート / Received Requests 表示 / 近くの Session 人数 / Session 再開ショートカット
