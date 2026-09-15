import 'package:flutter/widgets.dart';

import '../../state/app_state.dart';
import '../../state/mock_data.dart';
import '../../theme/tokens.dart';
import '../../widgets/s_toast.dart';
import '../s_scaffold.dart';

/// L4 Blocked users · 0件時は「ブロックしたユーザーはいません」のみ。
class BlockedUsersScreen extends StatelessWidget {
  const BlockedUsersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final state = AppScope.of(context);
    final blocked = state.blocked;

    return SScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SHeader(title: 'ブロックしたユーザー', onBack: () => Navigator.of(context).maybePop()),
          Expanded(
            child: blocked.isEmpty
                ? const Center(
                    child: Text(
                      'ブロックしたユーザーはいません',
                      style: TextStyle(fontSize: 14, color: SColor.muted),
                    ),
                  )
                : ListView(
                    padding: const EdgeInsets.fromLTRB(20, 22, 20, 24),
                    children: [
                      for (final b in blocked) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          decoration: BoxDecoration(
                            color: SColor.surface,
                            borderRadius: BorderRadius.circular(SRadius.field),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 44,
                                height: 44,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  gradient: LinearGradient(
                                    begin: const Alignment(-0.6, -1),
                                    end: const Alignment(0.4, 1),
                                    colors: b.gradientSeed.isEven
                                        ? MockGradients.warmGrey
                                        : MockGradients.sand,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      b.name,
                                      style: const TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.w600,
                                        color: SColor.text,
                                      ),
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      b.blockedOn,
                                      style: const TextStyle(fontSize: 12, color: SColor.muted2),
                                    ),
                                  ],
                                ),
                              ),
                              GestureDetector(
                                behavior: HitTestBehavior.opaque,
                                onTap: () {
                                  AppScope.read(context).unblock(b);
                                  SToast.show(context, 'ブロックを解除しました');
                                },
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: SColor.raised,
                                    borderRadius: BorderRadius.circular(SRadius.chip),
                                  ),
                                  child: const Text(
                                    '解除',
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                      color: SColor.text,
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),
                      ],
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 6, vertical: 8),
                        child: Text(
                          'ブロック中はお互いに表示されません。'
                          '解除しても過去のSessionとメッセージは復元されません。',
                          style: TextStyle(fontSize: 12, height: 1.65, color: SColor.muted2),
                        ),
                      ),
                    ],
                  ),
          ),
        ],
      ),
    );
  }
}
