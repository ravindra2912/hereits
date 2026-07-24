import React, { createContext, useContext, useEffect, useState } from 'react';
import { setAuthToken } from '../services/api';
import { authService } from '../services/authService';

interface UserProfile {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  contact?: string;
  role?: number;
  credit_balance?: number;
}

interface AuthContextType {
  user: UserProfile | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
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
  login: async () => ({ success: false }),
  register: async () => ({ success: false }),
  logout: async () => {},
  refreshProfile: async () => {},
});

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<UserProfile | null>(null);
  const [token, setTokenState] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const updateToken = (newToken: string | null) => {
    setTokenState(newToken);
    setAuthToken(newToken);
  };

  const login = async (email: string, pass: string) => {
    setIsLoading(true);
    const res = await authService.login(email, pass);
    setIsLoading(false);

    if (res.success && res.data?.token) {
      updateToken(res.data.token);
      setUser(res.data.user_details || null);
      return { success: true, message: res.message };
    }
    return { success: false, message: res.message || 'Login failed' };
  };

  const register = async (payload: any) => {
    setIsLoading(true);
    const res = await authService.register(payload);
    setIsLoading(false);

    if (res.success && res.data?.token) {
      updateToken(res.data.token);
      setUser(res.data.user_details || null);
      return { success: true, message: res.message };
    }
    return { success: false, message: res.message || 'Registration failed' };
  };

  const logout = async () => {
    if (token) {
      await authService.logout();
    }
    updateToken(null);
    setUser(null);
  };

  const refreshProfile = async () => {
    if (!token) return;
    const res = await authService.getProfile();
    if (res.success && res.data) {
      setUser(res.data);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isAuthenticated: !!token,
        isLoading,
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
