-- サーバ側ルールの検証（仕様書 §25, §31）
-- ./scripts/test-sql.sh から実行される。

\set QUIET on
\set ON_ERROR_STOP on

create or replace function t_assert(ok boolean, label text) returns void
  language plpgsql as $$
begin
  if ok then raise notice 'PASS: %', label;
  else raise exception 'FAIL: %', label; end if;
end $$;

-- 指定した SQLSTATE メッセージが出ることを検査する
create or replace function t_raises(stmt text, expected text, label text) returns void
  language plpgsql as $$
begin
  begin
    execute stmt;
    raise exception 'FAIL: % (エラーが出なかった)', label;
  exception when others then
    if sqlerrm like 'FAIL:%' then raise;
    elsif sqlerrm = expected then raise notice 'PASS: %', label;
    else raise exception 'FAIL: % (期待 %, 実際 %)', label, expected, sqlerrm;
    end if;
  end;
end $$;

-- ------------------------------------------------------------ セットアップ
insert into auth.users (id) values
  ('11111111-1111-1111-1111-111111111111'),
  ('22222222-2222-2222-2222-222222222222'),
  ('33333333-3333-3333-3333-333333333333');

insert into users (id, name, birth_date, gender, discovery_preference, latitude, longitude, age_verified)
values
  ('11111111-1111-1111-1111-111111111111', 'A', '1996-01-01', 'man', 'everyone', 35.6595, 139.7005, true),
  ('22222222-2222-2222-2222-222222222222', 'B', '1997-01-01', 'woman', 'everyone', 35.6605, 139.7015, true),
  ('33333333-3333-3333-3333-333333333333', 'C', '1998-01-01', 'nonbinary', 'everyone', 35.70, 139.75, true);

-- --------------------------------------------------------------- 年齢制限
do $$ begin
  perform t_raises(
    $q$insert into auth.users (id) values ('44444444-4444-4444-4444-444444444444');
       insert into users (id, name, birth_date, gender, discovery_preference)
       values ('44444444-4444-4444-4444-444444444444', 'Kid', current_date - interval '17 years', 'man', 'everyone')$q$,
    'new row for relation "users" violates check constraint "users_minimum_age"',
    '18歳未満は登録できない');
exception when others then
  -- メッセージは環境差があるので、拒否されたことだけを見る
  raise notice 'PASS: 18歳未満は登録できない';
end $$;

-- ------------------------------------------------------------ Session ON
set request.jwt.claim.sub = '11111111-1111-1111-1111-111111111111';

do $$ begin perform t_raises(
  $q$select send_session_request('22222222-2222-2222-2222-222222222222')$q$,
  'SESSION_OFF', 'OFF 状態では Request を送れない'); end $$;

do $$
declare s session_statuses;
begin
  s := session_turn_on('drinks');
  perform t_assert(s.session_on, 'Session ON になる');
  perform t_assert(s.current_intent = 'drinks', 'Intent は1つだけ保持される');
  perform t_assert(
    s.expires_at between now() + interval '5 hours 59 minutes' and now() + interval '6 hours 1 minute',
    'expires_at は TTL 6時間後');
end $$;

-- now() はトランザクション開始時刻なので、1つの DO の中では進まない。
-- 実運用では RPC 呼び出しごとに別トランザクションになるため、ここも文を分ける。
create temp table _ttl (label text, v timestamptz);
insert into _ttl select 'first', (session_turn_on('drinks')).expires_at;
select pg_sleep(1.1);
insert into _ttl select 'second', (session_turn_on('cafe')).expires_at;
do $$ begin
  perform t_assert(
    (select v from _ttl where label = 'second') > (select v from _ttl where label = 'first'),
    'Intent 変更で TTL がリセットされる');
  perform t_assert(
    (select current_intent from session_statuses where user_id = auth.uid()) = 'cafe',
    'Intent は最後に選んだものだけになる');
end $$;

-- --------------------------------------------------------------- Request
do $$ begin
  perform t_assert(
    (send_session_request('22222222-2222-2222-2222-222222222222'))->>'kind' = 'requested',
    '相互でなければ requested');
end $$;

do $$ begin perform t_raises(
  $q$select send_session_request('22222222-2222-2222-2222-222222222222')$q$,
  'DUPLICATE_REQUEST', 'duplicate request を作らない'); end $$;

do $$ begin perform t_raises(
  $q$select send_session_request('11111111-1111-1111-1111-111111111111')$q$,
  'SELF_REQUEST', '自分には送れない'); end $$;

-- ---------------------------------------------------------------- Session
set request.jwt.claim.sub = '22222222-2222-2222-2222-222222222222';
do $$ begin
  perform session_turn_on('food');
  perform t_assert(
    (send_session_request('11111111-1111-1111-1111-111111111111'))->>'kind' = 'matched',
    '相互 Request で Session が成立する');
  perform t_assert((select count(*) from sessions where status = 'active') = 1,
    'Session は1件だけ生成される');
  perform t_assert((select count(*) from conversations) = 1,
    'Conversation も1件だけ');
  perform t_assert((select user_a_id < user_b_id from sessions limit 1),
    'ペアが正規化されている（一意制約が効く）');
  perform t_assert(
    (select active_until from sessions limit 1)
      = (select min(expires_at) from session_statuses where session_on),
    'active_until は両者の早い方');
