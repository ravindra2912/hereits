import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useNavigation, useRoute } from '@react-navigation/native';
import { authService } from '../services/authService';
import FallbackImage from '../components/FallbackImage';
import { OrderDetailSkeleton } from '../components/SkeletonLoader';

const lightTheme = {
  cardBg: { backgroundColor: '#FFFFFF' },
  skeletonBg: { backgroundColor: '#E2E8F0' },
};

export const OrderDetailScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const route = useRoute<any>();

  const { orderId, initialOrder } = route.params || {};

  const [order, setOrder] = useState<any>(initialOrder || null);
  const [loading, setLoading] = useState(!initialOrder);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  // Review states
  const [rating, setRating] = useState(5);
  const [reviewText, setReviewText] = useState('');
  const [submittingReview, setSubmittingReview] = useState(false);

  useEffect(() => {
    if (orderId) {
      fetchOrderDetails();
    }
  }, [orderId]);

  const fetchOrderDetails = async () => {
    try {
      setErrorMessage(null);
      if (!order) setLoading(true);
      const res = await authService.getOrderDetails(orderId);
      console.log('Order detail response:', res);
      if (res && res.success && res.data) {
        setOrder(res.data);
      } else if (!order) {
        setErrorMessage(res?.message || 'Order details not found.');
      }
    } catch (error: any) {
      console.error('Failed to fetch order details:', error);
      if (!order) {
        setErrorMessage(error?.message || 'Error loading order details.');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleSubmitReview = async () => {
    if (!reviewText.trim()) {
      Alert.alert('Required', 'Please enter your review comments.');
      return;
    }

    try {
      setSubmittingReview(true);
      const res = await authService.submitOrderReview({
        business_id: order.business_id,
        order_id: order.id,
        rating,
        review: reviewText.trim(),
      });

      if (res && res.success) {
        Alert.alert('Thank You!', 'Your review has been submitted successfully.');
        setOrder((prev: any) => ({
          ...prev,
          user_review: res.data,
        }));
      } else {
        Alert.alert('Error', res?.message || 'Failed to submit review.');
      }
    } catch (error: any) {
      Alert.alert('Error', error?.message || 'Something went wrong submitting review.');
    } finally {
      setSubmittingReview(false);
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

  if (loading && !order) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
            <Text style={styles.backBtnText}>‹</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Order Details</Text>
          <View style={{ width: 32 }} />
        </View>
        <ScrollView contentContainerStyle={styles.content}>
          <OrderDetailSkeleton theme={lightTheme} />
        </ScrollView>
      </SafeAreaView>
    );
  }

  if (!order) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
            <Text style={styles.backBtnText}>‹</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Order Details</Text>
          <View style={{ width: 32 }} />
        </View>
        <View style={styles.centered}>
          <Text style={{ fontSize: 48, marginBottom: 12 }}>⚠️</Text>
          <Text style={styles.emptyTitle}>{errorMessage || 'Order Not Found'}</Text>
          <TouchableOpacity style={styles.retryBtn} onPress={fetchOrderDetails}>
            <Text style={styles.retryBtnText}>Retry</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  const statusStyle = getStatusColor(order.order_status || 'Pending');
  const items = order.items || [];
  const isDelivered = (order.order_status || '').toLowerCase() === 'delivered';

  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.backBtnText}>‹</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Order #{order.id}</Text>
        <View style={{ width: 32 }} />
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        {/* Status Card */}
        <View style={styles.card}>
          <View style={styles.statusRow}>
            <View style={{ flex: 1 }}>
              <Text style={styles.invoiceText}>
                {order.invoice_number || `Order #${order.id}`}
              </Text>
              <Text style={styles.dateText}>
                Placed on {order.created_at ? new Date(order.created_at).toLocaleDateString('en-US', {
                  weekday: 'short',
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
                {(order.order_status || 'Pending').toUpperCase()}
              </Text>
            </View>
          </View>
        </View>

        {/* Review & Rating Card for Delivered Orders */}
        {isDelivered && (
          <View style={[styles.card, styles.reviewCard]}>
            <Text style={styles.sectionTitle}>⭐ Place Order Review</Text>
            {order.user_review ? (
              <View style={styles.existingReviewBox}>
                <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 6 }}>
                  <Text style={styles.starText}>
                    {'⭐'.repeat(Math.round(order.user_review.rating || 5))}
                  </Text>
                  <Text style={styles.ratingNumText}>({order.user_review.rating}/5)</Text>
                </View>
                <Text style={styles.existingReviewComment}>"{order.user_review.review}"</Text>
                <Text style={styles.reviewSubmittedLabel}>✓ Review Submitted</Text>
              </View>
            ) : (
              <View>
                <Text style={styles.reviewInstruction}>
                  How was your experience? Rate the items and leave a review:
                </Text>
                <View style={styles.starRow}>
                  {[1, 2, 3, 4, 5].map((star) => (
                    <TouchableOpacity
                      key={star}
                      onPress={() => setRating(star)}
                      style={styles.starBtn}
                      activeOpacity={0.7}
                    >
                      <Text style={{ fontSize: 28, opacity: star <= rating ? 1 : 0.25 }}>
                        ⭐
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
                <TextInput
                  style={styles.reviewInput}
                  placeholder="Write your review comments here..."
                  placeholderTextColor="#94A3B8"
                  multiline
                  numberOfLines={3}
                  value={reviewText}
                  onChangeText={setReviewText}
                />
                <TouchableOpacity
                  style={[styles.submitReviewBtn, submittingReview && { opacity: 0.6 }]}
                  onPress={handleSubmitReview}
                  disabled={submittingReview}
                  activeOpacity={0.8}
                >
                  {submittingReview ? (
                    <ActivityIndicator size="small" color="#FFFFFF" />
                  ) : (
                    <Text style={styles.submitReviewBtnText}>Submit Review</Text>
                  )}
                </TouchableOpacity>
              </View>
            )}
          </View>
        )}

        {/* Business Info */}
        {order.business && (
          <View style={styles.card}>
            <Text style={styles.sectionTitle}>Merchant Details</Text>
            <View style={styles.businessRow}>
              <FallbackImage
                source={order.business.business_image ? { uri: order.business.business_image } : undefined}
                fallbackSource={require('../assets/business_icon.png')}
                style={styles.businessImg}
              />
              <View style={{ flex: 1, marginLeft: 12 }}>
                <Text style={styles.businessName}>{order.business.name}</Text>
                {order.business.area && (
                  <Text style={styles.businessLocation}>
                    📍 {order.business.area}{order.business.city?.name ? `, ${order.business.city.name}` : ''}
                  </Text>
                )}
              </View>
            </View>
          </View>
        )}

        {/* Ordered Items */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Ordered Items ({items.length})</Text>
          {items.length === 0 ? (
            <Text style={styles.noItemsText}>No items found for this order.</Text>
          ) : (
            items.map((item: any, idx: number) => {
              const itemPrice = parseFloat(item.price || 0);
              const qty = parseInt(item.quantity || 1, 10);
              const total = itemPrice * qty;

              return (
                <View key={item.id || idx} style={styles.itemRow}>
                  <View style={{ flex: 1 }}>
                    <Text style={styles.itemName}>{item.item_name || 'Item'}</Text>
                    <Text style={styles.itemMeta}>
                      ₹{itemPrice.toFixed(2)} × {qty}
                    </Text>
                  </View>
                  <Text style={styles.itemTotal}>₹{total.toFixed(2)}</Text>
                </View>
              );
            })
          )}
        </View>

        {/* Bill Summary */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Payment Summary</Text>
          <View style={styles.billRow}>
            <Text style={styles.billLabel}>Subtotal</Text>
            <Text style={styles.billValue}>₹{parseFloat(order.subtotal || 0).toFixed(2)}</Text>
          </View>
          {parseFloat(order.discount || 0) > 0 && (
            <View style={styles.billRow}>
              <Text style={styles.billLabel}>Discount</Text>
              <Text style={[styles.billValue, { color: '#16A34A' }]}>
                -₹{parseFloat(order.discount).toFixed(2)}
              </Text>
            </View>
          )}
          {parseFloat(order.total_tax || 0) > 0 && (
            <View style={styles.billRow}>
              <Text style={styles.billLabel}>Taxes (GST)</Text>
              <Text style={styles.billValue}>₹{parseFloat(order.total_tax).toFixed(2)}</Text>
            </View>
          )}
          {parseFloat(order.shipping_charge || 0) > 0 && (
            <View style={styles.billRow}>
              <Text style={styles.billLabel}>Delivery Charge</Text>
              <Text style={styles.billValue}>₹{parseFloat(order.shipping_charge).toFixed(2)}</Text>
            </View>
          )}
          <View style={[styles.billRow, styles.grandTotalRow]}>
            <Text style={styles.grandTotalLabel}>Total Amount</Text>
            <Text style={styles.grandTotalValue}>₹{parseFloat(order.total || 0).toFixed(2)}</Text>
          </View>
          <View style={styles.paymentInfoRow}>
            <Text style={styles.paymentLabel}>Payment Method: {order.payment_method || 'COD'}</Text>
            <Text style={styles.paymentLabel}>Status: {(order.payment_status || 'Unpaid').toUpperCase()}</Text>
          </View>
        </View>

        {/* Delivery Address */}
        {(order.address || order.customer_name) && (
          <View style={styles.card}>
            <Text style={styles.sectionTitle}>Delivery Information</Text>
            {order.customer_name && (
              <Text style={styles.addressName}>👤 {order.customer_name}</Text>
            )}
            {order.customer_contact && (
              <Text style={styles.addressContact}>📞 {order.customer_contact}</Text>
            )}
            {order.address && (
              <Text style={styles.addressText}>
                🏠 {order.address}{order.city ? `, ${order.city}` : ''}{order.pincode ? ` - ${order.pincode}` : ''}
              </Text>
            )}
          </View>
        )}
      </ScrollView>
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
  emptyTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#64748B',
    marginBottom: 8,
  },
  retryBtn: {
    marginTop: 12,
    paddingHorizontal: 20,
    paddingVertical: 10,
    backgroundColor: '#4F46E5',
    borderRadius: 10,
  },
  retryBtnText: {
    color: '#FFFFFF',
    fontWeight: '600',
  },
  content: {
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
  reviewCard: {
    borderColor: '#E0E7FF',
    backgroundColor: '#FAF5FF',
  },
  statusRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  invoiceText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#0F172A',
  },
  dateText: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 4,
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
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0F172A',
    marginBottom: 12,
  },
  reviewInstruction: {
    fontSize: 13,
    color: '#475569',
    marginBottom: 10,
  },
  starRow: {
    flexDirection: 'row',
    marginBottom: 14,
  },
  starBtn: {
    marginRight: 8,
  },
  reviewInput: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 12,
    padding: 12,
    fontSize: 14,
    color: '#1E293B',
    textAlignVertical: 'top',
    minHeight: 80,
    marginBottom: 14,
  },
  submitReviewBtn: {
    backgroundColor: '#4F46E5',
    borderRadius: 12,
    paddingVertical: 12,
    alignItems: 'center',
  },
  submitReviewBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
  },
  existingReviewBox: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  starText: {
    fontSize: 16,
    marginRight: 6,
  },
  ratingNumText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#475569',
  },
  existingReviewComment: {
    fontSize: 13,
    color: '#334155',
    fontStyle: 'italic',
    marginBottom: 6,
  },
  reviewSubmittedLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#166534',
  },
  businessRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  businessImg: {
    width: 44,
    height: 44,
    borderRadius: 10,
    backgroundColor: '#F1F5F9',
  },
  businessName: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1E293B',
  },
  businessLocation: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  itemName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1E293B',
  },
  itemMeta: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  itemTotal: {
    fontSize: 14,
    fontWeight: '700',
    color: '#0F172A',
  },
  noItemsText: {
    fontSize: 13,
    color: '#64748B',
    fontStyle: 'italic',
  },
  billRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 4,
  },
  billLabel: {
    fontSize: 13,
    color: '#64748B',
  },
  billValue: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B',
  },
  grandTotalRow: {
    marginTop: 8,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
  },
  grandTotalLabel: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0F172A',
  },
  grandTotalValue: {
    fontSize: 17,
    fontWeight: '700',
    color: '#4F46E5',
  },
  paymentInfoRow: {
    marginTop: 10,
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  paymentLabel: {
    fontSize: 12,
    color: '#64748B',
  },
  addressName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1E293B',
  },
  addressContact: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 2,
  },
  addressText: {
    fontSize: 13,
    color: '#475569',
    marginTop: 4,
    lineHeight: 18,
  },
});

export default OrderDetailScreen;
