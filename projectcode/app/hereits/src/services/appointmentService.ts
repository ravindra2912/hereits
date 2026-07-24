import { apiRequest } from './api';

export const appointmentService = {
  getExperts: (businessId: number) => apiRequest(`/business/${businessId}/experts`),

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

  getMyAppointments: () => apiRequest('/my-appointments'),
};
