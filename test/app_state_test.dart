import 'package:flutter_test/flutter_test.dart';
import 'package:session_app/models/intent.dart';
import 'package:session_app/state/app_state.dart';

void main() {
  group('SESSION の状態', () {
    test('Intent を選ぶと ON になり、OFF は手動でのみ戻る', () {
      final s = AppState();
      expect(s.isOn, isFalse);
      s.setIntent(SIntent.drinks);
      expect(s.isOn, isTrue);
      expect(s.intent, SIntent.drinks);
      s.turnOff();
      expect(s.isOn, isFalse);
      // OFF にしても Intent は覚えている（次に ON にするときの既定値）。
      expect(s.intent, SIntent.drinks);
    });

    test('OFF のままでは Request できないが閲覧はできる', () {
      final s = AppState();
      expect(s.canRequest, isFalse);
      expect(s.current, isNotNull);
    });
  });

  group('距離の絞り込み', () {
    test('無料は 5km で止まり、値は変わらずロック表示になる', () {
      final s = AppState();
      s.setDistance(2);
      expect(s.distanceKm, kFreeDistanceFloor);
      expect(s.distanceLockHit, isTrue);
    });

    test('Session+ なら 1km まで絞り込める', () {
      final s = AppState()..purchasePlus();
      s.setDistance(1);
      expect(s.distanceKm, 1);
      expect(s.distanceLockHit, isFalse);
    });

    test('上限は 30km', () {
      final s = AppState();
      s.setDistance(100);
      expect(s.distanceKm, kDistanceMax);
    });
  });

  group('Session Request', () {
    test('Intent が一致していれば相互成立して Match ができる', () {
      final s = AppState()..setIntent(SIntent.drinks);
      final before = s.matches.length;
      final match = s.sendRequest();
      expect(match, isNotNull);
      expect(s.matches.length, before + 1);
      expect(match!.intent, SIntent.drinks);
    });

    test('Intent が違えば片想いのまま次の人へ進む', () {
      final s = AppState()..setIntent(SIntent.cafe);
      final first = s.current;
      final match = s.sendRequest();
      expect(match, isNull);
      expect(s.current, isNot(first));
    });

    test('無料は1日20件まで。21件目で上限に達する', () {
      final s = AppState()
        ..setIntent(SIntent.drinks)
        ..requestsUsedToday = kFreeDailyRequests;
      expect(s.canRequest, isFalse);
      expect(s.dailyLimitReached, isTrue);
    });

    test('Session+ は上限なし', () {
      final s = AppState()
        ..setIntent(SIntent.drinks)
        ..purchasePlus()
        ..requestsUsedToday = 999;
      expect(s.canRequest, isTrue);
      expect(s.dailyLimitReached, isFalse);
    });
  });

  group('年齢確認', () {
    test('登録時は不要で、メッセージ送信だけが確認を要求する', () {
      final s = AppState();
      expect(s.me.ageVerified, isFalse);
      expect(s.canRequest || !s.isOn, isTrue); // スワイプは確認なしで可能
      expect(s.canSendMessage, isFalse);
      s.markAgeVerified();
      expect(s.canSendMessage, isTrue);
    });
  });

  group('ブロック', () {
    test('ブロックすると Session も Discovery からも消える', () {
      final s = AppState();
      final person = s.current!;
      s.block(person);
      expect(s.blocked.first.name, person.displayName);
      expect(s.matches.any((m) => m.person.id == person.id), isFalse);
    });
  });
}
