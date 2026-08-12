import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Image,
  PermissionsAndroid,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { Skeleton } from '../components/SkeletonLoader';
import { chatService } from '../services/chatService';
import { useAuth } from '../context/AuthContext';
import { useNavigation, useRoute } from '@react-navigation/native';

import FallbackImage from '../components/FallbackImage';
import { launchCamera, launchImageLibrary } from 'react-native-image-picker';

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
  const [selectedImages, setSelectedImages] = useState<any[]>([]);
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
    if (!inputText.trim() && selectedImages.length === 0) return;
    const msgText = inputText.trim();
    const imagesToSend = [...selectedImages];
    const tempId = `temp-${Date.now()}`;

    const tempMsg = {
      id: tempId,
      sender_id: user?.id,
      sender_type: 'user',
      body: msgText,
      message: msgText,
      message_type: imagesToSend.length > 0 ? 'image' : 'text',
      image_url: imagesToSend.length > 0 ? imagesToSend[0].uri : null,
      attachments: imagesToSend.map(a => ({ url: a.uri })),
      isSending: true,
      created_at: new Date().toISOString(),
    };

    setInputText('');
    setSelectedImages([]);
    setMessages(prev => [tempMsg, ...prev]);
    setSending(true);

    let payload: any;
    if (imagesToSend.length > 0) {
      const formData = new FormData();
      if (msgText) formData.append('message', msgText);

      imagesToSend.forEach((asset, idx) => {
        const fileObj = {
          uri: asset.uri,
          type: asset.type || 'image/jpeg',
          name: asset.fileName || `chat_img_${Date.now()}_${idx}.jpg`,
        };
        formData.append('images[]', fileObj as any);
        if (idx === 0) formData.append('image', fileObj as any);
      });
      payload = formData;
    } else {
      payload = msgText;
    }

    const res = await chatService.sendMessage(conversationId, payload);
    setSending(false);

    if (res.success && res.data) {
      setMessages(prev =>
        prev.map(m => (m.id === tempId ? { ...res.data, isSending: false } : m))
      );
    } else {
      fetchMessages();
    }
  };

  const requestImagePermissions = async (sourceType: 'camera' | 'library') => {
    if (Platform.OS !== 'android') return true;
    try {
      if (sourceType === 'camera') {
        const granted = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.CAMERA,
          {
            title: 'Camera Permission',
            message: 'App needs camera access to capture photos for chat.',
            buttonNeutral: 'Ask Later',
            buttonNegative: 'Cancel',
            buttonPositive: 'OK',
          }
        );
        return granted === PermissionsAndroid.RESULTS.GRANTED;
      } else {
        if (Number(Platform.Version) >= 33) {
          const granted = await PermissionsAndroid.request(
            PermissionsAndroid.PERMISSIONS.READ_MEDIA_IMAGES
          );
          return granted === PermissionsAndroid.RESULTS.GRANTED;
        } else {
          const granted = await PermissionsAndroid.request(
            PermissionsAndroid.PERMISSIONS.READ_EXTERNAL_STORAGE
          );
          return granted === PermissionsAndroid.RESULTS.GRANTED;
        }
      }
    } catch (err) {
      console.warn('Permission error:', err);
      return true;
    }
  };

  const handleRemoveSelectedImage = (index: number) => {
    setSelectedImages(prev => prev.filter((_, i) => i !== index));
  };

  const openCamera = async () => {
    if (selectedImages.length >= 4) {
      Alert.alert('Limit Reached', 'You can select a maximum of 4 images.');
      return;
    }
    const hasPerm = await requestImagePermissions('camera');
    if (!hasPerm) {
      Alert.alert('Permission Denied', 'Camera permission is required to take photos.');
      return;
    }
    const result = await launchCamera({ mediaType: 'photo', quality: 0.8 });
    if (result.assets && result.assets.length > 0) {
      setSelectedImages(prev => [...prev, result.assets![0]].slice(0, 4));
    }
  };

  const openGallery = async () => {
    const remaining = 4 - selectedImages.length;
    if (remaining <= 0) {
      Alert.alert('Limit Reached', 'You can select a maximum of 4 images.');
      return;
    }
    const hasPerm = await requestImagePermissions('library');
    if (!hasPerm) {
      Alert.alert('Permission Denied', 'Storage permission is required to select photos.');
      return;
    }
    const result = await launchImageLibrary({
      mediaType: 'photo',
      selectionLimit: remaining,
      quality: 0.8,
    });
    if (result.assets && result.assets.length > 0) {
      setSelectedImages(prev => [...prev, ...result.assets!].slice(0, 4));
    }
  };

  const handlePickImage = () => {
    Alert.alert(
      'Send Image',
      'Choose image source',
      [
        { text: '📷 Take Photo', onPress: openCamera },
        { text: '🖼 Gallery', onPress: openGallery },
        { text: 'Cancel', style: 'cancel' },
      ],
      { cancelable: true }
    );
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
        <View style={styles.messagesList}>
          <View style={[styles.msgBubble, styles.otherMsgBubble, { width: 150 }]}>
            <Skeleton style={[theme.skeletonBg, { width: '100%', height: 14 }]} borderRadius={8} />
          </View>
          <View style={[styles.msgBubble, styles.myMsgBubble, { width: 180 }]}>
            <Skeleton style={{ width: '100%', height: 14, backgroundColor: '#818CF8' }} borderRadius={8} />
          </View>
          <View style={[styles.msgBubble, styles.otherMsgBubble, { width: 220 }]}>
            <Skeleton style={[theme.skeletonBg, { width: '100%', height: 14 }]} borderRadius={8} />
          </View>
          <View style={[styles.msgBubble, styles.myMsgBubble, { width: 100 }]}>
            <Skeleton style={{ width: '100%', height: 14, backgroundColor: '#818CF8' }} borderRadius={8} />
          </View>
        </View>
      ) : (
        <FlatList
          inverted
          data={messages}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.messagesList}
          renderItem={({ item }) => {
            const isMe = item.sender_id === user?.id;
            const attachments = item.attachments || [];

            const formatUrl = (u?: string) => {
              if (!u) return null;
              if (u.startsWith('http://') || u.startsWith('https://') || u.startsWith('file://') || u.startsWith('content://')) {
                return u;
              }
              return `https://cdn.hereits.com/local/${u}`;
            };

            const imageUrls: string[] = [];
            const addUrl = (raw?: string) => {
              const formatted = formatUrl(raw);
              if (formatted && !imageUrls.includes(formatted)) {
                imageUrls.push(formatted);
              }
            };

            addUrl(item.image_url);
            addUrl(item.image);
            attachments.forEach((att: any) => {
              addUrl(att.url);
              addUrl(att.path);
            });
            if (item.metadata?.url) addUrl(item.metadata.url);

            const textContent = (item.body || item.message || '').trim();
            const isImageMessage = item.message_type === 'image' || imageUrls.length > 0;
            const showText = textContent && (!isImageMessage || textContent !== item.image_url);

            return (
              <View
                style={[
                  styles.msgBubble,
                  isMe ? styles.myMsgBubble : styles.otherMsgBubble,
                ]}
              >
                {imageUrls.map((imgUrl: string, idx: number) => (
                  <FallbackImage
                    key={`img-${idx}`}
                    source={{ uri: imgUrl }}
                    fallbackSource={require('../assets/app_icon.png')}
                    style={styles.attachmentImage}
                    resizeMode="cover"
                  />
                ))}
                {showText ? (
                  <Text style={isMe ? styles.myMsgText : styles.otherMsgText}>
                    {textContent}
                  </Text>
                ) : null}
                {item.isSending ? (
                  <View style={styles.sendingContainer}>
                    <ActivityIndicator size="small" color={isMe ? '#FFFFFF' : '#6366F1'} />
                    <Text style={[styles.sendingText, { color: isMe ? 'rgba(255,255,255,0.85)' : '#64748B' }]}>
                      Sending...
                    </Text>
                  </View>
                ) : null}
              </View>
            );
          }}
        />
      )}

      {/* Selected Images Preview Strip */}
      {selectedImages.length > 0 && (
        <View style={[styles.previewStripContainer, theme.cardBg]}>
          <View style={styles.previewHeader}>
            <Text style={[styles.previewTitle, theme.secondaryText]}>
              Selected Images ({selectedImages.length}/4)
            </Text>
            <TouchableOpacity onPress={() => setSelectedImages([])}>
              <Text style={styles.clearAllText}>Clear all</Text>
            </TouchableOpacity>
          </View>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.previewList}>
            {selectedImages.map((asset, idx) => (
              <View key={`preview-${idx}`} style={styles.previewItem}>
                <Image source={{ uri: asset.uri }} style={styles.previewThumb} />
                <TouchableOpacity
                  onPress={() => handleRemoveSelectedImage(idx)}
                  style={styles.removeBadge}
                >
                  <Text style={styles.removeBadgeText}>✕</Text>
                </TouchableOpacity>
              </View>
            ))}
          </ScrollView>
        </View>
      )}

      {/* Input Bar */}
      <View style={[styles.inputBar, theme.cardBg]}>
        <TouchableOpacity
          disabled={sending}
          onPress={handlePickImage}
          style={styles.attachBtn}
        >
          <Text style={styles.attachIcon}>📷</Text>
        </TouchableOpacity>
        <TextInput
          value={inputText}
          onChangeText={setInputText}
          placeholder="Type your message..."
          placeholderTextColor={isDarkMode ? '#64748B' : '#94A3B8'}
          style={[styles.textInput, theme.primaryText]}
        />
        <TouchableOpacity
          disabled={sending || (!inputText.trim() && selectedImages.length === 0)}
          onPress={handleSend}
          style={[
            styles.sendButton,
            (!inputText.trim() && selectedImages.length === 0) && { opacity: 0.5 },
          ]}
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
  attachmentImage: {
    width: 200,
    height: 150,
    borderRadius: 12,
    marginBottom: 6,
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
  sendingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 4,
    gap: 6,
  },
  sendingText: {
    fontSize: 11,
    fontStyle: 'italic',
  },
  previewStripContainer: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
  },
  previewHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  previewTitle: {
    fontSize: 12,
    fontWeight: '600',
  },
  clearAllText: {
    fontSize: 12,
    color: '#EF4444',
    fontWeight: '600',
  },
  previewList: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  previewItem: {
    position: 'relative',
    marginRight: 8,
  },
  previewThumb: {
    width: 60,
    height: 60,
    borderRadius: 8,
  },
  removeBadge: {
    position: 'absolute',
    top: -6,
    right: -6,
    backgroundColor: '#EF4444',
    width: 20,
    height: 20,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 10,
  },
  removeBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '900',
  },
  attachBtn: {
    padding: 8,
    marginRight: 4,
  },
  attachIcon: {
    fontSize: 22,
  },
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
  skeletonBg: { backgroundColor: '#CBD5E1' },
});

const darkTheme = StyleSheet.create({
  background: { backgroundColor: '#0F172A' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
  skeletonBg: { backgroundColor: '#475569' },
});

export default ChatDetailScreen;
