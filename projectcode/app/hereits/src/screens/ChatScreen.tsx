import React, { useCallback, useState } from 'react';
import {
  FlatList,
  StyleSheet,
  Text,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { ChatListItemSkeleton } from '../components/SkeletonLoader';
import { chatService } from '../services/chatService';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import FallbackImage from '../components/FallbackImage';
import RetryView from '../components/RetryView';

interface ChatScreenProps {
  onSelectConversation?: (conversationId: number, title: string) => void;
}

export const ChatScreen: React.FC<ChatScreenProps> = () => {
  const navigation = useNavigation<any>();
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const [conversations, setConversations] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [errorInfo, setErrorInfo] = useState<{ message?: string; isTimeout?: boolean } | null>(null);

  const fetchConversations = async () => {
    setLoading(true);
    setErrorInfo(null);
    const res = await chatService.getConversations();
    if (res.success && res.data) {
      setConversations(res.data);
      setErrorInfo(null);
    } else {
      setErrorInfo({
        message: res.message || 'Failed to load conversations.',
        isTimeout: res.is_timeout,
      });
    }
    setLoading(false);
  };

  useFocusEffect(
    useCallback(() => {
      fetchConversations();
    }, [])
  );

  return (
    <View style={[styles.container, theme.background]}>
      <View style={styles.header}>
        <Text style={[styles.title, theme.primaryText]}>Live Messages</Text>
        <Text style={[styles.subtitle, theme.secondaryText]}>
          Direct chat with businesses & experts
        </Text>
      </View>

      {loading ? (
        <View style={styles.listContent}>
          {Array.from({ length: 4 }).map((_, index) => (
            <ChatListItemSkeleton key={`skeleton-${index}`} theme={theme} />
          ))}
        </View>
      ) : errorInfo && conversations.length === 0 ? (
        <RetryView
          message={errorInfo.message}
          isTimeout={errorInfo.isTimeout}
          onRetry={fetchConversations}
        />
      ) : (
        <FlatList
          data={conversations}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.listContent}
          renderItem={({ item }) => (
            <TouchableOpacity
              onPress={() =>
                navigation.navigate('ChatDetail', {
                  conversationId: item.id,
                  title: item.title || 'Chat Conversation',
                })
              }
              style={[styles.chatRow, theme.cardBg]}
            >
              <FallbackImage
                source={item.image ? { uri: item.image } : undefined}
                fallbackSource={require('../assets/app_icon.png')}
                style={styles.avatar}
              />
              <View style={styles.chatInfo}>
                <View style={styles.topRow}>
                  <Text style={[styles.chatTitle, theme.primaryText]} numberOfLines={1}>
                    {item.title || 'Direct Chat'}
                  </Text>
                  <Text style={[styles.chatTime, theme.secondaryText]}>
                    {item.last_message_at ? new Date(item.last_message_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''}
                  </Text>
                </View>
                <Text style={[styles.lastMsg, theme.secondaryText]} numberOfLines={1}>
                  {item.last_message?.message || 'Tap to view conversation'}
                </Text>
              </View>
            </TouchableOpacity>
          )}
          ListEmptyComponent={
            <View style={styles.emptyView}>
              <Text style={{ fontSize: 36, marginBottom: 8 }}>💬</Text>
              <Text style={[styles.emptyText, theme.secondaryText]}>
                No active chat conversations.
              </Text>
            </View>
          }
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingHorizontal: 20,
    paddingTop: 16,
  },
  header: {
    marginBottom: 16,
  },
  title: {
    fontSize: 24,
    fontWeight: '800',
  },
  subtitle: {
    fontSize: 13,
    marginTop: 2,
  },
  listContent: {
    paddingBottom: 40,
  },
  chatRow: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 16,
    marginBottom: 10,
    alignItems: 'center',
  },
  avatar: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  chatInfo: {
    flex: 1,
  },
  topRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  chatTitle: {
    fontSize: 15,
    fontWeight: '700',
    flex: 1,
  },
  chatTime: {
    fontSize: 11,
  },
  lastMsg: {
    fontSize: 13,
  },
  emptyView: {
    alignItems: 'center',
    marginTop: 60,
  },
  emptyText: {
    fontSize: 14,
  },
});

const lightTheme = StyleSheet.create({
  background: { backgroundColor: '#F8FAFC' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
  cardBg: { backgroundColor: '#FFFFFF' },
  skeletonBg: { backgroundColor: '#E2E8F0' },
});

const darkTheme = StyleSheet.create({
  background: { backgroundColor: '#0F172A' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
  skeletonBg: { backgroundColor: '#334155' },
});

export default ChatScreen;
