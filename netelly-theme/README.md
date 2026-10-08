# netelly-theme

Netelly株式会社 コーポレートサイト用 WordPress クラシックテーマ。

## ローカル環境

```bash
npm install
npx wp-env start      # http://localhost:8888 （管理画面 /wp-admin  admin / password）
npm run seed          # サンプルデータ投入（段階2で追加）
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
| `assets/netelly-*.svg` | 支給ロゴ（原本）。`assets/images/*.min.svg` はインライン用にメタデータを除いた版 |
| `inc/` | PHP（setup / enqueue / helpers …） |
