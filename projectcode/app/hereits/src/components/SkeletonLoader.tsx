import React, { useEffect, useRef } from 'react';
import { Animated, View, StyleSheet, ViewStyle } from 'react-native';

interface SkeletonProps {
  style?: ViewStyle | ViewStyle[];
  borderRadius?: number;
}

export const Skeleton: React.FC<SkeletonProps> = ({ style, borderRadius = 8 }) => {
  const pulseValue = useRef(new Animated.Value(0.3)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(pulseValue, {
          toValue: 0.7,
          duration: 800,
          useNativeDriver: true,
        }),
        Animated.timing(pulseValue, {
          toValue: 0.3,
          duration: 800,
          useNativeDriver: true,
        }),
      ])
    );
    animation.start();
    return () => animation.stop();
  }, []);

  return (
    <Animated.View
      style={[
        styles.skeleton,
        { borderRadius, opacity: pulseValue },
        style,
      ]}
    />
  );
};

interface SkeletonCardProps {
  theme: any;
}

// 1. Business list card skeleton (used in HomeScreen featured, BusinessListScreen, etc.)
export const BusinessCardSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.businessCard, theme.cardBg]}>
      <Skeleton style={[styles.bizAvatar, theme.skeletonBg]} borderRadius={14} />
      <View style={styles.bizContent}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '85%', height: 14 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '50%', height: 11, marginTop: 6 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '70%', height: 11, marginTop: 6 }]} />
      </View>
    </View>
  );
};

// 1b. Full-width row business list item skeleton (used in FollowingScreen, search lists, etc.)
export const BusinessListItemSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.businessListItem, theme.cardBg]}>
      <Skeleton style={[styles.bizRowLogo, theme.skeletonBg]} borderRadius={14} />
      <View style={styles.bizRowContent}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '70%', height: 16 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '45%', height: 12, marginTop: 8 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '35%', height: 11, marginTop: 8 }]} />
      </View>
      <Skeleton style={[styles.unfollowBtnSkeleton, theme.skeletonBg]} borderRadius={12} />
    </View>
  );
};

// 2. Category list item skeleton (used in HomeScreen explore)
export const CategoryItemSkeleton: React.FC<SkeletonCardProps> = ({ theme, style }) => {
  return (
    <View style={[styles.categoryCard, theme.cardBg, style]}>
      <Skeleton style={[styles.categoryIconBg, theme.skeletonBg]} borderRadius={23} />
      <Skeleton style={[styles.textLine, theme.skeletonBg, { width: 50, height: 12 }]} />
    </View>
  );
};

// 2b. Category 2-column grid item skeleton (used in BusinessCategoryListScreen)
export const CategoryGridSkeleton: React.FC<SkeletonCardProps> = ({ theme, style }) => {
  return (
    <View style={[styles.categoryGridCard, theme.cardBg, style]}>
      <Skeleton style={[styles.categoryGridIconBg, theme.skeletonBg]} borderRadius={30} />
      <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '60%', height: 12, marginTop: 8 }]} />
    </View>
  );
};

// 3. Product grid item skeleton (used in BusinessDetailScreen products grid, BusinessProductListScreen, etc.)
export const ProductGridSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.productGridCard, theme.cardBg]}>
      <Skeleton style={[styles.productImage, theme.skeletonBg]} borderRadius={14} />
      <View style={styles.productInfo}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '80%', height: 14 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 12, marginTop: 6 }]} />
      </View>
    </View>
  );
};

// 4. Service list item skeleton (used in BusinessDetailScreen services list, BusinessServiceListScreen, etc.)
export const ServiceListItemSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.serviceItemCard, theme.cardBg]}>
      <Skeleton style={[styles.serviceImage, theme.skeletonBg]} borderRadius={12} />
      <View style={styles.serviceItemMain}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '70%', height: 14 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '90%', height: 12, marginTop: 6 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '50%', height: 12, marginTop: 6 }]} />
      </View>
      <Skeleton style={[styles.textLine, theme.skeletonBg, { width: 50, height: 14, alignSelf: 'center' }]} />
    </View>
  );
};

// 5. Specialist/Expert item skeleton (used in BusinessDetailScreen, BusinessSpecialistListScreen)
export const SpecialistItemSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.expertListItem, theme.cardBg]}>
      <Skeleton style={[styles.expertListAvatar, theme.skeletonBg]} borderRadius={28} />
      <View style={styles.expertListInfo}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '50%', height: 14 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '30%', height: 12, marginTop: 6 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '80%', height: 12, marginTop: 6 }]} />
      </View>
    </View>
  );
};

