-- 写真ストレージ（プロフィール写真 / チャット画像）
--
-- パスの規約: <user_id>/<uuid>.<ext>
-- 先頭セグメントを所有者の user_id にすることで、
-- 「自分のフォルダにしか書けない」を RLS で表現できる。

insert into storage.buckets (id, name, public)
values ('photos', 'photos', true)
on conflict (id) do nothing;

-- 誰でも読める（プロフィール写真は他ユーザーへ表示するため）。
-- パスに UUID を含めるので URL は推測できないが、URL を知る者は誰でも見られる。
-- 公開範囲を絞る場合は public=false にし、署名付き URL へ切り替える（README の残リスク参照）。
create policy "photos_public_read" on storage.objects
  for select using (bucket_id = 'photos');

-- 書き込みは本人のフォルダのみ。
create policy "photos_insert_own" on storage.objects
  for insert to authenticated
  with check (
    bucket_id = 'photos'
    and (storage.foldername(name))[1] = auth.uid()::text
  );

create policy "photos_update_own" on storage.objects
  for update to authenticated
  using (bucket_id = 'photos' and (storage.foldername(name))[1] = auth.uid()::text);

create policy "photos_delete_own" on storage.objects
  for delete to authenticated
  using (bucket_id = 'photos' and (storage.foldername(name))[1] = auth.uid()::text);
