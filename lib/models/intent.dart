/// Intent は5種。常に1つだけ。複数選択・全選択は存在しない。
/// サーバー値は snake_case（free_now）。
enum SIntent { drinks, food, cafe, phone, freeNow }

extension SIntentX on SIntent {
  String get emoji => switch (this) {
    SIntent.drinks => '🍸',
    SIntent.food => '🍽',
    SIntent.cafe => '☕',
    SIntent.phone => '📞',
    SIntent.freeNow => '🌙',
  };

  String get label => switch (this) {
    SIntent.drinks => '飲みに行く',
    SIntent.food => 'ご飯',
    SIntent.cafe => 'カフェ',
    SIntent.phone => '電話したい',
    SIntent.freeNow => '今ひま',
  };

  /// ヘッダーやチップで使う短縮形。
  String get short => switch (this) {
    SIntent.drinks => '飲み',
    SIntent.food => 'ご飯',
    SIntent.cafe => 'カフェ',
    SIntent.phone => '電話',
    SIntent.freeNow => 'ひま',
  };

  /// 「Yunaも今夜、飲みに行きたい。」のように文に埋め込む形。
  String get wantSentence => switch (this) {
    SIntent.drinks => '飲みに行きたい',
    SIntent.food => 'ご飯に行きたい',
    SIntent.cafe => 'カフェに行きたい',
    SIntent.phone => '電話したい',
    SIntent.freeNow => '誰かと過ごしたい',
  };

  String get apiValue => switch (this) {
    SIntent.drinks => 'drinks',
    SIntent.food => 'food',
    SIntent.cafe => 'cafe',
    SIntent.phone => 'phone',
    SIntent.freeNow => 'free_now',
  };
}
