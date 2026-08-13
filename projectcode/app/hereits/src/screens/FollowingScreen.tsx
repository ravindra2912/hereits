import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { authService } from '../services/authService';
import { businessService } from '../services/businessService';
import FallbackImage from '../components/FallbackImage';
import DataNotFound from '../components/DataNotFound';
import { BusinessListItemSkeleton } from '../components/SkeletonLoader';

export const FollowingScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const [followingList, setFollowingList] = useState<any[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [refreshing, setRefreshing] = useState<boolean>(false);
  const [unfollowingId, setUnfollowingId] = useState<number | null>(null);

  const fallbackLogo = require('../assets/business_icon.png');

  useEffect(() => {
    fetchFollowing();
  }, []);

  const fetchFollowing = async () => {
    setLoading(true);
    try {
      const res = await authService.getFavorites(1, 'business');
      if (res && res.success && res.data) {
        let items: any[] = [];
        if (Array.isArray(res.data)) {
          items = res.data;
        } else if (res.data && Array.isArray(res.data.data)) {
          items = res.data.data;
        }

        const validList = items
          .filter((item: any) => item.favorite_type === 'business' || item.business)
          .map((item: any) => ({
            id: item.id,
            business_id: item.business_id || item.business?.id,
            business: item.business || item,
          }));

        setFollowingList(validList);
      }
    } catch (e) {
      console.warn('Failed to fetch following businesses:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const handleRefresh = () => {
    setRefreshing(true);
    fetchFollowing();
  };

  const handleUnfollow = async (item: any) => {
    const targetBusinessId = item.business_id || item.business?.id;
    if (!targetBusinessId) return;

    Alert.alert(
      'Unfollow Business',
      `Are you sure you want to unfollow "${item.business?.name || 'this business'}"?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Unfollow',
          style: 'destructive',
          onPress: async () => {
            setUnfollowingId(item.id);
            // Optimistic update
            setFollowingList((prev) => prev.filter((i) => i.id !== item.id));

            try {
              const res = await businessService.toggleFavorite(targetBusinessId, 'business', targetBusinessId);
              if (!res || !res.success) {
                // Revert if failed
                fetchFollowing();
                Alert.alert('Failed to unfollow business.');
              }
            } catch (e) {
              fetchFollowing();
              console.warn('Unfollow error:', e);
            } finally {
              setUnfollowingId(null);
            }
          },
        },
      ]
    );
  };

  const renderItem = ({ item }: { item: any }) => {
    const biz = item.business || {};
    const logoSource = biz.business_image && !biz.business_image.includes('default.png')
      ? { uri: biz.business_image }
      : fallbackLogo;

    return (
      <TouchableOpacity
        style={[styles.card, theme.cardBg]}
        activeOpacity={0.85}
        onPress={() => navigation.navigate('BusinessDetail', { businessId: biz.id || item.business_id })}
      >
        <FallbackImage
          source={logoSource}
          fallbackSource={fallbackLogo}
          style={styles.logo}
          resizeMode="cover"
        />

        <View style={styles.cardContent}>
          <Text style={[styles.businessName, theme.primaryText]} numberOfLines={1}>
            {biz.name || 'Business'}
          </Text>

          <View style={styles.metaRow}>
            <Text style={styles.ratingText}>⭐ {biz.rating || '4.5'}</Text>
            {biz.area && (
              <Text style={[styles.areaText, theme.secondaryText]} numberOfLines={1}>
                • 📍 {biz.area}{biz.city?.name ? `, ${biz.city.name}` : ''}
              </Text>
            )}
          </View>

          {biz.business_category?.name && (
            <Text style={[styles.categoryBadge, theme.secondaryText]} numberOfLines={1}>
              {biz.business_category.name}
            </Text>
          )}
        </View>

        <TouchableOpacity
          style={styles.unfollowBtn}
          onPress={() => handleUnfollow(item)}
          disabled={unfollowingId === item.id}
          activeOpacity={0.7}
        >
          {unfollowingId === item.id ? (
            <ActivityIndicator size="small" color="#6366F1" />
          ) : (
            <Text style={styles.unfollowBtnText}>Unfollow</Text>
          )}
        </TouchableOpacity>
      </TouchableOpacity>
    );
  };

  return (
    <View style={[styles.container, theme.background]}>
      {/* Top Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
          <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.title, theme.primaryText]}>Following Businesses</Text>
        {followingList.length > 0 && (
          <View style={styles.countBadge}>
            <Text style={styles.countText}>{followingList.length}</Text>
          </View>
        )}
      </View>

      {loading ? (
        <View style={{ paddingTop: 4 }}>
          <BusinessListItemSkeleton theme={theme} />
          <BusinessListItemSkeleton theme={theme} />
          <BusinessListItemSkeleton theme={theme} />
          <BusinessListItemSkeleton theme={theme} />
        </View>
      ) : followingList.length === 0 ? (
        <DataNotFound
          title="No Followed Businesses"
          description="Businesses you follow will appear here. Visit a business page and tap Follow to stay updated."
          buttonText="Explore Businesses"
          onButtonPress={() => navigation.navigate('HomeTab')}
          theme={theme}
        />
      ) : (
        <FlatList
          data={followingList}
          keyExtractor={(item, index) => (item.id || index).toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.listContainer}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              colors={['#6366F1']}
            />
          }
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingHorizontal: 16,
    paddingTop: 16,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
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
  title: {
    fontSize: 18,
    fontWeight: '800',
    flex: 1,
  },
  countBadge: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  countText: {
    color: '#6366F1',
    fontWeight: '800',
    fontSize: 12,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
  },
  listContainer: {
    paddingBottom: 24,
  },
  card: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#F1F5F9',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  logo: {
    width: 60,
    height: 60,
    borderRadius: 14,
    backgroundColor: '#F8FAFC',
    marginRight: 14,
  },
  cardContent: {
    flex: 1,
    marginRight: 10,
  },
  businessName: {
    fontSize: 16,
    fontWeight: '800',
    marginBottom: 4,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 4,
  },
  ratingText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#F59E0B',
  },
  areaText: {
    fontSize: 12,
    marginLeft: 6,
    flex: 1,
  },
  categoryBadge: {
    fontSize: 11,
    fontWeight: '600',
  },
  unfollowBtn: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: '#E2E8F0',
    backgroundColor: '#F8FAFC',
  },
  unfollowBtnText: {
    color: '#64748B',
    fontSize: 12,
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

export default FollowingScreen;
