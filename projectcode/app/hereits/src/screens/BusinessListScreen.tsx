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
import { BusinessCardSkeleton } from '../components/SkeletonLoader';
import { businessService } from '../services/businessService';
import { useLocation } from '../context/LocationContext';
import FallbackImage from '../components/FallbackImage';
import { useNavigation, useRoute } from '@react-navigation/native';
import ComingSoon from '../components/ComingSoon';

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
  const [loadingMore, setLoadingMore] = useState(false);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  useEffect(() => {
    if (route.params?.categoryId !== undefined) {
      setActiveCategory(route.params.categoryId);
    }
  }, [route.params?.categoryId]);

  const fetchCategories = async () => {
    const res = await businessService.getCategories();
    if (res && res.success && res.data) {
      setCategories(res.data);
    }
  };

  const fetchBusinesses = async (pageNum: number, resetList = false) => {
    if (pageNum === 1) {
      setLoading(true);
    } else {
      setLoadingMore(true);
    }
    const res = await businessService.getBusinesses({
      category_id: activeCategory ? String(activeCategory) : undefined,
      search: search || undefined,
      page: pageNum,
    });
    if (res && res.success && res.data) {
      const items = res.data.data || [];
      const currentPage = res.data.current_page || 1;
      const lastPage = res.data.last_page || 1;

      if (resetList) {
        setBusinesses(items);
      } else {
        setBusinesses(prev => [...prev, ...items]);
      }
      setPage(currentPage);
      setHasMore(currentPage < lastPage);
    } else {
      if (resetList) {
        setBusinesses([]);
      }
    }
    setLoading(false);
    setLoadingMore(false);
  };

  useEffect(() => {
    fetchCategories();
  }, []);

  useEffect(() => {
    setPage(1);
    setHasMore(true);
    fetchBusinesses(1, true);
  }, [activeCategory, search, location]);

  const handleLoadMore = () => {
    if (!loading && !loadingMore && hasMore) {
      fetchBusinesses(page + 1);
    }
  };

  return (
    <View style={[styles.container, theme.background]}>
      {/* Title Header */}
      <View style={styles.header}>
        <Text style={[styles.title, theme.primaryText]}>Explore Businesses</Text>
        <Text style={[styles.subtitle, theme.secondaryText]}>
          Find verified local services near you
        </Text>
      </View>

      {/* Business Directory List */}
      {loading ? (
        <View style={styles.skeletonContainer}>
          {Array.from({ length: 8 }).map((_, index) => (
            <BusinessCardSkeleton key={`skeleton-${index}`} theme={theme} />
          ))}
        </View>
      ) : (
        <FlatList
          data={businesses}
          keyExtractor={item => String(item.id)}
          numColumns={2}
          columnWrapperStyle={styles.columnWrapper}
          contentContainerStyle={styles.listContainer}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.2}
          ListFooterComponent={loadingMore ? <ActivityIndicator size="small" color="#6366F1" style={{ marginVertical: 12 }} /> : null}
          renderItem={({ item }) => (
            <TouchableOpacity
              onPress={() => navigation.navigate('BusinessDetail', { businessId: item.id })}
              style={[styles.bizCard, theme.cardBg]}
            >
              <View style={styles.bizAvatar}>
                <FallbackImage
                  source={item.business_image ? { uri: item.business_image } : null}
                  type="business"
                  style={styles.bizAvatarImage}
                  resizeMode="cover"
                />
                {(item.is_verified === 1 || item.is_verified === true) && (
                  <Text style={styles.badgeTextOverlay}>Verified</Text>
                )}
              </View>
              <View style={styles.bizContent}>
                <Text style={[styles.bizName, theme.primaryText]} numberOfLines={1}>
                  {item.name}
                </Text>
                <Text style={[styles.bizCategory, theme.secondaryText]} numberOfLines={1}>
                  {item.business_category?.name || 'Local Store'}
                </Text>
                <Text style={[styles.bizAddress, theme.secondaryText]} numberOfLines={1}>
                  📍 {item.area && item.city?.name ? `${item.area}, ${item.city.name}` : (item.area || item.city?.name || 'Surat')}
                </Text>
              </View>
            </TouchableOpacity>
          )}
          ListEmptyComponent={
            search ? (
              <View style={styles.emptyView}>
                <Text style={{ fontSize: 32, marginBottom: 8 }}>🔍</Text>
                <Text style={[styles.emptyText, theme.secondaryText]}>
                  No businesses found matching your search.
                </Text>
              </View>
            ) : (
              <ComingSoon theme={theme} />
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
  skeletonContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  columnWrapper: {
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  listContainer: {
    paddingBottom: 40,
  },
  bizCard: {
    width: '48%',
    padding: 10,
    borderRadius: 16,
  },
  bizAvatar: {
    width: '100%',
    height: 110,
    borderRadius: 14,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
    overflow: 'hidden',
    marginBottom: 8,
  },
  bizAvatarText: {
    fontSize: 24,
  },
  bizAvatarImage: {
    width: '100%',
    height: 110,
    borderRadius: 14,
  },
  bizContent: {
    paddingHorizontal: 2,
  },
  bizName: {
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 2,
  },
  badgeTextOverlay: {
    position: 'absolute',
    top: 8,
    right: 8,
    fontSize: 9,
    fontWeight: '800',
    color: '#6366F1',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  bizCategory: {
    fontSize: 12,
    marginBottom: 4,
  },
  bizAddress: {
    fontSize: 11,
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
  skeletonBg: { backgroundColor: '#E2E8F0' },
});

const darkTheme = StyleSheet.create({
  background: { backgroundColor: '#0F172A' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
  skeletonBg: { backgroundColor: '#334155' },
});

export default BusinessListScreen;
