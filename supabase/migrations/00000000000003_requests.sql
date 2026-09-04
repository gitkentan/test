-- Session Request と成立（仕様書 §11, §12, §25）
--
-- 「Mutual Request 作成は transaction / idempotent にする。
--   同時操作でも同じ Session が2件生成されないこと。」
--
-- 排他制御の方針:
--   ペアを (least, greatest) で正規化し、その組に対して advisory lock を取る。
--   ロック取得後にしか判定と挿入を行わないので、両者が同時に右 Swipe しても
--   片方が待たされ、二重成立が起きない。
--   さらに sessions の部分一意インデックス (user_a_id, user_b_id) where status='active'
--   が最後の砦として働く。

create or replace function pair_lock(a uuid, b uuid) returns void
  language sql as $$
  -- uuid をそのままロックキーにできないので、正規化したペアのハッシュを使う。
  select pg_advisory_xact_lock(
    hashtextextended(least(a::text, b::text) || ':' || greatest(a::text, b::text), 0)
  );
$$;

-- 左 Swipe = Skip（§11）。24時間は同じ相手を再表示しない。
create or replace function swipe_skip(p_receiver uuid)
  returns swipes
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
  result swipes;
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;
  if me = p_receiver then raise exception 'SELF_REQUEST'; end if;

  insert into swipes (sender_id, receiver_id, type, sender_intent, expires_at, status)
  values (me, p_receiver, 'skip', null, null, 'cancelled')
  returning * into result;

  return result;
end;
$$;

-- 右 Swipe = Session Request（§11, §12）。
-- 検証 → Request 作成 → 相互成立の判定 → Session / Conversation 作成までを1トランザクションで行う。
create or replace function send_session_request(p_receiver uuid)
  returns jsonb
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
  my_status session_statuses;
  their_status session_statuses;
  receiver users;
  new_request swipes;
  reciprocal swipes;
  existing sessions;
  new_session sessions;
  new_conversation conversations;
  ordered_a uuid;
  ordered_b uuid;
  a_intent intent;
  b_intent intent;
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;
  if me = p_receiver then raise exception 'SELF_REQUEST'; end if;

  perform settle_expiry();
  -- 以降の判定と挿入はこのロックの内側でのみ行う。
  perform pair_lock(me, p_receiver);

  select * into receiver from users where id = p_receiver;
  if receiver is null then raise exception 'USER_UNAVAILABLE'; end if;

  if is_blocked_between(me, p_receiver) then raise exception 'BLOCKED'; end if;

  if not (select age_verified from users where id = me) then
    raise exception 'AGE_NOT_VERIFIED';
  end if;

  select * into my_status from session_statuses where user_id = me;
  if my_status is null or not my_status.session_on then
    raise exception 'SESSION_OFF';
  end if;

  if active_session_count(me) >= app_max_active_sessions() then
    raise exception 'ACTIVE_SESSION_LIMIT';
  end if;

  -- duplicate request は部分一意インデックスでも弾かれるが、
  -- 呼び出し側へ意味のあるエラーを返すためここでも見る。
  if exists (
    select 1 from swipes
     where sender_id = me and receiver_id = p_receiver
       and type = 'session_request' and status = 'pending'
  ) then
    raise exception 'DUPLICATE_REQUEST';
  end if;

  insert into swipes (sender_id, receiver_id, type, sender_intent, expires_at, status)
  values (me, p_receiver, 'session_request', my_status.current_intent, my_status.expires_at, 'pending')
  returning * into new_request;

  -- 相手からの有効な Request があるか（期限切れは対象にしない §29）
  select * into reciprocal
    from swipes
   where sender_id = p_receiver and receiver_id = me
     and type = 'session_request' and status = 'pending'
     and (expires_at is null or expires_at > now())
   limit 1;

  if reciprocal is null then
    return jsonb_build_object('kind', 'requested', 'request', to_jsonb(new_request));
  end if;

  -- 重複 Session がないことを確認（§12 手順1）
  select * into existing
    from sessions
   where status = 'active' and active_until > now()
     and ((user_a_id = me and user_b_id = p_receiver)
       or (user_a_id = p_receiver and user_b_id = me));

  if existing is not null then
    return jsonb_build_object('kind', 'requested', 'request', to_jsonb(new_request));
  end if;

  -- 相手側の枠も確認する（§7）
  if active_session_count(p_receiver) >= app_max_active_sessions() then
    raise exception 'ACTIVE_SESSION_LIMIT';
  end if;

  select * into their_status from session_statuses where user_id = p_receiver;

  -- ペアを正規化して挿入する（一意制約を効かせるため）
  ordered_a := least(me::text, p_receiver::text)::uuid;
  ordered_b := greatest(me::text, p_receiver::text)::uuid;
  if ordered_a = me then
    a_intent := new_request.sender_intent;
    b_intent := reciprocal.sender_intent;
  else
    a_intent := reciprocal.sender_intent;
    b_intent := new_request.sender_intent;
  end if;

  insert into sessions (user_a_id, user_b_id, user_a_intent, user_b_intent, started_at, active_until, status)
  values (
    ordered_a, ordered_b, a_intent, b_intent, now(),
    -- active_until は両者の Session expiry の早い方（§12）
    least(
      coalesce(my_status.expires_at, now() + app_session_ttl()),
      coalesce(their_status.expires_at, now() + app_session_ttl())
    ),
    'active'
  )
  returning * into new_session;

  update swipes set status = 'matched'
   where id in (new_request.id, reciprocal.id);

  insert into conversations (session_id, user_a_id, user_b_id)
  values (new_session.id, ordered_a, ordered_b)
  returning * into new_conversation;

  return jsonb_build_object(
    'kind', 'matched',
    'request', to_jsonb(new_request),
    'session', to_jsonb(new_session),
    'conversation_id', new_conversation.id
  );
