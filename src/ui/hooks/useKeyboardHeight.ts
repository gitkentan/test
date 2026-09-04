import { useEffect, useState } from 'react';
import { Keyboard, Platform } from 'react-native';

/**
 * 表示中のキーボードの高さ（pt）。閉じているときは 0。
 *
 * iOS はキーボードが出ても画面がリサイズされないため、レイアウト側で持ち上げる必要がある。
 * Android は adjustResize でウィンドウ自体が縮むので、ここでは常に 0 を返して
 * 二重に持ち上げないようにする。
 *
 * `KeyboardAvoidingView` を使わずに高さを直接扱うのは、
 * Modal の中（Bottom Sheet）での挙動が端末やバージョンで揺れるため。
 */
export function useKeyboardHeight(): number {
  const [height, setHeight] = useState(0);

  useEffect(() => {
    if (Platform.OS !== 'ios') return;

    const onChange = Keyboard.addListener('keyboardWillChangeFrame', (event) => {
      const { endCoordinates, screenY } = event as typeof event & { screenY?: number };
      void screenY;
      setHeight(Math.max(0, endCoordinates.height));
    });
    const onHide = Keyboard.addListener('keyboardWillHide', () => setHeight(0));

    return () => {
      onChange.remove();
      onHide.remove();
    };
  }, []);

  return height;
}
