import { apiRequest } from './api';

export const chatService = {
  getConversations: () => apiRequest('/chat/conversations'),

  getMessages: (conversationId: number) => apiRequest(`/chat/conversations/${conversationId}/messages`),

  sendMessage: (conversationId: number, message: string) =>
    apiRequest(`/chat/conversations/${conversationId}/messages`, {
      method: 'POST',
      body: { message },
    }),
};