end;
$$;

-- ---------------------------------------------------------------- chat

-- メッセージ送信（§16, §19 gate, §20）。
create or replace function send_message(
  p_conversation uuid, p_type message_type, p_text text, p_image_url text
) returns messages
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
  convo conversations;
  other uuid;
  result messages;
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;

  select * into convo from conversations where id = p_conversation;
  if convo is null then raise exception 'CONVERSATION_UNAVAILABLE'; end if;
  if convo.user_a_id <> me and convo.user_b_id <> me then
    raise exception 'CONVERSATION_UNAVAILABLE';
  end if;

  other := case when convo.user_a_id = me then convo.user_b_id else convo.user_a_id end;
  if is_blocked_between(me, other) then raise exception 'BLOCKED'; end if;

  -- 年齢未確認ユーザーは free-form message を送信できない（§19）
  if not (select age_verified from users where id = me) then
    raise exception 'AGE_NOT_VERIFIED';
  end if;

  insert into messages (conversation_id, sender_id, type, text, image_url)
  values (p_conversation, me, p_type, p_text, p_image_url)
  returning * into result;

  update conversations set last_message_at = now() where id = p_conversation;

  return result;
end;
$$;

-- --------------------------------------------------------------- safety

create or replace function block_user(p_blocked uuid)
  returns void
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;
  if me = p_blocked then return; end if;

  insert into blocks (blocker_id, blocked_id) values (me, p_blocked)
  on conflict do nothing;

  -- 進行中の Request は成立させない（§20, §29）
  update swipes set status = 'blocked'
   where type = 'session_request' and status = 'pending'
     and ((sender_id = me and receiver_id = p_blocked)
       or (sender_id = p_blocked and receiver_id = me));
end;
$$;

-- ------------------------------------------------------- age verification

-- 確認試行の採番（§19）。client はここまでしかできない。
create or replace function start_age_verification()
  returns age_verifications
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
  result age_verifications;
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;

  insert into age_verifications (reference, user_id, status)
  values ('agv_' || replace(uuid_generate_v4()::text, '-', ''), me, 'pending')
  returning * into result;

  return result;
end;
$$;

-- 結果の照会のみ。ここで age_verified を立てることはできない。
create or replace function confirm_age_verification(p_reference text)
  returns jsonb
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
  attempt age_verifications;
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;

  select * into attempt from age_verifications
   where reference = p_reference and user_id = me;

  if attempt is null then
    return jsonb_build_object('status', 'rejected', 'reason', 'unknown_reference');
  end if;

  if attempt.status = 'verified' then
    return jsonb_build_object('status', 'verified', 'user', to_jsonb((select u from users u where u.id = me)));
  elsif attempt.status = 'rejected' then
    return jsonb_build_object('status', 'rejected', 'reason', attempt.reason);
  else
    return jsonb_build_object('status', 'pending');
  end if;
end;
$$;

-- プロバイダの結果を取り込む（サーバ側の入口）。
-- service role だけが実行できるよう、下の権限設定で anon/authenticated から revoke する。
create or replace function apply_age_verification_result(
  p_reference text, p_verified boolean, p_reason text
) returns users
  language plpgsql security definer set search_path = public as $$
declare
  attempt age_verifications;
  result users;
begin
  select * into attempt from age_verifications where reference = p_reference;
  if attempt is null then raise exception 'unknown_reference'; end if;

  update age_verifications
     set status = case when p_verified then 'verified'::verification_state else 'rejected'::verification_state end,
         reason = p_reason,
         resolved_at = now()
   where reference = p_reference;

  update users
     set age_verified = p_verified,
         age_verified_at = case when p_verified then now() else null end,
         age_verification_reference = p_reference,
         updated_at = now()
   where id = attempt.user_id
  returning * into result;

  return result;
end;
$$;
