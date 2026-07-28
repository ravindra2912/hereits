import { apiRequest } from './api';

export interface LocationPayload {
  type: 'current_location' | 'search';
  location_name: string;
  full_address?: string;
  latitude: number;
  longitude: number;
  radius?: number;
  city_id?: number | null;
  area_lat_long?: string;
}

export const locationService = {
  searchCities: (query: string) =>
    apiRequest(`/location/search-cities?q=${encodeURIComponent(query)}`),
};