// 6. Business Detail Page skeleton
export const BusinessDetailSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={styles.flex1}>
      {/* Profile Card Skeleton */}
      <View style={[styles.profileCard, theme.cardBg]}>
        <View style={styles.profileHeader}>
          <Skeleton style={[styles.businessLogo, theme.skeletonBg]} borderRadius={34} />
          <View style={styles.titleInfo}>
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '70%', height: 20 }]} />
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 14, marginTop: 8 }]} />
          </View>
        </View>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '90%', height: 12, marginTop: 16 }]} />
        <View style={styles.actionButtonsRow}>
          <Skeleton style={[styles.actionBtn, theme.skeletonBg]} borderRadius={22} />
          <Skeleton style={[styles.actionBtn, theme.skeletonBg]} borderRadius={22} />
          <Skeleton style={[styles.actionBtn, theme.skeletonBg]} borderRadius={22} />
          <Skeleton style={[styles.actionBtn, theme.skeletonBg]} borderRadius={22} />
        </View>
      </View>
      {/* Sections Skeletons */}
      <View style={styles.sectionContainer}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 16, marginBottom: 12 }]} />
        <View style={styles.row}>
          <Skeleton style={[styles.categoryChip, theme.skeletonBg]} borderRadius={14} />
          <Skeleton style={[styles.categoryChip, theme.skeletonBg]} borderRadius={14} />
          <Skeleton style={[styles.categoryChip, theme.skeletonBg]} borderRadius={14} />
        </View>
      </View>
      <View style={styles.sectionContainer}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '30%', height: 16, marginBottom: 12 }]} />
        {Array.from({ length: 4 }).map((_, index) => (
          <ServiceListItemSkeleton theme={theme} />
        ))}
      </View>
    </View>
  );
};

// 7. Appointment list card skeleton
export const AppointmentCardSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.appointmentCard, theme.cardBg]}>
      <View style={styles.appointmentHeader}>
        <View style={styles.flex1}>
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '70%', height: 16 }]} />
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 12, marginTop: 6 }]} />
        </View>
        <Skeleton style={[styles.statusBadge, theme.skeletonBg]} borderRadius={8} />
      </View>
      <View style={styles.appointmentDetails}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '90%', height: 12 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '80%', height: 12, marginTop: 6 }]} />
      </View>
    </View>
  );
};

// 8. Chat list item skeleton
export const ChatListItemSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.chatItem, theme.cardBg]}>
      <Skeleton style={[styles.chatAvatar, theme.skeletonBg]} borderRadius={22} />
      <View style={styles.chatContent}>
        <View style={styles.chatHeader}>
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '50%', height: 14 }]} />
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '20%', height: 12 }]} />
        </View>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '80%', height: 12, marginTop: 6 }]} />
      </View>
    </View>
  );
};
// 9. Order card skeleton (used in OrdersListScreen)
export const OrderCardSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={[styles.orderCard, theme.cardBg]}>
      <View style={styles.orderHeader}>
        <View style={styles.flex1}>
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '60%', height: 16 }]} />
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 12, marginTop: 6 }]} />
        </View>
        <Skeleton style={[styles.statusBadge, theme.skeletonBg]} borderRadius={12} />
      </View>
      <View style={styles.orderBusinessRow}>
        <Skeleton style={[styles.orderImg, theme.skeletonBg]} borderRadius={10} />
        <View style={[styles.flex1, { marginLeft: 12 }]}>
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '70%', height: 14 }]} />
          <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 12, marginTop: 6 }]} />
        </View>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: 60, height: 16 }]} />
      </View>
      <View style={styles.orderFooter}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '50%', height: 12 }]} />
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: 70, height: 12 }]} />
      </View>
    </View>
  );
};

