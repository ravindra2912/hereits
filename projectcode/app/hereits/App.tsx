import React, { useEffect, useState } from 'react';
import { StatusBar } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';

import { AuthProvider, useAuth } from './src/context/AuthContext';
import { LocationProvider, useLocation } from './src/context/LocationContext';
import SplashScreen from './src/screens/SplashScreen';
import HomeScreen from './src/screens/HomeScreen';
import BusinessListScreen from './src/screens/BusinessListScreen';
import BusinessDetailScreen from './src/screens/BusinessDetailScreen';
import ProductDetailScreen from './src/screens/ProductDetailScreen';
import ServiceDetailScreen from './src/screens/ServiceDetailScreen';
import AppointmentScreen from './src/screens/AppointmentScreen';
import ChatScreen from './src/screens/ChatScreen';
import ChatDetailScreen from './src/screens/ChatDetailScreen';
import ProfileScreen from './src/screens/ProfileScreen';
import LocationModal from './src/screens/LocationModal';
import AuthModal from './src/screens/AuthModal';
import BottomNavBar from './src/components/BottomNavBar';
import SearchScreen from './src/screens/SearchScreen';
import { RootStackParamList, MainTabParamList } from './src/navigation/types';

const Stack = createNativeStackNavigator<RootStackParamList>();
const Tab = createBottomTabNavigator<MainTabParamList>();

function MainTabs() {
  return (
    <Tab.Navigator
      tabBar={props => <BottomNavBar {...props} />}
      screenOptions={{ headerShown: false }}
    >
      <Tab.Screen name="HomeTab" component={HomeScreen} />
      <Tab.Screen name="BusinessesTab" component={BusinessListScreen} />
      <Tab.Screen name="SearchTab" component={SearchScreen} />
      <Tab.Screen name="MessagesTab" component={ChatScreen} />
      <Tab.Screen name="AccountTab" component={ProfileScreen} />
    </Tab.Navigator>
  );
}

function MainNavigator() {
  const { isLocationSet, locationModalVisible, setLocationModalVisible } = useLocation();
  const { authModalVisible, setAuthModalVisible } = useAuth();

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        <Stack.Screen name="Main" component={MainTabs} />
        <Stack.Screen name="BusinessDetail" component={BusinessDetailScreen} />
        <Stack.Screen name="ProductDetail" component={ProductDetailScreen} />
        <Stack.Screen name="ServiceDetail" component={ServiceDetailScreen} />
        <Stack.Screen name="ChatDetail" component={ChatDetailScreen} />
      </Stack.Navigator>

      <LocationModal
        visible={locationModalVisible}
        allowClose={isLocationSet}
        onClose={() => setLocationModalVisible(false)}
      />

      <AuthModal
        visible={authModalVisible}
        onClose={() => setAuthModalVisible(false)}
      />
    </NavigationContainer>
  );
}

function App() {
  const isDarkMode = false;
  const [showSplash, setShowSplash] = useState(true);

  return (
    <SafeAreaProvider>
      <AuthProvider>
        <LocationProvider>
          <StatusBar barStyle={isDarkMode ? 'light-content' : 'dark-content'} />
          {showSplash ? (
            <SplashScreen onFinish={() => setShowSplash(false)} />
          ) : (
            <MainNavigator />
          )}
        </LocationProvider>
      </AuthProvider>
    </SafeAreaProvider>
  );
}

export default App;
