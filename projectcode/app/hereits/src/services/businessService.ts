import { apiRequest } from './api';


export const businessService = {
  getHomeData: () => apiRequest('/home'),

  getCategories: () => apiRequest('/categories'),

  getBusinesses: (params?: {
    category_id?: string;
    search?: string;
    page?: number;
  }) => {
    let query = '';
    if (params) {
      const parts: string[] = [];
      if (params.category_id) parts.push(`category_id=${params.category_id}`);
      if (params.search) parts.push(`search=${encodeURIComponent(params.search)}`);
      if (params.page) parts.push(`page=${params.page}`);
      if (parts.length > 0) query = `?${parts.join('&')}`;
    }
    return apiRequest(`/businesses${query}`);
  },

  getBusinessDetail: (id: string | number) => apiRequest(`/business/${id}`),

  getProductDetail: (id: string | number) => apiRequest(`/business/product/${id}`),

  getServiceDetail: (id: string | number) => apiRequest(`/business/service/${id}`),

  getServices: (id: string | number, params?: { page?: number; category_id?: number | string }) => {
    let query = '';
    const parts: string[] = [];
    if (params?.page) parts.push(`page=${params.page}`);
    if (params?.category_id) parts.push(`category_id=${params.category_id}`);
    if (parts.length > 0) query = `?${parts.join('&')}`;
    return apiRequest(`/business/${id}/services${query}`);
  },

  getProducts: (id: string | number, params?: { page?: number; category_id?: number | string }) => {
    let query = '';
    const parts: string[] = [];
    if (params?.page) parts.push(`page=${params.page}`);
    if (params?.category_id) parts.push(`category_id=${params.category_id}`);
    if (parts.length > 0) query = `?${parts.join('&')}`;
    return apiRequest(`/business/${id}/products${query}`);
  },

  getExperts: (id: string | number, params?: { page?: number }) => {
    let query = '';
    if (params?.page) query = `?page=${params.page}`;
    return apiRequest(`/business/${id}/experts${query}`);
  },

  getReviews: (id: string | number) => apiRequest(`/business/${id}/reviews`),

  getExpertDetail: (id: string | number) => apiRequest(`/expert/${id}`),

  getExpertTiming: (expertId: number, date: string) =>
    apiRequest('/expert-timing', {
      method: 'POST',
      body: { expert_id: expertId, booking_date: date },
    }),

  bookAppointment: (payload: {
    business_id: number;
    expert_id: number;
    booking_date: string;
    slot_start_time: string;
    slot_end_time: string;
    user_name: string;
    user_contact: string;
    note?: string;
  }) =>
    apiRequest('/book-appointment', {
      method: 'POST',
      body: payload,
    }),

  toggleFavorite: (businessId: number, type: 'business' | 'product' | 'service' | 'expert' = 'business', itemId?: number) =>
    apiRequest('/toggle-favorite', {
      method: 'POST',
      body: {
        business_id: businessId,
        type,
        item_id: itemId ?? businessId,
      },
    }),
};
