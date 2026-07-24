import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { chatService } from '../services/chatService';
import { useNavigation } from '@react-navigation/native';

interface ChatScreenProps {
  onSelectConversation?: (conversationId: number, title: string) => void;
}

export const ChatScreen: React.FC<ChatScreenProps> = () => {
  const navigation = useNavigation<any>();
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const [conversations, setConversations] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchConversations = async () => {
    setLoading(true);
    const res = await chatService.getConversations();
    if (res.success && res.data) {
      setConversations(res.data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchConversations();
  }, []);

  return (
    <View style={[styles.container, theme.background]}>
      <View style={styles.header}>
        <Text style={[styles.title, theme.primaryText]}>Live Messages</Text>
        <Text style={[styles.subtitle, theme.secondaryText]}>
          Direct chat with businesses & experts
        </Text>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#6366F1" style={{ marginTop: 40 }} />
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
              <View style={styles.avatar}>
                <Text style={{ fontSize: 20 }}>💬</Text>
              </View>
              <View style={styles.chatInfo}>
                <View style={styles.topRow}>
                  <Text style={[styles.chatTitle, theme.primaryText]} numberOfLines={1}>
                    {item.title || 'Support Chat'}
                  </Text>
                  <Text style={[styles.chatTime, theme.secondaryText]}>Today</Text>
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
});

const darkTheme = StyleSheet.create({
  background: { backgroundColor: '#0F172A' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
});

export default ChatScreen;
