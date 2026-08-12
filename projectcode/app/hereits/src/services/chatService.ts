import { apiRequest } from './api';

export const chatService = {
  getConversations: () => apiRequest('/chat/conversations'),

  getMessages: (conversationId: number) => apiRequest(`/chat/conversations/${conversationId}/messages`),

  sendMessage: (conversationId: number, data: string | FormData) =>
    apiRequest(`/chat/conversations/${conversationId}/messages`, {
      method: 'POST',
      body: typeof FormData !== 'undefined' && data instanceof FormData ? data : { message: data },
    }),

  startConversation: (businessId: number) =>
    apiRequest('/chat/conversations/start', {
      method: 'POST',
      body: { business_id: businessId },
    }),
};
