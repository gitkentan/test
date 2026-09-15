import 'package:flutter/material.dart';

import 'screens/onboarding/splash_screen.dart';
import 'state/app_state.dart';
import 'theme/tokens.dart';

/// MaterialApp は Router としてのみ使う。
/// UI は Material も Cupertino も使わず、両 OS で同一の自前ウィジェットで作る。
class SessionApp extends StatefulWidget {
  const SessionApp({super.key});

  @override
  State<SessionApp> createState() => _SessionAppState();
}

class _SessionAppState extends State<SessionApp> {
  final AppState _state = AppState();

  @override
  void dispose() {
    _state.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AppScope(
      state: _state,
      child: MaterialApp(
        title: 'Session',
        debugShowCheckedModeBanner: false,
        // ダーク固定。OS のライトモードには追従しない。
        theme: ThemeData(
          brightness: Brightness.dark,
          scaffoldBackgroundColor: SColor.bg,
          canvasColor: SColor.bg,
          splashFactory: NoSplash.splashFactory,
          highlightColor: const Color(0x00000000),
          textTheme: Typography.whiteCupertino.apply(
            bodyColor: SColor.text,
            displayColor: SColor.text,
          ),
        ),
        builder: (context, child) => MediaQuery.withClampedTextScaling(
          // 1.3 まで崩れないこと。それ以上は崩れるのでクランプする。
          maxScaleFactor: 1.3,
          child: child ?? const SizedBox.shrink(),
        ),
        home: const SplashScreen(),
      ),
    );
  }
}

/// F / I / I2 / K1 は下からのフルスクリーン。
Route<T> sUpRoute<T>(Widget page) => PageRouteBuilder<T>(
  transitionDuration: SMotion.sheet,
  reverseTransitionDuration: SMotion.sheet,
  opaque: true,
  pageBuilder: (_, __, ___) => page,
  transitionsBuilder: (_, animation, __, child) => SlideTransition(
    position: Tween(
      begin: const Offset(0, 1),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: animation, curve: SMotion.sheetEase)),
    child: child,
  ),
);

/// 設定配下（L1〜L6）だけが階層遷移。
Route<T> sPushRoute<T>(Widget page) => PageRouteBuilder<T>(
  transitionDuration: const Duration(milliseconds: 280),
  pageBuilder: (_, __, ___) => page,
  transitionsBuilder: (_, animation, __, child) => SlideTransition(
    position: Tween(
      begin: const Offset(0.18, 0),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: animation, curve: SMotion.sheetEase)),
    child: FadeTransition(opacity: animation, child: child),
  ),
);

/// フェードのみ（Splash → Welcome / Discovery、G の演出後など）。
Route<T> sFadeRoute<T>(Widget page) => PageRouteBuilder<T>(
  transitionDuration: SMotion.fade,
  pageBuilder: (_, __, ___) => page,
  transitionsBuilder: (_, animation, __, child) => FadeTransition(opacity: animation, child: child),
);
