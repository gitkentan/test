# netelly-theme

Netelly株式会社 コーポレートサイト用 WordPress クラシックテーマ。

## ローカル環境

**Node.js 22（LTS）を使ってください**（`.nvmrc`）。Node 25 以降では `wp-env start` が途中で無言終了します。Docker Desktop の起動も必要です。

```bash
npm install
npx wp-env start      # http://localhost:8888 （管理画面 /wp-admin  admin / password）
npm run seed          # サンプルデータ投入（日英。再実行すると投入分を上書き）
```

- プラグイン（`.wp-env.json` で自動導入・有効化）: Secure Custom Fields / Polylang / Snow Monkey Forms
- 起動後に日本語化・テーマ有効化・パーマリンク設定（`/news/%post_id%/`）を自動で行います。

## 本番公開（さくらのレンタルサーバ）

手順は `docs/DEPLOY-sakura.md`。`npm run zip` で `netelly-theme.zip`（src を除いたテーマ一式）を作り、管理画面からアップロードします。WP-CLI がないサーバーでは、初期データは 管理画面 › ツール › Netelly 初期データ から作成できます（`npm run seed` と同じ処理）。

## ビルド

```bash
npm run build   # src/ → assets/dist/（Vite。manifest を inc/enqueue.php が読む）
npm run dev     # 監視ビルド
```

ビルド済みの `assets/dist/` もコミットしているため、`wp-env start` だけで表示できます。

## 構成

| パス | 内容 |
|---|---|
| `src/css/tokens.css` | デザイントークン（README の値そのまま。SP ≤768px で `--pad-x`/`--sec` を切替） |
| `src/css/base.css` | ベース（デザインのグローバルスタイル＋フォーカスリング） |
| `assets/fonts/` | 自前ホストの Google Fonts（Zen Kaku Gothic New / Archivo / IBM Plex Mono。unicode-range 分割の woff2） |
| `assets/netelly-wordmark.svg` | サイトのロゴ（Archivo 字幅125%・600・字間.02em をアウトライン化。`tools/build-wordmark.py` で再生成） |
| `assets/netelly-logo.svg` / `netelly-l-mark.svg` | 支給ロゴ（原本、ロゴとしては未使用）／L字マーク。`assets/images/*.min.svg` はインライン用 |
| `inc/` | PHP（setup / enqueue / helpers …） |

## 多言語（Polylang 無料版）

- 日本語＝既定（URL接頭辞なし）、英語＝`/en/`。英語トップは `/en/`。
- 固定ページ・作品・ニュース・募集職種は言語ごとに別投稿（翻訳として紐付け）。
- 日英で同じスラッグを使えます（`/company/` と `/en/company/`）。Polylang Pro の機能をテーマ側で実装：`inc/i18n.php`。
- ニュースのカテゴリーは言語ごとに別ターム（英語のスラッグは `-en` 付き：`/en/news/category/press-en/`）。
- テーマ内の固定文言は「言語 › 翻訳」（グループ「Netelly テーマ」）で日英とも編集できます。定義：`inc/strings.php`。

## どこで編集するか

| 内容 | 編集場所 |
|---|---|
| ヒーロー・縦型ショートドラマ・トップの各セクション見出し・事業カード | サイト設定 › 日本語 / English（タブ「トップ：…」） |
| 代表メッセージ・会社概要の表・沿革・連絡先・SNS・Creators Fund URL・プレフッター・採用帯・作品一覧ページ | サイト設定 › 日本語 / English |
| 各ページの英字タイトル・本文 | 固定ページの編集画面（ページヒーロー＋ページごとのフィールド） |
| 作品 / 募集職種 / ニュース | 左メニュー「作品」「募集職種」「投稿」 |
| ヘッダー / フッターのメニュー | 外観 › メニュー（`primary-ja` など。言語ごとに1つ）。URL に `#creators-fund` と入れると Creators Fund URL（新しいタブ・↗）になります |
| プレスキット（紹介文・ロゴ・経営陣・ガイドライン・一括ダウンロードZIP・広報メール） | 固定ページ「プレスキット」の編集画面。会社概要の表はサイト設定の「会社概要」、プレスリリースはカテゴリー「プレスリリース」の最新3件を自動表示 |
| お問い合わせフォーム（項目・選択肢・ボタン名・完了画面・通知／自動返信メール） | 左メニュー「Snow Monkey Forms」の「お問い合わせ」「お問い合わせ (EN)」。どのフォームを表示するかはお問い合わせページの「フォーム」欄 |
| 必須／任意バッジ・エラー文・「送信中…」 | 言語 › 翻訳（グループ「Netelly テーマ」） |
| 採用ページから来たときの選択肢・挿入文 | お問い合わせページの「「採用」の選択肢名」「応募職種の挿入文」 |
| reCAPTCHA v3 | 設定 › Snow Monkey Forms（reCAPTCHA）にサイトキー／シークレットキーを入れると有効（スクリプトはお問い合わせページだけで読み込み） |

