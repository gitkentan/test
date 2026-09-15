/// 興味・関心は運営管理のマスター24項目から 0〜5 個。
/// ユーザー定義タグは不可（表記ゆれとモデレーション回避）。
enum InterestCategory { nightlifeFood, culture, active, lifestyle }

extension InterestCategoryX on InterestCategory {
  String get label => switch (this) {
    InterestCategory.nightlifeFood => 'ナイトライフ・食',
    InterestCategory.culture => 'カルチャー',
    InterestCategory.active => 'アクティブ',
    InterestCategory.lifestyle => 'ライフスタイル',
  };
}

class Interest {
  const Interest(this.id, this.category, this.labelJa);

  final String id;
  final InterestCategory category;
  final String labelJa;
}

/// interest_master（24行・運営管理）。表示順はこの定義順。
abstract final class InterestMaster {
  static const all = <Interest>[
    Interest('sake', InterestCategory.nightlifeFood, '日本酒'),
    Interest('wine', InterestCategory.nightlifeFood, 'ワイン'),
    Interest('craft_beer', InterestCategory.nightlifeFood, 'クラフトビール'),
    Interest('sushi', InterestCategory.nightlifeFood, '寿司'),
    Interest('ramen', InterestCategory.nightlifeFood, 'ラーメン'),
    Interest('yakiniku', InterestCategory.nightlifeFood, '焼肉'),
    Interest('cafe_hopping', InterestCategory.nightlifeFood, 'カフェ巡り'),
    Interest('movies', InterestCategory.culture, '映画'),
    Interest('music', InterestCategory.culture, '音楽'),
    Interest('live', InterestCategory.culture, 'ライブ'),
    Interest('art', InterestCategory.culture, 'アート'),
    Interest('books', InterestCategory.culture, '読書'),
    Interest('anime', InterestCategory.culture, 'アニメ'),
    Interest('gym', InterestCategory.active, 'ジム'),
    Interest('running', InterestCategory.active, 'ランニング'),
    Interest('sauna', InterestCategory.active, 'サウナ'),
    Interest('camp', InterestCategory.active, 'キャンプ'),
    Interest('snowboard', InterestCategory.active, 'スノボ'),
    Interest('travel', InterestCategory.lifestyle, '旅行'),
    Interest('fashion', InterestCategory.lifestyle, 'ファッション'),
    Interest('cooking', InterestCategory.lifestyle, '料理'),
    Interest('photo', InterestCategory.lifestyle, '写真'),
    Interest('football', InterestCategory.lifestyle, 'サッカー観戦'),
    Interest('games', InterestCategory.lifestyle, 'ゲーム'),
  ];

  static const maxSelectable = 5;

  static List<Interest> byCategory(InterestCategory c) =>
      all.where((i) => i.category == c).toList(growable: false);

  static Interest? byId(String id) {
    for (final i in all) {
      if (i.id == id) return i;
    }
    return null;
  }

  static String labelOf(String id) => byId(id)?.labelJa ?? id;
}
