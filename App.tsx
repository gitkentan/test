import React from 'react';
import { StatusBar } from 'expo-status-bar';
import { StyleSheet, View } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { RootNavigator } from './src/navigation/RootNavigator';
import { AppProvider } from './src/state/AppContext';
import { colors } from './src/theme';

/**
 * Session β
 *
 *   Session ON → Intent → Swipe → Session → Chat
 *
 * このループを最短・最軽量で回すことだけを目的にする（仕様書 §33）。
 */
export default function App() {
  return (
    <SafeAreaProvider>
      <View style={styles.root}>
        <StatusBar style="light" />
        <AppProvider>
          <RootNavigator />
        </AppProvider>
      </View>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: colors.bg },
});
