import React, { useEffect, useState } from 'react';
import {
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  Dimensions,
  Alert,
} from 'react-native';
import { businessService } from '../services/businessService';
import FallbackImage from '../components/FallbackImage';
import { useNavigation, useRoute } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import Svg, { Path } from 'react-native-svg';
import { Skeleton } from '../components/SkeletonLoader';

const { width } = Dimensions.get('window');
const fallbackImage = require('../assets/business_icon.png');

export const ServiceDetailScreen: React.FC = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const serviceId = route.params?.serviceId;
  const { isAuthenticated, setAuthModalVisible } = useAuth();

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const [loading, setLoading] = useState(true);
  const [serviceData, setServiceData] = useState<any>(null);
  const [isFavorited, setIsFavorited] = useState<boolean>(false);

  useEffect(() => {
    if (serviceData?.service) {
      setIsFavorited(!!serviceData.service.is_favorited);
    }
  }, [serviceData]);

  useEffect(() => {
    const loadServiceDetail = async () => {
      setLoading(true);
      const res = await businessService.getServiceDetail(serviceId);
      if (res && res.success && res.data) {
        setServiceData(res.data);
      }
      setLoading(false);
    };

    loadServiceDetail();
  }, [serviceId]);

  if (loading) {
    return (
      <View style={[styles.container, theme.background]}>
        {/* Top Header */}
        <View style={styles.topNav}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
            <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
          </TouchableOpacity>
          <Text style={[styles.navTitle, theme.primaryText]} numberOfLines={1}>
            Loading...
          </Text>
        </View>

        <ScrollView contentContainerStyle={styles.scrollContent}>
          {/* Cover Photo Placeholder */}
          <Skeleton style={[styles.coverImage, theme.skeletonBg]} borderRadius={20} />

          {/* Service Details Section Placeholder */}
          <View style={[styles.detailCard, theme.cardBg]}>
            <Skeleton style={[theme.skeletonBg, { width: 60, height: 12 }]} />
            <Skeleton style={[theme.skeletonBg, { width: '80%', height: 22, marginTop: 8 }]} />
            <Skeleton style={[theme.skeletonBg, { width: 100, height: 20, marginTop: 12 }]} />
          </View>

          {/* Description Section Placeholder */}
          <View style={[styles.sectionCard, theme.cardBg]}>
            <Skeleton style={[theme.skeletonBg, { width: 100, height: 16 }]} />
            <Skeleton style={[theme.skeletonBg, { width: '100%', height: 12, marginTop: 10 }]} />
            <Skeleton style={[theme.skeletonBg, { width: '90%', height: 12, marginTop: 6 }]} />
            <Skeleton style={[theme.skeletonBg, { width: '70%', height: 12, marginTop: 6 }]} />
          </View>
        </ScrollView>
      </View>
    );
  }

  if (!serviceData || !serviceData.service) {
    return (
      <View style={[styles.loadingCenter, theme.background]}>
        <Text style={[styles.emptyText, theme.secondaryText]}>Service details not found.</Text>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtnError}>
          <Text style={styles.backBtnErrorText}>Go Back</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const { service, business, recommendedServices = [] } = serviceData;



  const handleToggleFavorite = async () => {
    if (!isAuthenticated) {
      setAuthModalVisible(true);
      return;
    }

    const prev = isFavorited;
    setIsFavorited(!prev);

    const res = await businessService.toggleFavorite(business.id, 'service', service.id);
    if (!res || !res.success) {
      setIsFavorited(prev);
      Alert.alert('Failed to update favorite status.');
    }
  };

  return (
    <View style={[styles.container, theme.background]}>
      {/* Top Header */}
      <View style={styles.topNav}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
          <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.navTitle, theme.primaryText]} numberOfLines={1}>
          {service.name || 'Service Details'}
        </Text>
        <TouchableOpacity onPress={handleToggleFavorite} style={[styles.favBtnHeader, theme.cardBg]}>
          <Svg width="20" height="20" viewBox="0 0 24 24" fill={isFavorited ? '#EF4444' : 'none'} stroke={isFavorited ? '#EF4444' : (isDarkMode ? '#F8FAFC' : '#64748B')} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <Path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
          </Svg>
        </TouchableOpacity>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Cover Photo */}
        <FallbackImage
          source={service.image_url ? { uri: service.image_url } : null}
          fallbackSource={fallbackImage}
          style={styles.coverImage}
          resizeMode="cover"
        />

        {/* Service Details Section */}
        <View style={[styles.detailCard, theme.cardBg]}>
          <Text style={[styles.categoryLabel, theme.secondaryText]}>
            {service.category?.name || 'Service'}
          </Text>
          <Text style={[styles.serviceName, theme.primaryText]}>{service.name}</Text>

          {/* Slashed / Formatted Price UI */}
          {service.price_type === "PriceInRange" && (
            <Text style={styles.servicePrice}>₹{service.min_price} - ₹{service.max_price}</Text>
          )}
          {service.price_type === "FixPrice" && (
            <View style={styles.priceRow}>
              <Text style={styles.servicePrice}>₹{service.price || '0'}</Text>
            </View>
          )}
          {service.price_type === "WithoutPrice" && (
            <Text style={[styles.priceContact, theme.secondaryText]}>Contact for Price</Text>
          )}
        </View>

        {/* Description Section */}
        {service.description ? (
          <View style={[styles.sectionCard, theme.cardBg]}>
            <Text style={[styles.sectionTitle, theme.primaryText]}>Description</Text>
            <Text style={[styles.descriptionText, theme.secondaryText]}>
              {service.description}
            </Text>
          </View>
        ) : null}

        {/* Store Card */}
        {business && (
          <View style={[styles.merchantCard, theme.cardBg]}>
            <View style={styles.merchantHeader}>
              <View style={styles.merchantInfo}>
                <Text style={[styles.merchantTitle, theme.primaryText]}>Offered By</Text>
                <Text style={[styles.merchantName, theme.primaryText]}>{business.name}</Text>
                <Text style={[styles.merchantRating]}>⭐ {business.rating || '4.5'}</Text>
                <Text style={[styles.merchantAddress, theme.secondaryText]}>📍 {business.address || 'Surat'}</Text>
              </View>
              <TouchableOpacity
                onPress={() => navigation.navigate('BusinessDetail', { businessId: business.id })}
                style={styles.viewStoreBtn}
              >
                <Text style={styles.viewStoreBtnText}>View Shop</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}

        {/* Recommended Services Grid */}
        {recommendedServices.length > 0 && (
          <View style={styles.relatedSection}>
            <Text style={[styles.relatedTitle, theme.primaryText]}>Recommended Services</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.horizontalScroll}>
              {recommendedServices.map((rs: any) => (
                <TouchableOpacity
                  key={rs.id}
                  style={[styles.relatedCard, theme.cardBg]}
                  onPress={() => navigation.push('ServiceDetail', { serviceId: rs.id })}
                >
                  <FallbackImage
                    source={rs.image_url ? { uri: rs.image_url } : null}
                    fallbackSource={fallbackImage}
                    style={styles.relatedCardImage}
                    resizeMode="cover"
                  />
                  <View style={styles.relatedCardInfo}>
                    <Text style={[styles.relatedCardName, theme.primaryText]} numberOfLines={1}>{rs.name}</Text>
                    {rs.price_type === "PriceInRange" ? (
                      <Text style={styles.relatedCardPrice} numberOfLines={1}>₹{rs.min_price} - ₹{rs.max_price}</Text>
                    ) : rs.price_type === "FixPrice" ? (
                      <Text style={styles.relatedCardPrice} numberOfLines={1}>₹{rs.price}</Text>
                    ) : (
                      <Text style={[styles.relatedCardContact, theme.secondaryText]} numberOfLines={1}>Contact</Text>
                    )}
                  </View>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        )}
      </ScrollView>
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
    padding: 24,
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
  favBtnHeader: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    marginLeft: 12,
    justifyContent: 'center',
    alignItems: 'center',
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
  scrollContent: {
    paddingHorizontal: 20,
    paddingBottom: 30,
  },
  coverImage: {
    width: '100%',
    height: 200,
    borderRadius: 20,
    backgroundColor: '#FFFFFF',
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  detailCard: {
    borderRadius: 20,
    padding: 20,
    marginBottom: 16,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  categoryLabel: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 4,
  },
  serviceName: {
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 10,
  },
  servicePrice: {
    fontSize: 20,
    fontWeight: '800',
    color: '#10B981',
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  priceContact: {
    fontSize: 15,
    fontWeight: '700',
  },
  sectionCard: {
    borderRadius: 20,
    padding: 20,
    marginBottom: 16,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '800',
    marginBottom: 8,
  },
  descriptionText: {
    fontSize: 14,
    lineHeight: 20,
  },
  merchantCard: {
    borderRadius: 20,
    padding: 20,
    marginBottom: 20,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  merchantHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  merchantInfo: {
    flex: 1,
    marginRight: 10,
  },
  merchantTitle: {
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    color: '#6366F1',
    marginBottom: 2,
  },
  merchantName: {
    fontSize: 16,
    fontWeight: '800',
  },
  merchantRating: {
    fontSize: 12,
    color: '#EAB308',
    fontWeight: '700',
    marginTop: 2,
  },
  merchantAddress: {
    fontSize: 12,
    marginTop: 4,
  },
  viewStoreBtn: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 12,
  },
  viewStoreBtnText: {
    color: '#6366F1',
    fontWeight: '700',
    fontSize: 13,
  },
  relatedSection: {
    marginBottom: 20,
  },
  relatedTitle: {
    fontSize: 16,
    fontWeight: '800',
    marginBottom: 12,
  },
  horizontalScroll: {
    paddingRight: 16,
  },
  relatedCard: {
    width: 140,
    borderRadius: 16,
    marginRight: 12,
    overflow: 'hidden',
    elevation: 2,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  relatedCardImage: {
    width: 140,
    height: 100,
    backgroundColor: '#EEF2FF',
  },
  relatedCardInfo: {
    padding: 10,
  },
  relatedCardName: {
    fontSize: 13,
    fontWeight: '700',
  },
  relatedCardPrice: {
    fontSize: 12,
    fontWeight: '800',
    color: '#10B981',
    marginTop: 2,
  },
  relatedCardContact: {
    fontSize: 11,
    fontWeight: '700',
    marginTop: 2,
  },
  emptyText: {
    textAlign: 'center',
    fontSize: 14,
    marginBottom: 16,
  },
  backBtnError: {
    backgroundColor: '#6366F1',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 12,
  },
  backBtnErrorText: {
    color: '#FFFFFF',
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

export default ServiceDetailScreen;
