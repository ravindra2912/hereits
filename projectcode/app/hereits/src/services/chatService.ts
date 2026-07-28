import { apiRequest } from './api';

export const chatService = {
  getConversations: () => apiRequest('/chat/conversations'),

  getMessages: (conversationId: number) => apiRequest(`/chat/conversations/${conversationId}/messages`),

  sendMessage: (conversationId: number, message: string) =>
    apiRequest(`/chat/conversations/${conversationId}/messages`, {
      method: 'POST',
      body: { message },
    }),

  startConversation: (businessId: number) =>
    apiRequest('/chat/conversations/start', {
      method: 'POST',
      body: { business_id: businessId },
    }),
};
