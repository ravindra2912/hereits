import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { businessService } from '../services/businessService';
import FallbackImage from '../components/FallbackImage';
import { useNavigation, useRoute } from '@react-navigation/native';
import { Skeleton } from '../components/SkeletonLoader';

const fallbackImage = require('../assets/business_icon.png');

export const BusinessServiceListScreen: React.FC = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const businessId = route.params?.businessId;
  const categoryId = route.params?.categoryId;
  const categoryName = route.params?.categoryName;

  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [services, setServices] = useState<any[]>([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [selectedCategoryId, setSelectedCategoryId] = useState<number | undefined>(categoryId);
  const [businessName, setBusinessName] = useState('');

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const loadServices = async (pageNum: number, resetList = false) => {
    if (pageNum === 1) {
      setLoading(true);
    } else {
      setLoadingMore(true);
    }

    const res = await businessService.getServices(businessId, {
      page: pageNum,
      category_id: selectedCategoryId || undefined,
    });

    if (res && res.success && res.data) {
      const items = res.data.data || [];
      const currentPage = res.data.current_page || 1;
      const lastPage = res.data.last_page || 1;

      if (resetList) {
        setServices(items);
      } else {
        setServices(prev => [...prev, ...items]);
      }
      setPage(currentPage);
      setHasMore(currentPage < lastPage);
    }
    setLoading(false);
    setLoadingMore(false);
  };

  useEffect(() => {
    const fetchBusinessName = async () => {
      const detailRes = await businessService.getBusinessDetail(businessId);
      if (detailRes && detailRes.success && detailRes.data) {
        setBusinessName(detailRes.data.business?.name || 'Business');
      }
    };
    fetchBusinessName();
  }, [businessId]);

  useEffect(() => {
    setPage(1);
    setHasMore(true);
    loadServices(1, true);
  }, [businessId, selectedCategoryId]);

  const handleLoadMore = () => {
    if (!loading && !loadingMore && hasMore) {
      loadServices(page + 1);
    }
  };

  return (
    <View style={[styles.container, theme.background]}>
      {/* Header */}
      <View style={styles.topNav}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
          <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.navTitle, theme.primaryText]} numberOfLines={1}>
          {businessName} - Services
        </Text>
      </View>

      {/* Category Filter Banner */}
      {selectedCategoryId && (
        <View style={styles.filterBanner}>
          <Text style={[styles.filterText, theme.primaryText]}>
            Category: <Text style={styles.filterHighlight}>{categoryName || 'Filtered'}</Text>
          </Text>
          <TouchableOpacity
            style={styles.clearFilterBtn}
            onPress={() => setSelectedCategoryId(undefined)}
          >
            <Text style={styles.clearFilterText}>Clear X</Text>
          </TouchableOpacity>
        </View>
      )}

      {loading ? (
        <View style={styles.listContent}>
          {Array.from({ length: 4 }).map((_, index) => (
            <View key={`skeleton-${index}`} style={[styles.serviceCard, theme.cardBg]}>
              <View style={styles.serviceImageContainer}>
                <Skeleton style={[styles.serviceImage, theme.skeletonBg]} borderRadius={12} />
              </View>
              <View style={styles.serviceInfo}>
                <Skeleton style={[theme.skeletonBg, { width: '70%', height: 14 }]} />
                <Skeleton style={[theme.skeletonBg, { width: '90%', height: 12, marginTop: 8 }]} />
              </View>
              <Skeleton style={[theme.skeletonBg, { width: 50, height: 14 }]} />
            </View>
          ))}
        </View>
      ) : (
        <FlatList
          data={services}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.listContent}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.2}
          ListFooterComponent={loadingMore ? <ActivityIndicator size="small" color="#6366F1" style={{ marginVertical: 12 }} /> : null}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={[styles.serviceCard, theme.cardBg]}
              onPress={() => navigation.navigate('ServiceDetail', { serviceId: item.id })}
            >
              <View style={styles.serviceImageContainer}>
                <FallbackImage
                  source={item.image_url ? { uri: item.image_url } : null}
                  type="service"
                  style={styles.serviceImage}
                  resizeMode="cover"
                />
              </View>
              <View style={styles.serviceInfo}>
                <Text style={[styles.serviceName, theme.primaryText]} numberOfLines={1}>
                  {item.name}
                </Text>
                <Text style={[styles.serviceDesc, theme.secondaryText]} numberOfLines={2}>
                  {item.description}
                </Text>
              </View>
              <View style={styles.priceContainer}>
                {item.price_type === 'PriceInRange' && (
                  <Text style={styles.servicePrice}>₹{item.min_price}-₹{item.max_price}</Text>
                )}
                {item.price_type === 'FixPrice' && (
                  <Text style={styles.servicePrice}>₹{item.price}</Text>
                )}
                {item.price_type === 'WithoutPrice' && (
                  <Text style={[styles.priceContact, theme.secondaryText]}>Contact</Text>
                )}
              </View>
            </TouchableOpacity>
          )}
          ListEmptyComponent={
            <Text style={[styles.emptyText, theme.secondaryText]}>No services listed.</Text>
          }
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingCenter: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  topNav: {
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
  backIcon: {
    fontSize: 14,
    fontWeight: '700',
  },
  navTitle: {
    fontSize: 18,
    fontWeight: '800',
    flex: 1,
  },
  listContent: {
    paddingHorizontal: 20,
    paddingBottom: 30,
  },
  serviceCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 20,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#F1F5F9',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
  },
  serviceImageContainer: {
    width: 60,
    height: 60,
    borderRadius: 12,
    overflow: 'hidden',
    marginRight: 12,
    backgroundColor: '#EEF2FF',
  },
  serviceImage: {
    width: 60,
    height: 60,
  },
  serviceInfo: {
    flex: 1,
    marginRight: 10,
  },
  serviceName: {
    fontSize: 15,
    fontWeight: '700',
  },
  serviceDesc: {
    fontSize: 12,
    marginTop: 2,
  },
  priceContainer: {
    alignItems: 'flex-end',
  },
  servicePrice: {
    fontSize: 14,
    fontWeight: '800',
    color: '#10B981',
  },
  priceContact: {
    fontSize: 11,
    fontWeight: '600',
  },
  emptyText: {
    textAlign: 'center',
    marginTop: 40,
    fontSize: 14,
  },
  filterBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 16,
    paddingVertical: 10,
    marginHorizontal: 20,
    marginBottom: 12,
    borderRadius: 12,
  },
  filterText: {
    fontSize: 13,
    fontWeight: '600',
  },
  filterHighlight: {
    fontWeight: '800',
    color: '#6366F1',
  },
  clearFilterBtn: {
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  clearFilterText: {
    fontSize: 11,
    color: '#EF4444',
    fontWeight: '700',
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

export default BusinessServiceListScreen;
