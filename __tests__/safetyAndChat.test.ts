import { createTestBackend, createVerifiedUser, draft } from './helpers';

/** Acceptance Criteria — Chat / Safety（仕様書 §16, §19 Gate, §20, §31）。 */

async function createSession(backend: Awaited<ReturnType<typeof createTestBackend>>['backend']) {
  await createVerifiedUser(backend, 'a');
  await createVerifiedUser(backend, 'b');
  await backend.turnSessionOn('a', 'drinks');
  await backend.turnSessionOn('b', 'drinks');
  await backend.sendRequest('a', 'b');
  const outcome = await backend.sendRequest('b', 'a');
  if (outcome.kind !== 'matched') throw new Error('expected a match');
  return outcome.conversationId;
}

describe('Chat', () => {
  it('Session 成立時に Conversation が作られ、テキストと画像を送れる', async () => {
    const { backend } = createTestBackend();
    const conversationId = await createSession(backend);

    await backend.sendText(conversationId, 'a', '20時に渋谷で');
    await backend.sendImage(conversationId, 'b', 'file:///photo.jpg');

    const messages = await backend.listMessages(conversationId, 'a');
    expect(messages.map((m) => m.type)).toEqual(['text', 'image']);
    expect(messages[0].text).toBe('20時に渋谷で');
  });

  it('Active 終了後もチャット履歴は残る', async () => {
    const { backend, advanceHours } = createTestBackend();
    const conversationId = await createSession(backend);
    await backend.sendText(conversationId, 'a', 'また今度');

    advanceHours(7);

    expect(await backend.listMessages(conversationId, 'a')).toHaveLength(1);
    const [summary] = await backend.listConversations('a');
    expect(summary.isActive).toBe(false);
  });

  it('年齢未確認ユーザーは free-form message を送信できない', async () => {
    const { backend } = createTestBackend();
    const conversationId = await createSession(backend);

    // 何らかの理由で確認が取り消された状態を再現する。
    await backend.applyVerificationResult({
      userId: 'a',
      ageVerified: false,
      verifiedAt: Date.now(),
      providerReference: 'revoked',
    });

    await expect(backend.sendText(conversationId, 'a', 'hello')).rejects.toMatchObject({
      code: 'AGE_NOT_VERIFIED',
    });
  });

  it('会話の当事者以外は読み書きできない', async () => {
    const { backend } = createTestBackend();
    const conversationId = await createSession(backend);
    await createVerifiedUser(backend, 'stranger');

    await expect(backend.listMessages(conversationId, 'stranger')).rejects.toMatchObject({
      code: 'CONVERSATION_UNAVAILABLE',
    });
  });
});

describe('Block', () => {
  it('Block 後は Discovery / Request / Chat のすべてで遮断される', async () => {
    const { backend } = createTestBackend();
    const conversationId = await createSession(backend);

    await backend.block('a', 'b');

    expect((await backend.loadDiscovery('a')).candidates).toHaveLength(0);
    expect((await backend.loadDiscovery('b')).candidates).toHaveLength(0);

    await expect(backend.sendText(conversationId, 'a', 'hello')).rejects.toMatchObject({
      code: 'BLOCKED',
    });
    // ブロックされた側からも送れない。
    await expect(backend.sendText(conversationId, 'b', 'hello')).rejects.toMatchObject({
      code: 'BLOCKED',
    });

    // existing chat は UI 上非表示になる。
    expect(await backend.listConversations('a')).toHaveLength(0);
    expect(await backend.listConversations('b')).toHaveLength(0);
  });

  it('Block 中は新しい Request を送れず、解除すると元に戻る', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');
    await backend.turnSessionOn('a', 'drinks');

    await backend.block('b', 'a');
    await expect(backend.sendRequest('a', 'b')).rejects.toMatchObject({ code: 'BLOCKED' });

    await backend.unblock('b', 'a');
    expect((await backend.sendRequest('a', 'b')).kind).toBe('requested');
  });

  it('進行中の pending Request は Block で成立しなくなる', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');
    await backend.turnSessionOn('a', 'drinks');
    await backend.turnSessionOn('b', 'drinks');

    await backend.sendRequest('a', 'b');
    await backend.block('b', 'a');

    await expect(backend.sendRequest('b', 'a')).rejects.toMatchObject({ code: 'BLOCKED' });
    expect(await backend.listSessions('a')).toHaveLength(0);
  });
});

describe('Report', () => {
  it('理由と任意の詳細を記録できる', async () => {
    const { backend } = createTestBackend();
    await createVerifiedUser(backend, 'a');
    await createVerifiedUser(backend, 'b');

    const report = await backend.report('a', 'b', 'harassment', '不快なメッセージ');

    expect(report.reason).toBe('harassment');
    expect(report.details).toBe('不快なメッセージ');
  });
});

describe('Onboarding gate', () => {
  it('18歳未満はプロフィールを作れない', async () => {
    const { backend, clock } = createTestBackend();
    const seventeen = new Date(clock.now);
    seventeen.setUTCFullYear(seventeen.getUTCFullYear() - 17);

    await expect(
      backend.createProfile('kid', draft({ birthDate: seventeen.toISOString().slice(0, 10) })),
    ).rejects.toMatchObject({ code: 'UNDER_MINIMUM_AGE' });
  });
});
