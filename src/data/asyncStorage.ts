import AsyncStorage from '@react-native-async-storage/async-storage';
import type { KeyValueStorage } from './storage';

/** 端末側の永続化。アプリ再起動後も Session 状態を正しく復元するために使う（§31）。 */
export const deviceStorage: KeyValueStorage = {
  getItem: (key) => AsyncStorage.getItem(key),
  setItem: (key, value) => AsyncStorage.setItem(key, value),
  removeItem: (key) => AsyncStorage.removeItem(key),
};
