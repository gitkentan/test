# Session 事前登録ページ（LP）

`site/` がそのまま公開できる完成品。ビルド不要・依存なしの静的サイトで、合計 約0.3MB。
デザインハンドオフ `design_handoff_session_lp` の内容をそのまま収めている。

```
site/
├── index.html      ページ本体（CSS・JS 込み、273行）
└── assets/         アプリ画面（WebP）、ロゴ、favicon、apple-touch-icon、OGP（1200×630）
```

## ローカルで見る

```bash
python3 -m http.server 8077 --directory site
# → http://127.0.0.1:8077/
```

## 公開手順

1. `site/index.html` 末尾の2行を実際の値に書き換える

   ```js
   var LINE_URL = 'https://lin.ee/xxxxxxx';   // LINE公式アカウントの友だち追加URL
   var SITE_URL = 'https://session.app';      // 公開URL（ASK AI の質問文に入る）
   ```

   `<head>` の `og:image`（現在 `https://session.app/assets/og.jpg`）も公開ドメインに合わせる。

2. `site/` を https://app.netlify.com/drop にドラッグ → アカウントを作って Claim
3. 独自ドメインは Netlify の「Domain management」から接続

## 公開前に必ず差し替えるもの

| 箇所 | 現在の値 | 備考 |
|---|---|---|
| `LINE_URL`（JS） | `https://lin.ee/xxxxxxx` | ヘッダー・ヒーロー・最終CTAの3箇所に反映される |
| `SITE_URL`（JS） | `https://session.app` | ASK AI の質問文に埋め込まれる |
| `og:image`（head） | `https://session.app/assets/og.jpg` | 絶対URLでないとSNSで表示されない |
| 届出番号（footer） | `受理番号：00000000000` | **ダミー。実際の受理番号に差し替えるまで公開しない** |
| SNS・規約類のリンク（footer） | `href="#"` | 運営会社／お問い合わせ／利用規約／プライバシーポリシー／特商法表記 |
| アプリ画面内の人物写真 | ダミー | 利用許諾のある写真か実画面に差し替え |

## 実装メモ

- 外部読み込みは Google Fonts（Noto Sans JP）のみ。読めない環境でも
  Helvetica / Hiragino Sans にフォールバックする
- FAQ は `<details>` で、JS により常に1つだけ開く
- ASK AI は ChatGPT / Claude / Perplexity はクエリ付きURLで開き、
  Gemini だけはクエリパラメータが無いのでクリップボードにコピーして案内を出す
- 装飾のグロー（`.hero-glow` / `.women-glow`）は意図的に画面外へはみ出していて、
  `body{overflow-x:hidden}` で切られる。横スクロールは発生しない
- 390 / 360 / 768 / 1280px で描画を確認済み
