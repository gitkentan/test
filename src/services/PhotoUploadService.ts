import { getSupabaseClient } from '../data/supabase/client';
import { photoStoragePath } from '../data/supabase/repositories';
import { PHOTO_BUCKET, isSupabaseConfigured } from '../config/env';
import { createId } from '../domain/ids';
import type { Photo, UserId } from '../domain/types';

/**
 * 画像アップロード（項目4）。
 *
 * ImagePicker が返すのは端末内のローカル URI なので、そのまま保存すると
 * 相手の端末では開けない。プロフィール写真とチャット画像は、
 * 保存の前にストレージへ上げて、配信 URL に差し替える。
 *
 * バックエンド未設定のときはローカル URI のまま返す。
 * 開発環境で1台だけ動かす分には成立し、実バックエンドを繋いだ時点で
 * 自動的にアップロード経路へ切り替わる。
 */
export class PhotoUploadService {
  /** アップロードが有効か。false ならローカル URI のまま扱う。 */
  get enabled(): boolean {
    return isSupabaseConfigured;
  }

  /**
   * ローカル URI をストレージへ上げ、配信 URL を持つ Photo を返す。
   * すでに http(s) の URL ならアップロード済みとみなしてそのまま返す。
   */
  async upload(userId: UserId, localUri: string): Promise<Photo> {
    const id = createId('pht');
    if (!this.enabled || /^https?:\/\//.test(localUri)) {
      return { id, uri: localUri };
    }

    const supabase = getSupabaseClient();
    const extension = extensionOf(localUri);
    const path = photoStoragePath(userId, `${id}.${extension}`);

    // fetch 経由で ArrayBuffer にする。React Native の Blob は
    // supabase-js の期待する形と噛み合わないことがあるため。
    const response = await fetch(localUri);
    const bytes = await response.arrayBuffer();

    const { error } = await supabase.storage.from(PHOTO_BUCKET).upload(path, bytes, {
      contentType: contentTypeOf(extension),
      upsert: false,
    });
    if (error) throw new Error(`画像のアップロードに失敗しました: ${error.message}`);

    const { data } = supabase.storage.from(PHOTO_BUCKET).getPublicUrl(path);
    return { id, uri: data.publicUrl };
  }

  /** プロフィール写真をまとめて上げる。既にアップロード済みのものは触らない。 */
  async uploadAll(userId: UserId, photos: Photo[]): Promise<Photo[]> {
    if (!this.enabled) return photos;
    return Promise.all(
      photos.map(async (photo) =>
        /^https?:\/\//.test(photo.uri) ? photo : this.upload(userId, photo.uri),
      ),
    );
  }
}

function extensionOf(uri: string): string {
  const match = /\.([a-zA-Z0-9]+)(?:\?|$)/.exec(uri);
  const ext = match?.[1]?.toLowerCase() ?? 'jpg';
  return ['jpg', 'jpeg', 'png', 'heic', 'webp'].includes(ext) ? ext : 'jpg';
}

function contentTypeOf(extension: string): string {
  switch (extension) {
    case 'png':
      return 'image/png';
    case 'webp':
      return 'image/webp';
    case 'heic':
      return 'image/heic';
    default:
      return 'image/jpeg';
  }
}
