import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  useColorScheme,
  View,
  Image,
} from 'react-native';
import { businessService } from '../services/businessService';
import FallbackImage from '../components/FallbackImage';
import { useNavigation, useRoute } from '@react-navigation/native';

interface BusinessDetailScreenProps {
  businessId?: number;
  onBack?: () => void;
  onBookAppointment?: (businessId: number, businessName: string) => void;
}

const fallbackImage = require('../assets/business_icon.png');

export const BusinessDetailScreen: React.FC<BusinessDetailScreenProps> = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const businessId = route.params?.businessId;

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const [business, setBusiness] = useState<any>(null);
  const [services, setServices] = useState<any[]>([]);
  const [products, setProducts] = useState<any[]>([]);
  const [reviews, setReviews] = useState<any[]>([]);
  const [activeTab, setActiveTab] = useState<'services' | 'products' | 'reviews'>('services');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadDetail = async () => {
      setLoading(true);
      const bRes = await businessService.getBusinessDetail(businessId);
      if (bRes.success && bRes.data) {
        setBusiness(bRes.data);
      }
      const sRes = await businessService.getServices(businessId);
      if (sRes.success && sRes.data) setServices(sRes.data);

      const pRes = await businessService.getProducts(businessId);
      if (pRes.success && pRes.data) setProducts(pRes.data);

      const rRes = await businessService.getReviews(businessId);
      if (rRes.success && rRes.data) setReviews(rRes.data);

      setLoading(false);
    };

    loadDetail();
  }, [businessId]);

  if (loading) {
    return (
      <View style={[styles.loadingCenter, theme.background]}>
        <ActivityIndicator size="large" color="#6366F1" />
      </View>
    );
  }

  return (
    <View style={[styles.container, theme.background]}>
      {/* Top Navigation Bar */}
      <View style={styles.topNav}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
          <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.navTitle, theme.primaryText]} numberOfLines={1}>
          {business?.name || 'Business Detail'}
        </Text>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Hero Banner Header */}
        <View style={styles.heroCard}>
          <View style={styles.heroAvatar}>
            <FallbackImage
              source={business?.business_logo || business?.business_image ? { uri: business.business_logo || business.business_image } : null}
              fallbackSource={fallbackImage}
              style={styles.heroAvatarImage}
              resizeMode="cover"
            />
          </View>
          <Text style={styles.heroName}>{business?.name}</Text>
          <Text style={styles.heroCategory}>
            {business?.business_category?.name || 'Local Store'}
          </Text>
          <Text style={styles.heroAddress}>📍 {business?.address || 'Location'}</Text>
        </View>

        {/* Action Tabs Header */}
        <View style={styles.tabsHeader}>
          <TouchableOpacity
            onPress={() => setActiveTab('services')}
            style={[styles.tabItem, activeTab === 'services' && styles.activeTabItem]}
          >
            <Text style={[styles.tabText, activeTab === 'services' && styles.activeTabText]}>
              Services ({services.length})
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            onPress={() => setActiveTab('products')}
            style={[styles.tabItem, activeTab === 'products' && styles.activeTabItem]}
          >
            <Text style={[styles.tabText, activeTab === 'products' && styles.activeTabText]}>
              Products ({products.length})
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            onPress={() => setActiveTab('reviews')}
            style={[styles.tabItem, activeTab === 'reviews' && styles.activeTabItem]}
          >
            <Text style={[styles.tabText, activeTab === 'reviews' && styles.activeTabText]}>
              Reviews ({reviews.length})
            </Text>
          </TouchableOpacity>
        </View>

        {/* Tab Content Rendering */}
        {activeTab === 'services' && (
          <View style={styles.tabContentSection}>
            {services.length === 0 ? (
              <Text style={[styles.emptyText, theme.secondaryText]}>No services listed.</Text>
            ) : (
              services.map(s => (
                <View key={s.id} style={[styles.itemCard, theme.cardBg]}>
                  <View style={styles.itemMain}>
                    <Text style={[styles.itemName, theme.primaryText]}>{s.name}</Text>
                    <Text style={[styles.itemDesc, theme.secondaryText]}>{s.description}</Text>
                  </View>
                  <Text style={styles.itemPrice}>₹{s.price || s.charge_amount || '0'}</Text>
                </View>
              ))
            )}
          </View>
        )}

        {activeTab === 'products' && (
          <View style={styles.tabContentSection}>
            {products.length === 0 ? (
              <Text style={[styles.emptyText, theme.secondaryText]}>No products listed.</Text>
            ) : (
              products.map(p => (
                <View key={p.id} style={[styles.itemCard, theme.cardBg]}>
                  <View style={styles.itemMain}>
                    <Text style={[styles.itemName, theme.primaryText]}>{p.name}</Text>
                    <Text style={[styles.itemDesc, theme.secondaryText]}>{p.description}</Text>
                  </View>
                  <Text style={styles.itemPrice}>₹{p.price || '0'}</Text>
                </View>
              ))
            )}
          </View>
        )}

        {activeTab === 'reviews' && (
          <View style={styles.tabContentSection}>
            {reviews.length === 0 ? (
              <Text style={[styles.emptyText, theme.secondaryText]}>No reviews yet.</Text>
            ) : (
              reviews.map(r => (
                <View key={r.id} style={[styles.itemCard, theme.cardBg]}>
                  <View style={styles.itemMain}>
                    <Text style={[styles.itemName, theme.primaryText]}>
                      {r.user?.first_name} ⭐ {r.rating}/5
                    </Text>
                    <Text style={[styles.itemDesc, theme.secondaryText]}>{r.review_text}</Text>
                  </View>
                </View>
              ))
            )}
          </View>
        )}
      </ScrollView>

      {/* Floating CTA for Appointment */}
      <View style={styles.footerCta}>
        <TouchableOpacity
          onPress={() =>
            navigation.navigate('Main', {
              screen: 'BookingsTab',
              params: { businessId: businessId, businessName: business?.name || 'Business' },
            })
          }
          style={styles.ctaButton}
        >
          <Text style={styles.ctaText}>📅 Book Appointment</Text>
        </TouchableOpacity>
      </View>
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
  scrollContent: {
    paddingHorizontal: 20,
    paddingBottom: 100,
  },
  heroCard: {
    backgroundColor: '#6366F1',
    borderRadius: 20,
    padding: 24,
    alignItems: 'center',
    marginBottom: 20,
  },
  heroAvatar: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  heroAvatarImage: {
    width: 64,
    height: 64,
    borderRadius: 32,
  },
  heroName: {
    fontSize: 22,
    fontWeight: '800',
    color: '#FFFFFF',
  },
  heroCategory: {
    fontSize: 14,
    color: '#C7D2FE',
    marginTop: 2,
  },
  heroAddress: {
    fontSize: 13,
    color: '#EEF2FF',
    marginTop: 6,
  },
  tabsHeader: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
    marginBottom: 16,
  },
  tabItem: {
    paddingVertical: 10,
    marginRight: 20,
  },
  activeTabItem: {
    borderBottomWidth: 2,
    borderBottomColor: '#6366F1',
  },
  tabText: {
    fontSize: 14,
    color: '#64748B',
    fontWeight: '600',
  },
  activeTabText: {
    color: '#6366F1',
    fontWeight: '700',
  },
  tabContentSection: {
    paddingTop: 4,
  },
  itemCard: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderRadius: 14,
    marginBottom: 10,
  },
  itemMain: {
    flex: 1,
    marginRight: 12,
  },
  itemName: {
    fontSize: 15,
    fontWeight: '700',
  },
  itemDesc: {
    fontSize: 13,
    marginTop: 2,
  },
  itemPrice: {
    fontSize: 15,
    fontWeight: '800',
    color: '#10B981',
  },
  emptyText: {
    textAlign: 'center',
    marginTop: 20,
    fontSize: 14,
  },
  footerCta: {
    position: 'absolute',
    bottom: 20,
    left: 20,
    right: 20,
  },
  ctaButton: {
    backgroundColor: '#6366F1',
    borderRadius: 16,
    height: 54,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#6366F1',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
    elevation: 6,
  },
  ctaText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '800',
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

export default BusinessDetailScreen;
