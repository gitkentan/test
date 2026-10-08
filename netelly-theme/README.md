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

## フィールド定義（acf-json）

`acf-json/` が正です（Secure Custom Fields / ACF Pro のどちらでも自動読み込み）。管理画面でフィールドを変更すると、このフォルダの JSON が更新されます。
サイト設定は言語ごとに `post_id = netelly_ja / netelly_en` に保存され、テンプレートからは `netelly_opt( 'name' )` で現在の言語の値を取得します（英語が空なら日本語にフォールバック）。
