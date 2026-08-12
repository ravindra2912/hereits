import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  SafeAreaView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { authService } from '../services/authService';
import FallbackImage from '../components/FallbackImage';
import { OrderCardSkeleton } from '../components/SkeletonLoader';

const lightTheme = {
  cardBg: { backgroundColor: '#FFFFFF' },
  skeletonBg: { backgroundColor: '#E2E8F0' },
};

export const OrdersListScreen: React.FC = () => {
  const navigation = useNavigation<any>();

  const [orders, setOrders] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  useEffect(() => {
    fetchOrders(1, true);
  }, []);

  const fetchOrders = async (pageNo: number = 1, isInitial: boolean = false) => {
    try {
      setErrorMessage(null);
      if (isInitial) {
        setLoading(true);
      } else {
        setLoadingMore(true);
      }

      const res = await authService.getOrders(pageNo, 10);
      console.log('Orders response:', res);

      if (res && res.success) {
        const rawData = res.data;
        const newOrders = Array.isArray(rawData)
          ? rawData
          : rawData?.data && Array.isArray(rawData.data)
          ? rawData.data
          : [];

        if (pageNo === 1) {
          setOrders(newOrders);
        } else {
          setOrders(prev => [...prev, ...newOrders]);
        }

        if (res.pagination) {
          setHasMore(res.pagination.has_more ?? pageNo < res.pagination.last_page);
        } else if (rawData && rawData.last_page) {
          setHasMore(pageNo < rawData.last_page);
        } else {
          setHasMore(newOrders.length >= 10);
        }
        setPage(pageNo);
      } else {
        if (pageNo === 1) setOrders([]);
        setErrorMessage(res?.message || 'Failed to load orders.');
      }
    } catch (error: any) {
      console.error('Failed to fetch orders:', error);
      setErrorMessage(error?.message || 'An error occurred while loading orders.');
    } finally {
      setLoading(false);
      setRefreshing(false);
      setLoadingMore(false);
    }
  };

  const handleRefresh = () => {
    setRefreshing(true);
    fetchOrders(1, true);
  };

  const handleLoadMore = () => {
    if (!loadingMore && hasMore && !loading && !refreshing) {
      fetchOrders(page + 1);
    }
  };

  const getStatusColor = (status: string) => {
    const s = (status || '').toLowerCase();
    if (s === 'completed' || s === 'delivered') return { bg: '#DCFCE7', text: '#166534' };
    if (s === 'pending') return { bg: '#FEF3C7', text: '#92400E' };
    if (s === 'processing' || s === 'shipped') return { bg: '#DBEAFE', text: '#1E40AF' };
    if (s === 'cancelled' || s === 'canceled' || s === 'rejected') return { bg: '#FEE2E2', text: '#991B1B' };
    return { bg: '#F1F5F9', text: '#475569' };
  };

  const renderOrderItem = ({ item }: { item: any }) => {
    const statusStyle = getStatusColor(item.order_status || 'Pending');
    const itemsCount = item.items?.length || 0;

    return (
      <TouchableOpacity
        style={styles.card}
        activeOpacity={0.8}
        onPress={() => navigation.navigate('OrderDetail', { orderId: item.id, initialOrder: item })}
      >
        <View style={styles.cardHeader}>
          <View style={{ flex: 1 }}>
            <Text style={styles.invoiceNumber}>
              {item.invoice_number || `Order #${item.id}`}
            </Text>
            <Text style={styles.dateText}>
              {item.created_at ? new Date(item.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
              }) : ''}
            </Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: statusStyle.bg }]}>
            <Text style={[styles.statusText, { color: statusStyle.text }]}>
              {(item.order_status || 'Pending').toUpperCase()}
            </Text>
          </View>
        </View>

        <View style={styles.businessRow}>
          <FallbackImage
            source={item.business?.business_image ? { uri: item.business.business_image } : undefined}
            fallbackSource={require('../assets/business_icon.png')}
            style={styles.businessImg}
          />
          <View style={{ flex: 1, marginLeft: 12 }}>
            <Text style={styles.businessName}>
              {item.business?.name || 'Business'}
            </Text>
            <Text style={styles.itemsCount}>
              📦 {itemsCount} {itemsCount === 1 ? 'item' : 'items'}
            </Text>
          </View>
          <Text style={styles.totalAmount}>
            ₹{parseFloat(item.total || 0).toFixed(2)}
          </Text>
        </View>

        <View style={styles.cardFooter}>
          <Text style={styles.paymentMethod}>
            💳 {item.payment_method || 'COD'} • {(item.payment_status || 'Unpaid').toUpperCase()}
          </Text>
          <Text style={styles.viewDetailsBtnText}>View Details ›</Text>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backBtnText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>My Orders</Text>
        <View style={{ width: 32 }} />
      </View>

      {loading && page === 1 ? (
        <View style={styles.listContent}>
          {Array.from({ length: 4 }).map((_, idx) => (
            <OrderCardSkeleton key={idx} theme={lightTheme} />
          ))}
        </View>
      ) : errorMessage ? (
        <View style={styles.centered}>
          <Text style={{ fontSize: 48, marginBottom: 12 }}>⚠️</Text>
          <Text style={styles.emptyTitle}>Error Loading Orders</Text>
          <Text style={styles.emptySubtitle}>{errorMessage}</Text>
          <TouchableOpacity style={styles.retryBtn} onPress={() => fetchOrders(1, true)}>
            <Text style={styles.retryBtnText}>Retry</Text>
          </TouchableOpacity>
        </View>
      ) : orders.length === 0 ? (
        <View style={styles.centered}>
          <Text style={{ fontSize: 48, marginBottom: 12 }}>📦</Text>
          <Text style={styles.emptyTitle}>No Orders Found</Text>
          <Text style={styles.emptySubtitle}>You haven't placed any orders yet.</Text>
        </View>
      ) : (
        <FlatList
          data={orders}
          keyExtractor={item => item.id.toString()}
          renderItem={renderOrderItem}
          contentContainerStyle={styles.listContent}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} colors={['#4F46E5']} />
          }
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.3}
          ListFooterComponent={
            loadingMore ? (
              <View style={styles.footerLoader}>
                <ActivityIndicator size="small" color="#4F46E5" />
              </View>
            ) : null
          }
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8FAFC',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 14,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  backBtn: {
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backBtnText: {
    fontSize: 28,
    color: '#1E293B',
    fontWeight: '600',
    marginTop: -2,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0F172A',
  },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: '#64748B',
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1E293B',
    marginBottom: 6,
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#64748B',
    textAlign: 'center',
  },
  retryBtn: {
    marginTop: 16,
    paddingHorizontal: 20,
    paddingVertical: 10,
    backgroundColor: '#4F46E5',
    borderRadius: 10,
  },
  retryBtnText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  listContent: {
    padding: 16,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 16,
    marginBottom: 14,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  invoiceNumber: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0F172A',
  },
  dateText: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '700',
  },
  businessRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
  },
  businessImg: {
    width: 44,
    height: 44,
    borderRadius: 10,
    backgroundColor: '#F1F5F9',
  },
  businessName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1E293B',
  },
  itemsCount: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  totalAmount: {
    fontSize: 16,
    fontWeight: '700',
    color: '#4F46E5',
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
  },
  paymentMethod: {
    fontSize: 12,
    color: '#64748B',
  },
  viewDetailsBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#4F46E5',
  },
  footerLoader: {
    paddingVertical: 16,
    alignItems: 'center',
  },
});

export default OrdersListScreen;
