# Session

- **アプリ（Flutter）** … このリポジトリのルート。下記の通り。
- **事前登録ページ（LP）** … [`site/`](site/)。ビルド不要の静的サイト。公開手順は [`site/README.md`](site/README.md)。

---

## アプリ — Flutter 実装

デザイン「Session v4」（`Session v4 Core.dc.html` / `Session v4 App.dc.html`）を Flutter で実装したもの。
Session は「今、何をしたいか（intent）× 時間 × 距離」でマッチングする、iOS ファーストの日本市場向けアプリ。

- ブランド名：**Session** ／ タグライン：**今を、出会いに。**
- ルートは **Discovery のみ**（下部タブ・People タブ・Now ホームは存在しない）
- 可用性の概念は **SESSION ON** ひとつに統一（Live / Online / Status ON は使わない）

## 動かす

```bash
flutter pub get
flutter run                 # iOS / Android
flutter test                # 16 tests
flutter analyze             # 0 issues
```

Flutter 3.35 / Dart 3.9 で確認済み。**サードパーティ依存はゼロ**で、Material も Cupertino も使わず
両 OS 同一の自前ウィジェットで描いている（`MaterialApp` は Router としてのみ使用）。

## 実装の方針（ハンドオフの「Flutter mapping」に準拠）

| 項目 | 実装 |
|---|---|
| テーマ | ダーク固定。OS のライトモードには追従しない（`lib/theme/tokens.dart`） |
| ルート | A（Discovery）が唯一のルート。F / I / I2 / K1 は下からのフルスクリーン、C / D / E / G2 / M / N4 はボトムシート |
| スワイプ | 外部の swiper パッケージは使わず `GestureDetector` + `AnimationController` で自前実装。しきい値は画面幅の30% |
| アイコン | Material Icons は見た目が合わないので使わず、SVG と同じ 24×24 / stroke 1.8 / 丸端で `CustomPainter` に描き起こし（`lib/widgets/s_icons.dart`） |
| ロゴ | 四角星の path をそのまま `Path` に写した `SessionStar` |
| 絵文字 | Intent の 🍸🍽☕📞🌙 は OS のシステム絵文字のまま（画像化しない） |
| レイアウト | モックは 393×852pt 固定高だが、実装は縦を `Expanded` で可変に。360×800dp でも崩れないことをテストで確認 |
| 文字サイズ | `textScaleFactor` 1.3 までクランプ。1行固定の Intent チップのみさらにクランプ |

## 画面 ID とファイルの対応

デザインの `data-screen-label` がそのまま画面 ID。

| ID | 画面 | ファイル |
|---|---|---|
| A / B | Discovery（OFF / ON） | `screens/discovery/discovery_screen.dart` |
| B2 | 初回コーチマーク | `screens/discovery/coach_mark.dart` |
| C | Intent シート | `screens/discovery/intent_sheet.dart` |
| D / D2 | 絞り込み（距離ロック含む） | `screens/discovery/filter_sheet.dart` |
| E | プロフィール詳細 | `screens/discovery/profile_detail_sheet.dart` |
| F / N5 | メッセージ一覧・空 | `screens/messages/messages_screen.dart` |
| G | IT'S A SESSION | `screens/discovery/its_a_session_screen.dart` |
| G2 | 年齢確認が必要 | `screens/messages/age_required_sheet.dart` |
| H | チャット | `screens/messages/chat_screen.dart` |
| I / I2 / I3 | プロフィール・編集・プレビュー | `screens/profile/` |
| J1〜J10 / O | オンボーディング | `screens/onboarding/` |
| K1 / K2 | Session+ / 1日の上限 | `screens/paywall/` |
| L1〜L6 | 設定・プライバシー・通知・ブロック・退会 | `screens/settings/` |
| M1〜M4 | メニュー・通報・ブロック・受付完了 | `screens/safety/` |
| N1 / N2 / N3 | 空・ローディング・オフライン | `screens/discovery/discovery_states.dart` |
| N4-01〜09 | エラーシート | `screens/system/error_sheets.dart` |

共有ウィジェットは `lib/widgets/`：`SwipeCard`（A / B / B2 / N1 / N2 / **I3** で同一実装）、
`SessionControlRow`、`SessionBadge`、`IntentPill`、`SBottomSheet`、`SPrimaryButton`、
`SSettingsGroup`、`SToggle`、`SAgeRangeSlider` / `SDistanceSlider`、`MessageBubble`、`PhotoSlot`。

## 仕様として守っているルール

コードで意図的に固定してある、プロダクトの前提：

- **人数を出さない。** 「近くに34人」のような表示はどこにもない（無料は定性表示のみ）。
- **距離は常に丸める。** 表示は 1 / 3 / 5 / 10 km。地図・駅名・住所は出さない。
- **Intent は常に1つ。** 複数選択も全選択もない。ON は Request の必須条件で、OFF でも閲覧はできる。
- **年齢確認は登録時に求めない。** 閲覧・スワイプ・Session 成立は確認なしで可能で、
  **メッセージ送信だけ**が確認を要求する（`AppState.canSendMessage` → G2 → J5 → J6）。
  生年月日入力やチェックボックスは確認と見なさない。
- **表示名は登録後に変更不可。** 編集画面でも読み取り専用（API 側でも拒否する前提）。
- **自己紹介は200文字。** 超過時はインライン警告＋保存無効。Discovery には出さず E のみ。
- **写真は 3:4 固定・3〜6枚。**
- **距離の絞り込みは 1〜30km、無料は 5km 下限。** 下限より左へドラッグしても値は変えず、
  ロック案内（D2）を出すだけ。Discovery 自体は常に機能する。
- **無料は Request 1日20件。** 21件目で K2。
- **緑は全体の5〜8%。** ON 状態・主 CTA・成立・選択中のみ。常時グローは使わない。
  破壊的操作は白ボタン＋確認シートで、赤（`SColor.danger`）は削除の最終確認とバリデーションだけ。

## まだ入っていないもの（差し込み口はある）

バックエンドと OS 連携は未接続で、`lib/state/app_state.dart` がそのまま API クライアントの差し込み口。

| 箇所 | 本来の実装 | 現状 |
|---|---|---|
| 写真の選択 | `image_picker`（OS 純正ピッカー） | `screens/onboarding/photos_screen.dart` でプレースホルダを生成 |
| 3:4 切り抜き | `image_cropper`（iOS TOCropViewController / Android uCrop、`lockAspectRatio: true`） | `crop_screen.dart` は**自作しない前提**の設定指示書として枠だけを描画 |
| 年齢確認 | `url_launcher` の `inAppBrowserView` → App Links / Universal Links で復帰 | 画面遷移のみ（成功として扱う） |
| 課金 | StoreKit 2 / Google Play Billing 6（`in_app_purchase`） | K1 のタップで entitlement を立てるだけ |
| メッセージ・成立 | WebSocket / Firestore。ON の失効はサーバー時刻が正 | モックの定型返信（1.4s の入力中インジケータ付き） |
| 写真 | 実画像の読み込み | すべてグラデーションのプレースホルダ（`SPhotoView` が実画像に差し替え可能） |
| 権限・通知 | `permission_handler` / APNs・FCM | UI のみ |

## テスト

- `test/app_state_test.dart` — ON/OFF、距離のロック、1日の上限、年齢確認ゲート、ブロックの副作用
- `test/screens_smoke_test.dart` — Splash→Welcome、コーチマーク→カード、Intent シートで ON、
  右スワイプで成立→G、主要7画面が 393×852 と 360×800 の両方で例外なく描画されること
