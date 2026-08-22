import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';

interface RetryViewProps {
  message?: string;
  onRetry: () => void;
  onBack?: () => void;
  loading?: boolean;
  title?: string;
  isTimeout?: boolean;
  compact?: boolean;
}

export const RetryView: React.FC<RetryViewProps> = ({
  message,
  onRetry,
  onBack,
  loading = false,
  title,
  isTimeout = false,
  compact = false,
}) => {
  const displayTitle = title || (isTimeout ? 'Request Timed Out' : 'Connection Problem');
  const displayMessage =
    message ||
    (isTimeout
      ? 'The server took longer than 30 seconds to respond. Please check your internet connection and try again.'
      : 'Unable to connect to the server. Please check your network and try again.');

  if (compact) {
    return (
      <View style={styles.compactContainer}>
        <Text style={styles.compactEmoji}>{isTimeout ? '⏱️' : '⚠️'}</Text>
        <Text style={styles.compactText} numberOfLines={2}>
          {displayMessage}
        </Text>
        <TouchableOpacity
          style={styles.compactRetryBtn}
          onPress={onRetry}
          disabled={loading}
          activeOpacity={0.8}
        >
          {loading ? (
            <ActivityIndicator size="small" color="#FFFFFF" />
          ) : (
            <Text style={styles.compactRetryBtnText}>Retry 🔄</Text>
          )}
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.card}>
        <View style={styles.iconCircle}>
          <Text style={styles.iconEmoji}>{isTimeout ? '⏱️' : '📡'}</Text>
        </View>

        <Text style={styles.title}>{displayTitle}</Text>
        <Text style={styles.description}>{displayMessage}</Text>

        <View style={styles.actionsRow}>
          {onBack && (
            <TouchableOpacity
              style={styles.backBtn}
              onPress={onBack}
              disabled={loading}
              activeOpacity={0.8}
            >
              <Text style={styles.backBtnText}>← Go Back</Text>
            </TouchableOpacity>
          )}

          <TouchableOpacity
            style={[styles.retryBtn, loading && styles.retryBtnDisabled]}
            onPress={onRetry}
            disabled={loading}
            activeOpacity={0.85}
          >
            {loading ? (
              <ActivityIndicator color="#FFFFFF" size="small" />
            ) : (
              <Text style={styles.retryBtnText}>🔄 Retry Now</Text>
            )}
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  card: {
    width: '100%',
    maxWidth: 380,
    backgroundColor: '#FFFFFF',
    borderRadius: 24,
    padding: 24,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 4,
  },
  iconCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#C7D2FE',
  },
  iconEmoji: {
    fontSize: 30,
  },
  title: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0F172A',
    marginBottom: 8,
    textAlign: 'center',
  },
  description: {
    fontSize: 13,
    color: '#64748B',
    textAlign: 'center',
    lineHeight: 18,
    marginBottom: 24,
  },
  actionsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    width: '100%',
  },
  backBtn: {
    flex: 1,
    height: 46,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: '#CBD5E1',
    backgroundColor: '#F8FAFC',
    justifyContent: 'center',
    alignItems: 'center',
  },
  backBtnText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#475569',
  },
  retryBtn: {
    flex: 1.2,
    height: 46,
    borderRadius: 12,
    backgroundColor: '#6366F1',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#6366F1',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.25,
    shadowRadius: 6,
    elevation: 3,
  },
  retryBtnDisabled: {
    backgroundColor: '#94A3B8',
    shadowOpacity: 0,
  },
  retryBtnText: {
    fontSize: 14,
    fontWeight: '800',
    color: '#FFFFFF',
  },
  compactContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FEF2F2',
    borderRadius: 14,
    padding: 12,
    marginHorizontal: 16,
    marginVertical: 10,
    borderWidth: 1,
    borderColor: '#FECACA',
  },
  compactEmoji: {
    fontSize: 18,
    marginRight: 8,
  },
  compactText: {
    flex: 1,
    fontSize: 12,
    color: '#991B1B',
    fontWeight: '600',
    marginRight: 10,
  },
  compactRetryBtn: {
    backgroundColor: '#EF4444',
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
  compactRetryBtnText: {
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: '800',
  },
});

export default RetryView;
