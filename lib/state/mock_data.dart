import 'package:flutter/widgets.dart';

import '../models/chat.dart';
import '../models/distance.dart';
import '../models/intent.dart';
import '../models/photo.dart';
import '../models/safety.dart';
import '../models/user.dart';

/// プロトタイプの写真はすべてグラデーションのプレースホルダ。
/// 実装では実画像に差し替え、読み込み中の下地としてこの色を残す。
abstract final class MockGradients {
  static const warmGrey = [Color(0xFF3C403C), Color(0xFF22252A), Color(0xFF141614)];
  static const plum = [Color(0xFF3F3A4A), Color(0xFF25222A), Color(0xFF141614)];
  static const clay = [Color(0xFF4A3F3A), Color(0xFF2A2522), Color(0xFF141614)];
  static const moss = [Color(0xFF3A4A3F), Color(0xFF222A25), Color(0xFF141614)];
  static const sand = [Color(0xFF45403A), Color(0xFF282522), Color(0xFF141614)];
  static const slate = [Color(0xFF39404A), Color(0xFF22252A), Color(0xFF141614)];
}

SPhoto _photo(String id, List<Color> g) => SPhoto(id: id, gradient: g);

abstract final class MockData {
  static SUser me() => SUser(
    id: 'me',
    displayName: 'Ren',
    age: 25,
    gender: Gender.male,
    bio: '美味しい店と旅行が好き。金曜はだいたい飲んでる。',
    interests: ['travel', 'cafe_hopping', 'sushi'],
    photos: [
      _photo('me-1', MockGradients.plum),
      _photo('me-2', MockGradients.slate),
      _photo('me-3', MockGradients.sand),
    ],
  );

  static List<SUser> discoveryQueue() => [
    SUser(
      id: 'yuna',
      displayName: 'Yuna',
      age: 24,
      gender: Gender.female,
      isOn: true,
      intent: SIntent.drinks,
      distance: DistanceBand.km3,
      bio:
          '旅行と美味しい店が好きです。金曜はだいたい恵比寿か中目黒にいます。'
          '急に決まる飲みが好きなので、気軽に声かけてください。',
      interests: ['travel', 'cafe_hopping', 'sushi', 'wine'],
      photos: [
        _photo('yuna-1', MockGradients.warmGrey),
        _photo('yuna-2', MockGradients.plum),
        _photo('yuna-3', MockGradients.moss),
      ],
    ),
    SUser(
      id: 'mio',
      displayName: 'Mio',
      age: 26,
      gender: Gender.female,
      isOn: true,
      intent: SIntent.freeNow,
      distance: DistanceBand.km1,
      bio: '渋谷と代官山あたり。ふらっとご飯行ける人を探しています。',
      interests: ['ramen', 'movies', 'sauna'],
      photos: [_photo('mio-1', MockGradients.clay), _photo('mio-2', MockGradients.sand)],
    ),
    SUser(
      id: 'aoi',
      displayName: 'Aoi',
      age: 23,
      gender: Gender.female,
      isOn: false,
      intent: SIntent.cafe,
      distance: DistanceBand.km5,
      bio: 'コーヒーと写真。土日は大体どこかのカフェにいます。',
      interests: ['cafe_hopping', 'photo', 'books'],
      photos: [_photo('aoi-1', MockGradients.moss), _photo('aoi-2', MockGradients.slate)],
    ),
    SUser(
      id: 'rina',
      displayName: 'Rina',
      age: 27,
      gender: Gender.female,
      isOn: true,
      intent: SIntent.food,
      distance: DistanceBand.km10,
      bio: '焼肉と日本酒。新しい店を開拓するのが好き。',
      interests: ['yakiniku', 'sake', 'travel'],
      photos: [_photo('rina-1', MockGradients.sand), _photo('rina-2', MockGradients.clay)],
    ),
  ];

  static List<SMatch> matches() {
    final now = DateTime.now();
    final yuna = discoveryQueue()[0];
    final mio = discoveryQueue()[1];
    return [
      SMatch(
        person: yuna,
        intent: SIntent.drinks,
        createdAt: now.subtract(const Duration(minutes: 6)),
        unread: 2,
        messages: [
          SMessage(
            body: '今夜、飲みに行けますか？',
            mine: true,
            createdAt: now.subtract(const Duration(minutes: 5)),
          ),
          SMessage(
            body: '行けます！何時ごろ行く？',
            mine: false,
            createdAt: now.subtract(const Duration(minutes: 2)),
          ),
        ],
      ),
      SMatch(
        person: mio,
        intent: SIntent.freeNow,
        createdAt: now.subtract(const Duration(minutes: 40)),
        messages: [
          SMessage(
            body: '渋谷でどう？',
            mine: false,
            createdAt: now.subtract(const Duration(minutes: 15)),
          ),
        ],
      ),
      SMatch(
        person: SUser(
          id: 'rina',
          displayName: 'Rina',
          age: 27,
          gender: Gender.female,
          photos: [_photo('rina-1', MockGradients.moss)],
        ),
        intent: SIntent.food,
        createdAt: now.subtract(const Duration(days: 6)),
        active: false,
        messages: [
          SMessage(body: 'また今度！', mine: false, createdAt: now.subtract(const Duration(days: 6))),
        ],
      ),
      SMatch(
        person: SUser(
          id: 'aoi2',
          displayName: 'Aoi',
          age: 23,
          gender: Gender.female,
          photos: [_photo('aoi-1', MockGradients.sand)],
        ),
        intent: SIntent.cafe,
        createdAt: now.subtract(const Duration(days: 7)),
        active: false,
        messages: [
          SMessage(body: 'ありがとう〜', mine: false, createdAt: now.subtract(const Duration(days: 7))),
        ],
      ),
    ];
  }

  /// 「あなたに興味あり」は無料ではぼかし表示なので、人物データは持たせない。
  static const interestedCount = 8;
  static const interestedIntents = [SIntent.drinks, SIntent.freeNow];

  static List<BlockedUser> blocked() => [
    const BlockedUser(name: 'Kana', blockedOn: '2026.09.08', gradientSeed: 0),
    const BlockedUser(name: 'Miku', blockedOn: '2026.08.30', gradientSeed: 1),
  ];
}
