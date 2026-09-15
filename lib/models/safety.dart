class BlockedUser {
  const BlockedUser({required this.name, required this.blockedOn, required this.gradientSeed});

  final String name;
  final String blockedOn;
  final int gradientSeed;
}

enum ReportReason { impersonation, harassment, sexual, spam, underage, other }

extension ReportReasonX on ReportReason {
  String get label => switch (this) {
    ReportReason.impersonation => 'なりすまし・偽プロフィール',
    ReportReason.harassment => '嫌がらせ・不快な言動',
    ReportReason.sexual => '性的なコンテンツ',
    ReportReason.spam => 'スパム・勧誘・詐欺',
    ReportReason.underage => '18歳未満の可能性',
    ReportReason.other => 'その他',
  };
}

enum DeleteReason { found, rest, fewPeople, badExperience, other }

extension DeleteReasonX on DeleteReason {
  String get label => switch (this) {
    DeleteReason.found => '会える人が見つかった',
    DeleteReason.rest => 'しばらく休みたい',
    DeleteReason.fewPeople => '近くに人が少ない',
    DeleteReason.badExperience => '不快な体験があった',
    DeleteReason.other => 'その他',
  };
}
