import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RouteProp } from '@react-navigation/native';
import { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';

export type RootStackParamList = {
  Splash: undefined;
  Main: undefined;
  BusinessDetail: { businessId: number };
  ChatDetail: { conversationId: number; title: string };
};

export type MainTabParamList = {
  HomeTab: undefined;
  ExploreTab: { categoryId?: number | null } | undefined;
  BookingsTab: { businessId?: number | null; businessName?: string | null } | undefined;
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
