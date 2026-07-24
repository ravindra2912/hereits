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
  Image,
} from 'react-native';
import { businessService } from '../services/businessService';
import { useLocation } from '../context/LocationContext';
import FallbackImage from '../components/FallbackImage';
import { useNavigation, useRoute } from '@react-navigation/native';

interface BusinessListScreenProps {
  onSelectBusiness?: (id: number) => void;
  selectedCategoryId?: number | null;
}

const fallbackImage = require('../assets/business_icon.png');

export const BusinessListScreen: React.FC<BusinessListScreenProps> = ({
  onSelectBusiness,
  selectedCategoryId = null,
}) => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const navigation = useNavigation<any>();
  const route = useRoute<any>();

  const { location } = useLocation();

  const [search, setSearch] = useState('');
  const [activeCategory, setActiveCategory] = useState<number | null>(selectedCategoryId);
  const [categories, setCategories] = useState<any[]>([]);
  const [businesses, setBusinesses] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (route.params?.categoryId !== undefined) {
      setActiveCategory(route.params.categoryId);
    }
  }, [route.params?.categoryId]);

  const fetchCategories = async () => {
    const res = await businessService.getCategories();
    if (res.success && res.data) {
      setCategories(res.data);
    }
  };

  const fetchBusinesses = async () => {
    setLoading(true);
    const res = await businessService.getBusinesses({
      category_id: activeCategory ? String(activeCategory) : undefined,
      search: search || undefined,
      latitude: location?.latitude,
      longitude: location?.longitude,
      radius: location?.radius,
      city_id: location?.city_id,
    });
    if (res.success && res.data) {
      setBusinesses(res.data.data || res.data || []);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchCategories();
  }, []);

  useEffect(() => {
    fetchBusinesses();
  }, [activeCategory, search, location]);

  return (
    <View style={[styles.container, theme.background]}>
      {/* Title Header */}
      <View style={styles.header}>
        <Text style={[styles.title, theme.primaryText]}>Explore Businesses</Text>
        <Text style={[styles.subtitle, theme.secondaryText]}>
          Find verified local services near you
        </Text>
      </View>

      {/* Search Field */}
      <View style={[styles.searchBox, theme.cardBg]}>
        <Text style={styles.searchIcon}>🔍</Text>
        <TextInput
          placeholder="Search by name, service or location..."
          placeholderTextColor={isDarkMode ? '#64748B' : '#94A3B8'}
          value={search}
          onChangeText={setSearch}
          style={[styles.searchInput, theme.primaryText]}
        />
        {search !== '' && (
          <TouchableOpacity onPress={() => setSearch('')}>
            <Text style={{ fontSize: 16, color: '#64748B' }}>✕</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Category Pills */}
      <View style={styles.categoryRow}>
        <FlatList
          horizontal
          showsHorizontalScrollIndicator={false}
          data={[{ id: null, name: 'All' }, ...categories]}
          keyExtractor={item => String(item.id ?? 'all')}
          renderItem={({ item }) => {
            const isSelected = activeCategory === item.id;
            return (
              <TouchableOpacity
                onPress={() => setActiveCategory(item.id)}
                style={[
                  styles.chip,
                  isSelected ? styles.selectedChip : theme.cardBg,
                ]}
              >
                <Text
                  style={[
                    styles.chipText,
                    isSelected ? styles.selectedChipText : theme.primaryText,
                  ]}
                >
                  {item.name}
                </Text>
              </TouchableOpacity>
            );
          }}
        />
      </View>

      {/* Business Directory List */}
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
            <View style={styles.emptyView}>
              <Text style={{ fontSize: 32, marginBottom: 8 }}>🔍</Text>
              <Text style={[styles.emptyText, theme.secondaryText]}>
                No businesses found matching your criteria.
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
  searchBox: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    borderRadius: 14,
    height: 48,
    marginBottom: 14,
  },
  searchIcon: {
    fontSize: 16,
    marginRight: 8,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
  },
  categoryRow: {
    marginBottom: 16,
    height: 38,
  },
  chip: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    marginRight: 8,
    justifyContent: 'center',
  },
  selectedChip: {
    backgroundColor: '#6366F1',
  },
  chipText: {
    fontSize: 13,
    fontWeight: '600',
  },
  selectedChipText: {
    color: '#FFFFFF',
  },
  listContainer: {
    paddingBottom: 40,
  },
  bizCard: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 16,
    marginBottom: 12,
    alignItems: 'center',
  },
  bizAvatar: {
    width: 48,
    height: 48,
    borderRadius: 14,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  bizAvatarText: {
    fontSize: 24,
  },
  bizAvatarImage: {
    width: 48,
    height: 48,
    borderRadius: 14,
  },
  bizContent: {
    flex: 1,
  },
  bizRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  bizName: {
    fontSize: 15,
    fontWeight: '700',
    flex: 1,
  },
  openBadge: {
    fontSize: 10,
    fontWeight: '700',
    color: '#10B981',
    backgroundColor: '#D1FAE5',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 8,
  },
  bizCategory: {
    fontSize: 12,
    marginTop: 2,
  },
  bizAddress: {
    fontSize: 12,
    marginTop: 4,
  },
  emptyView: {
    alignItems: 'center',
    marginTop: 60,
  },
  emptyText: {
    fontSize: 14,
    textAlign: 'center',
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

export default BusinessListScreen;
