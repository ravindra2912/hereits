import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { chatService } from '../services/chatService';
import { useAuth } from '../context/AuthContext';
import { useNavigation, useRoute } from '@react-navigation/native';

interface ChatDetailScreenProps {
  conversationId?: number;
  title?: string;
  onBack?: () => void;
}

export const ChatDetailScreen: React.FC<ChatDetailScreenProps> = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const conversationId = route.params?.conversationId;
  const title = route.params?.title;

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const { user } = useAuth();

  const [messages, setMessages] = useState<any[]>([]);
  const [inputText, setInputText] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);

  const fetchMessages = async () => {
    const res = await chatService.getMessages(conversationId);
    if (res.success && res.data) {
      setMessages(res.data.data || res.data || []);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchMessages();
  }, [conversationId]);

  const handleSend = async () => {
    if (!inputText.trim()) return;
    const msgText = inputText;
    setInputText('');
    setSending(true);

    const res = await chatService.sendMessage(conversationId, msgText);
    setSending(false);

    if (res.success) {
      fetchMessages();
    }
  };

  return (
    <View style={[styles.container, theme.background]}>
      {/* Top Bar */}
      <View style={styles.topBar}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
          <Text style={[styles.backText, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.chatTitle, theme.primaryText]} numberOfLines={1}>
          {title}
        </Text>
      </View>

      {/* Messages Feed */}
      {loading ? (
        <ActivityIndicator size="large" color="#6366F1" style={{ marginTop: 40 }} />
      ) : (
        <FlatList
          inverted
          data={messages}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.messagesList}
          renderItem={({ item }) => {
            const isMe = item.sender_id === user?.id;
            return (
              <View
                style={[
                  styles.msgBubble,
                  isMe ? styles.myMsgBubble : styles.otherMsgBubble,
                ]}
              >
                <Text style={isMe ? styles.myMsgText : styles.otherMsgText}>
                  {item.message}
                </Text>
              </View>
            );
          }}
        />
      )}

      {/* Input Bar */}
      <View style={[styles.inputBar, theme.cardBg]}>
        <TextInput
          value={inputText}
          onChangeText={setInputText}
          placeholder="Type your message..."
          placeholderTextColor={isDarkMode ? '#64748B' : '#94A3B8'}
          style={[styles.textInput, theme.primaryText]}
        />
        <TouchableOpacity
          disabled={sending || !inputText.trim()}
          onPress={handleSend}
          style={styles.sendButton}
        >
          <Text style={styles.sendIcon}>➔</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1 },
  topBar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 12,
  },
  backBtn: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    marginRight: 12,
  },
  backText: { fontSize: 14, fontWeight: '700' },
  chatTitle: { fontSize: 18, fontWeight: '800', flex: 1 },
  messagesList: { paddingHorizontal: 16, paddingVertical: 12 },
  msgBubble: {
    maxWidth: '80%',
    padding: 12,
    borderRadius: 16,
    marginBottom: 10,
  },
  myMsgBubble: {
    alignSelf: 'flex-end',
    backgroundColor: '#6366F1',
    borderBottomRightRadius: 4,
  },
  otherMsgBubble: {
    alignSelf: 'flex-start',
    backgroundColor: '#E2E8F0',
    borderBottomLeftRadius: 4,
  },
  myMsgText: { color: '#FFFFFF', fontSize: 14 },
  otherMsgText: { color: '#0F172A', fontSize: 14 },
  inputBar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 10,
  },
  textInput: {
    flex: 1,
    height: 44,
    fontSize: 14,
    paddingHorizontal: 12,
  },
  sendButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#6366F1',
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: 8,
  },
  sendIcon: { color: '#FFFFFF', fontSize: 18 },
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

export default ChatDetailScreen;
