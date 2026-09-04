-- Session β — サーバ側の検証と操作（仕様書 §25）
--
-- client は table を直接 update しない。状態を変えるのはこの RPC だけ。
-- RLS と合わせて、client からは「読める範囲を読む」ことしかできなくする。

-- 定数（§27）。remote config へ移す場合もここを起点にする。
create or replace function app_session_ttl() returns interval
  language sql immutable as $$ select interval '6 hours' $$;

create or replace function app_max_active_sessions() returns int
  language sql immutable as $$ select 3 $$;

create or replace function app_skip_cooldown() returns interval
  language sql immutable as $$ select interval '24 hours' $$;

-- ------------------------------------------------------------- helpers

-- 期限に達した Session ON を OFF へ落とす（§6）。読み出しの前に必ず通す。
create or replace function settle_expiry() returns void
  language sql security definer set search_path = public as $$
  update session_statuses
     set session_on = false, current_intent = null, started_at = null,
         expires_at = null, updated_at = now()
   where session_on and expires_at <= now();

  update swipes
     set status = 'expired'
   where type = 'session_request' and status = 'pending'
     and expires_at is not null and expires_at <= now();

  update sessions
     set status = 'past'
   where status = 'active' and active_until <= now();
$$;

create or replace function is_blocked_between(a uuid, b uuid) returns boolean
  language sql stable security definer set search_path = public as $$
  select exists (
    select 1 from blocks
     where (blocker_id = a and blocked_id = b)
        or (blocker_id = b and blocked_id = a)
  )
$$;

create or replace function active_session_count(target uuid) returns int
  language sql stable security definer set search_path = public as $$
  select count(*)::int from sessions
   where status = 'active' and active_until > now()
     and (user_a_id = target or user_b_id = target)
$$;

-- 表示対象ルール（§18）。non-binary は everyone を選んでいる相手にだけ出る。
-- domain/discoveryPreference.ts と同じ規則を DB 側でも持つ。
create or replace function preference_includes(pref discovery_preference, g gender)
  returns boolean language sql immutable as $$
  select case pref
    when 'everyone' then true
    when 'women' then g = 'woman'
    when 'men' then g = 'man'
  end
$$;

-- 距離バケット（§10）。正確な距離は決して返さない。
create or replace function distance_label(km double precision) returns text
  language sql immutable as $$
  select case
    when km is null then '距離は非公開'
    when km <= 1 then '1km以内'
    when km <= 3 then '3km以内'
    when km <= 5 then '5km以内'
    when km <= 10 then '10km以内'
    else '10km以上'
  end
$$;

create or replace function distance_km(
  lat1 double precision, lon1 double precision,
  lat2 double precision, lon2 double precision
) returns double precision language sql immutable as $$
  select case
    when lat1 is null or lon1 is null or lat2 is null or lon2 is null then null
    else 2 * 6371 * asin(least(1, sqrt(
      sin(radians(lat2 - lat1) / 2) ^ 2
      + cos(radians(lat1)) * cos(radians(lat2)) * sin(radians(lon2 - lon1) / 2) ^ 2
    )))
  end
$$;

-- --------------------------------------------------------- session on/off

-- Session ON / Intent 変更（§5, §28）。Intent 変更でも TTL を引き直す。
create or replace function session_turn_on(p_intent intent)
  returns session_statuses
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
  verified boolean;
  result session_statuses;
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;

  select age_verified into verified from users where id = me;
  if verified is null then raise exception 'USER_UNAVAILABLE'; end if;
  -- 年齢未確認では Session ON できない（§19 gate）。
  if not verified then raise exception 'AGE_NOT_VERIFIED'; end if;

  insert into session_statuses (user_id, session_on, current_intent, started_at, expires_at, updated_at)
  values (me, true, p_intent, now(), now() + app_session_ttl(), now())
  on conflict (user_id) do update
    set session_on = true, current_intent = p_intent, started_at = now(),
        expires_at = now() + app_session_ttl(), updated_at = now()
  returning * into result;

  return result;