## お問い合わせフォーム（Snow Monkey Forms）

- 見た目はテーマの `src/css/components/form.css`（プラグインの CSS は読み込みません）。UX は `src/js/form.js`（README 9：blur 時バリデーション、⚠ エラー、同意まで送信不可、送信中表示、`?type=recruit&position=` の自動入力）。
- 項目を追加しても見た目は自動で揃います（1行入力は PC で2列、それ以外は全幅。必須／任意バッジは「必須」チェックから自動）。
- ローカル（wp-env）ではメールを送れないため、`dev/netelly-local-mail.php` が `wp-content/uploads/netelly-mail.log` に書き出します（本番には入りません）。
- メールアドレス検証はプラグインがドメインの DNS を確認します。

## パフォーマンス / ファビコン / 説明文

- フォント CSS（`assets/fonts/fonts.min.css`、日本語の unicode-range 分割で約 87KB gzip）は描画をブロックしないよう preload → stylesheet で読み込みます（`font-display: swap`）。`fonts.css` は可読版の元ファイル。
- トップのイントロ（README 1）は CSS アニメーション（`motion.css`「1. Intro」）で最初の描画から始まります。`intro.js` はセッション記録・スキップ・後片付けのみ。背景は黒いカバーを消す形でフェードインするため、ヒーローのポスター画像がそのまま LCP として扱われます。**ヒーローのポスター画像は必ず設定してください**（未設定だと LCP がイントロ最後のボタンになり、スコアが下がります）。
- ファビコンはロゴ「NETELLY」の「N」を黒地に置いたもの（`assets/favicon/`）。外観 › カスタマイズ › サイトアイコンを設定するとそちらが優先されます。
- meta description：ニュース＝抜粋（なければ本文）、作品＝あらすじ、それ以外＝サイト設定「検索結果の説明文」。

## SEO・AI検索（inc/seo.php）

- 各ページに `meta description`・OGP / X カード・構造化データ（JSON-LD）を出力。会社（Organization）・サイト（WebSite）・代表者（Person）は全ページ共通の @id で、**代表メッセージページが代表者のプロフィールページ（ProfilePage）**。ページ下部の「プロフィール」欄が構造化データと同じ内容を表示します。
- ニュース記事は NewsArticle、作品は TVSeries / Movie（出演者・監督つき）、全ページにパンくず。
- `/llms.txt`：AI アシスタント向けの会社概要とページ一覧（自動生成）。サイトマップは WordPress 標準の `/wp-sitemap.xml`（ユーザー一覧は除外）。
- サイト設定 › **アクセス解析**：GA4 測定ID と Search Console の確認コードを貼るだけで有効。GA4 はページ表示後に読み込み（速度に影響なし）、ログイン中の管理者は計測しません。お問い合わせ送信は `generate_lead`（種類つき）として記録されます。
- サイト設定 › **トップ：数字の帯**：実績の数字を最大4つ。空なら帯ごと非表示。数字だけの値はカウントアップします。
- 入力場所：サイト設定 › **SEO・SNS共有**（トップのタイトル、共有画像、設立日、会社の公式プロフィールURL）／サイト設定 › **代表メッセージ**（よみがな、プロフィール文、経歴、本人の公式プロフィールURL）。

## フィールド定義（acf-json）

`acf-json/` が正です（Secure Custom Fields / ACF Pro のどちらでも自動読み込み）。管理画面でフィールドを変更すると、このフォルダの JSON が更新されます。
サイト設定は言語ごとに `post_id = netelly_ja / netelly_en` に保存され、テンプレートからは `netelly_opt( 'name' )` で現在の言語の値を取得します（英語が空なら日本語にフォールバック）。