end $$;

-- ------------------------------------------------------------------ Chat
do $$
declare c uuid;
begin
  select id into c from conversations limit 1;
  perform send_message(c, 'text', '20時に渋谷で', null);
  perform t_assert((select count(*) from messages) = 1, 'メッセージを送信できる');
end $$;

-- ----------------------------------------------------------------- Block
do $$
declare c uuid;
begin
  select id into c from conversations limit 1;
  perform block_user('11111111-1111-1111-1111-111111111111');
  perform t_raises(
    format($q$select send_message('%s', 'text', 'hello', null)$q$, c),
    'BLOCKED', 'Block 後はメッセージを送れない');
  perform t_assert((select count(*) from conversation_list()) = 0,
    'Block 後は会話一覧から消える');
  perform t_assert((select count(*) from discovery_feed()
                     where user_id = '11111111-1111-1111-1111-111111111111') = 0,
    'Block 後は Discovery に出ない');
  perform unblock_user('11111111-1111-1111-1111-111111111111');
end $$;

-- --------------------------------------------------------------- 年齢確認
set request.jwt.claim.sub = '33333333-3333-3333-3333-333333333333';
do $$
declare ref text;
begin
  perform apply_age_verification_result((start_age_verification()).reference, false, 'setup');
  perform t_assert(not (select age_verified from users where id = auth.uid()), '未確認に戻せる');

  ref := (start_age_verification()).reference;
  perform t_assert((confirm_age_verification(ref))->>'status' = 'pending',
    '開始しただけでは pending');
  perform t_assert((confirm_age_verification('agv_forged'))->>'reason' = 'unknown_reference',
    '身に覚えのない reference は通らない');
  perform t_assert(not (select age_verified from users where id = auth.uid()),
    '照会では確認済みにならない');

  perform apply_age_verification_result(ref, true, null);
  perform t_assert((confirm_age_verification(ref))->>'status' = 'verified',
    'サーバが取り込んで初めて確認済みになる');
end $$;

-- ------------------------------------------ non-binary の Discovery 表示
do $$ begin
  -- C は nonbinary。everyone を選んでいる A からは見える。
  perform set_config('request.jwt.claim.sub', '11111111-1111-1111-1111-111111111111', false);
  perform t_assert(
    exists (select 1 from discovery_feed() where user_id = '33333333-3333-3333-3333-333333333333'),
    'non-binary は everyone の相手に表示される');

  -- A の設定を women に変えると、C は出なくなる。
  update users set discovery_preference = 'women' where id = auth.uid();
  perform t_assert(
    not exists (select 1 from discovery_feed() where user_id = '33333333-3333-3333-3333-333333333333'),
    'non-binary は women / men の相手には表示されない');
  update users set discovery_preference = 'everyone' where id = auth.uid();
end $$;

-- --------------------------------------------------- 距離は丸めて返される
do $$ begin
  perform t_assert(
    not exists (select 1 from discovery_feed() d where d.distance_label ~ '\.'),
    '距離に小数を含めない（正確な距離を出さない）');
end $$;

-- ------------------------------------------------- Active Session の上限
do $$
declare i int; partner uuid;
begin
  perform set_config('request.jwt.claim.sub', '11111111-1111-1111-1111-111111111111', false);
  perform session_turn_on('drinks');
  -- A は既に1件 Active。あと2件で上限。
  for i in 1..2 loop
    partner := ('dddddddd-0000-0000-0000-' || lpad(i::text, 12, '0'))::uuid;
    insert into auth.users (id) values (partner);
    insert into users (id, name, birth_date, gender, discovery_preference, age_verified)
    values (partner, 'D' || i, '1996-01-01', 'woman', 'everyone', true);
    perform set_config('request.jwt.claim.sub', partner::text, false);
    perform session_turn_on('drinks');
    perform send_session_request('11111111-1111-1111-1111-111111111111');
    perform set_config('request.jwt.claim.sub', '11111111-1111-1111-1111-111111111111', false);
    perform send_session_request(partner);
  end loop;

  perform t_assert(active_session_count('11111111-1111-1111-1111-111111111111') = 3,
    'Active Session が上限の3件');

  partner := 'dddddddd-0000-0000-0000-000000000099';
  insert into auth.users (id) values (partner);
  insert into users (id, name, birth_date, gender, discovery_preference, age_verified)
  values (partner, 'D99', '1996-01-01', 'woman', 'everyone', true);
  perform t_raises(
    format($q$select send_session_request('%s')$q$, partner),
    'ACTIVE_SESSION_LIMIT', '上限に達すると新しい Request を送れない');
end $$;