end;
$$;

create or replace function session_turn_off()
  returns session_statuses
  language plpgsql security definer set search_path = public as $$
declare
  me uuid := auth.uid();
  result session_statuses;
begin
  if me is null then raise exception 'NOT_AUTHENTICATED'; end if;

  insert into session_statuses (user_id, session_on, current_intent, started_at, expires_at, updated_at)
  values (me, false, null, null, null, now())
  on conflict (user_id) do update
    set session_on = false, current_intent = null, started_at = null,
        expires_at = null, updated_at = now()
  returning * into result;

  return result;
end;
$$;

-- ------------------------------------------------------------- discovery

-- Discovery（§8, §9, §10）。
-- 緯度経度は返さず、丸めたラベルと ranking 用の priority だけを返す。
create or replace function discovery_feed()
  returns table (
    user_id uuid, name text, age int, photos jsonb, bio text, interests text[],
    session_on boolean, intent intent, distance_label text, last_active_at timestamptz,
    priority int, radius_km double precision, pool_is_low boolean
  )
  language plpgsql security definer set search_path = public as $$
declare
  me users;
  my_intent intent;
  step double precision;
  chosen double precision := null;
  within_count int;
begin
  if auth.uid() is null then raise exception 'NOT_AUTHENTICATED'; end if;
  perform settle_expiry();

  select * into me from users where id = auth.uid();
  if me is null then raise exception 'PROFILE_INCOMPLETE'; end if;

  select s.current_intent into my_intent from session_statuses s where s.user_id = me.id;

  create temp table if not exists _pool (
    user_id uuid, name text, age int, photos jsonb, bio text, interests text[],
    session_on boolean, intent intent, km double precision, last_active_at timestamptz
  ) on commit drop;
  delete from _pool;

  insert into _pool
  select u.id, u.name,
         extract(year from age(u.birth_date))::int,
         u.photos, u.bio, u.interests,
         coalesce(s.session_on, false), s.current_intent,
         distance_km(me.latitude, me.longitude, u.latitude, u.longitude),
         u.last_active_at
    from users u
    left join session_statuses s on s.user_id = u.id
   where u.id <> me.id
     -- Block は双方向に遮断（§20）
     and not is_blocked_between(me.id, u.id)
     -- 表示対象ルールは相互成立が条件（§18）
     and preference_includes(me.discovery_preference, u.gender)
     and preference_includes(u.discovery_preference, me.gender)
     -- Skip のクールダウン中と、送信済みの有効な Request は出さない（§11）
     and not exists (
       select 1 from swipes w
        where w.sender_id = me.id and w.receiver_id = u.id
          and (
            (w.type = 'skip' and w.created_at > now() - app_skip_cooldown())
            or (w.type = 'session_request' and w.status in ('pending', 'matched'))
          )
     )
     -- すでに Session 中の相手は板に戻さない
     and not exists (
       select 1 from sessions x
        where x.status = 'active' and x.active_until > now()
          and ((x.user_a_id = me.id and x.user_b_id = u.id)
            or (x.user_a_id = u.id and x.user_b_id = me.id))
     );

  -- 候補が足りなければ半径を広げる（§9）。距離不明は半径内として扱う。
  foreach step in array array[5, 10, 30] loop
    select count(*) into within_count from _pool p where p.km is null or p.km <= step;
    chosen := step;
    exit when within_count >= 20;
  end loop;

  return query
  select p.user_id, p.name, p.age, p.photos, p.bio, p.interests,
         p.session_on, p.intent, distance_label(p.km), p.last_active_at,
         case
           when p.km is not null and p.km > chosen then 4
           when not p.session_on then 3
           when my_intent is not null and p.intent = my_intent then 1
           else 2
         end::int,
         chosen,
         (within_count < 20)
    from _pool p
   order by 11,
            coalesce(p.km, 100)
              + least(extract(epoch from (now() - p.last_active_at)) / 3600, 72) / 24
              + random() * 0.35;
end;
$$;
