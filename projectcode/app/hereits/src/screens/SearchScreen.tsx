import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { businessService } from '../services/businessService';
import FallbackImage from '../components/FallbackImage';
import { useNavigation } from '@react-navigation/native';

const fallbackImage = require('../assets/business_icon.png');

export const SearchScreen: React.FC = () => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const navigation = useNavigation<any>();

  const [search, setSearch] = useState('');
  const [businesses, setBusinesses] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);

  const fetchBusinesses = async () => {
    if (search.trim() === '') {
      setBusinesses([]);
      return;
    }
    setLoading(true);
    const res = await businessService.getBusinesses({
      search: search || undefined,
    });
    if (res && res.success && res.data) {
      setBusinesses(res.data.data || res.data || []);
    }
    setLoading(false);
  };

  useEffect(() => {
    const timer = setTimeout(() => {
      fetchBusinesses();
    }, 400);

    return () => clearTimeout(timer);
  }, [search]);

  return (
    <View style={[styles.container, theme.background]}>
      {/* Search Input Box */}
      <View style={[styles.searchBox, theme.cardBg]}>
        <Text style={styles.searchIcon}>🔍</Text>
        <TextInput
          placeholder="Search businesses, services..."
          placeholderTextColor={isDarkMode ? '#64748B' : '#94A3B8'}
          value={search}
          onChangeText={setSearch}
          style={[styles.searchInput, theme.primaryText]}
          autoFocus={true}
        />
        {search !== '' && (
          <TouchableOpacity onPress={() => setSearch('')}>
            <Text style={{ fontSize: 16, color: '#64748B' }}>✕</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Results List */}
      {loading ? (
        <ActivityIndicator size="large" color="#6366F1" style={{ marginTop: 40 }} />
      ) : (
        <FlatList
          data={businesses}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.listContainer}
          renderItem={({ item }) => (
            <TouchableOpacity
              onPress={() => navigation.navigate('BusinessDetail', { businessId: item.id })}
              style={[styles.bizCard, theme.cardBg]}
            >
              <View style={styles.bizAvatar}>
                <FallbackImage
                  source={item.business_logo || item.business_image ? { uri: item.business_logo || item.business_image } : null}
                  fallbackSource={fallbackImage}
                  style={styles.bizAvatarImage}
                  resizeMode="cover"
                />
              </View>
              <View style={styles.bizContent}>
                <View style={styles.bizRow}>
                  <Text style={[styles.bizName, theme.primaryText]} numberOfLines={1}>
                    {item.name}
                  </Text>
                  <Text style={styles.openBadge}>Active</Text>
                </View>
                <Text style={[styles.bizCategory, theme.secondaryText]}>
                  {item.business_category?.name || 'Local Store'}
                </Text>
                <Text style={[styles.bizAddress, theme.secondaryText]} numberOfLines={1}>
                  📍 {item.address || 'Surat'}
                </Text>
              </View>
            </TouchableOpacity>
          )}
          ListEmptyComponent={
            search.trim() !== '' ? (
              <View style={styles.emptyView}>
                <Text style={{ fontSize: 32, marginBottom: 8 }}>🔍</Text>
                <Text style={[styles.emptyText, theme.secondaryText]}>
                  No businesses found matching "{search}".
                </Text>
              </View>
            ) : (
              <View style={styles.emptyView}>
                <Text style={{ fontSize: 32, marginBottom: 8 }}>🏢</Text>
                <Text style={[styles.emptyText, theme.secondaryText]}>
                  Type above to search local businesses.
                </Text>
              </View>
            )
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
  searchBox: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    height: 48,
    borderRadius: 14,
    marginBottom: 16,
  },
  searchIcon: {
    fontSize: 16,
    marginRight: 8,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    fontWeight: '500',
    padding: 0,
  },
  listContainer: {
    paddingBottom: 24,
  },
  bizCard: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 18,
    marginBottom: 14,
    alignItems: 'center',
  },
  bizAvatar: {
    width: 54,
    height: 54,
    borderRadius: 16,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 14,
  },
  bizAvatarImage: {
    width: 54,
    height: 54,
    borderRadius: 16,
  },
  bizContent: {
    flex: 1,
  },
  bizRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  bizName: {
    fontSize: 15,
    fontWeight: '700',
    flex: 1,
    marginRight: 8,
  },
  openBadge: {
    fontSize: 10,
    fontWeight: '600',
    color: '#10B981',
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
  },
  bizCategory: {
    fontSize: 12,
    fontWeight: '500',
    marginBottom: 4,
  },
  bizAddress: {
    fontSize: 11,
  },
  emptyView: {
    alignItems: 'center',
    marginTop: 80,
    paddingHorizontal: 30,
  },
  emptyText: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
  },
});

const lightTheme = {
  background: { backgroundColor: '#F8FAFC' },
  cardBg: { backgroundColor: '#FFFFFF' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
};

const darkTheme = {
  background: { backgroundColor: '#0F172A' },
  cardBg: { backgroundColor: '#1E293B' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
};

export default SearchScreen;
