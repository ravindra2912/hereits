import React, { useEffect, useState } from 'react';
import { StatusBar } from 'react-native';
import Toast from 'react-native-toast-message';
import { SafeAreaProvider, SafeAreaView } from 'react-native-safe-area-context';
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
import ProfileEditScreen from './src/screens/ProfileEditScreen';
import FavoritesScreen from './src/screens/FavoritesScreen';
import SpecialistDetailScreen from './src/screens/SpecialistDetailScreen';
import LocationModal from './src/screens/LocationModal';
import AuthModal from './src/screens/AuthModal';
import BottomNavBar from './src/components/BottomNavBar';
import SearchScreen from './src/screens/SearchScreen';
import BusinessCategoryListScreen from './src/screens/BusinessCategoryListScreen';
import BusinessProductListScreen from './src/screens/BusinessProductListScreen';
import BusinessServiceListScreen from './src/screens/BusinessServiceListScreen';
import BusinessSpecialistListScreen from './src/screens/BusinessSpecialistListScreen';
import OrdersListScreen from './src/screens/OrdersListScreen';
import OrderDetailScreen from './src/screens/OrderDetailScreen';
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
        <Stack.Screen name="BusinessCategoryList" component={BusinessCategoryListScreen} />
        <Stack.Screen name="BusinessProductList" component={BusinessProductListScreen} />
        <Stack.Screen name="BusinessServiceList" component={BusinessServiceListScreen} />
        <Stack.Screen name="BusinessSpecialistList" component={BusinessSpecialistListScreen} />
        <Stack.Screen name="ProfileEdit" component={ProfileEditScreen} />
        <Stack.Screen name="Favorites" component={FavoritesScreen} />
        <Stack.Screen name="SpecialistDetail" component={SpecialistDetailScreen} />
        <Stack.Screen name="Appointments" component={AppointmentScreen} />
        <Stack.Screen name="OrdersList" component={OrdersListScreen} />
        <Stack.Screen name="OrderDetail" component={OrderDetailScreen} />
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
          <StatusBar
            barStyle={isDarkMode ? 'light-content' : 'dark-content'}
            backgroundColor="#F8FAFC"
            translucent={false}
          />
          {showSplash ? (
            <SplashScreen onFinish={() => setShowSplash(false)} />
          ) : (
            <SafeAreaView style={{ flex: 1, backgroundColor: '#F8FAFC' }} edges={['top']}>
              <MainNavigator />
            </SafeAreaView>
          )}
        </LocationProvider>
      </AuthProvider>
      {/* Toast must be last child so it renders above all screens & modals */}
      <Toast />
    </SafeAreaProvider>
  );
}

export default App;
