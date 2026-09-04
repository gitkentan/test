/**
 * ドメイン層とバックエンド規則のテスト。
 * UI に依存しない層だけを対象にし、Acceptance Criteria（仕様書 §31）を守る。
 */
module.exports = {
  preset: 'jest-expo',
  testMatch: ['**/__tests__/**/*.test.ts'],
  collectCoverageFrom: ['src/domain/**/*.ts', 'src/data/**/*.ts'],
};
