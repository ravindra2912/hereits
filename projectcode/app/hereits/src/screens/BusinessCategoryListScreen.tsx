import React, { useEffect, useState } from 'react';
import {
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

export const BusinessCategoryListScreen: React.FC = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const businessId = route.params?.businessId;
  const initialType = route.params?.type || 'Products';

  const [loading, setLoading] = useState(true);
  const [categories, setCategories] = useState<any[]>([]);
  const [activeTab, setActiveTab] = useState<'Products' | 'Services'>(initialType);
  const [businessName, setBusinessName] = useState('');

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  useEffect(() => {
    const loadCategories = async () => {
      setLoading(true);
      const res = await businessService.getBusinessDetail(businessId);
      if (res && res.success && res.data) {
        setBusinessName(res.data.business?.name || 'Business');
        const details = res.data.details || {};
        if (activeTab === 'Products') {
          setCategories(details.productCategories || []);
        } else {
          setCategories(details.serviceCategories || []);
        }
      }
      setLoading(false);
    };

    loadCategories();
  }, [businessId, activeTab]);

  return (
    <View style={[styles.container, theme.background]}>
      {/* Header */}
      <View style={styles.topNav}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
          <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.navTitle, theme.primaryText]} numberOfLines={1}>
          {businessName} - Categories
        </Text>
      </View>

      {/* Tabs */}
      <View style={styles.tabContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'Products' && styles.activeTab]}
          onPress={() => setActiveTab('Products')}
        >
          <Text style={[styles.tabText, activeTab === 'Products' && styles.activeTabText]}>
            Product Categories
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'Services' && styles.activeTab]}
          onPress={() => setActiveTab('Services')}
        >
          <Text style={[styles.tabText, activeTab === 'Services' && styles.activeTabText]}>
            Service Categories
          </Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <View style={styles.listContent}>
          <View style={styles.columnWrapper}>
            <View style={[styles.categoryCard, theme.cardBg]}>
              <Skeleton style={[styles.categoryImage, theme.skeletonBg]} borderRadius={34} />
              <Skeleton style={[theme.skeletonBg, { width: 80, height: 12, marginTop: 4 }]} />
            </View>
            <View style={[styles.categoryCard, theme.cardBg]}>
              <Skeleton style={[styles.categoryImage, theme.skeletonBg]} borderRadius={34} />
              <Skeleton style={[theme.skeletonBg, { width: 80, height: 12, marginTop: 4 }]} />
            </View>
          </View>
          <View style={styles.columnWrapper}>
            <View style={[styles.categoryCard, theme.cardBg]}>
              <Skeleton style={[styles.categoryImage, theme.skeletonBg]} borderRadius={34} />
              <Skeleton style={[theme.skeletonBg, { width: 80, height: 12, marginTop: 4 }]} />
            </View>
            <View style={[styles.categoryCard, theme.cardBg]}>
              <Skeleton style={[styles.categoryImage, theme.skeletonBg]} borderRadius={34} />
              <Skeleton style={[theme.skeletonBg, { width: 80, height: 12, marginTop: 4 }]} />
            </View>
          </View>
        </View>
      ) : (
        <FlatList
          data={categories}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.listContent}
          numColumns={2}
          columnWrapperStyle={styles.columnWrapper}
          renderItem={({ item }) => (
            <TouchableOpacity
              style={[styles.categoryCard, theme.cardBg]}
              onPress={() => {
                if (activeTab === 'Products') {
                  navigation.navigate('BusinessProductList', { businessId: businessId, categoryId: item.id, categoryName: item.name });
                } else {
                  navigation.navigate('BusinessServiceList', { businessId: businessId, categoryId: item.id, categoryName: item.name });
                }
              }}
            >
              <FallbackImage
                source={item.image_url ? { uri: item.image_url } : null}
                fallbackSource={fallbackImage}
                style={styles.categoryImage}
                resizeMode="cover"
              />
              <Text style={[styles.categoryName, theme.primaryText]} numberOfLines={2}>
                {item.name}
              </Text>
            </TouchableOpacity>
          )}
          ListEmptyComponent={
            <Text style={[styles.emptyText, theme.secondaryText]}>No categories available.</Text>
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
  tabContainer: {
    flexDirection: 'row',
    paddingHorizontal: 20,
    marginBottom: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  tab: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
  },
  activeTab: {
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
  listContent: {
    paddingHorizontal: 20,
    paddingBottom: 30,
  },
  columnWrapper: {
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  categoryCard: {
    width: '48%',
    alignItems: 'center',
    paddingVertical: 18,
    paddingHorizontal: 10,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#F1F5F9',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
  },
  categoryImage: {
    width: 68,
    height: 68,
    borderRadius: 34,
    marginBottom: 12,
    backgroundColor: '#EEF2FF',
  },
  categoryName: {
    fontSize: 13,
    fontWeight: '700',
    textAlign: 'center',
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

export default BusinessCategoryListScreen;
