import { apiRequest } from './api';

export const authService = {
  login: (email: string, password: string) =>
    apiRequest('/login', {
      method: 'POST',
      body: { email, password },
    }),

  googleLogin: (payload: {
    email: string;
    first_name?: string;
    last_name?: string;
    google_id?: string;
    profile?: string;
  }) =>
    apiRequest('/google-login', {
      method: 'POST',
      body: payload,
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

  updateProfile: (data: any) =>
    apiRequest('/user/profile/update', {
      method: 'POST',
      body: data,
    }),

  updatePassword: (data: any) =>
    apiRequest('/user/profile/update-password', {
      method: 'POST',
      body: data,
    }),

  getOrders: (page: number = 1, perPage: number = 10) =>
    apiRequest(`/user/orders?page=${page}&per_page=${perPage}`),
  getOrderDetails: (id: number | string) =>
    apiRequest(`/user/orders/${id}`),
  submitOrderReview: (payload: {
    business_id: number;
    order_id: number;
    rating: number;
    review: string;
  }) =>
    apiRequest('/user/orders/review', {
      method: 'POST',
      body: payload,
    }),
  getFavorites: (page?: number, type?: string) =>
    apiRequest(`/user/favorites?${page ? `page=${page}&` : ''}${type ? `type=${type}` : ''}`),
};
