import 'package:flutter/widgets.dart';

import 'intent.dart';
import 'distance.dart';
import 'photo.dart';

enum Gender { female, male, other }

extension GenderX on Gender {
  String get label => switch (this) {
    Gender.female => '女性',
    Gender.male => '男性',
    Gender.other => 'すべて',
  };
}

/// user { id, display_name（不変）, birth_date, gender, bio(≤200),
///        interests[≤5], photos[3–6], age_verified_at, discoverable }
class SUser {
  SUser({
    required this.id,
    required this.displayName,
    required this.age,
    required this.gender,
    required this.photos,
    this.bio = '',
    List<String>? interests,
    this.ageVerifiedAt,
    this.discoverable = true,
    this.isOn = false,
    this.intent,
    this.distance = DistanceBand.km3,
  }) : interests = interests ?? <String>[];

  final String id;

  /// オンボーディング後は変更不可（API 側でも更新を拒否）。
  final String displayName;
  final int age;
  final Gender gender;
  List<SPhoto> photos;
  String bio;
  List<String> interests;
  DateTime? ageVerifiedAt;
  bool discoverable;

  /// SESSION は「今会う意思がある」状態。Online ではない。
  bool isOn;
  SIntent? intent;
  final DistanceBand distance;

  static const bioMaxLength = 200;
  static const minPhotos = 3;
  static const maxPhotos = 6;

  bool get ageVerified => ageVerifiedAt != null;
  SPhoto get mainPhoto => photos.first;
}

/// 年齢の範囲（Material の RangeValues は使わない — UI は全て自前）。
@immutable
class SRange {
  const SRange(this.start, this.end);

  final double start;
  final double end;

  SRange copyWith({double? start, double? end}) => SRange(start ?? this.start, end ?? this.end);
}
