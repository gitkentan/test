import { createTestBackend, draft } from './helpers';

/**
 * Age Verification（仕様書 §19）。
 *
 * 検証したいのは「client が確認済みを主張できないこと」。
 * age_verified を立てられるのは、プロバイダの結果を取り込むサーバ側の経路だけ。
 */
describe('年齢確認', () => {
  it('プロフィール作成直後は未確認で、Session ON できない', async () => {
    const { backend } = createTestBackend();
    await backend.createProfile('u', draft({ name: 'u' }));

    const user = await backend.getUser('u');
    expect(user?.ageVerified).toBe(false);
    await expect(backend.turnSessionOn('u', 'drinks')).rejects.toMatchObject({
      code: 'AGE_NOT_VERIFIED',
    });
  });

  it('開始しただけでは pending のままで、確認済みにはならない', async () => {
    const { backend } = createTestBackend();
    await backend.createProfile('u', draft({ name: 'u' }));

    const { reference } = await backend.startVerification('u');
    const outcome = await backend.confirmVerification('u', reference);

    expect(outcome.status).toBe('pending');
    expect((await backend.getUser('u'))?.ageVerified).toBe(false);
  });

  it('プロバイダの結果を取り込んで初めて確認済みになる', async () => {
    const { backend, clock } = createTestBackend();
    await backend.createProfile('u', draft({ name: 'u' }));
    const { reference } = await backend.startVerification('u');

    // サーバ側の入口（webhook 相当）
    await backend.applyVerificationResult({
      userId: 'u',
      ageVerified: true,
      verifiedAt: clock.now,
      providerReference: reference,
    });

    const outcome = await backend.confirmVerification('u', reference);
    expect(outcome.status).toBe('verified');
    if (outcome.status !== 'verified') throw new Error('unreachable');
    expect(outcome.user.ageVerified).toBe(true);
    expect(outcome.user.ageVerificationReference).toBe(reference);

    // 確認後は Session ON できる
    const status = await backend.turnSessionOn('u', 'drinks');
    expect(status.sessionOn).toBe(true);
  });

  it('プロバイダが否認した場合は rejected を返し、確認済みにしない', async () => {
    const { backend, clock } = createTestBackend();
    await backend.createProfile('u', draft({ name: 'u' }));
    const { reference } = await backend.startVerification('u');

    await backend.applyVerificationResult({
      userId: 'u',
      ageVerified: false,
      verifiedAt: clock.now,
      providerReference: reference,
    });

    const outcome = await backend.confirmVerification('u', reference);
    expect(outcome.status).toBe('rejected');
    expect((await backend.getUser('u'))?.ageVerified).toBe(false);
  });

  it('身に覚えのない reference では確認済みにならない', async () => {
    const { backend } = createTestBackend();
    await backend.createProfile('u', draft({ name: 'u' }));

    // client が適当な参照を送っても通らない。
    const outcome = await backend.confirmVerification('u', 'agv_forged');
    expect(outcome).toEqual({ status: 'rejected', reason: 'unknown_reference' });
    expect((await backend.getUser('u'))?.ageVerified).toBe(false);
  });

  it('他人の確認試行を自分のものとして確定できない', async () => {
    const { backend, clock } = createTestBackend();
    await backend.createProfile('a', draft({ name: 'a' }));
    await backend.createProfile('b', draft({ name: 'b' }));

    const { reference } = await backend.startVerification('a');
    await backend.applyVerificationResult({
      userId: 'a',
      ageVerified: true,
      verifiedAt: clock.now,
      providerReference: reference,
    });

    // a の確認済み reference を b が使っても通らない。
    const outcome = await backend.confirmVerification('b', reference);
    expect(outcome).toEqual({ status: 'rejected', reason: 'unknown_reference' });
    expect((await backend.getUser('b'))?.ageVerified).toBe(false);
  });

  it('確認が取り消されると Session ON も Chat 送信もできなくなる', async () => {
    const { backend, clock } = createTestBackend();
    await backend.createProfile('u', draft({ name: 'u' }));
    const { reference } = await backend.startVerification('u');
    await backend.applyVerificationResult({
      userId: 'u',
      ageVerified: true,
      verifiedAt: clock.now,
      providerReference: reference,
    });
    await backend.turnSessionOn('u', 'drinks');

    await backend.applyVerificationResult({
      userId: 'u',
      ageVerified: false,
      verifiedAt: clock.now,
      providerReference: reference,
    });

    await expect(backend.turnSessionOn('u', 'cafe')).rejects.toMatchObject({
      code: 'AGE_NOT_VERIFIED',
    });
  });
});
