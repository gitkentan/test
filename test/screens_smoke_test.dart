import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:session_app/app.dart';
import 'package:session_app/models/intent.dart';
import 'package:session_app/screens/discovery/discovery_screen.dart';
import 'package:session_app/screens/messages/chat_screen.dart';
import 'package:session_app/screens/messages/messages_screen.dart';
import 'package:session_app/screens/onboarding/welcome_screen.dart';
import 'package:session_app/screens/paywall/paywall_screen.dart';
import 'package:session_app/screens/profile/profile_preview_screen.dart';
import 'package:session_app/screens/profile/profile_screen.dart';
import 'package:session_app/screens/settings/settings_screen.dart';
import 'package:session_app/state/app_state.dart';
import 'package:session_app/widgets/swipe_card.dart';

/// モックは iPhone 393×852pt 基準。Android の 360×800 dp でも崩れないことを別途確認する。
const _iphone = Size(393, 852);
const _android = Size(360, 800);

void _resize(WidgetTester tester, Size size) {
  tester.view.physicalSize = size * tester.view.devicePixelRatio;
  tester.view.devicePixelRatio = tester.view.devicePixelRatio;
  addTearDown(tester.view.resetPhysicalSize);
}

Widget _host(AppState state, Widget child) => AppScope(
  state: state,
  child: MaterialApp(
    debugShowCheckedModeBanner: false,
    theme: ThemeData(brightness: Brightness.dark),
    home: child,
  ),
);

void main() {
  testWidgets('Splash から Welcome まで進む', (tester) async {
    _resize(tester, _iphone);
    await tester.pumpWidget(const SessionApp());
    expect(find.text('今を、出会いに。'), findsOneWidget);

    await tester.pump(const Duration(milliseconds: 1400));
    await tester.pumpAndSettle();
    expect(find.byType(WelcomeScreen), findsOneWidget);
    expect(find.text('今、会いたい人と。'), findsOneWidget);
  });

  testWidgets('Discovery はコーチマークのあとカードを出す', (tester) async {
    _resize(tester, _iphone);
    final state = AppState()..signedIn = true;
    await tester.pumpWidget(_host(state, const DiscoveryScreen()));

    // B2: 初回だけコーチマーク。
    expect(find.text('右へ · Session'), findsOneWidget);
    await tester.tap(find.text('はじめる'));
    await tester.pumpAndSettle();

    expect(find.byType(SwipeCard), findsWidgets);
    expect(find.text('Session'), findsOneWidget); // ヘッダーの wordmark
    expect(find.text('OFF'), findsOneWidget);
  });

  testWidgets('Intent シートで SESSION が ON になる', (tester) async {
    _resize(tester, _iphone);
    final state = AppState()
      ..signedIn = true
      ..markCoachMarkSeen();
    await tester.pumpWidget(_host(state, const DiscoveryScreen()));

    await tester.tap(find.text('OFF'));
    await tester.pumpAndSettle();
    expect(find.text('今、何したい？'), findsOneWidget);

    await tester.tap(find.text('飲みに行く'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('SESSION ON にする'));
    await tester.pumpAndSettle();
    // トースト（1.8s で自動的に消える）を流しきる。
    await tester.pump(const Duration(seconds: 2));
    await tester.pumpAndSettle();

    expect(state.isOn, isTrue);
    expect(state.intent, SIntent.drinks);
    expect(find.text('ON'), findsOneWidget);
  });

  testWidgets('右スワイプで Session Request が飛ぶ', (tester) async {
    _resize(tester, _iphone);
    final state = AppState()
      ..signedIn = true
      ..markCoachMarkSeen()
      ..setIntent(SIntent.drinks);
    await tester.pumpWidget(_host(state, const DiscoveryScreen()));

    final before = state.matches.length;
    await tester.drag(find.byType(SwipeDeck), const Offset(300, 0));
    await tester.pumpAndSettle();

    // 相手も同じ Intent で ON なので相互成立し、G へ遷移する。
    expect(state.matches.length, before + 1);
    expect(find.text("IT'S A\nSESSION"), findsOneWidget);
  });

  testWidgets('主要画面が 393×852 と 360×800 の両方で描画できる', (tester) async {
    for (final size in [_iphone, _android]) {
      _resize(tester, size);
      final state = AppState()
        ..signedIn = true
        ..markCoachMarkSeen();

      for (final screen in <Widget>[
        const DiscoveryScreen(),
        const MessagesScreen(),
        const ProfileScreen(),
        const ProfilePreviewScreen(),
        const SettingsScreen(),
        const PaywallScreen(),
        ChatScreen(match: state.matches.first),
      ]) {
        await tester.pumpWidget(_host(state, screen));
        await tester.pumpAndSettle();
        expect(tester.takeException(), isNull,
            reason: '${screen.runtimeType} @ ${size.width.toInt()}x${size.height.toInt()}');
      }
    }
  });
}
