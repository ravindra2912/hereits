import { BASE_API_URL as ENV_BASE_API_URL } from '@env';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Centralized API Service for Hereits Mobile App
// Note: Android devices/emulators cannot resolve custom domain names like 'hereits.test' directly.
// Use '10.0.2.2' for Android Emulator, or your local Wi-Fi IP '192.168.0.101' for Physical Devices.
export const BASE_API_URL = ENV_BASE_API_URL;

let authToken: string | null = null;

export const setAuthToken = (token: string | null) => {
  authToken = token;
};

export const getAuthToken = () => authToken;

export async function apiRequest<T = any>(
  endpoint: string,
  options: {
    method?: 'GET' | 'POST' | 'PUT' | 'DELETE';
    body?: any;
    headers?: Record<string, string>;
  } = {}
): Promise<{ success: boolean; data?: T; message?: string; status_code?: number }> {
  const { method = 'GET', body, headers = {} } = options;

  const reqHeaders: Record<string, string> = {
    Accept: 'application/json',
    ...headers,
  };

  const isFormData = typeof FormData !== 'undefined' && body instanceof FormData;
  if (!isFormData) {
    reqHeaders['Content-Type'] = 'application/json';
  }

  if (authToken) {
    reqHeaders['Authorization'] = `Bearer ${authToken}`;
  }

  try {
    const locJson = await AsyncStorage.getItem('user_location');
    if (locJson) {
      const loc = JSON.parse(locJson);
      if (loc) {
        if (loc.latitude) reqHeaders['X-Latitude'] = String(loc.latitude);
        if (loc.longitude) reqHeaders['X-Longitude'] = String(loc.longitude);
        if (loc.radius) reqHeaders['X-Radius'] = String(loc.radius);
        if (loc.area_lat_long) reqHeaders['X-Area-Lat-Long'] = loc.area_lat_long;
        if (loc.city_id) reqHeaders['X-City-Id'] = String(loc.city_id);
      }
    }
  } catch (e) {
    console.warn('Failed to load location for headers:', e);
  }

  const fullUrl = `${BASE_API_URL}${endpoint}`;
  console.log(`[API Request] ${method} to: ${fullUrl}`);

  try {
    const response = await fetch(fullUrl, {
      method,
      headers: reqHeaders,
      body: body ? (isFormData ? body : JSON.stringify(body)) : undefined,
    });

    const result = await response.json();
    console.log(`[API Success] ${fullUrl}`, result);
    return result;
  } catch (error: any) {
    console.error(`[API Error] Failed to fetch ${method} ${fullUrl}:`, error);
    return {
      success: false,
      message: error.message || 'Network request failed',
      status_code: 500,
    };
  }
}
