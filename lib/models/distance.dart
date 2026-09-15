/// 表示用の距離バンド。実距離は端末に返さない前提で、
/// サーバーから受け取った丸め値だけを持つ。
enum DistanceBand { km1, km3, km5, km10 }

extension DistanceBandX on DistanceBand {
  String get label => switch (this) {
    DistanceBand.km1 => '1km以内',
    DistanceBand.km3 => '3km以内',
    DistanceBand.km5 => '5km以内',
    DistanceBand.km10 => '10km以内',
  };

  int get km => switch (this) {
    DistanceBand.km1 => 1,
    DistanceBand.km3 => 3,
    DistanceBand.km5 => 5,
    DistanceBand.km10 => 10,
  };
}
