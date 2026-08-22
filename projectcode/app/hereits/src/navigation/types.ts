import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RouteProp } from '@react-navigation/native';
import { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';

export type RootStackParamList = {
  Splash: undefined;
  Main: undefined;
  BusinessDetail: { businessId: number };
  ProductDetail: { productId: number };
  ServiceDetail: { serviceId: number };
  ChatDetail: { conversationId: number; title: string; initialMessage?: string };
  BusinessCategoryList: { businessId: number; type: 'Products' | 'Services' };
  BusinessProductList: { businessId: number; categoryId?: number; categoryName?: string };
  BusinessServiceList: { businessId: number; categoryId?: number; categoryName?: string };
  BusinessSpecialistList: { businessId: number };
  ProfileEdit: undefined;
  Favorites: undefined;
  Following: undefined;
  SpecialistDetail: { specialistId: number };
  Appointments: { businessId?: number; businessName?: string } | undefined;
  OrdersList: undefined;
  OrderDetail: { orderId: number; initialOrder?: any };
};

export type MainTabParamList = {
  HomeTab: undefined;
  BusinessesTab: { categoryId?: number | null } | undefined;
  SearchTab: undefined;
  MessagesTab: undefined;
  AccountTab: undefined;
};

// Navigation helpers
export type RootStackNavigationProp<RouteName extends keyof RootStackParamList> =
  NativeStackNavigationProp<RootStackParamList, RouteName>;

export type RootStackRouteProp<RouteName extends keyof RootStackParamList> =
  RouteProp<RootStackParamList, RouteName>;

export type MainTabNavigationProp<RouteName extends keyof MainTabParamList> =
  BottomTabNavigationProp<MainTabParamList, RouteName>;

export type MainTabRouteProp<RouteName extends keyof MainTabParamList> =
  RouteProp<MainTabParamList, RouteName>;
