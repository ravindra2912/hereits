// Centralized API Service for Hereits Mobile App
// Note: Android devices/emulators cannot resolve custom domain names like 'hereits.test' directly.
// Use '10.0.2.2' for Android Emulator, or your local Wi-Fi IP '192.168.0.101' for Physical Devices.
export const BASE_API_URL = 'http://192.168.0.103:8000/api/v1'; // Change to 'http://192.168.0.101/api/v1' if testing on physical phone
// export const BASE_API_URL = 'http://10.0.2.2/api/v1'; // Change to 'http://192.168.0.101/api/v1' if testing on physical phone

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
    'Content-Type': 'application/json',
    Accept: 'application/json',
    Host: 'hereits.test', // Routes the request to Laragon's hereits.test virtual host on your PC
    ...headers,
  };

  if (authToken) {
    reqHeaders['Authorization'] = `Bearer ${authToken}`;
  }

  try {
    const response = await fetch(`${BASE_API_URL}${endpoint}`, {
      method,
      headers: reqHeaders,
      body: body ? JSON.stringify(body) : undefined,
    });

    const result = await response.json();
    return result;
  } catch (error: any) {
    console.error('API Error:', error);
    return {
      success: false,
      message: error.message || 'Network request failed',
      status_code: 500,
    };
  }
}
