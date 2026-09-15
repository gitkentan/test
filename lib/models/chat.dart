import 'intent.dart';
import 'user.dart';

class SMessage {
  SMessage({required this.body, required this.mine, required this.createdAt, this.pending = false});

  final String body;
  final bool mine;
  final DateTime createdAt;

  /// オフライン時はキューに保持し、復帰後に送信する。
  bool pending;
}

/// match { a, b, intent, created_at } — 相互 Request で生成される。
class SMatch {
  SMatch({
    required this.person,
    required this.intent,
    required this.createdAt,
    List<SMessage>? messages,
    this.unread = 0,
    this.active = true,
  }) : messages = messages ?? <SMessage>[];

  final SUser person;
  final SIntent intent;
  final DateTime createdAt;
  final List<SMessage> messages;
  int unread;

  /// Active Session（相手が SESSION ON）かどうか。緑ドットの条件。
  bool active;

  SMessage? get last => messages.isEmpty ? null : messages.last;
}

/// 「あなたに興味あり」= 自分に Request を送った相手。無料はぼかし表示。
class Interested {
  const Interested({required this.person, required this.intent, required this.elapsed});

  final SUser person;
  final SIntent intent;
  final String elapsed;
}
