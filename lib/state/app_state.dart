import 'package:flutter/widgets.dart';

import '../models/chat.dart';
import '../models/intent.dart';
import '../models/safety.dart';
import '../models/user.dart';
import 'mock_data.dart';

enum DiscoveryStatus { loading, ready, empty, offline }

/// 無料プランの上限。21回目の Request で K2（Daily limit）を出す。
const kFreeDailyRequests = 20;

/// 距離の絞り込みは 1〜30km。無料は 5km 下限（5未満は値を変えずに D2 を出す）。
const kDistanceMin = 1;
const kDistanceMax = 30;
const kFreeDistanceFloor = 5;

/// アプリ全体の状態。バックエンドはまだ無いので、
/// ここがそのまま API クライアントの差し込み口になる。
class AppState extends ChangeNotifier {
  AppState() {
    me = MockData.me();
    _queue = MockData.discoveryQueue();
    matches = MockData.matches();
    blocked = MockData.blocked();
  }

  // ---- auth ----------------------------------------------------------------
  bool signedIn = false;
  String phone = '+81 90 1234 5678';

  // ---- profile -------------------------------------------------------------
  late SUser me;

  // ---- session state（可用性。Online ではない）------------------------------
  bool get isOn => me.isOn;
  SIntent? get intent => me.intent;

  /// Intent を選ぶと SESSION ON になる。自動失効はサーバー側 TTL で、UI にタイマーは出さない。
  void setIntent(SIntent value) {
    me.intent = value;
    me.isOn = true;
    notifyListeners();
  }

  void turnOff() {
    me.isOn = false;
    notifyListeners();
  }

  // ---- discovery -----------------------------------------------------------
  late List<SUser> _queue;
  int _index = 0;
  DiscoveryStatus status = DiscoveryStatus.ready;
  bool coachMarkSeen = false;

  SUser? get current => _index < _queue.length ? _queue[_index] : null;
  SUser? get next => _index + 1 < _queue.length ? _queue[_index + 1] : null;

  void setStatus(DiscoveryStatus value) {
    status = value;
    notifyListeners();
  }

  void markCoachMarkSeen() {
    coachMarkSeen = true;
    notifyListeners();
  }

  /// 左スワイプ。Undo できるよう直前の1件だけ覚えておく（Undo は Session+）。
  SUser? lastSkipped;

  void skip() {
    lastSkipped = current;
    _advance();
  }

  void undoSkip() {
    if (!isPlus || lastSkipped == null || _index == 0) return;
    _index -= 1;
    lastSkipped = null;
    notifyListeners();
  }

  void _advance() {
    if (_index < _queue.length) _index += 1;
    if (current == null) status = DiscoveryStatus.empty;
    notifyListeners();
  }

  /// 右スワイプ = Session Request。ON が必須で、無料は1日20件まで。
  /// 相互なら SMatch を作って返す（= G へ遷移する合図）。
  SMatch? sendRequest() {
    final person = current;
    if (person == null) return null;
    requestsUsedToday += 1;
    final mutual = person.isOn && person.intent == me.intent;
    SMatch? created;
    if (mutual) {
      created = SMatch(
        person: person,
        intent: person.intent ?? me.intent ?? SIntent.drinks,
        createdAt: DateTime.now(),
      );
      matches.insert(0, created);
    }
    _advance();
    return created;
  }

  bool get canRequest => isOn && (isPlus || requestsUsedToday < kFreeDailyRequests);
  bool get dailyLimitReached => !isPlus && requestsUsedToday >= kFreeDailyRequests;

  void resetQueue() {
    _queue = MockData.discoveryQueue();
    _index = 0;
    status = DiscoveryStatus.ready;
    notifyListeners();
  }

  // ---- filters -------------------------------------------------------------
  SRange ageRange = const SRange(22, 32);
  Gender showMe = Gender.female;
  int distanceKm = 5;

  /// 無料ユーザーが 5km より左へドラッグしても値は変えず、D2 の案内だけ出す。
  bool distanceLockHit = false;

  int get distanceFloor => isPlus ? kDistanceMin : kFreeDistanceFloor;

  void setDistance(int km) {
    final clamped = km.clamp(distanceFloor, kDistanceMax);
    distanceLockHit = !isPlus && km < kFreeDistanceFloor;
    distanceKm = clamped;
    notifyListeners();
  }

