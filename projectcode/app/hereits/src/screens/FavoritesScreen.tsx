import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { authService } from '../services/authService';
import FallbackImage from '../components/FallbackImage';
import DataNotFound from '../components/DataNotFound';
import { businessService } from '../services/businessService';

type FavoriteFilterType = 'All' | 'product' | 'service' | 'expert';

export const FavoritesScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const [favorites, setFavorites] = useState<any[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [loadingMore, setLoadingMore] = useState<boolean>(false);
  const [page, setPage] = useState<number>(1);
  const [hasMore, setHasMore] = useState<boolean>(true);
  const [filterType, setFilterType] = useState<FavoriteFilterType>('All');

  useEffect(() => {
    fetchFavorites(1, false);
  }, []);

  const fetchFavorites = async (pageNum: number = 1, isLoadMore: boolean = false) => {
    if (isLoadMore) {
      setLoadingMore(true);
    } else {
      setLoading(true);
    }
    try {
      const res = await authService.getFavorites(pageNum);
      if (res.success && res.data) {
        let items: any[] = [];
        let moreAvailable = false;

        if (Array.isArray(res.data)) {
          items = res.data;
          moreAvailable = false;
        } else if (res.data && Array.isArray(res.data.data)) {
          items = res.data.data;
          moreAvailable = res.data.next_page_url !== null;
        }

        const validFavs = items.filter(
          (fav: any) =>
            fav.favorite_type === 'product' ||
            fav.favorite_type === 'service' ||
            fav.favorite_type === 'expert'
        );

        if (isLoadMore) {
          setFavorites((prev) => [...prev, ...validFavs]);
        } else {
          setFavorites(validFavs);
        }

        setPage(pageNum);
        setHasMore(moreAvailable);
      }
    } catch (e) {
      console.warn('Failed to load favorites:', e);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  };

  const handleLoadMore = () => {
    if (loading || loadingMore || !hasMore) return;
    fetchFavorites(page + 1, true);
  };

  const handleUnfavorite = async (favItem: any) => {
    try {
      const res = await businessService.toggleFavorite(
        favItem.business_id,
        favItem.favorite_type,
        favItem.favorite_item_id
      );
      if (res.success) {
        setFavorites((prev) => prev.filter((fav) => fav.id !== favItem.id));
      }
    } catch (e) {
      console.warn('Failed to unfavorite:', e);
    }
  };

  const getFilteredFavorites = () => {
    if (filterType === 'All') return favorites;
    return favorites.filter((fav) => fav.favorite_type === filterType);
  };

  const renderFavoriteItem = ({ item }: { item: any }) => {
    let title = '';
    let subtitle = '';
    let badgeText = '';
    let badgeColor = '';
    let imageUri = null;
    let fallbackImage = require('../assets/business_icon.png');
    let onPressHandler = () => {};

    if (item.favorite_type === 'product' && item.product) {
      title = item.product.name;
      subtitle = item.product.description || 'Product';
      badgeText = 'Product';
      badgeColor = '#3B82F6'; // Blue
      imageUri = item.product.first_image?.image_url || (item.product.first_image ? item.product.first_image.image_url : null);
      onPressHandler = () => navigation.navigate('ProductDetail', { productId: item.product.id });
    } else if (item.favorite_type === 'service' && item.service) {
      title = item.service.name;
      subtitle = item.service.description || 'Service';
      badgeText = 'Service';
      badgeColor = '#10B981'; // Green
      imageUri = item.service.image_url;
      onPressHandler = () => navigation.navigate('ServiceDetail', { serviceId: item.service.id });
    } else if (item.favorite_type === 'expert' && item.expert) {
      title = item.expert.expert_name;
      subtitle = item.expert.title || 'Professional Specialist';
      badgeText = 'Specialist';
      badgeColor = '#8B5CF6'; // Purple
      imageUri = item.expert.expert_image;
      fallbackImage = require('../assets/business_icon.png'); // Default avatar
      onPressHandler = () => navigation.navigate('SpecialistDetail', { specialistId: item.expert.id });
    }

    if (!title) return null;

    const renderPrice = () => {
      if (item.favorite_type === 'product' && item.product) {
        const prod = item.product;
        if (prod.price_type === 'PriceInRange') {
          return <Text style={styles.priceText}>₹{prod.min_price} - ₹{prod.max_price}</Text>;
        } else if (prod.price_type === 'FixPrice') {
          return <Text style={styles.priceText}>₹{prod.sell_price || prod.price}</Text>;
        } else if (prod.price_type === 'WithoutPrice') {
          return <Text style={[styles.priceContactText, theme.secondaryText]}>Contact for Price</Text>;
        }
      } else if (item.favorite_type === 'service' && item.service) {
        const serv = item.service;
        if (serv.price_type === 'PriceInRange') {
          return <Text style={styles.priceText}>₹{serv.min_price} - ₹{serv.max_price}</Text>;
        } else if (serv.price_type === 'FixPrice') {
          return <Text style={styles.priceText}>₹{serv.price}</Text>;
        } else if (serv.price_type === 'WithoutPrice') {
          return <Text style={[styles.priceContactText, theme.secondaryText]}>Contact for Price</Text>;
        }
      }
      return null;
    };

    return (
      <TouchableOpacity
        style={[styles.itemCard, theme.cardBg]}
        onPress={onPressHandler}
        activeOpacity={0.8}
      >
        <FallbackImage
          source={imageUri ? { uri: imageUri } : null}
          fallbackSource={fallbackImage}
          style={styles.itemImage}
          resizeMode="cover"
        />
        <View style={styles.itemInfo}>
          <View style={styles.titleRow}>
            <Text style={[styles.itemTitle, theme.primaryText]} numberOfLines={1}>
              {title}
            </Text>
            <View style={[styles.typeBadge, { backgroundColor: badgeColor }]}>
              <Text style={styles.typeBadgeText}>{badgeText}</Text>
            </View>
          </View>

          {item.favorite_type === 'expert' ? (
            <Text style={[styles.itemSubtitle, theme.secondaryText]} numberOfLines={1}>
              {subtitle}
            </Text>
          ) : (
            renderPrice()
          )}

          {item.favorite_type === 'expert' && item.expert?.rating > 0 && (
            <Text style={styles.ratingText}>⭐ {item.expert.rating}</Text>
          )}
        </View>

        <TouchableOpacity
          onPress={() => handleUnfavorite(item)}
          style={styles.unfavoriteBtn}
          hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}
        >
          <Text style={{ fontSize: 18 }}>❤️</Text>
        </TouchableOpacity>

        <Text style={styles.arrowIcon}>›</Text>
      </TouchableOpacity>
    );
  };

  const filterOptions: { label: string; value: FavoriteFilterType }[] = [
    { label: 'All', value: 'All' },
    { label: 'Products', value: 'product' },
    { label: 'Services', value: 'service' },
    { label: 'Specialists', value: 'expert' },
  ];

  const filteredData = getFilteredFavorites();

  return (
    <View style={[styles.container, theme.background]}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Text style={[styles.backText, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.headerTitle, theme.primaryText]}>Saved Favorites</Text>
        <View style={{ width: 60 }} />
      </View>

      {/* Filter Tabs */}
      <View style={styles.filterContainer}>
        {filterOptions.map((opt) => {
          const isSelected = filterType === opt.value;
          return (
            <TouchableOpacity
              key={opt.value}
              onPress={() => setFilterType(opt.value)}
              style={[
                styles.filterTab,
                isSelected ? styles.filterTabSelected : theme.cardBg,
              ]}
            >
              <Text
                style={[
                  styles.filterTabText,
                  isSelected ? styles.filterTabTextSelected : theme.secondaryText,
                ]}
              >
                {opt.label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </View>

      {/* Content */}
      {loading ? (
        <View style={styles.centered}>
          <ActivityIndicator size="large" color="#6366F1" />
        </View>
      ) : filteredData.length === 0 ? (
        <View style={styles.emptyContainer}>
          <DataNotFound
            title="No Favorites Found"
            description="You haven't saved any favorites of this type yet. Tap the ❤️ icon on products, services, or specialists to save them here."
            icon="❤️"
            theme={theme}
          />
        </View>
      ) : (
        <FlatList
          data={filteredData}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderFavoriteItem}
          contentContainerStyle={styles.listContent}
          showsVerticalScrollIndicator={false}
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.2}
          ListFooterComponent={
            loadingMore ? (
              <View style={{ paddingVertical: 16, alignItems: 'center' }}>
                <ActivityIndicator size="small" color="#6366F1" />
              </View>
            ) : null
          }
        />
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, paddingTop: 16 },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    marginBottom: 16,
  },
  backBtn: {
    paddingVertical: 8,
    width: 60,
  },
  backText: {
    fontSize: 14,
    fontWeight: '700',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '800',
    flex: 1,
    textAlign: 'center',
  },
  filterContainer: {
    flexDirection: 'row',
    paddingHorizontal: 20,
    marginBottom: 16,
    justifyContent: 'space-between',
  },
  filterTab: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    minWidth: 75,
    alignItems: 'center',
  },
  filterTabSelected: {
    backgroundColor: '#6366F1',
    borderColor: '#6366F1',
  },
  filterTabText: {
    fontSize: 12,
    fontWeight: '700',
  },
  filterTabTextSelected: {
    color: '#FFFFFF',
  },
  listContent: {
    paddingHorizontal: 20,
    paddingBottom: 40,
  },
  itemCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  itemImage: {
    width: 64,
    height: 64,
    borderRadius: 12,
    marginRight: 14,
  },
  itemInfo: {
    flex: 1,
  },
  titleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingRight: 10,
  },
  itemTitle: {
    fontSize: 15,
    fontWeight: '700',
    flex: 1,
    marginRight: 8,
  },
  typeBadge: {
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 6,
  },
  typeBadgeText: {
    color: '#FFF',
    fontSize: 9,
    fontWeight: '800',
  },
  itemSubtitle: {
    fontSize: 12,
    marginTop: 4,
  },
  ratingText: {
    fontSize: 11,
    color: '#F59E0B',
    marginTop: 4,
    fontWeight: '600',
  },
  arrowIcon: {
    fontSize: 20,
    color: '#94A3B8',
    marginLeft: 8,
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    paddingHorizontal: 20,
  },
  priceText: {
    fontSize: 14,
    fontWeight: '800',
    color: '#10B981',
    marginTop: 4,
  },
  priceContactText: {
    fontSize: 12,
    marginTop: 4,
    fontWeight: '600',
  },
  unfavoriteBtn: {
    padding: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 4,
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

export default FavoritesScreen;
