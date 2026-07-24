import { apiRequest } from './api';

export const authService = {
  login: (email: string, password: string) =>
    apiRequest('/login', {
      method: 'POST',
      body: { email, password },
    }),

  register: (payload: {
    first_name: string;
    last_name: string;
    email: string;
    contact: string;
    password: string;
    confirm_password: string;
  }) =>
    apiRequest('/registration', {
      method: 'POST',
      body: payload,
    }),

  logout: () =>
    apiRequest('/logout', {
      method: 'POST',
    }),

  getProfile: () => apiRequest('/user/profile'),

  updateProfile: (data: { first_name: string; last_name: string; contact?: string; dob?: string }) =>
    apiRequest('/user/profile/update', {
      method: 'POST',
      body: data,
    }),

  getOrders: () => apiRequest('/user/orders'),
  getFavorites: () => apiRequest('/user/favorites'),
};
