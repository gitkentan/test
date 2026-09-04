import { createDevSeed } from '../data/fixtures/devSeed';
import { SessionBackend } from '../data/memory/backend';
import { createRepositories } from '../data/memory/repositories';
import type { Repositories } from '../data/repositories';
import type { KeyValueStorage } from '../data/storage';
import { AgeVerificationService } from './AgeVerificationService';
import { ChatService } from './ChatService';
import { DiscoveryService } from './DiscoveryService';
import { ProfileService } from './ProfileService';
import { SafetyService } from './SafetyService';
import { SessionService } from './SessionService';

export interface Services {
  repos: Repositories;
  backend: SessionBackend;
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
  const backend = new SessionBackend({ storage, seed: createDevSeed() });
  const repos = createRepositories(backend);

  return {
    repos,
    backend,
    session: new SessionService(repos),
    discovery: new DiscoveryService(repos),
    chat: new ChatService(repos),
    safety: new SafetyService(repos),
    profile: new ProfileService(repos),
    ageVerification: new AgeVerificationService(repos),
  };
}

export { SessionService, DiscoveryService, ChatService, SafetyService, ProfileService, AgeVerificationService };
