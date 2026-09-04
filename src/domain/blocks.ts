import type { Block, UserId } from './types';

/**
 * Block（仕様書 §20）
 *
 * Block 後は Discovery / Request / Message のすべてで双方向に遮断する。
 * 判定は必ず server-side でも通す。
 */
export function isBlockedBetween(
  blocks: readonly Block[],
  a: UserId,
  b: UserId,
): boolean {
  return blocks.some(
    (block) =>
      (block.blockerId === a && block.blockedId === b) ||
      (block.blockerId === b && block.blockedId === a),
  );
}

/** viewer から見て遮断されている相手の集合（自分がブロックした側／された側の両方）。 */
export function blockedUserIds(blocks: readonly Block[], viewerId: UserId): Set<UserId> {
  const ids = new Set<UserId>();
  for (const block of blocks) {
    if (block.blockerId === viewerId) ids.add(block.blockedId);
    if (block.blockedId === viewerId) ids.add(block.blockerId);
  }
  return ids;
}