  void setAgeRange(SRange value) {
    ageRange = value;
    notifyListeners();
  }

  void setShowMe(Gender value) {
    showMe = value;
    notifyListeners();
  }

  void resetFilters() {
    ageRange = const SRange(22, 32);
    showMe = Gender.female;
    distanceKm = isPlus ? 5 : kFreeDistanceFloor;
    distanceLockHit = false;
    notifyListeners();
  }

  // ---- messages ------------------------------------------------------------
  late List<SMatch> matches;

  List<SMatch> get activeSessions => matches.where((m) => m.active).toList(growable: false);
  List<SMatch> get recentMessages => matches.where((m) => !m.active).toList(growable: false);
  int get interestedCount => MockData.interestedCount;
  int get unreadTotal => matches.fold(0, (sum, m) => sum + m.unread);

  /// POST /messages だけが age_verified_at != null を要求する（403 → G2）。
  bool get canSendMessage => me.ageVerified;

  void sendMessage(SMatch match, String body) {
    match.messages.add(SMessage(body: body, mine: true, createdAt: DateTime.now()));
    notifyListeners();
  }

  void receiveMessage(SMatch match, String body) {
    match.messages.add(SMessage(body: body, mine: false, createdAt: DateTime.now()));
    notifyListeners();
  }

  void markRead(SMatch match) {
    if (match.unread == 0) return;
    match.unread = 0;
    notifyListeners();
  }

  void unmatch(SMatch match) {
    matches.remove(match);
    notifyListeners();
  }

  // ---- age verification ----------------------------------------------------
  /// 外部の年齢確認プロバイダから受け取るのは boolean の結果のみ。
  void markAgeVerified() {
    me.ageVerifiedAt = DateTime.now();
    notifyListeners();
  }

  // ---- entitlements --------------------------------------------------------
  bool isPlus = false;
  int requestsUsedToday = 0;

  void purchasePlus() {
    isPlus = true;
    distanceLockHit = false;
    notifyListeners();
  }

  // ---- safety --------------------------------------------------------------
  late List<BlockedUser> blocked;

  void block(SUser person) {
    blocked.insert(0, BlockedUser(name: person.displayName, blockedOn: _today(), gradientSeed: 0));
    matches.removeWhere((m) => m.person.id == person.id);
    _queue.removeWhere((u) => u.id == person.id);
    if (_index >= _queue.length) _index = _queue.length;
    notifyListeners();
  }

  void unblock(BlockedUser user) {
    blocked.remove(user);
    notifyListeners();
  }

  // ---- settings ------------------------------------------------------------
  bool discoverable = true;
  bool notifyOnSession = true;
  bool notifyOnMessage = true;
  bool notifyOnInterest = true;

  void setDiscoverable(bool v) {
    discoverable = v;
    notifyListeners();
  }

  void setNotifyOnSession(bool v) {
    notifyOnSession = v;
    notifyListeners();
  }

  void setNotifyOnMessage(bool v) {
    notifyOnMessage = v;
    notifyListeners();
  }

  void setNotifyOnInterest(bool v) {
    notifyOnInterest = v;
    notifyListeners();
  }

  // ---- profile edits -------------------------------------------------------
  void updateBio(String value) {
    me.bio = value;
    notifyListeners();
  }

  void updateInterests(List<String> ids) {
    me.interests = ids.take(5).toList();
    notifyListeners();
  }

  void signOut() {
    signedIn = false;
    notifyListeners();
  }

  static String _today() {
    final n = DateTime.now();
    return '${n.year}.${n.month.toString().padLeft(2, '0')}.${n.day.toString().padLeft(2, '0')}';
  }
}

/// Provider を足さずに済ませるための最小限のスコープ。
class AppScope extends InheritedNotifier<AppState> {
  const AppScope({super.key, required AppState state, required super.child})
    : super(notifier: state);

  static AppState of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<AppScope>();
    assert(scope != null, 'AppScope が見つかりません');
    return scope!.notifier!;
  }

  /// 再描画を伴わない読み出し（コールバック内から使う）。
  static AppState read(BuildContext context) {
    final scope = context.getInheritedWidgetOfExactType<AppScope>();
    assert(scope != null, 'AppScope が見つかりません');
    return scope!.notifier!;
  }
}
