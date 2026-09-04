-- Row Level Security（仕様書 §10, §20, §25）
--
-- 方針: client が table を直接読み書きできる範囲を最小にする。
--   * users は自分の行しか select できない。
--     他人のプロフィールは SECURITY DEFINER の RPC 経由でのみ、
--     公開してよい列だけが返る（latitude / longitude は決して返さない）。
--   * 状態を変える操作はすべて RPC。table への insert / update / delete は与えない。

alter table users enable row level security;
alter table session_statuses enable row level security;
alter table swipes enable row level security;
alter table sessions enable row level security;
alter table conversations enable row level security;
alter table messages enable row level security;
alter table blocks enable row level security;
alter table reports enable row level security;
alter table age_verifications enable row level security;

-- ------------------------------------------------------------------ users

-- 自分の行だけ。他人の緯度経度が漏れる経路を作らない。
create policy users_select_self on users
  for select using (id = auth.uid());

-- プロフィールの作成と更新は本人のみ。
-- age_verified を含む列は下のトリガで保護する。
create policy users_insert_self on users
  for insert with check (id = auth.uid());

create policy users_update_self on users
  for update using (id = auth.uid()) with check (id = auth.uid());

-- client が age_verified を自分で立てられないようにする（§19）。
-- RPC は security definer なのでこのトリガの対象外にはならないため、
-- セッション変数で「サーバ側の更新である」ことを示したときだけ許す。
create or replace function guard_age_verified() returns trigger
  language plpgsql as $$
begin
  if new.age_verified is distinct from old.age_verified
     or new.age_verified_at is distinct from old.age_verified_at
     or new.age_verification_reference is distinct from old.age_verification_reference then
    if coalesce(current_setting('app.allow_verification_write', true), '') <> 'on' then
      raise exception 'AGE_VERIFICATION_READONLY';
    end if;
  end if;
  return new;
end;
$$;

create trigger users_guard_age_verified
  before update on users
  for each row execute function guard_age_verified();

-- 取り込み RPC のときだけフラグを立てる。
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

  perform set_config('app.allow_verification_write', 'on', true);

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

  perform set_config('app.allow_verification_write', 'off', true);
  return result;
end;
$$;

-- -------------------------------------------------------- 参加者向けの読み

create policy session_statuses_select_self on session_statuses
  for select using (user_id = auth.uid());

create policy swipes_select_own on swipes
  for select using (sender_id = auth.uid() or receiver_id = auth.uid());

create policy sessions_select_participant on sessions
  for select using (user_a_id = auth.uid() or user_b_id = auth.uid());

create policy conversations_select_participant on conversations
  for select using (user_a_id = auth.uid() or user_b_id = auth.uid());

-- Block 済みの相手とのメッセージは読ませない（§20）。
create policy messages_select_participant on messages
  for select using (
    exists (
      select 1 from conversations c
       where c.id = messages.conversation_id
         and (c.user_a_id = auth.uid() or c.user_b_id = auth.uid())
         and not is_blocked_between(c.user_a_id, c.user_b_id)
    )
  );

create policy blocks_select_own on blocks
  for select using (blocker_id = auth.uid());

create policy reports_insert_self on reports
  for insert with check (reporter_id = auth.uid());

create policy age_verifications_select_own on age_verifications
  for select using (user_id = auth.uid());

-- ------------------------------------------------- 公開してよい形での読み

-- 会話一覧（§15）。相手の名前と写真だけを返し、位置情報は含めない。
create or replace function conversation_list()
  returns table (
    conversation_id uuid, session_id uuid, partner_id uuid, partner_name text,
    partner_photo jsonb, is_active boolean, partner_intent intent,
    last_message_preview text, last_message_at timestamptz, active_until timestamptz
  )
  language plpgsql security definer set search_path = public as $$
declare me uuid := auth.uid();
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;
  perform settle_expiry();

  return query
  select c.id, s.id,
         p.id, p.name,
         case when jsonb_array_length(p.photos) > 0 then p.photos -> 0 else null end,
         (s.status = 'active' and s.active_until > now()),
         case when s.user_a_id = p.id then s.user_a_intent else s.user_b_intent end,
         (select case when m.type = 'image' then '画像を送信しました' else m.text end
            from messages m where m.conversation_id = c.id
           order by m.created_at desc limit 1),
         c.last_message_at,
         s.active_until
    from conversations c
    join sessions s on s.id = c.session_id
    join users p on p.id = case when c.user_a_id = me then c.user_b_id else c.user_a_id end
   where (c.user_a_id = me or c.user_b_id = me)
     and not is_blocked_between(me, p.id)
   order by (s.status = 'active' and s.active_until > now()) desc,
            coalesce(c.last_message_at, s.active_until) desc;
end;
$$;

-- ブロック一覧（§17）。
create or replace function blocked_list()
  returns table (user_id uuid, name text, photo jsonb, created_at timestamptz)
  language plpgsql security definer set search_path = public as $$
declare me uuid := auth.uid();
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;
  return query
  select u.id, u.name,
         case when jsonb_array_length(u.photos) > 0 then u.photos -> 0 else null end,
         b.created_at
    from blocks b join users u on u.id = b.blocked_id
   where b.blocker_id = me
   order by b.created_at desc;
end;
$$;

create or replace function unblock_user(p_blocked uuid) returns void
  language plpgsql security definer set search_path = public as $$
begin
  if auth.uid() is null then raise exception 'NOT_AUTHENTICATED'; end if;
  delete from blocks where blocker_id = auth.uid() and blocked_id = p_blocked;
end;
$$;

-- 自分宛の有効な Request 件数（P1 の Received Requests 表示の土台）。
create or replace function incoming_request_count() returns int
  language plpgsql security definer set search_path = public as $$
declare me uuid := auth.uid();
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;
  perform settle_expiry();
  return (
    select count(*)::int from swipes w
     where w.receiver_id = me and w.type = 'session_request' and w.status = 'pending'
       and (w.expires_at is null or w.expires_at > now())
       and not is_blocked_between(me, w.sender_id)
  );
end;
$$;

-- ---------------------------------------------------------------- 権限

-- 取り込み RPC は service role 専用。client からは呼べない。
revoke all on function apply_age_verification_result(text, boolean, text) from public;
