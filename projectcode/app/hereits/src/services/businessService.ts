import { apiRequest } from './api';


export const businessService = {
  getHomeData: () => apiRequest('/home'),

  getCategories: () => apiRequest('/categories'),

  getBusinesses: (params?: {
    category_id?: string;
    search?: string;
  }) => {
    let query = '';
    if (params) {
      const parts: string[] = [];
      if (params.category_id) parts.push(`category_id=${params.category_id}`);
      if (params.search) parts.push(`search=${encodeURIComponent(params.search)}`);
      if (parts.length > 0) query = `?${parts.join('&')}`;
    }
    return apiRequest(`/businesses${query}`);
  },

  getBusinessDetail: (id: string | number) => apiRequest(`/business/${id}`),

  getProductDetail: (id: string | number) => apiRequest(`/business/product/${id}`),

  getServiceDetail: (id: string | number) => apiRequest(`/business/service/${id}`),

  getServices: (id: string | number) => apiRequest(`/business/${id}/services`),

  getProducts: (id: string | number) => apiRequest(`/business/${id}/products`),

  getReviews: (id: string | number) => apiRequest(`/business/${id}/reviews`),

  toggleFavorite: (businessId: number, type: 'business' | 'product' | 'service' = 'business', itemId?: number) =>
    apiRequest('/toggle-favorite', {
      method: 'POST',
      body: {
        business_id: businessId,
        type,
        item_id: itemId ?? businessId,
      },
    }),
};
