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

export const BusinessSpecialistListScreen: React.FC = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const businessId = route.params?.businessId;

  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [experts, setExperts] = useState<any[]>([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [businessName, setBusinessName] = useState('');

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const loadSpecialists = async (pageNum: number, resetList = false) => {
    if (pageNum === 1) {
      setLoading(true);
    } else {
      setLoadingMore(true);
    }

    const res = await businessService.getExperts(businessId, { page: pageNum });
    if (res && res.success && res.data) {
      const items = res.data.data || [];
      const currentPage = res.data.current_page || 1;
      const lastPage = res.data.last_page || 1;

      if (resetList) {
        setExperts(items);
      } else {
        setExperts(prev => [...prev, ...items]);
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
    loadSpecialists(1, true);
  }, [businessId]);

  const handleLoadMore = () => {
    if (!loading && !loadingMore && hasMore) {
      loadSpecialists(page + 1);
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
          {businessName} - Specialists
        </Text>
      </View>

      {loading ? (
        <View style={styles.listContent}>
          {Array.from({ length: 4 }).map((_, index) => (
            <View key={`skeleton-${index}`} style={[styles.expertListItem, theme.cardBg]}>
              <Skeleton style={[styles.expertListAvatar, theme.skeletonBg]} borderRadius={32} />
              <View style={styles.expertListInfo}>
                <Skeleton style={[theme.skeletonBg, { width: '50%', height: 14 }]} />
                <Skeleton style={[theme.skeletonBg, { width: '30%', height: 12, marginTop: 6 }]} />
                <Skeleton style={[theme.skeletonBg, { width: '80%', height: 12, marginTop: 6 }]} />
              </View>
            </View>
          ))}
        </View>
      ) : (
        <FlatList
          data={experts}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.listContent}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.2}
          ListFooterComponent={loadingMore ? <ActivityIndicator size="small" color="#6366F1" style={{ marginVertical: 12 }} /> : null}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={[styles.expertListItem, theme.cardBg]}
              onPress={() => navigation.navigate('SpecialistDetail', { specialistId: item.id })}
              activeOpacity={0.8}
            >
              <FallbackImage
                source={item.expert_image ? { uri: item.expert_image } : null}
                fallbackSource={fallbackImage}
                style={styles.expertListAvatar}
                resizeMode="cover"
              />
              <View style={styles.expertListInfo}>
                <Text style={[styles.expertListName, theme.primaryText]}>{item.expert_name}</Text>
                <Text style={[styles.expertListTitle, theme.secondaryText]}>
                  {item.department?.department_name || item.title || 'Specialist'}
                </Text>
                {item.description ? (
                  <Text style={[styles.expertListDesc, theme.secondaryText]} numberOfLines={3}>
                    {item.description}
                  </Text>
                ) : null}
              </View>
              {item.rating > 0 && (
                <View style={styles.expertListRatingCol}>
                  <Text style={styles.expertListRating}>⭐ {item.rating}</Text>
                </View>
              )}
            </TouchableOpacity>
          )}
          ListEmptyComponent={
            <Text style={[styles.emptyText, theme.secondaryText]}>No specialists found.</Text>
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
  expertListItem: {
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
  expertListAvatar: {
    width: 64,
    height: 64,
    borderRadius: 32,
    marginRight: 14,
    backgroundColor: '#EEF2FF',
  },
  expertListInfo: {
    flex: 1,
  },
  expertListName: {
    fontSize: 15,
    fontWeight: '700',
  },
  expertListTitle: {
    fontSize: 12,
    marginTop: 2,
  },
  expertListDesc: {
    fontSize: 11,
    marginTop: 4,
  },
  expertListRatingCol: {
    justifyContent: 'center',
  },
  expertListRating: {
    fontSize: 13,
    fontWeight: '700',
    color: '#D97706',
  },
  emptyText: {
    textAlign: 'center',
    marginTop: 40,
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

export default BusinessSpecialistListScreen;
