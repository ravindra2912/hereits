import React, { useEffect, useState } from 'react';
import {
  Image,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { businessService } from '../services/businessService';
import { useLocation } from '../context/LocationContext';
import FallbackImage from '../components/FallbackImage';
import { useNavigation } from '@react-navigation/native';
import { CategoryItemSkeleton, BusinessCardSkeleton } from '../components/SkeletonLoader';
import ComingSoon from '../components/ComingSoon';
import QRScannerModal from '../components/QRScannerModal';

import Svg, { Path, Rect } from 'react-native-svg';

const QRScanSvgIcon: React.FC<{ size?: number; color?: string }> = ({ size = 22, color = '#6366F1' }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Path d="M3 8V5a2 2 0 012-2h3M16 3h3a2 2 0 012 2v3M21 16v3a2 2 0 01-2 2h-3M8 21H5a2 2 0 01-2-2v-3" stroke={color} strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
    <Rect x="7" y="7" width="3.5" height="3.5" rx="0.5" fill={color} />
    <Rect x="13.5" y="7" width="3.5" height="3.5" rx="0.5" fill={color} />
    <Rect x="7" y="13.5" width="3.5" height="3.5" rx="0.5" fill={color} />
    <Rect x="13.5" y="13.5" width="3.5" height="3.5" rx="0.5" fill={color} />
  </Svg>
);

interface HomeScreenProps {
  onSelectBusiness?: (businessId: number) => void;
  onNavigateToCategory?: (catId: number) => void;
  onOpenLocationModal?: () => void;
}

const fallbackImage = require('../assets/business_icon.png');

export const HomeScreen: React.FC<HomeScreenProps> = ({
  onSelectBusiness,
  onNavigateToCategory,
  onOpenLocationModal,
}) => {
  const navigation = useNavigation<any>();
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const { location, setLocationModalVisible } = useLocation();

  const [banners, setBanners] = useState<any[]>([]);
  const [categories, setCategories] = useState<any[]>([]);
  const [businesses, setBusinesses] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [isQRScannerVisible, setIsQRScannerVisible] = useState(false);

  const loadData = async () => {
    const res = await businessService.getHomeData();

    if (res && res.success && res.data) {
      console.log("featured_businesses", res.data.featured_businesses);
      setBanners(res.data.banners || []);
      setCategories(res.data.categories || []);
      setBusinesses(res.data.featured_businesses || []);
    } else {
      setBanners([]);
      setCategories([]);
      setBusinesses([]);
    }
    setLoading(false);
    setRefreshing(false);
  };

  useEffect(() => {
    loadData();
  }, [location]);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const skeletons = Array.from({ length: 5 }, (_, i) => i);

  return (
    <ScrollView
      style={[styles.container, theme.background]}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#6366F1" />
      }
    >
      {/* Header Bar with App Icon + Location Selector */}
      <View style={styles.header}>
        <Image
          source={require('../assets/header_icon.png')}
          style={styles.headerAppIcon}
          resizeMode="cover"
        />

        <TouchableOpacity
          style={{ flex: 1, marginRight: 10 }}
          onPress={() => setLocationModalVisible(true)}
          activeOpacity={0.7}
        >
          <Text style={[styles.locationLabel, theme.secondaryText]}>
            📍 LOCATION
          </Text>
          <View style={styles.locationSelector}>
            <Text style={[styles.locationText, theme.primaryText]} numberOfLines={1}>
              {location?.location_name || 'Select Location'}
            </Text>
            <Text style={styles.dropdownIcon}> ▾</Text>
          </View>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.qrScanButton}
          onPress={() => setIsQRScannerVisible(true)}
          activeOpacity={0.8}
        >
          <QRScanSvgIcon size={22} color="#6366F1" />
        </TouchableOpacity>
      </View>

      <QRScannerModal
        visible={isQRScannerVisible}
        onClose={() => setIsQRScannerVisible(false)}
      />


      {/* Promotional Banner */}
      {/* <View style={styles.bannerCard}>
        <View style={styles.bannerTextContainer}>
          <Text style={styles.bannerTag}>NEARBY SERVICES</Text>
          <Text style={styles.bannerTitle}>
            {banners.length > 0 ? banners[0].title : 'Book Local Experts in Seconds'}
          </Text>
          <Text style={styles.bannerSubtitle}>
            Instant slot booking, ratings & verified business profiles near {location?.location_name || 'you'}.
          </Text>
        </View>
      </View> */}

      {!loading && categories.length === 0 && businesses.length === 0 ? (
        <ComingSoon theme={theme} />
      ) : (
        <>
          {/* Categories Horizontal Scroll */}
          <View style={styles.sectionHeader}>
            <Text style={[styles.sectionTitle, theme.primaryText]}>
              Explore Categories
            </Text>
          </View>

          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.categoryList}
          >
            {loading ? (
              skeletons.map(index => (
                <CategoryItemSkeleton key={`skeleton-${index}`} theme={theme} />
              ))
            ) : (
              categories.map(item => (
                <TouchableOpacity
                  key={item.id}
                  onPress={() => navigation.navigate('BusinessesTab', { categoryId: item.id })}
                  style={[styles.categoryCard, theme.cardBg]}
                >
                  <View style={styles.categoryIconBg}>
                    {item.image ? (
                      <FallbackImage
                        source={{ uri: item.image }}
                        style={styles.categoryImage}
                        fallbackSource={fallbackImage}
                        resizeMode="cover"
                      />
                    ) : (
                      <Text style={styles.categoryIcon}>{item.icon || '📍'}</Text>
                    )}
                  </View>
                  <Text style={[styles.categoryName, theme.primaryText]} numberOfLines={1}>
                    {item.name}
                  </Text>
                </TouchableOpacity>
              ))
            )}
          </ScrollView>

          {/* Featured Businesses Section */}
          <View style={styles.sectionHeader}>
            <Text style={[styles.sectionTitle, theme.primaryText]}>
              Top Rated Nearby
            </Text>
          </View>

          {loading ? (
            <View style={styles.businessList}>
              {skeletons.slice(0, 6).map(index => (
                <BusinessCardSkeleton key={`biz-skeleton-${index}`} theme={theme} />
              ))}
            </View>
          ) : (
            <View style={styles.businessList}>
              {businesses.map((biz: any) => (
                <TouchableOpacity
                  key={biz.id}
                  onPress={() => navigation.navigate('BusinessDetail', { businessId: biz.id })}
                  style={[styles.businessCard, theme.cardBg]}
                >
                  <View style={styles.bizAvatar}>
                    <FallbackImage
                      source={biz.business_image ? { uri: biz.business_image } : null}
                      fallbackSource={fallbackImage}
                      style={styles.bizAvatarImage}
                      resizeMode="cover"
                    />
                    {(biz.is_verified === 1 || biz.is_verified === true) && (
                      <Text style={styles.badgeTextOverlay}>Verified</Text>
                    )}
                  </View>
                  <View style={styles.bizInfo}>
                    <Text style={[styles.bizName, theme.primaryText]} numberOfLines={1}>
                      {biz.name}
                    </Text>
                    <Text style={[styles.bizCategory, theme.secondaryText]} numberOfLines={1}>
                      {biz.business_category?.name || 'Local Business'}
                    </Text>
                    <View style={styles.bizMetaRow}>
                      <Text style={styles.ratingText}>⭐ {biz.rating || '0.0'}</Text>
                      <Text style={[styles.metaDot, theme.secondaryText]}> • </Text>
                      <Text style={[styles.reviewText, theme.secondaryText]} numberOfLines={1}>
                        {biz.area && biz.city?.name ? `${biz.area}, ${biz.city.name}` : (biz.area || biz.city?.name || 'Local Address')}
                      </Text>
                    </View>
                  </View>
                </TouchableOpacity>
              ))}
            </View>
          )}
        </>
      )}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingHorizontal: 20,
    paddingTop: 16,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  headerAppIcon: {
    width: 42,
    height: 42,
    borderRadius: 10,
    marginRight: 12,
  },
  locationLabel: {
    fontSize: 10,
    fontWeight: '700',
    letterSpacing: 1,
  },
  locationSelector: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 2,
  },
  locationText: {
    fontSize: 18,
    fontWeight: '700',
  },
  dropdownIcon: {
    color: '#6366F1',
    fontWeight: 'bold',
  },
  qrScanButton: {
    width: 44,
    height: 44,
    borderRadius: 14,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: 6,
  },
  qrScanIcon: {
    fontSize: 22,
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    borderRadius: 14,
    height: 52,
    marginBottom: 24,
  },
  searchIcon: {
    fontSize: 18,
    marginRight: 10,
  },
  searchInput: {
    flex: 1,
    fontSize: 15,
  },
  bannerCard: {
    backgroundColor: '#6366F1',
    borderRadius: 20,
    padding: 22,
    marginBottom: 28,
  },
  bannerTextContainer: {
    maxWidth: '90%',
  },
  bannerTag: {
    color: '#EEF2FF',
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 1,
    marginBottom: 6,
  },
  bannerTitle: {
    color: '#FFFFFF',
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 6,
  },
  bannerSubtitle: {
    color: '#C7D2FE',
    fontSize: 13,
    lineHeight: 18,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 14,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
  },
  categoryList: {
    paddingBottom: 24,
  },
  categoryCard: {
    alignItems: 'center',
    padding: 14,
    borderRadius: 16,
    marginRight: 12,
    width: 90,
  },
  categoryIconBg: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  categoryIcon: {
    fontSize: 22,
  },
  categoryImage: {
    width: 46,
    height: 46,
    borderRadius: 23,
  },
  categoryName: {
    fontSize: 12,
    fontWeight: '600',
    textAlign: 'center',
  },
  businessList: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    paddingBottom: 40,
  },
  businessCard: {
    width: '48%',
    padding: 10,
    borderRadius: 18,
    marginBottom: 14,
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
    marginBottom: 10,
  },
  bizAvatarIcon: {
    fontSize: 26,
  },
  bizAvatarImage: {
    width: '100%',
    height: 110,
    borderRadius: 14,
  },
  bizInfo: {
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
    marginBottom: 6,
  },
  bizMetaRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  ratingText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#D97706',
  },
  metaDot: {
    fontSize: 11,
  },
  reviewText: {
    fontSize: 11,
    flex: 1,
  },
  skeletonText: {
    width: 50,
    height: 12,
    borderRadius: 6,
    marginTop: 4,
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

export default HomeScreen;
