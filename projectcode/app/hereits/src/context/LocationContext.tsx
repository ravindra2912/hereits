import React, { createContext, useContext, useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform, PermissionsAndroid, Alert, Linking } from 'react-native';
import Geolocation from '@react-native-community/geolocation';
import { LocationPayload } from '../services/locationService';
import { GOOGLE_MAP_KEY } from '@env';

const LOCATION_STORAGE_KEY = 'user_location';
const LOCATION_HISTORY_KEY = 'location_history';

interface LocationContextType {
  location: LocationPayload | null;
  recentLocations: LocationPayload[];
  isLocationSet: boolean;
  isDetectingGPS: boolean;
  isLoadingStorage: boolean;
  locationModalVisible: boolean;
  setLocationModalVisible: (visible: boolean) => void;
  setLocationData: (loc: LocationPayload) => Promise<void>;
  detectCurrentLocation: () => Promise<LocationPayload | null>;
}

const LocationContext = createContext<LocationContextType>({
  location: null,
  recentLocations: [],
  isLocationSet: false,
  isDetectingGPS: false,
  isLoadingStorage: true,
  locationModalVisible: false,
  setLocationModalVisible: () => { },
  setLocationData: async () => { },
  detectCurrentLocation: async () => null,
});

export const LocationProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [location, setLocationState] = useState<LocationPayload | null>(null);
  const [recentLocations, setRecentLocations] = useState<LocationPayload[]>([]);
  const [isDetectingGPS, setIsDetectingGPS] = useState(false);
  const [isLoadingStorage, setIsLoadingStorage] = useState(true);
  const [locationModalVisible, setLocationModalVisible] = useState(false);

  // Load stored location and history when app boots
  useEffect(() => {
    const loadPersistedLocationData = async () => {
      try {
        const storedLocJson = await AsyncStorage.getItem(LOCATION_STORAGE_KEY);
        const storedHistoryJson = await AsyncStorage.getItem(LOCATION_HISTORY_KEY);

        if (storedLocJson) {
          const parsedLoc: LocationPayload = JSON.parse(storedLocJson);
          setLocationState(parsedLoc);
        }

        if (storedHistoryJson) {
          const parsedHistory: LocationPayload[] = JSON.parse(storedHistoryJson);
          setRecentLocations(parsedHistory);
        }
      } catch (err) {
        console.error('Error loading location from AsyncStorage:', err);
      } finally {
        setIsLoadingStorage(false);
      }
    };

    loadPersistedLocationData();
  }, []);

  // Auto trigger LocationModal on first launch if location not set after storage load
  useEffect(() => {
    if (!isLoadingStorage && !location) {
      setLocationModalVisible(true);
    }
  }, [isLoadingStorage, location]);

  const setLocationData = async (loc: LocationPayload) => {
    setLocationState(loc);

    try {
      // Persist active location to app storage
      await AsyncStorage.setItem(LOCATION_STORAGE_KEY, JSON.stringify(loc));

      // Manage last 5 location history items (remove duplicate location_name)
      const existingHistoryJson = await AsyncStorage.getItem(LOCATION_HISTORY_KEY);
      let historyList: LocationPayload[] = existingHistoryJson ? JSON.parse(existingHistoryJson) : [];

      historyList = historyList.filter(item => item.location_name !== loc.location_name);
      historyList.unshift(loc);
      historyList = historyList.slice(0, 5); // Keep last 5 locations

      setRecentLocations(historyList);
      await AsyncStorage.setItem(LOCATION_HISTORY_KEY, JSON.stringify(historyList));
    } catch (err) {
      console.error('Error saving location to AsyncStorage:', err);
    }
  };

  const detectCurrentLocation = async (): Promise<LocationPayload | null> => {
    setIsDetectingGPS(true);

    try {
      if (Platform.OS === 'android') {
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
          {
            title: 'Location Permission',
            message: 'Hereits needs access to your location to find nearby services.',
            buttonNeutral: 'Ask Me Later',
            buttonNegative: 'Cancel',
            buttonPositive: 'OK',
          }
        );
        if (granted !== PermissionsAndroid.RESULTS.GRANTED) {
          console.warn('Location permission denied');
          setIsDetectingGPS(false);
          if (granted === PermissionsAndroid.RESULTS.NEVER_ASK_AGAIN) {
            Alert.alert(
              'Location Permission Required',
              'Please grant location permissions in your app settings to detect your current location.',
              [
                { text: 'Cancel', style: 'cancel' },
                { text: 'Open Settings', onPress: () => Linking.openSettings() }
              ]
            );
          }
          return null;
        }
      }

      const position = await new Promise<any>((resolve, reject) => {
        Geolocation.getCurrentPosition(
          (pos) => resolve(pos),
          (err) => {
            console.log('GPS high accuracy failed or timed out. Falling back to network location...', err);
            Geolocation.getCurrentPosition(
              (pos2) => resolve(pos2),
              (err2) => reject(err2),
              { enableHighAccuracy: false, timeout: 15000, maximumAge: 10000 }
            );
          },
          { enableHighAccuracy: true, timeout: 5000, maximumAge: 10000 }
        );
      });

      const { latitude, longitude } = position.coords;
      console.log('[GPS Location Detected]', latitude, longitude);

      let locationName = 'Current Location';
      let fullAddress = `Detected Location (${latitude.toFixed(4)}, ${longitude.toFixed(4)})`;

      try {
        const geoResponse = await fetch(
          `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${GOOGLE_MAP_KEY}`
        );
        const geoJson = await geoResponse.json();
        if (geoJson.status === 'OK' && geoJson.results && geoJson.results[0]) {
          const result = geoJson.results[0];
          let area = '';
          let city = '';

          if (result.address_components) {
            for (const component of result.address_components) {
              if (component.types.includes('sublocality_level_1')) {
                area = component.long_name;
              }
              if (component.types.includes('locality')) {
                city = component.long_name;
              }
              if (!area && component.types.includes('sublocality')) {
                area = component.long_name;
              }
            }
          }

          locationName = area && city ? `${area}, ${city}` : (city || area || "Current Location");
          fullAddress = result.formatted_address || fullAddress;
        }
      } catch (geoErr) {
        console.warn('Reverse geocoding failed:', geoErr);
      }

      const currentLoc: LocationPayload = {
        type: 'current_location',
        location_name: locationName,
        full_address: fullAddress,
        latitude: latitude,
        longitude: longitude,
        radius: 50,
      };

      await setLocationData(currentLoc);
      setIsDetectingGPS(false);
      return currentLoc;
    } catch (error: any) {
      console.error('GPS error:', error);
      setIsDetectingGPS(false);

      const isPermissionError = error && (error.code === 1 || error.message?.toLowerCase().includes('permission') || error.message?.toLowerCase().includes('denied'));

      Alert.alert(
        isPermissionError ? 'Location Permission Required' : 'Location Services Disabled',
        isPermissionError
          ? 'Please enable location permissions in settings to use current location.'
          : 'Please make sure your device\'s GPS / Location services are enabled to detect your current location.',
        [
          { text: 'Cancel', style: 'cancel' },
          {
            text: isPermissionError ? 'Open Settings' : 'Enable GPS',
            onPress: () => {
              if (Platform.OS === 'android') {
                if (isPermissionError) {
                  Linking.openSettings();
                } else {
                  Linking.sendIntent('android.settings.LOCATION_SOURCE_SETTINGS');
                }
              } else {
                Linking.openSettings();
              }
            },
          },
        ]
      );

      return null;
    }
  };

  return (
    <LocationContext.Provider
      value={{
        location,
        recentLocations,
        isLocationSet: !!location,
        isDetectingGPS,
        isLoadingStorage,
        locationModalVisible,
        setLocationModalVisible,
        setLocationData,
        detectCurrentLocation,
      }}
    >
      {children}
    </LocationContext.Provider>
  );
};

export const useLocation = () => useContext(LocationContext);
