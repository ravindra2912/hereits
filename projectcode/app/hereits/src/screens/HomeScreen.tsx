import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Image,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { businessService } from '../services/businessService';
import { useLocation } from '../context/LocationContext';
import FallbackImage from '../components/FallbackImage';
import { useNavigation } from '@react-navigation/native';

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

  const { location } = useLocation();

  const [banners, setBanners] = useState<any[]>([]);
  const [categories, setCategories] = useState<any[]>([]);
  const [businesses, setBusinesses] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    const locParams = location
      ? {
        latitude: location.latitude,
        longitude: location.longitude,
        radius: location.radius,
        city_id: location.city_id,
      }
      : undefined;

    const res = await businessService.getHomeData(locParams);
    console.log("featured_businesses", res.data.featured_businesses);

    if (res.success && res.data) {
      if (res.data.banners?.length) setBanners(res.data.banners);
      if (res.data.categories?.length) setCategories(res.data.categories);
      if (res.data.featured_businesses) setBusinesses(res.data.featured_businesses);
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

  const defaultCategories = [
    { id: 1, name: 'Doctors', icon: '🩺' },
    { id: 2, name: 'Salons', icon: '✂️' },
    { id: 3, name: 'Restaurants', icon: '🍽️' },
    { id: 4, name: 'Electrician', icon: '⚡' },
    { id: 5, name: 'Plumber', icon: '🔧' },
    { id: 6, name: 'Fitness', icon: '🏋️' },
  ];

  const displayCategories = categories.length > 0 ? categories : defaultCategories;

  return (
    <ScrollView
      style={[styles.container, theme.background]}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#6366F1" />
      }
    >
      {/* Header Bar with Location Selector */}
      <View style={styles.header}>
        <View style={{ flex: 1, marginRight: 10 }}>
          <Text style={[styles.locationLabel, theme.secondaryText]}>
            📍 CURRENT LOCATION
          </Text>
          <TouchableOpacity onPress={onOpenLocationModal} style={styles.locationSelector}>
            <Text style={[styles.locationText, theme.primaryText]} numberOfLines={1}>
              {location?.location_name || 'Select Location'}
            </Text>
            <Text style={styles.dropdownIcon}> ▾</Text>
          </TouchableOpacity>
        </View>
        <TouchableOpacity style={[styles.profileButton, theme.cardBg]}>
          <Text style={styles.profileIcon}>👤</Text>
        </TouchableOpacity>
      </View>

      {/* Search Input */}
      <View style={[styles.searchContainer, theme.cardBg]}>
        <Text style={styles.searchIcon}>🔍</Text>
        <TextInput
          placeholder="Search local businesses, experts, services..."
          placeholderTextColor={isDarkMode ? '#64748B' : '#94A3B8'}
          style={[styles.searchInput, theme.primaryText]}
        />
      </View>

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
        {displayCategories.map(item => (
          <TouchableOpacity
            key={item.id}
            onPress={() => navigation.navigate('ExploreTab', { categoryId: item.id })}
            style={[styles.categoryCard, theme.cardBg]}
          >
            <View style={styles.categoryIconBg}>
              <Text style={styles.categoryIcon}>{item.icon || '📍'}</Text>
            </View>
            <Text style={[styles.categoryName, theme.primaryText]} numberOfLines={1}>
              {item.name}
            </Text>
          </TouchableOpacity>
        ))}
      </ScrollView>

      {/* Featured Businesses Section */}
      <View style={styles.sectionHeader}>
        <Text style={[styles.sectionTitle, theme.primaryText]}>
          Top Rated Nearby
        </Text>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#6366F1" style={{ marginVertical: 30 }} />
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
                  source={biz.business_logo || biz.business_image ? { uri: biz.business_logo || biz.business_image } : null}
                  fallbackSource={fallbackImage}
                  style={styles.bizAvatarImage}
                  resizeMode="cover"
                />
              </View>
              <View style={styles.bizInfo}>
                <View style={styles.bizHeaderRow}>
                  <Text style={[styles.bizName, theme.primaryText]} numberOfLines={1}>
                    {biz.name}
                  </Text>
                  <Text style={styles.badgeText}>Verified</Text>
                </View>
                <Text style={[styles.bizCategory, theme.secondaryText]}>
                  {biz.business_category?.name || 'Local Business'}
                </Text>
                <View style={styles.bizMetaRow}>
                  <Text style={styles.ratingText}>⭐ {biz.rating || '0.0'}</Text>
                  <Text style={[styles.metaDot, theme.secondaryText]}> • </Text>
                  <Text style={[styles.reviewText, theme.secondaryText]} numberOfLines={1}>
                    {biz.address || 'Local Address'}
                  </Text>
                </View>
              </View>
            </TouchableOpacity>
          ))}
        </View>
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
  profileButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },
  profileIcon: {
    fontSize: 20,
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
  categoryName: {
    fontSize: 12,
    fontWeight: '600',
    textAlign: 'center',
  },
  businessList: {
    paddingBottom: 40,
  },
  businessCard: {
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
  bizAvatarIcon: {
    fontSize: 26,
  },
  bizAvatarImage: {
    width: 54,
    height: 54,
    borderRadius: 16,
  },
  bizInfo: {
    flex: 1,
  },
  bizHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  bizName: {
    fontSize: 16,
    fontWeight: '700',
    flex: 1,
  },
  badgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#6366F1',
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 10,
  },
  bizCategory: {
    fontSize: 13,
    marginTop: 2,
    marginBottom: 6,
  },
  bizMetaRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  ratingText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#D97706',
  },
  metaDot: {
    fontSize: 12,
  },
  reviewText: {
    fontSize: 12,
    flex: 1,
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
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
});

export default HomeScreen;
