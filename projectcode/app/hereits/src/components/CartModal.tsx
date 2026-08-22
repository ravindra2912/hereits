import React, { useState } from 'react';
import {
  Modal,
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Dimensions,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { useCart, CartItem } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { chatService } from '../services/chatService';
import { useNavigation } from '@react-navigation/native';
import FallbackImage from './FallbackImage';
import Toast from 'react-native-toast-message';

const { height } = Dimensions.get('window');

interface CartModalProps {
  visible: boolean;
  onClose: () => void;
  businessId: number;
  businessName: string;
}

export const CartModal: React.FC<CartModalProps> = ({
  visible,
  onClose,
  businessId,
  businessName,
}) => {
  const navigation = useNavigation<any>();
  const { getBusinessCart, updateQuantity, removeFromCart, getBusinessSubtotal, clearBusinessCart } = useCart();
  const { isAuthenticated, setAuthModalVisible } = useAuth();
  const [checkingOut, setCheckingOut] = useState(false);

  const businessCart = getBusinessCart(businessId);
  const items: CartItem[] = businessCart?.items || [];
  const subtotal = getBusinessSubtotal(businessId);

  const handleCheckout = async () => {
    if (items.length === 0) {
      Alert.alert('Empty Cart', 'Please add items to your cart before proceeding.');
      return;
    }

    if (!isAuthenticated) {
      onClose();
      setAuthModalVisible(true);
      return;
    }

    try {
      setCheckingOut(true);

      // Start conversation with the business
      const res = await chatService.startConversation(businessId);
      setCheckingOut(false);

      if (res && res.success && res.data) {
        // Build inquiry message
        let inquiryMsg = `🛍️ *Order Inquiry & Cart Details*\n`;
        inquiryMsg += `📍 *Store:* ${businessName}\n\n`;
        inquiryMsg += `*Items List:*\n`;

        items.forEach((item, index) => {
          const itemPriceStr =
            item.price > 0
              ? `₹${item.price.toLocaleString('en-IN')}`
              : item.minPrice && item.maxPrice
                ? `₹${item.minPrice} - ₹${item.maxPrice}`
                : 'Contact for Price';

          const lineTotalStr =
            item.price > 0
              ? ` = ₹${(item.price * item.quantity).toLocaleString('en-IN')}`
              : '';

          inquiryMsg += `${index + 1}. *${item.name}*\n   • Qty: ${item.quantity} × ${itemPriceStr}${lineTotalStr}\n`;
        });

        inquiryMsg += `\n━━━━━━━━━━━━━━━━━━━━\n`;
        if (subtotal > 0) {
          inquiryMsg += `💰 *Estimated Total:* ₹${subtotal.toLocaleString('en-IN')}\n\n`;
        }
        inquiryMsg += `Hello! I would like to inquire about purchasing these items. Could you please confirm availability and payment/delivery details?`;

        onClose();

        navigation.navigate('ChatDetail', {
          conversationId: res.data.id,
          title: businessName,
          initialMessage: inquiryMsg,
        });

        Toast.show({
          type: 'success',
          text1: 'Cart Inquiry Created! 💬',
          text2: `Connecting to ${businessName} on chat...`,
        });
      } else {
        Alert.alert('Error', res?.message || 'Failed to initiate chat with the store.');
      }
    } catch (e) {
      setCheckingOut(false);
      console.error('Checkout error:', e);
      Alert.alert('Error', 'Unable to initiate chat checkout. Please try again.');
    }
  };

  return (
    <Modal
      visible={visible}
      animationType="slide"
      transparent
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <TouchableOpacity
          style={styles.backdrop}
          activeOpacity={1}
          onPress={onClose}
        />

        <View style={styles.sheetContainer}>
          {/* Header */}
          <View style={styles.header}>
            <View style={styles.handleBar} />
            <View style={styles.headerRow}>
              <View style={{ flex: 1 }}>
                <Text style={styles.headerTitle}>🛒 Your Cart</Text>
                <Text style={styles.storeName} numberOfLines={1}>
                  {businessName}
                </Text>
              </View>
              {items.length > 0 && (
                <TouchableOpacity
                  onPress={() => {
                    Alert.alert(
                      'Clear Cart',
                      `Are you sure you want to remove all items from ${businessName}?`,
                      [
                        { text: 'Cancel', style: 'cancel' },
                        {
                          text: 'Clear',
                          style: 'destructive',
                          onPress: () => clearBusinessCart(businessId),
                        },
                      ]
                    );
                  }}
                  style={styles.clearBtn}
                >
                  <Text style={styles.clearBtnText}>Clear All</Text>
                </TouchableOpacity>
              )}
              <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
                <Text style={styles.closeBtnText}>✕</Text>
              </TouchableOpacity>
            </View>
          </View>

          {/* Cart Content */}
          {items.length === 0 ? (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyEmoji}>🛍️</Text>
              <Text style={styles.emptyTitle}>Your cart is empty</Text>
              <Text style={styles.emptySub}>
                Add products from {businessName} to inquire and order directly!
              </Text>
              <TouchableOpacity onPress={onClose} style={styles.startShoppingBtn}>
                <Text style={styles.startShoppingBtnText}>Browse Products</Text>
              </TouchableOpacity>
            </View>
          ) : (
            <>
              <ScrollView
                style={styles.itemsList}
                contentContainerStyle={styles.itemsListContent}
                showsVerticalScrollIndicator={false}
              >
                {items.map(item => (
                  <View key={`cart-item-${item.id}`} style={styles.itemCard}>
                    <FallbackImage
                      source={item.image ? { uri: item.image } : null}
                      type="product"
                      style={styles.itemImage}
                      resizeMode="cover"
                    />

                    <View style={styles.itemDetails}>
                      <Text style={styles.itemName} numberOfLines={2}>
                        {item.name}
                      </Text>

                      <View style={styles.itemPriceRow}>
                        {item.price > 0 ? (
                          <Text style={styles.itemPrice}>
                            ₹{(item.price * item.quantity).toLocaleString('en-IN')}
                            {item.quantity > 1 && (
                              <Text style={styles.unitPriceText}>
                                {' '}(₹{item.price.toLocaleString('en-IN')}/ea)
                              </Text>
                            )}
                          </Text>
                        ) : (
                          <Text style={styles.itemPriceContact}>Contact for Price</Text>
                        )}
                      </View>

                      {/* Quantity Controls */}
                      <View style={styles.qtyRow}>
                        <View style={styles.qtyControls}>
                          <TouchableOpacity
                            onPress={() => updateQuantity(businessId, item.id, item.quantity - 1)}
                            style={styles.qtyBtn}
                            activeOpacity={0.7}
                          >
                            <Text style={styles.qtyBtnText}>−</Text>
                          </TouchableOpacity>

                          <Text style={styles.qtyText}>{item.quantity}</Text>

                          <TouchableOpacity
                            onPress={() => updateQuantity(businessId, item.id, item.quantity + 1)}
                            style={styles.qtyBtn}
                            activeOpacity={0.7}
                          >
                            <Text style={styles.qtyBtnText}>+</Text>
                          </TouchableOpacity>
                        </View>

                        <TouchableOpacity
                          onPress={() => removeFromCart(businessId, item.id)}
                          style={styles.removeBtn}
                        >
                          <Text style={styles.removeBtnText}>🗑️ Remove</Text>
                        </TouchableOpacity>
                      </View>
                    </View>
                  </View>
                ))}

                {/* Information Card */}
                <View style={styles.infoBox}>
                  <Text style={styles.infoBoxTitle}>💬 Instant Store Inquiry</Text>
                  <Text style={styles.infoBoxDesc}>
                    When you check out, a detailed inquiry message with these cart items will be prepared in chat so you can finalize pricing, availability, and delivery with the merchant directly!
                  </Text>
                </View>
              </ScrollView>

              {/* Bottom Checkout Section */}
              <View style={styles.footer}>
                {subtotal > 0 && (
                  <View style={styles.summaryRow}>
                    <Text style={styles.subtotalLabel}>Estimated Total</Text>
                    <Text style={styles.subtotalValue}>
                      ₹{subtotal.toLocaleString('en-IN')}
                    </Text>
                  </View>
                )}

                <TouchableOpacity
                  style={[styles.checkoutBtn, checkingOut && styles.checkoutBtnDisabled]}
                  onPress={handleCheckout}
                  disabled={checkingOut}
                  activeOpacity={0.85}
                >
                  {checkingOut ? (
                    <ActivityIndicator color="#FFFFFF" />
                  ) : (
                    <>
                      <Text style={styles.checkoutBtnIcon}>💬</Text>
                      <Text style={styles.checkoutBtnText}>
                        Checkout & Inquire on Chat ({items.reduce((s, i) => s + i.quantity, 0)})
                      </Text>
                    </>
                  )}
                </TouchableOpacity>
              </View>
            </>
          )}
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.65)',
    justifyContent: 'flex-end',
  },
  backdrop: {
    flex: 1,
  },
  sheetContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    maxHeight: height * 0.85,
    minHeight: height * 0.45,
    paddingBottom: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 20,
  },
  handleBar: {
    width: 40,
    height: 4,
    backgroundColor: '#E2E8F0',
    borderRadius: 2,
    alignSelf: 'center',
    marginBottom: 12,
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0F172A',
  },
  storeName: {
    fontSize: 12,
    color: '#6366F1',
    fontWeight: '700',
    marginTop: 2,
  },
  clearBtn: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
    backgroundColor: '#FEE2E2',
    marginRight: 8,
  },
  clearBtnText: {
    fontSize: 11,
    color: '#EF4444',
    fontWeight: '700',
  },
  closeBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#F1F5F9',
    alignItems: 'center',
    justifyContent: 'center',
  },
  closeBtnText: {
    fontSize: 14,
    color: '#64748B',
    fontWeight: '700',
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 36,
  },
  emptyEmoji: {
    fontSize: 54,
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#1E293B',
    marginBottom: 6,
  },
  emptySub: {
    fontSize: 13,
    color: '#64748B',
    textAlign: 'center',
    lineHeight: 18,
    marginBottom: 20,
  },
  startShoppingBtn: {
    backgroundColor: '#6366F1',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 12,
  },
  startShoppingBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
  },
  itemsList: {
    flex: 1,
    paddingHorizontal: 20,
  },
  itemsListContent: {
    paddingVertical: 14,
  },
  itemCard: {
    flexDirection: 'row',
    backgroundColor: '#F8FAFC',
    borderRadius: 16,
    padding: 12,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  itemImage: {
    width: 70,
    height: 70,
    borderRadius: 12,
    backgroundColor: '#FFFFFF',
  },
  itemDetails: {
    flex: 1,
    marginLeft: 12,
    justifyContent: 'space-between',
  },
  itemName: {
    fontSize: 14,
    fontWeight: '700',
    color: '#1E293B',
  },
  itemPriceRow: {
    marginVertical: 4,
  },
  itemPrice: {
    fontSize: 14,
    fontWeight: '800',
    color: '#6366F1',
  },
  unitPriceText: {
    fontSize: 11,
    color: '#64748B',
    fontWeight: '500',
  },
  itemPriceContact: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748B',
  },
  qtyRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 6,
  },
  qtyControls: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#CBD5E1',
  },
  qtyBtn: {
    width: 28,
    height: 28,
    alignItems: 'center',
    justifyContent: 'center',
  },
  qtyBtnText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1E293B',
  },
  qtyText: {
    fontSize: 13,
    fontWeight: '800',
    color: '#1E293B',
    paddingHorizontal: 10,
  },
  removeBtn: {
    paddingHorizontal: 8,
    paddingVertical: 4,
  },
  removeBtnText: {
    fontSize: 11,
    color: '#EF4444',
    fontWeight: '600',
  },
  infoBox: {
    backgroundColor: '#EEF2FF',
    borderRadius: 14,
    padding: 12,
    borderWidth: 1,
    borderColor: '#C7D2FE',
    marginTop: 4,
    marginBottom: 8,
  },
  infoBoxTitle: {
    fontSize: 12,
    fontWeight: '800',
    color: '#4F46E5',
    marginBottom: 4,
  },
  infoBoxDesc: {
    fontSize: 11,
    color: '#4338CA',
    lineHeight: 16,
  },
  footer: {
    paddingHorizontal: 20,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F1F5F9',
    backgroundColor: '#FFFFFF',
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  subtotalLabel: {
    fontSize: 14,
    color: '#64748B',
    fontWeight: '600',
  },
  subtotalValue: {
    fontSize: 18,
    fontWeight: '900',
    color: '#0F172A',
  },
  checkoutBtn: {
    backgroundColor: '#6366F1',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    borderRadius: 16,
    shadowColor: '#6366F1',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  checkoutBtnDisabled: {
    backgroundColor: '#94A3B8',
    shadowOpacity: 0,
  },
  checkoutBtnIcon: {
    fontSize: 16,
    marginRight: 8,
  },
  checkoutBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '800',
  },
});

export default CartModal;