// 10. Order detail page skeleton (used in OrderDetailScreen)
export const OrderDetailSkeleton: React.FC<SkeletonCardProps> = ({ theme }) => {
  return (
    <View style={styles.flex1}>
      <View style={[styles.orderCard, theme.cardBg]}>
        <View style={styles.orderHeader}>
          <View style={styles.flex1}>
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '60%', height: 18 }]} />
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '45%', height: 12, marginTop: 8 }]} />
          </View>
          <Skeleton style={[styles.statusBadge, theme.skeletonBg]} borderRadius={12} />
        </View>
      </View>
      <View style={[styles.orderCard, theme.cardBg]}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 16, marginBottom: 12 }]} />
        <View style={styles.orderBusinessRow}>
          <Skeleton style={[styles.orderImg, theme.skeletonBg]} borderRadius={10} />
          <View style={[styles.flex1, { marginLeft: 12 }]}>
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '60%', height: 15 }]} />
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '40%', height: 12, marginTop: 6 }]} />
          </View>
        </View>
      </View>
      <View style={[styles.orderCard, theme.cardBg]}>
        <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '45%', height: 16, marginBottom: 12 }]} />
        {Array.from({ length: 3 }).map((_, idx) => (
          <View key={idx} style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 10 }}>
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '50%', height: 14 }]} />
            <Skeleton style={[styles.textLine, theme.skeletonBg, { width: '20%', height: 14 }]} />
          </View>
        ))}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  skeleton: {},
  flex1: {
    flex: 1,
  },
  row: {
    flexDirection: 'row',
  },
  textLine: {
    backgroundColor: '#E2E8F0',
  },
  // Business Card styles (matches HomeScreen featured / BusinessListScreen)
  businessCard: {
    width: '48%',
    borderRadius: 16,
    padding: 10,
    marginBottom: 12,
  },
  businessListItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: 16,
    marginBottom: 12,
    width: '100%',
  },
  bizRowLogo: {
    width: 60,
    height: 60,
    marginRight: 14,
    backgroundColor: '#E2E8F0',
  },
  bizRowContent: {
    flex: 1,
    marginRight: 10,
  },
  unfollowBtnSkeleton: {
    width: 76,
    height: 34,
    backgroundColor: '#E2E8F0',
  },
  bizAvatar: {
    width: '100%',
    height: 110,
    backgroundColor: '#E2E8F0',
    marginBottom: 8,
  },
  bizContent: {
    paddingHorizontal: 2,
  },
  bizRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  // Category Card styles
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
    backgroundColor: '#E2E8F0',
    marginBottom: 8,
  },
  // Category Grid Card styles (2-column grid)
  categoryGridCard: {
    width: '48%',
    alignItems: 'center',
    paddingVertical: 18,
    paddingHorizontal: 10,
    borderRadius: 20,
  },
  categoryGridIconBg: {
    width: 60,
    height: 60,
    backgroundColor: '#E2E8F0',
    marginBottom: 8,
  },
  // Product Card styles
  productGridCard: {
    width: '48%',
    borderRadius: 16,
    padding: 12,
    marginBottom: 14,
  },
  productImage: {
    width: '100%',
    height: 120,
    backgroundColor: '#E2E8F0',
  },
  productInfo: {
    marginTop: 8,
  },
  // Service Card styles
  serviceItemCard: {
    flexDirection: 'row',
    padding: 12,
    borderRadius: 16,
    marginBottom: 10,
  },
  serviceImage: {
    width: 60,
    height: 60,
    backgroundColor: '#E2E8F0',
    marginRight: 12,
  },
  serviceItemMain: {
    flex: 1,
    marginRight: 8,
  },
  // Expert Specialist styles
  expertListItem: {
    flexDirection: 'row',
    padding: 12,
    borderRadius: 16,
    marginBottom: 10,
    alignItems: 'center',
  },
  expertListAvatar: {
    width: 56,
    height: 56,
    backgroundColor: '#E2E8F0',
    marginRight: 12,
  },
  expertListInfo: {
    flex: 1,
  },
  // Detail page styles
  profileCard: {
    borderRadius: 20,
    padding: 16,
    marginBottom: 20,
  },
  profileHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  businessLogo: {
    width: 68,
    height: 68,
    backgroundColor: '#E2E8F0',
    marginRight: 16,
  },
  titleInfo: {
    flex: 1,
  },
  actionButtonsRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: 14,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
  },
  actionBtn: {
    width: 44,
    height: 44,
    backgroundColor: '#E2E8F0',
  },
  sectionContainer: {
    marginBottom: 20,
  },
  categoryChip: {
    width: 100,
    height: 38,
    backgroundColor: '#E2E8F0',
    marginRight: 8,
  },
  // Appointment skeleton styles
  appointmentCard: {
    padding: 16,
    borderRadius: 16,
    marginBottom: 12,
  },
  appointmentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
    paddingBottom: 10,
    marginBottom: 10,
  },
  statusBadge: {
    width: 80,
    height: 24,
    backgroundColor: '#E2E8F0',
  },
  appointmentDetails: {
    marginTop: 4,
  },
  // Chat list item styles
  chatItem: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 16,
    marginBottom: 12,
    alignItems: 'center',
  },
  chatAvatar: {
    width: 44,
    height: 44,
    backgroundColor: '#E2E8F0',
    marginRight: 12,
  },
  chatContent: {
    flex: 1,
  },
  chatHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  // Order skeleton styles
  orderCard: {
    borderRadius: 16,
    padding: 16,
    marginBottom: 14,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  orderBusinessRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
  },
  orderImg: {
    width: 44,
    height: 44,
    backgroundColor: '#E2E8F0',
  },
  orderFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
  },
});

export default Skeleton;
