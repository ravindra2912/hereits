import React, { createContext, useContext, useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Toast from 'react-native-toast-message';

export interface CartItem {
  id: number;
  name: string;
  price: number;
  sellPrice?: number;
  originalPrice?: number;
  priceType: 'FixPrice' | 'PriceInRange' | 'WithoutPrice' | string;
  minPrice?: number;
  maxPrice?: number;
  image?: string;
  quantity: number;
  businessId: number;
  businessName: string;
}

export interface BusinessCart {
  businessId: number;
  businessName: string;
  items: CartItem[];
  updatedAt: string;
}

interface CartContextType {
  carts: Record<number, BusinessCart>;
  addToCart: (product: any, business: any, quantity?: number) => void;
  updateQuantity: (businessId: number, productId: number, quantity: number) => void;
  removeFromCart: (businessId: number, productId: number) => void;
  clearBusinessCart: (businessId: number) => void;
  getBusinessCart: (businessId: number) => BusinessCart | undefined;
  getBusinessItemCount: (businessId: number) => number;
  getBusinessSubtotal: (businessId: number) => number;
  getItemQuantity: (businessId: number, productId: number) => number;
  getAllCarts: () => BusinessCart[];
  totalItemCount: number;
}

const STORAGE_KEY = '@hereits_carts_by_business_v1';

const CartContext = createContext<CartContextType>({
  carts: {},
  addToCart: () => { },
  updateQuantity: () => { },
  removeFromCart: () => { },
  clearBusinessCart: () => { },
  getBusinessCart: () => undefined,
  getBusinessItemCount: () => 0,
  getBusinessSubtotal: () => 0,
  getItemQuantity: () => 0,
  getAllCarts: () => [],
  totalItemCount: 0,
});

export const CartProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [carts, setCarts] = useState<Record<number, BusinessCart>>({});
  const [loaded, setLoaded] = useState(false);

  // Load carts from AsyncStorage on mount
  useEffect(() => {
    const loadCarts = async () => {
      try {
        const stored = await AsyncStorage.getItem(STORAGE_KEY);
        if (stored) {
          const parsed = JSON.parse(stored);
          if (parsed && typeof parsed === 'object') {
            setCarts(parsed);
          }
        }
      } catch (err) {
        console.error('Error loading cart from storage:', err);
      } finally {
        setLoaded(true);
      }
    };

    loadCarts();
  }, []);

  // Save carts to AsyncStorage whenever carts state changes
  useEffect(() => {
    if (!loaded) return;
    const saveCarts = async () => {
      try {
        await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify(carts));
      } catch (err) {
        console.error('Error saving cart to storage:', err);
      }
    };

    saveCarts();
  }, [carts, loaded]);

  const addToCart = (product: any, business: any, quantity: number = 1) => {
    if (!product || !business) return;

    const bId = Number(business.id);
    const bName = business.name || 'Store';
    const pId = Number(product.id);

    // Calculate effective price
    let effectivePrice = 0;
    if (product.price_type === 'FixPrice') {
      effectivePrice = Number(product.sell_price || product.price || 0);
    } else if (product.price_type === 'PriceInRange') {
      effectivePrice = Number(product.min_price || 0);
    }

    const firstImage = product.first_image?.image_url || product.images?.[0]?.image_url || product.image_url || '';

    setCarts(prev => {
      const existingBusinessCart = prev[bId] || {
        businessId: bId,
        businessName: bName,
        items: [],
        updatedAt: new Date().toISOString(),
      };

      const existingItemIndex = existingBusinessCart.items.findIndex(item => item.id === pId);
      let updatedItems: CartItem[];

      if (existingItemIndex > -1) {
        // Increment quantity
        updatedItems = existingBusinessCart.items.map((item, idx) => {
          if (idx === existingItemIndex) {
            return {
              ...item,
              quantity: item.quantity + quantity,
            };
          }
          return item;
        });
      } else {
        // Add new item
        const newItem: CartItem = {
          id: pId,
          name: product.name || 'Product',
          price: effectivePrice,
          sellPrice: product.sell_price ? Number(product.sell_price) : undefined,
          originalPrice: product.price ? Number(product.price) : undefined,
          priceType: product.price_type || 'FixPrice',
          minPrice: product.min_price ? Number(product.min_price) : undefined,
          maxPrice: product.max_price ? Number(product.max_price) : undefined,
          image: firstImage,
          quantity: Math.max(1, quantity),
          businessId: bId,
          businessName: bName,
        };
        updatedItems = [...existingBusinessCart.items, newItem];
      }

      return {
        ...prev,
        [bId]: {
          ...existingBusinessCart,
          businessName: bName,
          items: updatedItems,
          updatedAt: new Date().toISOString(),
        },
      };
    });

    Toast.show({
      type: 'success',
      text1: 'Added to Cart 🛒',
      text2: `${product.name} added to your ${bName} cart.`,
      visibilityTime: 2500,
    });
  };

  const updateQuantity = (businessId: number, productId: number, quantity: number) => {
    setCarts(prev => {
      const businessCart = prev[businessId];
      if (!businessCart) return prev;

      if (quantity <= 0) {
        // Remove item
        const filteredItems = businessCart.items.filter(item => item.id !== productId);
        if (filteredItems.length === 0) {
          const copy = { ...prev };
          delete copy[businessId];
          return copy;
        }
        return {
          ...prev,
          [businessId]: {
            ...businessCart,
            items: filteredItems,
            updatedAt: new Date().toISOString(),
          },
        };
      }

      // Update quantity
      const updatedItems = businessCart.items.map(item =>
        item.id === productId ? { ...item, quantity } : item
      );

      return {
        ...prev,
        [businessId]: {
          ...businessCart,
          items: updatedItems,
          updatedAt: new Date().toISOString(),
        },
      };
    });
  };

  const removeFromCart = (businessId: number, productId: number) => {
    updateQuantity(businessId, productId, 0);
    Toast.show({
      type: 'info',
      text1: 'Item Removed',
      text2: 'Product removed from your cart.',
      visibilityTime: 2000,
    });
  };

  const clearBusinessCart = (businessId: number) => {
    setCarts(prev => {
      const copy = { ...prev };
      delete copy[businessId];
      return copy;
    });
  };

  const getBusinessCart = (businessId: number): BusinessCart | undefined => {
    return carts[businessId];
  };

  const getBusinessItemCount = (businessId: number): number => {
    const bCart = carts[businessId];
    if (!bCart || !bCart.items) return 0;
    return bCart.items.reduce((sum, item) => sum + item.quantity, 0);
  };

  const getBusinessSubtotal = (businessId: number): number => {
    const bCart = carts[businessId];
    if (!bCart || !bCart.items) return 0;
    return bCart.items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  };

  const getItemQuantity = (businessId: number, productId: number): number => {
    const bCart = carts[businessId];
    if (!bCart || !bCart.items) return 0;
    const found = bCart.items.find(item => item.id === productId);
    return found ? found.quantity : 0;
  };

  const getAllCarts = (): BusinessCart[] => {
    return Object.values(carts).filter(c => c && c.items && c.items.length > 0);
  };

  const totalItemCount = Object.values(carts).reduce((total, bCart) => {
    if (!bCart || !bCart.items) return total;
    return total + bCart.items.reduce((sum, item) => sum + item.quantity, 0);
  }, 0);

  return (
    <CartContext.Provider
      value={{
        carts,
        addToCart,
        updateQuantity,
        removeFromCart,
        clearBusinessCart,
        getBusinessCart,
        getBusinessItemCount,
        getBusinessSubtotal,
        getItemQuantity,
        getAllCarts,
        totalItemCount,
      }}
    >
      {children}
    </CartContext.Provider>
  );
};

export const useCart = () => useContext(CartContext);
