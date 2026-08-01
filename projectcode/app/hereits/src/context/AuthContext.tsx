import React, { createContext, useContext, useEffect, useState } from 'react';
import { setAuthToken } from '../services/api';
import { authService } from '../services/authService';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface UserProfile {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  contact?: string;
  role?: number;
  credit_balance?: number;
  dob?: string;
  profile?: string;
}

interface AuthContextType {
  user: UserProfile | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  authModalVisible: boolean;
  setAuthModalVisible: (visible: boolean) => void;
  login: (email: string, pass: string) => Promise<{ success: boolean; message?: string }>;
  register: (payload: any) => Promise<{ success: boolean; message?: string }>;
  logout: () => Promise<void>;
  refreshProfile: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType>({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: false,
  authModalVisible: false,
  setAuthModalVisible: () => {},
  login: async () => ({ success: false }),
  register: async () => ({ success: false }),
  logout: async () => {},
  refreshProfile: async () => {},
});

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<UserProfile | null>(null);
  const [token, setTokenState] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [authModalVisible, setAuthModalVisible] = useState<boolean>(false);

  // Load persisted credentials on mount
  useEffect(() => {
    const bootstrapAsync = async () => {
      try {
        const savedToken = await AsyncStorage.getItem('auth_token');
        const savedUserStr = await AsyncStorage.getItem('auth_user');
        if (savedToken) {
          setTokenState(savedToken);
          setAuthToken(savedToken);
        }
        if (savedUserStr) {
          setUser(JSON.parse(savedUserStr));
        }
      } catch (e) {
        console.error('Failed to load persisted auth state:', e);
      }
    };

    bootstrapAsync();
  }, []);

  const updateToken = async (newToken: string | null) => {
    setTokenState(newToken);
    setAuthToken(newToken);
    try {
      if (newToken) {
        await AsyncStorage.setItem('auth_token', newToken);
      } else {
        await AsyncStorage.removeItem('auth_token');
      }
    } catch (e) {
      console.error('Failed to persist auth token:', e);
    }
  };

  const login = async (email: string, pass: string) => {
    setIsLoading(true);
    const res = await authService.login(email, pass);
    setIsLoading(false);

    if (res.success && res.data?.token) {
      await updateToken(res.data.token);
      const userDetails = res.data.user_details || null;
      setUser(userDetails);
      try {
        if (userDetails) {
          await AsyncStorage.setItem('auth_user', JSON.stringify(userDetails));
        } else {
          await AsyncStorage.removeItem('auth_user');
        }
      } catch (e) {
        console.error('Failed to persist user details:', e);
      }
      setAuthModalVisible(false); // Auto close login modal on success
      return { success: true, message: res.message };
    }
    return { success: false, message: res.message || 'Login failed' };
  };

  const register = async (payload: any) => {
    setIsLoading(true);
    const res = await authService.register(payload);
    setIsLoading(false);

    if (res.success && res.data?.token) {
      await updateToken(res.data.token);
      const userDetails = res.data.user_details || null;
      setUser(userDetails);
      try {
        if (userDetails) {
          await AsyncStorage.setItem('auth_user', JSON.stringify(userDetails));
        } else {
          await AsyncStorage.removeItem('auth_user');
        }
      } catch (e) {
        console.error('Failed to persist user details:', e);
      }
      setAuthModalVisible(false); // Auto close register modal on success
      return { success: true, message: res.message };
    }
    return { success: false, message: res.message || 'Registration failed' };
  };

  const logout = async () => {
    if (token) {
      await authService.logout();
    }
    await updateToken(null);
    setUser(null);
    try {
      await AsyncStorage.removeItem('auth_user');
    } catch (e) {
      console.error('Failed to clear user details:', e);
    }
  };

  const refreshProfile = async () => {
    if (!token) return;
    const res = await authService.getProfile();
    if (res.success && res.data) {
      setUser(res.data);
      try {
        await AsyncStorage.setItem('auth_user', JSON.stringify(res.data));
      } catch (e) {
        console.error('Failed to update persisted user profile:', e);
      }
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isAuthenticated: !!token,
        isLoading,
        authModalVisible,
        setAuthModalVisible,
        login,
        register,
        logout,
        refreshProfile,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
