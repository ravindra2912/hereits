import React, { useEffect, useState } from 'react';
import {
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  Image,
  Linking,
  Dimensions,
  Platform,
  Share,
  Alert,
} from 'react-native';
import { businessService } from '../services/businessService';
import { chatService } from '../services/chatService';
import { useAuth } from '../context/AuthContext';
import FallbackImage from '../components/FallbackImage';
import { useNavigation, useRoute } from '@react-navigation/native';
import Svg, { Path } from 'react-native-svg';
import { BusinessDetailSkeleton } from '../components/SkeletonLoader';

interface BusinessDetailScreenProps {
  businessId?: number;
  onBack?: () => void;
  onBookAppointment?: (businessId: number, businessName: string) => void;
}

const { width } = Dimensions.get('window');
const fallbackImage = require('../assets/business_icon.png');

export const BusinessDetailScreen: React.FC<BusinessDetailScreenProps> = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const businessId = route.params?.businessId || route.params?.id;
  const { isAuthenticated, setAuthModalVisible } = useAuth();

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const [detailData, setDetailData] = useState<any>(null);
  const [reviews, setReviews] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [isFavorited, setIsFavorited] = useState<boolean>(false);

  useEffect(() => {
    if (detailData?.business) {
      setIsFavorited(!!detailData.business.is_favorited);
    }
  }, [detailData]);

  useEffect(() => {
    const loadDetail = async () => {
      if (!businessId) {
        setLoading(false);
        return;
      }
      setLoading(true);
      const res = await businessService.getBusinessDetail(businessId);
      if (res && res.success && res.data) {
        setDetailData(res.data);
        if (res.data.reviews) {
          setReviews(res.data.reviews);
        }
      } else {
        setDetailData(null);
      }
      setLoading(false);
    };

    loadDetail();
  }, [businessId]);

  if (loading) {
    return (
      <View style={[styles.container, theme.background]}>
        <View style={styles.topNav}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
            <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
          </TouchableOpacity>
          <Text style={[styles.navTitle, theme.primaryText]} numberOfLines={1}>
            Loading...
          </Text>
        </View>
        <ScrollView contentContainerStyle={styles.scrollContent}>
          <BusinessDetailSkeleton theme={theme} />
        </ScrollView>
      </View>
    );
  }

  if (!detailData || !detailData.business) {
    return (
      <View style={[styles.container, theme.background]}>
        <View style={styles.topNav}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={[styles.backBtn, theme.cardBg]}>
            <Text style={[styles.backIcon, theme.primaryText]}>← Back</Text>
          </TouchableOpacity>
          <Text style={[styles.navTitle, theme.primaryText]}>Business Details</Text>
        </View>
        <View style={styles.notFoundContent}>
          <Text style={styles.notFoundIcon}>🏢</Text>
          <Text style={[styles.notFoundTitle, theme.primaryText]}>Business Not Found</Text>
          <Text style={[styles.notFoundSub, theme.secondaryText]}>
            The business you are looking for does not exist or may have been removed.
          </Text>
          <TouchableOpacity
            style={styles.homeBtn}
            onPress={() => navigation.navigate('HomeTab')}
          >
            <Text style={styles.homeBtnText}>Go to Home</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  const {
    business,
    setting,
    isSubscriptionActive,
    experts = [],
    details = {},
    galleries = [],
  } = detailData;

  const productCategories = details.productCategories || [];
  const categoriesWithProducts = details.categoriesWithProducts || [];
  const fallbackProducts = details.products || [];

  const serviceCategories = details.serviceCategories || [];
  const categoriesWithServices = details.categoriesWithServices || [];
  const fallbackServices = details.services || [];

  const totalServicesCount = categoriesWithServices.reduce(
    (acc: number, cat: any) => acc + (cat.services?.length || 0),
    0
  ) || fallbackServices.length;

  const totalProductsCount = categoriesWithProducts.reduce(
    (acc: number, cat: any) => acc + (cat.products?.length || 0),
    0
  ) || fallbackProducts.length;

  const openSocialLink = (url: string) => {
    if (!url) return;
    try {
      Linking.openURL(url);
    } catch (e) {
      console.warn("Could not open social URL:", e);
    }
  };

  const getBusinessLogoSource = () => {
    if (business.business_logo && !business.business_logo.includes('default.png')) {
      return { uri: business.business_logo };
    }
    return fallbackImage;
  };

  const handleCall = () => {
    if (business.contact) {
      Linking.openURL(`tel:${business.contact}`);
    } else {
      Alert.alert("This business doesn't have a contact number.");
    }
  };

  const handleMessage = async () => {
    if (!isAuthenticated) {
      setAuthModalVisible(true);
      return;
    }

    try {
      setLoading(true);
      const res = await chatService.startConversation(business.id);
      setLoading(false);
      if (res && res.success && res.data) {
        navigation.navigate('ChatDetail', {
          conversationId: res.data.id,
          title: business.name,
        });
      } else {
        Alert.alert(res.message || 'Failed to start chat conversation.');
      }
    } catch (e) {
      setLoading(false);
      console.error('Chat error:', e);
      Alert.alert('Failed to connect to chat service.');
    }
  };

  const handleDirections = () => {
    if (business.latitude && business.longitude) {
      const label = encodeURIComponent(business.name);
      const url = Platform.select({
        ios: `maps:0,0?q=${label}@${business.latitude},${business.longitude}`,
        android: `geo:0,0?q=${business.latitude},${business.longitude}(${label})`,
        default: `https://www.google.com/maps/search/?api=1&query=${business.latitude},${business.longitude}`,
      });
      Linking.openURL(url);
    } else {
      Alert.alert("This business doesn't have coordinates registered.");
    }
  };

  const handleShare = async () => {
    try {
      await Share.share({
        message: `Check out ${business.name} on Hereits! Address: ${business.address || 'Surat'}. Contact: ${business.contact || ''}`,
      });
    } catch (error) {
      console.warn('Share error:', error);
    }
  };

  const handleToggleFavorite = async () => {
    if (!isAuthenticated) {
      setAuthModalVisible(true);
      return;
    }

    const prev = isFavorited;
    setIsFavorited(!prev);

    const res = await businessService.toggleFavorite(business.id, 'business', business.id);
    if (!res || !res.success) {
      setIsFavorited(prev);
      Alert.alert('Failed to update favorite status.');
    }
  };

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


        {/* Business Premium Card */}
        <View style={[styles.profileCard, theme.cardBg]}>
          <View style={styles.profileHeader}>
            <View style={styles.logoContainer}>
              <FallbackImage
                source={getBusinessLogoSource()}
                fallbackSource={fallbackImage}
                style={styles.businessLogo}
                resizeMode="cover"
              />
            </View>
            <View style={styles.titleInfo}>
              <View style={styles.badgeRow}>
                {setting?.is_verified && (
                  <View style={styles.verifiedBadge}>
                    <Text style={styles.verifiedBadgeText}>✓ Verified</Text>
                  </View>
                )}
                {isSubscriptionActive && (
                  <View style={styles.premiumBadge}>
                    <Text style={styles.premiumBadgeText}>★ Premium</Text>
                  </View>
                )}
              </View>
              <Text style={[styles.bizName, theme.primaryText]}>{business?.name}</Text>
              <View style={styles.ratingRow}>
                <Text style={styles.ratingStars}>⭐ {business?.rating || '4.5'}</Text>
                <Text style={[styles.categoryLabel, theme.secondaryText]}>
                  • {business?.business_category?.name || 'Local Store'}
                </Text>
              </View>
            </View>
          </View>

          {/* Social Links Row */}
          {(business?.facebook || business?.twitter || business?.instagram || business?.linkedin || business?.youtube) && (
            <View style={styles.socialRow}>
              {business.facebook && (
                <TouchableOpacity style={styles.socialCircle} onPress={() => openSocialLink(business.facebook)}>
                  <Text style={styles.socialText}>🔵</Text>
                </TouchableOpacity>
              )}
              {business.instagram && (
                <TouchableOpacity style={styles.socialCircle} onPress={() => openSocialLink(business.instagram)}>
                  <Text style={styles.socialText}>📸</Text>
                </TouchableOpacity>
              )}
              {business.twitter && (
                <TouchableOpacity style={styles.socialCircle} onPress={() => openSocialLink(business.twitter)}>
                  <Text style={styles.socialText}>🐦</Text>
                </TouchableOpacity>
              )}
              {business.linkedin && (
                <TouchableOpacity style={styles.socialCircle} onPress={() => openSocialLink(business.linkedin)}>
                  <Text style={styles.socialText}>💼</Text>
                </TouchableOpacity>
              )}
              {business.youtube && (
                <TouchableOpacity style={styles.socialCircle} onPress={() => openSocialLink(business.youtube)}>
                  <Text style={styles.socialText}>📺</Text>
                </TouchableOpacity>
              )}
            </View>
          )}

          <Text style={[styles.bizAddress, theme.secondaryText]}>
            📍 {business?.address || 'Vesu Surat'}
          </Text>

          {/* Follow Button (uses toggleFavorite API) */}
          <TouchableOpacity
            style={[styles.followBtn, isFavorited ? styles.followingBtn : styles.followBtnActive]}
            onPress={handleToggleFavorite}
            activeOpacity={0.8}
          >
            <Text style={[styles.followBtnText, isFavorited ? styles.followingBtnText : styles.followBtnActiveText]}>
              {isFavorited ? 'Following' : 'Follow'}
            </Text>
          </TouchableOpacity>

          <View style={styles.actionButtonsRow}>
            <TouchableOpacity style={[styles.actionBtn, theme.buttonCircleBg]} onPress={handleCall}>
              <Svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke={isDarkMode ? '#F8FAFC' : '#6366F1'} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <Path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
              </Svg>
            </TouchableOpacity>

            <TouchableOpacity style={[styles.actionBtn, theme.buttonCircleBg]} onPress={handleMessage}>
              <Svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke={isDarkMode ? '#F8FAFC' : '#6366F1'} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <Path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
              </Svg>
            </TouchableOpacity>

            <TouchableOpacity style={[styles.actionBtn, theme.buttonCircleBg]} onPress={handleDirections}>
              <Svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke={isDarkMode ? '#F8FAFC' : '#6366F1'} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <Path d="M3 11l19-9-9 19-2-8-8-2z" />
              </Svg>
            </TouchableOpacity>

            <TouchableOpacity style={[styles.actionBtn, theme.buttonCircleBg]} onPress={handleShare}>
              <Svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke={isDarkMode ? '#F8FAFC' : '#6366F1'} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <Path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                <Path d="M16 6l-4-4-4 4" />
                <Path d="M12 2v13" />
              </Svg>
            </TouchableOpacity>
          </View>
        </View>



        {/* 1. Product Categories */}
        {productCategories.length > 0 && (
          <View style={styles.sectionContainer}>
            <View style={styles.sectionHeaderRow}>
              <Text style={[styles.sectionTitle, theme.primaryText]}>Product Categories</Text>
              <TouchableOpacity onPress={() => navigation.navigate('BusinessCategoryList', { businessId: business.id, type: 'Products' })}>
                <Text style={styles.viewMoreText}>View More →</Text>
              </TouchableOpacity>
            </View>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.horizontalScroll}>
              {productCategories.map((cat: any) => (
                <TouchableOpacity
                  key={cat.id}
                  style={[styles.categoryChip, theme.cardBg]}
                  onPress={() => navigation.navigate('BusinessProductList', { businessId: business.id, categoryId: cat.id, categoryName: cat.name })}
                >
                  <FallbackImage
                    source={cat.image_url ? { uri: cat.image_url } : null}
                    type="business"
                    fallbackSource={fallbackImage}
                    style={styles.categoryChipImage}
                    resizeMode="cover"
                  />
                  <Text style={[styles.categoryChipText, theme.primaryText]} numberOfLines={1}>{cat.name}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        )}

        {/* 2. Service Categories */}
        {serviceCategories.length > 0 && (
          <View style={styles.sectionContainer}>
            <View style={styles.sectionHeaderRow}>
              <Text style={[styles.sectionTitle, theme.primaryText]}>Service Categories</Text>
              <TouchableOpacity onPress={() => navigation.navigate('BusinessCategoryList', { businessId: business.id, type: 'Services' })}>
                <Text style={styles.viewMoreText}>View More →</Text>
              </TouchableOpacity>
            </View>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.horizontalScroll}>
              {serviceCategories.map((cat: any) => (
                <TouchableOpacity
                  key={cat.id}
                  style={[styles.categoryChip, theme.cardBg]}
                  onPress={() => navigation.navigate('BusinessServiceList', { businessId: business.id, categoryId: cat.id, categoryName: cat.name })}
                >
                  <FallbackImage
                    source={cat.image_url ? { uri: cat.image_url } : null}
                    type="business"
                    fallbackSource={fallbackImage}
                    style={styles.categoryChipImage}
                    resizeMode="cover"
                  />
                  <Text style={[styles.categoryChipText, theme.primaryText]} numberOfLines={1}>{cat.name}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        )}

        {/* 3. Products */}
        {fallbackProducts.length > 0 && (
          <View style={styles.sectionContainer}>
            <View style={styles.sectionHeaderRow}>
              <Text style={[styles.sectionTitle, theme.primaryText]}>Products</Text>
              <TouchableOpacity onPress={() => navigation.navigate('BusinessProductList', { businessId: business.id })}>
                <Text style={styles.viewMoreText}>View More →</Text>
              </TouchableOpacity>
            </View>
            <View style={styles.productsGrid}>
              {fallbackProducts.map((p: any) => (
                <TouchableOpacity
                  key={p.id}
                  style={[styles.productGridCard, theme.cardBg]}
                  onPress={() => navigation.navigate('ProductDetail', { productId: p.id })}
                >
                  <FallbackImage
                    source={p.first_image?.image_url ? { uri: p.first_image.image_url } : null}
                    type="product"
                    fallbackSource={fallbackImage}
                    style={styles.productImage}
                    resizeMode="cover"
                  />
                  <View style={styles.productInfo}>
                    <Text style={[styles.itemName, theme.primaryText]} numberOfLines={1}>{p.name}</Text>
                    {p.price_type === "PriceInRange" && (
                      <Text style={styles.itemPrice}>₹{p.min_price} - ₹{p.max_price}</Text>
                    )}
                    {p.price_type === "FixPrice" && (
                      <View style={styles.priceRow}>
                        <Text style={styles.itemPrice}>₹{p.sell_price || p.price}</Text>
                        {p.price && p.price !== p.sell_price && p.sell_price && (
                          <Text style={[styles.originalPrice, theme.secondaryText]}>₹{p.price}</Text>
                        )}
                      </View>
                    )}
                    {p.price_type !== "PriceInRange" && p.price_type !== "FixPrice" && (
                      <Text style={styles.itemPrice}>₹{p.price || '0'}</Text>
                    )}
                  </View>
                </TouchableOpacity>
              ))}
            </View>
          </View>
        )}

        {/* 4. Services */}
        {fallbackServices.length > 0 && (
          <View style={styles.sectionContainer}>
            <View style={styles.sectionHeaderRow}>
              <Text style={[styles.sectionTitle, theme.primaryText]}>Services</Text>
              <TouchableOpacity onPress={() => navigation.navigate('BusinessServiceList', { businessId: business.id })}>
                <Text style={styles.viewMoreText}>View More →</Text>
              </TouchableOpacity>
            </View>
            {fallbackServices.map((s: any) => (
              <TouchableOpacity
                key={s.id}
                style={[styles.itemCard, theme.cardBg]}
                onPress={() => navigation.navigate('ServiceDetail', { serviceId: s.id })}
              >
                <View style={styles.serviceImageContainer}>
                  <FallbackImage
                    source={s.image_url ? { uri: s.image_url } : null}
                    type="service"
                    fallbackSource={fallbackImage}
                    style={styles.serviceImage}
                    resizeMode="cover"
                  />
                </View>
                <View style={styles.itemMain}>
                  <Text style={[styles.itemName, theme.primaryText]}>{s.name}</Text>
                  <Text style={[styles.itemDesc, theme.secondaryText]} numberOfLines={2}>{s.description}</Text>
                </View>
                <View style={styles.pricingCol}>
                  {s.price_type === "PriceInRange" && (
                    <Text style={styles.itemPrice}>₹{s.min_price} - ₹{s.max_price}</Text>
                  )}
                  {s.price_type === "FixPrice" && (
                    <Text style={styles.itemPrice}>₹{s.price || '0'}</Text>
                  )}
                  {s.price_type === "WithoutPrice" && (
                    <Text style={[styles.priceType, theme.secondaryText]}>Contact for Price</Text>
                  )}
                </View>
              </TouchableOpacity>
            ))}
          </View>
        )}

        {/* 5. Categories with Products */}
        {categoriesWithProducts.length > 0 && (
          <View style={styles.sectionContainer}>
            {categoriesWithProducts.map((cat: any) => (
              <View key={cat.id} style={styles.categorySection}>
                <View style={styles.sectionHeaderRow}>
                  <Text style={[styles.categoryHeader, theme.primaryText]}>{cat.name}</Text>
                  <TouchableOpacity onPress={() => navigation.navigate('BusinessProductList', { businessId: business.id, categoryId: cat.id, categoryName: cat.name })}>
                    <Text style={styles.viewMoreText}>View More →</Text>
                  </TouchableOpacity>
                </View>
                <View style={styles.productsGrid}>
                  {cat.products?.map((p: any) => (
                    <TouchableOpacity
                      key={p.id}
                      style={[styles.productGridCard, theme.cardBg]}
                      onPress={() => navigation.navigate('ProductDetail', { productId: p.id })}
                    >
                      <FallbackImage
                        source={p.first_image?.image_url ? { uri: p.first_image.image_url } : null}
                        type="product"
                        fallbackSource={fallbackImage}
                        style={styles.productImage}
                        resizeMode="cover"
                      />
                      <View style={styles.productInfo}>
                        <Text style={[styles.itemName, theme.primaryText]} numberOfLines={1}>{p.name}</Text>
                        {p.price_type === "PriceInRange" && (
                          <Text style={styles.itemPrice}>₹{p.min_price} - ₹{p.max_price}</Text>
                        )}
                        {p.price_type === "FixPrice" && (
                          <View style={styles.priceRow}>
                            <Text style={styles.itemPrice}>₹{p.sell_price}</Text>
                            {p.price && p.price !== p.sell_price && (
                              <Text style={[styles.originalPrice, theme.secondaryText]}>₹{p.price}</Text>
                            )}
                          </View>
                        )}
                        {p.price_type === "WithoutPrice" && (
                          <Text style={[styles.priceContact, theme.secondaryText]}>Contact for Price</Text>
                        )}
                      </View>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>
            ))}
          </View>
        )}

        {/* 6. Categories with Services */}
        {categoriesWithServices.length > 0 && (
          <View style={styles.sectionContainer}>
            {categoriesWithServices.map((cat: any) => (
              <View key={cat.id} style={styles.categorySection}>
                <View style={styles.sectionHeaderRow}>
                  <Text style={[styles.categoryHeader, theme.primaryText]}>{cat.name}</Text>
                  <TouchableOpacity onPress={() => navigation.navigate('BusinessServiceList', { businessId: business.id, categoryId: cat.id, categoryName: cat.name })}>
                    <Text style={styles.viewMoreText}>View More →</Text>
                  </TouchableOpacity>
                </View>
                {cat.services?.map((s: any) => (
                  <TouchableOpacity
                    key={s.id}
                    style={[styles.itemCard, theme.cardBg]}
                    onPress={() => navigation.navigate('ServiceDetail', { serviceId: s.id })}
                  >
                    <View style={styles.serviceImageContainer}>
                      <FallbackImage
                        source={s.image_url ? { uri: s.image_url } : null}
                        type="service"
                        fallbackSource={fallbackImage}
                        style={styles.serviceImage}
                        resizeMode="cover"
                      />
                    </View>
                    <View style={styles.itemMain}>
                      <Text style={[styles.itemName, theme.primaryText]}>{s.name}</Text>
                      <Text style={[styles.itemDesc, theme.secondaryText]} numberOfLines={2}>{s.description}</Text>
                    </View>
                    <View style={styles.pricingCol}>
                      {s.price_type === "PriceInRange" && (
                        <Text style={styles.itemPrice}>₹{s.min_price} - ₹{s.max_price}</Text>
                      )}
                      {s.price_type === "FixPrice" && (
                        <Text style={styles.itemPrice}>₹{s.price || '0'}</Text>
                      )}
                      {s.price_type === "WithoutPrice" && (
                        <Text style={[styles.priceType, theme.secondaryText]}>Contact for Price</Text>
                      )}
                    </View>
                  </TouchableOpacity>
                ))}
              </View>
            ))}
          </View>
        )}

        {/* 7. Experts */}
        {experts.length > 0 && (
          <View style={styles.sectionContainer}>
            <View style={styles.sectionHeaderRow}>
              <Text style={[styles.sectionTitle, theme.primaryText]}>Specialists ({experts.length})</Text>
              <TouchableOpacity onPress={() => navigation.navigate('BusinessSpecialistList', { businessId: business.id })}>
                <Text style={styles.viewMoreText}>View More →</Text>
              </TouchableOpacity>
            </View>
            {experts.map((exp: any) => (
              <TouchableOpacity
                key={exp.id}
                style={[styles.expertListItem, theme.cardBg]}
                onPress={() => navigation.navigate('SpecialistDetail', { specialistId: exp.id })}
                activeOpacity={0.8}
              >
                <FallbackImage
                  source={exp.expert_image ? { uri: exp.expert_image } : null}
                  type="specialist"
                  fallbackSource={fallbackImage}
                  style={styles.expertListAvatar}
                  resizeMode="cover"
                />
                <View style={styles.expertListInfo}>
                  <Text style={[styles.expertListName, theme.primaryText]}>{exp.expert_name}</Text>
                  <Text style={[styles.expertListTitle, theme.secondaryText]}>
                    {exp.department?.department_name || exp.title || 'Specialist'}
                  </Text>
                  {exp.description ? (
                    <Text style={[styles.expertListDesc, theme.secondaryText]} numberOfLines={2}>
                      {exp.description}
                    </Text>
                  ) : null}
                </View>
                {exp.rating > 0 && (
                  <View style={styles.expertListRatingCol}>
                    <Text style={styles.expertListRating}>⭐ {exp.rating}</Text>
                  </View>
                )}
              </TouchableOpacity>
            ))}
          </View>
        )}

        {/* 8. Galleries */}
        {galleries.length > 0 && (
          <View style={styles.sectionContainer}>
            <Text style={[styles.sectionTitle, theme.primaryText]}>Gallery ({galleries.length})</Text>
            <View style={styles.galleryGrid}>
              {galleries.map((img: any) => (
                <View key={img.id} style={styles.galleryWrapper}>
                  <FallbackImage
                    source={img.image_url ? { uri: img.image_url } : null}
                    type="business"
                    fallbackSource={fallbackImage}
                    style={styles.galleryImg}
                    resizeMode="cover"
                  />
                </View>
              ))}
            </View>
          </View>
        )}

        {/* 9. About Us */}
        <View style={styles.sectionContainer}>
          <Text style={[styles.sectionTitle, theme.primaryText]}>About Us</Text>
          <View style={[styles.aboutCard, theme.cardBg]}>
            {business?.seo_description ? (
              <Text style={[styles.aboutDesc, theme.primaryText]}>
                {business.seo_description}
              </Text>
            ) : (
              <Text style={[styles.aboutDesc, theme.secondaryText]}>
                Welcome to {business?.name || 'our business'}. We offer the best products and services nearby.
              </Text>
            )}
            <View style={styles.aboutMeta}>
              <Text style={[styles.aboutMetaItem, theme.secondaryText]}>
                📍 <Text style={theme.primaryText}>{business?.address || 'Vesu Surat'}</Text>
              </Text>
              {business?.contact ? (
                <Text style={[styles.aboutMetaItem, theme.secondaryText]}>
                  📞 <Text style={theme.primaryText}>{business.contact}</Text>
                </Text>
              ) : null}
            </View>
          </View>
        </View>

        {/* 10. Reviews */}
        {reviews.length > 0 && (
          <View style={styles.sectionContainer}>
            <Text style={[styles.sectionTitle, theme.primaryText]}>Reviews ({reviews.length})</Text>
            {reviews.map(r => (
              <View key={r.id} style={[styles.itemCard, theme.cardBg]}>
                <View style={styles.itemMain}>
                  <Text style={[styles.itemName, theme.primaryText]}>
                    {r.user?.first_name} ⭐ {r.rating}/5
                  </Text>
                  <Text style={[styles.itemDesc, theme.secondaryText]}>{r.review}</Text>
                </View>
              </View>
            ))}
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
  followBtn: {
    width: '100%',
    paddingVertical: 12,
    paddingHorizontal: 18,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 12,
    borderWidth: 1.5,
  },
  followBtnActive: {
    backgroundColor: '#6366F1',
    borderColor: '#6366F1',
  },
  followingBtn: {
    backgroundColor: 'transparent',
    borderColor: '#6366F1',
  },
  followBtnText: {
    fontSize: 14,
    fontWeight: '700',
    textAlign: 'center',
  },
  followBtnActiveText: {
    color: '#FFFFFF',
  },
  followingBtnText: {
    color: '#6366F1',
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

  profileCard: {
    borderRadius: 20,
    padding: 16,
    marginBottom: 20,
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
  },
  profileHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  logoContainer: {
    width: 68,
    height: 68,
    borderRadius: 34,
    overflow: 'hidden',
    marginRight: 16,
    backgroundColor: '#EEF2FF',
    elevation: 2,
  },
  businessLogo: {
    width: 68,
    height: 68,
  },
  titleInfo: {
    flex: 1,
  },
  badgeRow: {
    flexDirection: 'row',
    marginBottom: 4,
  },
  verifiedBadge: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
    marginRight: 6,
  },
  verifiedBadgeText: {
    color: '#6366F1',
    fontSize: 11,
    fontWeight: '700',
  },
  premiumBadge: {
    backgroundColor: '#FEF3C7',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
  },
  premiumBadgeText: {
    color: '#D97706',
    fontSize: 11,
    fontWeight: '700',
  },
  bizName: {
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 4,
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  ratingStars: {
    fontSize: 13,
    fontWeight: '700',
    color: '#EAB308',
  },
  categoryLabel: {
    fontSize: 13,
    marginLeft: 6,
  },
  socialRow: {
    flexDirection: 'row',
    marginTop: 14,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
  },
  socialCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
    backgroundColor: '#EEF2FF',
  },
  socialText: {
    fontSize: 14,
  },
  bizAddress: {
    fontSize: 13,
    marginTop: 12,
  },
  actionButtonsRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: 16,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
  },
  actionBtn: {
    width: 48,
    height: 48,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
    elevation: 2,
    shadowColor: '#6366F1',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  sectionContainer: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '800',
    marginBottom: 10,
  },
  horizontalScroll: {
    paddingRight: 16,
  },
  expertListItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  expertListAvatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
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
  categorySection: {
    marginBottom: 18,
  },
  categoryHeader: {
    fontSize: 15,
    fontWeight: '800',
    marginBottom: 10,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  itemCard: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderRadius: 14,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#F1F5F9',
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
  pricingCol: {
    alignItems: 'flex-end',
  },
  itemPrice: {
    fontSize: 15,
    fontWeight: '800',
    color: '#10B981',
  },
  priceType: {
    fontSize: 10,
    marginTop: 2,
  },
  productsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  productGridCard: {
    width: '48%',
    borderRadius: 16,
    overflow: 'hidden',
    marginBottom: 12,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  productImage: {
    width: '100%',
    height: 110,
    backgroundColor: '#EEF2FF',
  },
  productInfo: {
    padding: 10,
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
    marginTop: 4,
  },
  originalPrice: {
    fontSize: 12,
    textDecorationLine: 'line-through',
    marginLeft: 6,
  },
  priceContact: {
    fontSize: 12,
    marginTop: 4,
    fontWeight: '600',
  },
  galleryGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  galleryWrapper: {
    width: '31.3%',
    height: 90,
    margin: '1%',
    borderRadius: 10,
    overflow: 'hidden',
  },
  galleryImg: {
    width: '100%',
    height: '100%',
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
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  viewMoreText: {
    fontSize: 13,
    color: '#6366F1',
    fontWeight: '700',
  },
  categoryChip: {
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 5,
    borderRadius: 16,
    marginRight: 4,
    width: 90,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  categoryChipImage: {
    width: 60,
    height: 60,
    // borderRadius: 28,
    marginBottom: 8,
  },
  categoryChipText: {
    fontSize: 11,
    fontWeight: '600',
    textAlign: 'center',
  },
  aboutCard: {
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  aboutDesc: {
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 12,
  },
  aboutMeta: {
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
    paddingTop: 12,
  },
  aboutMetaItem: {
    fontSize: 13,
    marginBottom: 6,
  },
  notFoundContent: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 24,
    paddingBottom: 60,
  },
  notFoundIcon: {
    fontSize: 54,
    marginBottom: 16,
  },
  notFoundTitle: {
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 8,
    textAlign: 'center',
  },
  notFoundSub: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 24,
  },
  homeBtn: {
    backgroundColor: '#6366F1',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 14,
  },
  homeBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
  },
});

const lightTheme = StyleSheet.create({
  background: { backgroundColor: '#F8FAFC' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
  cardBg: { backgroundColor: '#FFFFFF' },
  buttonCircleBg: { backgroundColor: '#EEF2FF' },
  skeletonBg: { backgroundColor: '#E2E8F0' },
});

const darkTheme = StyleSheet.create({
  background: { backgroundColor: '#0F172A' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
  buttonCircleBg: { backgroundColor: '#334155' },
  skeletonBg: { backgroundColor: '#334155' },
});

export default BusinessDetailScreen;
