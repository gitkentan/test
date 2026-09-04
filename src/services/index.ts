import { isSupabaseConfigured } from '../config/env';
import { createDevSeed } from '../data/fixtures/devSeed';
import { SessionBackend } from '../data/memory/backend';
import { createRepositories } from '../data/memory/repositories';
import { getSupabaseClient } from '../data/supabase/client';
import { createSupabaseRepositories } from '../data/supabase/repositories';
import type { Repositories } from '../data/repositories';
import type { KeyValueStorage } from '../data/storage';
import { PhotoUploadService } from './PhotoUploadService';
import { AgeVerificationService } from './AgeVerificationService';
import { ChatService } from './ChatService';
import { DiscoveryService } from './DiscoveryService';
import { ProfileService } from './ProfileService';
import { SafetyService } from './SafetyService';
import { SessionService } from './SessionService';

export interface Services {
  repos: Repositories;
  /**
   * 端末内バックエンド。Supabase 接続時は null。
   * 開発時のリセットなど、ローカル構成でのみ意味を持つ操作に使う。
   */
  backend: SessionBackend | null;
  /** 実バックエンドに接続しているか。UI の注意書きの出し分けに使う。 */
  isRemote: boolean;
  photos: PhotoUploadService;
  session: SessionService;
  discovery: DiscoveryService;
  chat: ChatService;
  safety: SafetyService;
  profile: ProfileService;
  ageVerification: AgeVerificationService;
}

/**
 * Service container。
 *
 * UI は Services 経由でしかデータへ触れない。
 * 実バックエンドへ差し替えるときは createRepositories() の実装を入れ替えるだけでよい。
 */
export function createServices(storage: KeyValueStorage): Services {
  // Supabase の設定が揃っていれば実バックエンドへ。
  // 揃っていなければ端末内で完結する構成へフォールバックする。
  const backend = isSupabaseConfigured
    ? null
    : new SessionBackend({ storage, seed: createDevSeed() });
  const repos = backend
    ? createRepositories(backend)
    : createSupabaseRepositories(getSupabaseClient());

  const photos = new PhotoUploadService();

  return {
    repos,
    backend,
    isRemote: isSupabaseConfigured,
    photos,
    session: new SessionService(repos),
    discovery: new DiscoveryService(repos),
    chat: new ChatService(repos, photos),
    safety: new SafetyService(repos),
    profile: new ProfileService(repos, photos),
    ageVerification: new AgeVerificationService(repos),
  };
}

export {
  SessionService,
  DiscoveryService,
  ChatService,
  SafetyService,
  ProfileService,
  AgeVerificationService,
  PhotoUploadService,
};
