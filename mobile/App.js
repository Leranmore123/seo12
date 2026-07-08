import React, { useState, useEffect } from 'react';
import { StyleSheet, View, Text, SafeAreaView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Import Screens
import LoginScreen from './screens/LoginScreen';
import DashboardScreen from './screens/DashboardScreen';
import ProjectsScreen from './screens/ProjectsScreen';
import RunScreen from './screens/RunScreen';

export default function App() {
  const [isLoading, setIsLoading] = useState(true);
  const [userToken, setUserToken] = useState(null);
  const [serverUrl, setServerUrl] = useState('');
  const [currentScreen, setCurrentScreen] = useState('Dashboard'); // 'Dashboard', 'Projects', 'Run'

  // Load session from AsyncStorage on startup
  useEffect(() => {
    const bootstrapAsync = async () => {
      try {
        const token = await AsyncStorage.getItem('user_api_key');
        const url = await AsyncStorage.getItem('server_url');
        
        if (token) {
          setUserToken(token);
        }
        if (url) {
          setServerUrl(url);
        }
      } catch (e) {
        console.error('Failed to load session:', e);
      } finally {
        setIsLoading(false);
      }
    };

    bootstrapAsync();
  }, []);

  const handleLogin = async (token, url) => {
    try {
      await AsyncStorage.setItem('user_api_key', token);
      await AsyncStorage.setItem('server_url', url);
      setUserToken(token);
      setServerUrl(url);
      setCurrentScreen('Dashboard');
    } catch (e) {
      console.error('Failed to save session:', e);
    }
  };

  const handleLogout = async () => {
    try {
      await AsyncStorage.removeItem('user_api_key');
      setUserToken(null);
      setCurrentScreen('Dashboard');
    } catch (e) {
      console.error('Failed to clear session:', e);
    }
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#4f46e5" />
      </SafeAreaView>
    );
  }

  // Render Login Screen if not authenticated
  if (!userToken) {
    return (
      <SafeAreaView style={styles.container}>
        <LoginScreen onLogin={handleLogin} />
        <StatusBar style="auto" />
      </SafeAreaView>
    );
  }

  // Render Screens based on active screen state
  const renderScreen = () => {
    switch (currentScreen) {
      case 'Dashboard':
        return <DashboardScreen userToken={userToken} serverUrl={serverUrl} onLogout={handleLogout} />;
      case 'Projects':
        return <ProjectsScreen userToken={userToken} serverUrl={serverUrl} />;
      case 'Run':
        return <RunScreen userToken={userToken} serverUrl={serverUrl} />;
      default:
        return <DashboardScreen userToken={userToken} serverUrl={serverUrl} onLogout={handleLogout} />;
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Active Screen Content */}
      <View style={styles.content}>
        {renderScreen()}
      </View>

      {/* Custom Bottom Navigation Bar */}
      <View style={styles.tabBar}>
        <TouchableOpacity 
          style={[styles.tabItem, currentScreen === 'Dashboard' && styles.activeTabItem]} 
          onPress={() => setCurrentScreen('Dashboard')}
        >
          <Text style={[styles.tabText, currentScreen === 'Dashboard' && styles.activeTabText]}>📊 Stats</Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={[styles.tabItem, currentScreen === 'Projects' && styles.activeTabItem]} 
          onPress={() => setCurrentScreen('Projects')}
        >
          <Text style={[styles.tabText, currentScreen === 'Projects' && styles.activeTabText]}>📁 Projects</Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={[styles.tabItem, currentScreen === 'Run' && styles.activeTabItem]} 
          onPress={() => setCurrentScreen('Run')}
        >
          <Text style={[styles.tabText, currentScreen === 'Run' && styles.activeTabText]}>🚀 Run SEO</Text>
        </TouchableOpacity>
      </View>

      <StatusBar style="auto" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f8fafc',
  },
  content: {
    flex: 1,
  },
  tabBar: {
    flexDirection: 'row',
    height: 64,
    backgroundColor: '#ffffff',
    borderTopWidth: 1,
    borderTopColor: '#e2e8f0',
    alignItems: 'center',
    justifyContent: 'space-around',
    paddingBottom: 8,
    paddingTop: 8,
  },
  tabItem: {
    paddingVertical: 6,
    paddingHorizontal: 20,
    borderRadius: 20,
  },
  activeTabItem: {
    backgroundColor: '#e0e7ff',
  },
  tabText: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#64748b',
  },
  activeTabText: {
    color: '#4f46e5',
  },
});
