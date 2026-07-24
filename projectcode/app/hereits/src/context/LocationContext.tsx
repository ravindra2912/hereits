import React, { createContext, useContext, useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { LocationPayload, locationService } from '../services/locationService';

const LOCATION_STORAGE_KEY = 'user_location';
const LOCATION_HISTORY_KEY = 'location_history';

interface LocationContextType {
  location: LocationPayload | null;
  recentLocations: LocationPayload[];
  isLocationSet: boolean;
  isDetectingGPS: boolean;
  isLoadingStorage: boolean;
  setLocationData: (loc: LocationPayload) => Promise<void>;
  detectCurrentLocation: () => Promise<LocationPayload | null>;
}

const LocationContext = createContext<LocationContextType>({
  location: null,
  recentLocations: [],
  isLocationSet: false,
  isDetectingGPS: false,
  isLoadingStorage: true,
  setLocationData: async () => {},
  detectCurrentLocation: async () => null,
});

export const LocationProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [location, setLocationState] = useState<LocationPayload | null>(null);
  const [recentLocations, setRecentLocations] = useState<LocationPayload[]>([]);
  const [isDetectingGPS, setIsDetectingGPS] = useState(false);
  const [isLoadingStorage, setIsLoadingStorage] = useState(true);

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

    // Notify backend API
    await locationService.setLocation(loc);
  };

  const detectCurrentLocation = async (): Promise<LocationPayload | null> => {
    setIsDetectingGPS(true);
    // GPS detection simulation (Surat, GJ: 21.1702 N, 72.8311 E)
    const currentLoc: LocationPayload = {
      type: 'current_location',
      location_name: 'Current Location',
      full_address: 'Vesu Main Road, Surat, Gujarat, India',
      latitude: 21.1702,
      longitude: 72.8311,
      radius: 100,
    };

    // Realistic GPS delay UX
    await new Promise<void>(resolve => setTimeout(() => resolve(), 800));

    await setLocationData(currentLoc);
    setIsDetectingGPS(false);
    return currentLoc;
  };

  return (
    <LocationContext.Provider
      value={{
        location,
        recentLocations,
        isLocationSet: !!location,
        isDetectingGPS,
        isLoadingStorage,
        setLocationData,
        detectCurrentLocation,
      }}
    >
      {children}
    </LocationContext.Provider>
  );
};

export const useLocation = () => useContext(LocationContext);
