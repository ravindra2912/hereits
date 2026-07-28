import React from 'react';
import {
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { BottomTabBarProps } from '@react-navigation/bottom-tabs';
import { useAuth } from '../context/AuthContext';

export const BottomNavBar: React.FC<BottomTabBarProps> = ({ state, navigation }) => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkStyles : lightStyles;

  const { isAuthenticated, setAuthModalVisible } = useAuth();

  const tabs: { key: string; label: string; icon: string }[] = [
    { key: 'HomeTab', label: 'Home', icon: '🏠' },
    { key: 'BusinessesTab', label: 'Businesses', icon: '🏢' },
    { key: 'SearchTab', label: 'Search', icon: '🔍' },
    { key: 'MessagesTab', label: 'Messages', icon: '💬' },
    { key: 'AccountTab', label: isAuthenticated ? 'Account' : 'Login', icon: '👤' },
  ];

  return (
    <View style={[styles.bar, theme.container]}>
      {tabs.map((t, index) => {
        const isActive = state.index === index;
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
          >
            <Text style={[styles.icon, isActive && styles.activeIcon]}>
              {t.icon}
            </Text>
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
    height: 64,
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
  icon: {
    fontSize: 20,
    marginBottom: 2,
  },
  activeIcon: {
    transform: [{ scale: 1.15 }],
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
    width: 20,
    height: 3,
    backgroundColor: '#6366F1',
    borderRadius: 2,
  },
});

const lightStyles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    borderTopColor: '#E2E8F0',
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
