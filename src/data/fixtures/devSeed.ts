import { allowDevFixtures } from '../../config/env';
import { turnOn } from '../../domain/sessionStatus';
import type { Intent, User } from '../../domain/types';
import type { Database, SeedFn } from '../memory/backend';

/**
 * 開発環境専用の fixture（仕様書 §0）。
 *
 * このファイルは `createDevSeed()` 経由でのみ使い、`allowDevFixtures` が false のときは
 * 何も投入しない。本番ロジックが fake user を掴むことはない。
 *
 * Cold start / ranking / radius fallback の挙動を手元で確認できるよう、
 * MIN_DISCOVERY_POOL（20）の前後を跨ぐ人数を、東京周辺に距離を散らして置いている。
 */

interface FixtureSpec {
  name: string;
  age: number;
  gender: User['gender'];
  bio: string;
  interests: string[];
  /** 渋谷駅からのおおよその距離（km）。距離バケットの確認用。 */
  offsetKm: number;
  bearingDeg: number;
  intent: Intent | null;
  /** 何時間前まで active だったか。recency ranking の確認用。 */
  activeHoursAgo: number;
}

const ORIGIN = { latitude: 35.6595, longitude: 139.7005 }; // 渋谷駅

const SPECS: FixtureSpec[] = [
  { name: 'Yuna', age: 24, gender: 'woman', bio: '週末はだいたい外にいます', interests: ['ワイン', '銭湯', '映画'], offsetKm: 0.7, bearingDeg: 20, intent: 'drinks', activeHoursAgo: 0.1 },
  { name: 'Rio', age: 27, gender: 'woman', bio: 'ラーメンの話ならいくらでも', interests: ['ラーメン', 'サウナ'], offsetKm: 1.4, bearingDeg: 95, intent: 'food', activeHoursAgo: 0.3 },
  { name: 'Haruki', age: 29, gender: 'man', bio: 'カメラ持って歩くのが好き', interests: ['写真', 'コーヒー', '古着'], offsetKm: 2.2, bearingDeg: 150, intent: 'cafe', activeHoursAgo: 0.5 },
  { name: 'Mei', age: 23, gender: 'woman', bio: '今日は予定なし', interests: ['音楽', 'クラフトビール'], offsetKm: 2.9, bearingDeg: 210, intent: 'drinks', activeHoursAgo: 0.2 },
  { name: 'Sota', age: 31, gender: 'man', bio: '静かめの店が好きです', interests: ['日本酒', '本'], offsetKm: 3.6, bearingDeg: 300, intent: 'drinks', activeHoursAgo: 1.2 },
  { name: 'Aoi', age: 26, gender: 'woman', bio: '甘いものは別腹', interests: ['カフェ巡り', 'アート'], offsetKm: 4.1, bearingDeg: 40, intent: 'cafe', activeHoursAgo: 0.8 },
  { name: 'Ken', age: 28, gender: 'man', bio: '締めの一杯まで付き合います', interests: ['焼き鳥', 'フットサル'], offsetKm: 4.8, bearingDeg: 120, intent: 'drinks', activeHoursAgo: 2.5 },
  { name: 'Nao', age: 25, gender: 'nonbinary', bio: '新しい店を開拓中', interests: ['タイ料理', 'レコード'], offsetKm: 1.1, bearingDeg: 260, intent: 'food', activeHoursAgo: 0.4 },
  { name: 'Saki', age: 22, gender: 'woman', bio: 'とりあえず外に出たい', interests: ['ダンス', 'カラオケ'], offsetKm: 2.4, bearingDeg: 330, intent: 'free_now', activeHoursAgo: 0.1 },
  { name: 'Ryo', age: 30, gender: 'man', bio: '朝までやってる店に詳しい', interests: ['バー', 'バイク'], offsetKm: 3.1, bearingDeg: 70, intent: 'free_now', activeHoursAgo: 1.8 },
  { name: 'Hana', age: 27, gender: 'woman', bio: '食べるのが趣味です', interests: ['寿司', '旅行', 'ヨガ'], offsetKm: 0.9, bearingDeg: 180, intent: null, activeHoursAgo: 5 },
  { name: 'Taku', age: 33, gender: 'man', bio: '休みが不定期です', interests: ['登山', 'コーヒー'], offsetKm: 5.4, bearingDeg: 15, intent: null, activeHoursAgo: 9 },
  { name: 'Emi', age: 24, gender: 'woman', bio: '猫と暮らしています', interests: ['猫', '純喫茶'], offsetKm: 6.2, bearingDeg: 200, intent: 'cafe', activeHoursAgo: 3 },
  { name: 'Jin', age: 26, gender: 'man', bio: 'よく笑うと言われます', interests: ['バスケ', '韓国料理'], offsetKm: 7.5, bearingDeg: 100, intent: 'food', activeHoursAgo: 1 },
  { name: 'Kaho', age: 29, gender: 'woman', bio: '仕事終わりに軽く', interests: ['ワイン', 'ランニング'], offsetKm: 8.3, bearingDeg: 250, intent: 'drinks', activeHoursAgo: 0.6 },
  { name: 'Yuto', age: 25, gender: 'man', bio: '映画の趣味が合う人と', interests: ['映画', 'ゲーム'], offsetKm: 9.1, bearingDeg: 340, intent: null, activeHoursAgo: 14 },
  { name: 'Rina', age: 28, gender: 'woman', bio: '海が近い街で育ちました', interests: ['サーフィン', 'カフェ巡り'], offsetKm: 9.8, bearingDeg: 60, intent: 'cafe', activeHoursAgo: 4 },
  { name: 'Sho', age: 32, gender: 'man', bio: '料理をよく作ります', interests: ['料理', 'ジャズ'], offsetKm: 11.5, bearingDeg: 140, intent: 'food', activeHoursAgo: 2 },
  { name: 'Miku', age: 23, gender: 'woman', bio: 'フェスによく行きます', interests: ['音楽', 'キャンプ'], offsetKm: 13.2, bearingDeg: 280, intent: 'free_now', activeHoursAgo: 0.9 },
  { name: 'Daiki', age: 27, gender: 'man', bio: 'サウナの後のビールが好き', interests: ['サウナ', 'ビール'], offsetKm: 15.6, bearingDeg: 30, intent: 'drinks', activeHoursAgo: 1.5 },
  { name: 'Yui', age: 26, gender: 'woman', bio: 'のんびり話せる人がいい', interests: ['読書', '紅茶'], offsetKm: 18.4, bearingDeg: 190, intent: null, activeHoursAgo: 20 },
  { name: 'Kota', age: 30, gender: 'man', bio: '週末は自転車で遠出', interests: ['自転車', 'カレー'], offsetKm: 21.0, bearingDeg: 110, intent: 'food', activeHoursAgo: 6 },
  { name: 'Ami', age: 25, gender: 'woman', bio: '写真を撮られるのは苦手', interests: ['植物', '陶芸'], offsetKm: 24.7, bearingDeg: 320, intent: 'cafe', activeHoursAgo: 8 },
  { name: 'Ren', age: 24, gender: 'nonbinary', bio: '深夜のドライブが好き', interests: ['ドライブ', 'テクノ'], offsetKm: 27.3, bearingDeg: 240, intent: 'free_now', activeHoursAgo: 0.7 },
  { name: 'Nana', age: 31, gender: 'woman', bio: '落ち着いた場所が好みです', interests: ['美術館', 'ワイン'], offsetKm: 29.1, bearingDeg: 80, intent: null, activeHoursAgo: 30 },
  { name: 'Itsuki', age: 28, gender: 'man', bio: '新しい人と話すのが好き', interests: ['旅行', 'ボードゲーム'], offsetKm: 34.5, bearingDeg: 160, intent: 'drinks', activeHoursAgo: 3.5 },
];

