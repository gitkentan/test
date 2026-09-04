-- Session β — スキーマ（仕様書 §24）
--
-- Supabase / Postgres 前提。auth.users は Supabase Auth が管理し、
-- ここではプロフィール以降を持つ。
--
-- 重要な設計方針:
--   * §25 の検証はすべて DB 側（制約 + RPC）で担保する。
--     client が何を送っても、ここを通らずに状態は変えられない。
--   * 相互 Request → Session の成立は一意制約と排他ロックで冪等にする。
--   * 緯度経度は他ユーザーへ公開しない。距離は RPC が丸めて返す。

create extension if not exists "uuid-ossp";

-- ---------------------------------------------------------------- enums

create type intent as enum ('drinks', 'food', 'cafe', 'free_now');
create type gender as enum ('woman', 'man', 'nonbinary');
create type discovery_preference as enum ('women', 'men', 'everyone');
create type swipe_type as enum ('session_request', 'skip');
create type request_status as enum ('pending', 'matched', 'expired', 'cancelled', 'blocked');
create type session_state as enum ('active', 'past');
create type message_type as enum ('text', 'image');
create type report_reason as enum (
  'inappropriate', 'harassment', 'impersonation', 'underage', 'solicitation', 'other'
);
create type verification_state as enum ('pending', 'verified', 'rejected');

-- ---------------------------------------------------------------- users

create table users (
  id uuid primary key references auth.users (id) on delete cascade,
  name text not null check (length(btrim(name)) between 1 and 20),
  birth_date date not null,
  gender gender not null,
  discovery_preference discovery_preference not null,
  bio text not null default '' check (length(bio) <= 120),
  interests text[] not null default '{}' check (array_length(interests, 1) is null or array_length(interests, 1) <= 5),
  photos jsonb not null default '[]',
  -- 内部 ranking 専用。RLS でこの列を他人に読ませない（下の users_public ビュー参照）。
  latitude double precision,
  longitude double precision,
  -- 自己申告では立たない。age_verifications 経由でのみ更新される（§19）。
  age_verified boolean not null default false,
  age_verified_at timestamptz,
  age_verification_reference text,
  last_active_at timestamptz not null default now(),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),

  -- 18歳未満は登録できない（§18）。DB 側でも足切りする。
  constraint users_minimum_age check (birth_date <= (current_date - interval '18 years'))
);

create index users_last_active_idx on users (last_active_at desc);

-- ------------------------------------------------------- session statuses

create table session_statuses (
  user_id uuid primary key references users (id) on delete cascade,
  session_on boolean not null default false,
  current_intent intent,
  started_at timestamptz,
  expires_at timestamptz,
  updated_at timestamptz not null default now(),

  -- ON なら intent と expiry が必ずある。OFF ならどちらも無い（§5, §6）。
  constraint session_status_consistent check (
    (session_on and current_intent is not null and expires_at is not null)
    or (not session_on and current_intent is null and expires_at is null)
  )
);

create index session_statuses_active_idx on session_statuses (session_on, expires_at);

-- --------------------------------------------------------------- blocks

create table blocks (
  blocker_id uuid not null references users (id) on delete cascade,
  blocked_id uuid not null references users (id) on delete cascade,
  created_at timestamptz not null default now(),
  primary key (blocker_id, blocked_id),
  constraint block_not_self check (blocker_id <> blocked_id)
);

create index blocks_blocked_idx on blocks (blocked_id);

-- --------------------------------------------------------------- swipes

create table swipes (
  id uuid primary key default uuid_generate_v4(),
  sender_id uuid not null references users (id) on delete cascade,
  receiver_id uuid not null references users (id) on delete cascade,
  type swipe_type not null,
  sender_intent intent,
  created_at timestamptz not null default now(),
  expires_at timestamptz,
  status request_status not null default 'pending',

  constraint swipe_not_self check (sender_id <> receiver_id)
);

-- 有効な Request は相手ごとに1件まで（§25 duplicate request）。
-- 期限切れ / matched / cancelled になれば再送できる。
create unique index swipes_one_active_request
  on swipes (sender_id, receiver_id)
  where (type = 'session_request' and status = 'pending');

create index swipes_receiver_idx on swipes (receiver_id, status);
create index swipes_sender_idx on swipes (sender_id, created_at desc);

-- -------------------------------------------------------------- sessions

create table sessions (
  id uuid primary key default uuid_generate_v4(),
  user_a_id uuid not null references users (id) on delete cascade,
  user_b_id uuid not null references users (id) on delete cascade,
  user_a_intent intent,
  user_b_intent intent,
  started_at timestamptz not null default now(),
  active_until timestamptz not null,
  status session_state not null default 'active',

  constraint session_not_self check (user_a_id <> user_b_id),
  -- ペアを正規化して持つことで、一意制約で重複 Session を防げるようにする。
  constraint session_pair_ordered check (user_a_id < user_b_id)
);

-- 同じ2人の Active Session は同時に1件だけ（§12, §25 duplicate match）。
create unique index sessions_one_active_pair
  on sessions (user_a_id, user_b_id)
  where (status = 'active');

create index sessions_user_a_idx on sessions (user_a_id, status);
create index sessions_user_b_idx on sessions (user_b_id, status);

-- --------------------------------------------------------- conversations

create table conversations (
  id uuid primary key default uuid_generate_v4(),
  session_id uuid not null unique references sessions (id) on delete cascade,
  user_a_id uuid not null references users (id) on delete cascade,
  user_b_id uuid not null references users (id) on delete cascade,
  created_at timestamptz not null default now(),
  last_message_at timestamptz
);

create index conversations_user_a_idx on conversations (user_a_id);
create index conversations_user_b_idx on conversations (user_b_id);

-- -------------------------------------------------------------- messages

create table messages (
  id uuid primary key default uuid_generate_v4(),
  conversation_id uuid not null references conversations (id) on delete cascade,
  sender_id uuid not null references users (id) on delete cascade,
  type message_type not null,
  text text check (length(text) <= 1000),
  image_url text,
  created_at timestamptz not null default now(),

  constraint message_payload check (
    (type = 'text' and text is not null and image_url is null)
    or (type = 'image' and image_url is not null and text is null)
  )
);

create index messages_conversation_idx on messages (conversation_id, created_at);

-- --------------------------------------------------------------- reports

create table reports (
  id uuid primary key default uuid_generate_v4(),
  reporter_id uuid not null references users (id) on delete cascade,
  reported_user_id uuid not null references users (id) on delete cascade,
  reason report_reason not null,
  details text check (length(details) <= 400),
  created_at timestamptz not null default now(),
  constraint report_not_self check (reporter_id <> reported_user_id)
);

-- ----------------------------------------------------- age verifications

-- 年齢確認の試行（§19）。
-- client は insert も update もできない。作成は RPC、確定は service role のみ。
create table age_verifications (
  reference text primary key,
  user_id uuid not null references users (id) on delete cascade,
  status verification_state not null default 'pending',
  reason text,
  created_at timestamptz not null default now(),
  resolved_at timestamptz
);

create index age_verifications_user_idx on age_verifications (user_id, created_at desc);
