import React from 'react';
import {
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import Svg, { Circle, Path } from 'react-native-svg';
import { BottomTabBarProps } from '@react-navigation/bottom-tabs';
import { useAuth } from '../context/AuthContext';

interface IconProps {
  color: string;
  size?: number;
}

const HomeIcon: React.FC<IconProps> = ({ color, size = 22 }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Path
      d="M3 10.182L10.318 3.5a2.5 2.5 0 013.364 0L21 10.182M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7M9 21v-6a2 2 0 012-2h2a2 2 0 012 2v6"
      stroke={color}
      strokeWidth={2}
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </Svg>
);

const BusinessIcon: React.FC<IconProps> = ({ color, size = 22 }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Path
      d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M9 18h.01M15 18h.01"
      stroke={color}
      strokeWidth={2}
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </Svg>
);

const SearchIcon: React.FC<IconProps> = ({ color, size = 22 }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Circle cx={11} cy={11} r={7} stroke={color} strokeWidth={2} />
    <Path
      d="M20 20l-4.35-4.35"
      stroke={color}
      strokeWidth={2}
      strokeLinecap="round"
    />
  </Svg>
);

const MessageIcon: React.FC<IconProps> = ({ color, size = 22 }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Path
      d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"
      stroke={color}
      strokeWidth={2}
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </Svg>
);

const AccountIcon: React.FC<IconProps> = ({ color, size = 22 }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Path
      d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z"
      stroke={color}
      strokeWidth={2}
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </Svg>
);

export const BottomNavBar: React.FC<BottomTabBarProps> = ({ state, navigation }) => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkStyles : lightStyles;
  const { isAuthenticated, setAuthModalVisible } = useAuth();

  const tabs = [
    { key: 'HomeTab', label: 'Home', renderIcon: (color: string) => <HomeIcon color={color} /> },
    { key: 'BusinessesTab', label: 'Businesses', renderIcon: (color: string) => <BusinessIcon color={color} /> },
    { key: 'SearchTab', label: 'Search', renderIcon: (color: string) => <SearchIcon color={color} /> },
    { key: 'MessagesTab', label: 'Messages', renderIcon: (color: string) => <MessageIcon color={color} /> },
    { key: 'AccountTab', label: isAuthenticated ? 'Account' : 'Login', renderIcon: (color: string) => <AccountIcon color={color} /> },
  ];

  return (
    <View style={[styles.bar, theme.container]}>
      {tabs.map((t, index) => {
        const isActive = state.index === index;
        const iconColor = isActive ? '#6366F1' : '#94A3B8';

        return (
          <TouchableOpacity
            key={t.key}
            onPress={() => {
              if ((t.key === 'AccountTab' || t.key === 'MessagesTab') && !isAuthenticated) {
                setAuthModalVisible(true);
                return;
              }

              const event = navigation.emit({
                type: 'tabPress',
                target: state.routes[index].key,
                canPreventDefault: true,
              });

              if (!isActive && !event.defaultPrevented) {
                navigation.navigate({ name: t.key, mergeRoute: true } as any);
              }
            }}
            style={styles.tabButton}
            activeOpacity={0.7}
          >
            <View style={[styles.iconContainer, isActive && styles.activeIconContainer]}>
              {t.renderIcon(iconColor)}
            </View>
            <Text
              style={[
                styles.label,
                theme.label,
                isActive && styles.activeLabel,
              ]}
            >
              {t.label}
            </Text>
            {isActive && <View style={styles.indicator} />}
          </TouchableOpacity>
        );
      })}
    </View>
  );
};

const styles = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    height: 65,
    borderTopWidth: 1,
    paddingBottom: 4,
    justifyContent: 'space-around',
    alignItems: 'center',
  },
  tabButton: {
    alignItems: 'center',
    justifyContent: 'center',
    flex: 1,
  },
  iconContainer: {
    marginBottom: 4,
    alignItems: 'center',
    justifyContent: 'center',
  },
  activeIconContainer: {
    transform: [{ scale: 1.1 }],
  },
  label: {
    fontSize: 11,
    fontWeight: '500',
  },
  activeLabel: {
    color: '#6366F1',
    fontWeight: '700',
  },
  indicator: {
    position: 'absolute',
    bottom: -4,
    width: 18,
    height: 3,
    backgroundColor: '#6366F1',
    borderRadius: 2,
  },
});

const lightStyles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    borderTopColor: '#F1F5F9',
  },
  label: {
    color: '#64748B',
  },
});

const darkStyles = StyleSheet.create({
  container: {
    backgroundColor: '#1E293B',
    borderTopColor: '#334155',
  },
  label: {
    color: '#94A3B8',
  },
});

export default BottomNavBar;