function offsetCoordinates(offsetKm: number, bearingDeg: number) {
  const bearing = (bearingDeg * Math.PI) / 180;
  const dLat = (offsetKm * Math.cos(bearing)) / 111;
  const dLon =
    (offsetKm * Math.sin(bearing)) /
    (111 * Math.cos((ORIGIN.latitude * Math.PI) / 180));
  return {
    latitude: ORIGIN.latitude + dLat,
    longitude: ORIGIN.longitude + dLon,
  };
}

function birthDateForAge(age: number, now: number): string {
  const today = new Date(now);
  const year = today.getUTCFullYear() - age;
  return `${year}-06-15`;
}

/** fixture 用のプレースホルダ写真。読み込みに失敗した場合は UI 側でブランド地に落ちる。 */
function fixturePhotos(seed: string) {
  return [1, 2, 3].map((n) => ({
    id: `${seed}-${n}`,
    uri: `https://picsum.photos/seed/session-${seed}-${n}/800/1200`,
  }));
}

/**
 * 開発環境でのみ fixture を返す。本番では undefined を返し、seed は一切走らない。
 */
export function createDevSeed(): SeedFn | undefined {
  if (!allowDevFixtures) return undefined;

  return (db: Database, now: number) => {
    // 二重投入を避ける。実ユーザーが作られた後でも fixture は増やさない。
    if (Object.values(db.users).some((user) => user.id.startsWith('fixture_'))) return;

    SPECS.forEach((spec, index) => {
      const id = `fixture_${index + 1}`;
      const seed = spec.name.toLowerCase();
      db.users[id] = {
        id,
        name: spec.name,
        birthDate: birthDateForAge(spec.age, now),
        gender: spec.gender,
        discoveryPreference: 'everyone',
        bio: spec.bio,
        interests: spec.interests,
        photos: fixturePhotos(seed),
        location: offsetCoordinates(spec.offsetKm, spec.bearingDeg),
        ageVerified: true,
        ageVerifiedAt: now,
        ageVerificationReference: `fixture-${id}`,
        lastActiveAt: now - spec.activeHoursAgo * 3_600_000,
        createdAt: now,
        updatedAt: now,
      };
      db.statuses[id] = spec.intent
        ? turnOn(id, spec.intent, now - spec.activeHoursAgo * 3_600_000)
        : {
            userId: id,
            sessionOn: false,
            currentIntent: null,
            startedAt: null,
            expiresAt: null,
            updatedAt: now,
          };
    });
  };
}
