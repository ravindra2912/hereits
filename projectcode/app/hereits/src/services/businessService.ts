import { apiRequest } from './api';

export interface LocationFilterParams {
  latitude?: number;
  longitude?: number;
  radius?: number;
  city_id?: number | null;
}

export const businessService = {
  getHomeData: (locationParams?: LocationFilterParams) => {
    let query = '';
    if (locationParams) {
      const parts: string[] = [];
      if (locationParams.latitude && locationParams.longitude) {
        parts.push(`latitude=${locationParams.latitude}`);
        parts.push(`longitude=${locationParams.longitude}`);
        if (locationParams.radius) parts.push(`radius=${locationParams.radius}`);
      } else if (locationParams.city_id) {
        parts.push(`city_id=${locationParams.city_id}`);
      }
      if (parts.length > 0) query = `?${parts.join('&')}`;
    }
    return apiRequest(`/home${query}`);
  },

  getCategories: () => apiRequest('/categories'),

  getBusinesses: (params?: {
    category_id?: string;
    search?: string;
    latitude?: number;
    longitude?: number;
    radius?: number;
    city_id?: number | null;
  }) => {
    let query = '';
    if (params) {
      const parts: string[] = [];
      if (params.category_id) parts.push(`category_id=${params.category_id}`);
      if (params.search) parts.push(`search=${encodeURIComponent(params.search)}`);
      if (params.latitude && params.longitude) {
        parts.push(`latitude=${params.latitude}`);
        parts.push(`longitude=${params.longitude}`);
        if (params.radius) parts.push(`radius=${params.radius}`);
      } else if (params.city_id) {
        parts.push(`city_id=${params.city_id}`);
      }
      if (parts.length > 0) query = `?${parts.join('&')}`;
    }
    return apiRequest(`/businesses${query}`);
  },

  getBusinessDetail: (id: string | number) => apiRequest(`/business/${id}`),

  getServices: (id: string | number) => apiRequest(`/business/${id}/services`),

  getProducts: (id: string | number) => apiRequest(`/business/${id}/products`),

  getReviews: (id: string | number) => apiRequest(`/business/${id}/reviews`),

  toggleFavorite: (businessId: number) =>
    apiRequest('/toggle-favorite', {
      method: 'POST',
      body: { business_id: businessId },
    }),
};
