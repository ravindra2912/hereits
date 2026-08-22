import { BASE_API_URL as ENV_BASE_API_URL } from '@env';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform } from 'react-native';

// Centralized API Service for Hereits Mobile App
export const BASE_API_URL = ENV_BASE_API_URL;
export const DEFAULT_TIMEOUT_MS = 30000; // 30 seconds timeout across all API calls

let authToken: string | null = null;

export const setAuthToken = (token: string | null) => {
  authToken = token;
};

export const getAuthToken = () => authToken;

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  status_code?: number;
  is_timeout?: boolean;
}

export async function apiRequest<T = any>(
  endpoint: string,
  options: {
    method?: 'GET' | 'POST' | 'PUT' | 'DELETE';
    body?: any;
    headers?: Record<string, string>;
    timeoutMs?: number;
  } = {}
): Promise<ApiResponse<T>> {
  const { method = 'GET', body, headers = {}, timeoutMs = DEFAULT_TIMEOUT_MS } = options;

  const reqHeaders: Record<string, string> = {
    Accept: 'application/json',
    'X-Device': Platform.OS === 'ios' ? (Platform.isPad ? 'tablet' : 'mobile') : 'mobile',
    'X-Platform': Platform.OS === 'ios' ? 'iOS' : 'Android',
    'X-Browser': 'Hereits App',
    Referer: 'Hereits App',
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
  console.log(`[API Request] (${timeoutMs}ms timeout) ${method} to: ${fullUrl}`);

  // Setup 30-second abort controller
  const controller = new AbortController();
  const timeoutId = setTimeout(() => {
    controller.abort();
  }, timeoutMs);

  try {
    const response = await fetch(fullUrl, {
      method,
      headers: reqHeaders,
      body: body ? (isFormData ? body : JSON.stringify(body)) : undefined,
      signal: controller.signal,
    });

    clearTimeout(timeoutId);

    const result = await response.json();
    console.log(`[API Success] ${fullUrl}`, result);
    return result;
  } catch (error: any) {
    clearTimeout(timeoutId);

    const isTimeout =
      error.name === 'AbortError' ||
      error.message?.toLowerCase().includes('aborted') ||
      error.message?.toLowerCase().includes('timeout');

    if (isTimeout) {
      console.warn(`[API Timeout] ${method} ${fullUrl} exceeded ${timeoutMs / 1000}s`);
      return {
        success: false,
        message: 'Request timed out after 30 seconds. Please check your connection and retry.',
        status_code: 408,
        is_timeout: true,
      };
    }

    console.error(`[API Error] Failed to fetch ${method} ${fullUrl}:`, error);
    return {
      success: false,
      message: error.message || 'Network request failed. Please retry.',
      status_code: 500,
      is_timeout: false,
    };
  }
}
