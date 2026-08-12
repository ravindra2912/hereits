import React, { useEffect, useState } from 'react';
import {
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { useAuth } from '../context/AuthContext';
import { authService } from '../services/authService';
import { useNavigation } from '@react-navigation/native';
import FallbackImage from '../components/FallbackImage';

export const ProfileScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const { user, isAuthenticated, logout, refreshProfile, setAuthModalVisible } = useAuth();

  const [favorites, setFavorites] = useState<any[]>([]);
  const [orders, setOrders] = useState<any[]>([]);

  useEffect(() => {
    if (!isAuthenticated) {
      setAuthModalVisible(true);
    }
  }, [isAuthenticated]);

  const handleLogout = async () => {
    await logout();
    navigation.navigate('HomeTab');
  };

  return (
    <ScrollView style={[styles.container, theme.background]}>
      {/* Profile Header */}
      <View style={[styles.profileCard, theme.cardBg]}>
        {isAuthenticated && user ? (
          <>
            <TouchableOpacity
              onPress={() => navigation.navigate('ProfileEdit')}
            >
              <View style={styles.avatar}>
                {user.profile ? (
                  <FallbackImage
                    source={{ uri: user.profile }}
                    fallbackSource={require('../assets/business_icon.png')}
                    style={styles.avatarImage}
                  />
                ) : (
                  <Text style={{ fontSize: 32 }}>👤</Text>
                )}
              </View>
            </TouchableOpacity>

            <TouchableOpacity
              onPress={() => navigation.navigate('ProfileEdit')}
              style={{ flex: 1 }}
            >
              <View style={styles.userInfo}>
                <Text style={[styles.userName, theme.primaryText]}>
                  {user.first_name} {user.last_name}
                </Text>
                <Text style={[styles.userEmail, theme.secondaryText]}>{user.email}</Text>
                <Text style={[styles.userContact, theme.secondaryText]}>
                  📞 {user.contact || 'No contact added'}
                </Text>
              </View>
            </TouchableOpacity>
          </>
        ) : (
          <>
            <View style={styles.avatar}>
              <Text style={{ fontSize: 32 }}>👤</Text>
            </View>
            <View style={styles.userInfo}>
              <Text style={[styles.userName, theme.primaryText]}>Guest User</Text>
              <Text style={[styles.userEmail, theme.secondaryText]}>
                Sign in to manage appointments & orders
              </Text>
              <TouchableOpacity
                onPress={() => setAuthModalVisible(true)}
                style={styles.loginBtn}
              >
                <Text style={styles.loginBtnText}>Login / Register</Text>
              </TouchableOpacity>
            </View>
          </>
        )}
      </View>

      {/* Account Actions */}
      <View style={styles.actionSection}>
        <Text style={[styles.sectionTitle, theme.primaryText]}>Account Settings</Text>

        <TouchableOpacity
          onPress={() => {
            if (!isAuthenticated) {
              setAuthModalVisible(true);
              return;
            }
            navigation.navigate('Appointments');
          }}
          style={[styles.actionRow, theme.cardBg]}
        >
          <Text style={styles.actionIcon}>📅</Text>
          <Text style={[styles.actionLabel, theme.primaryText]}>My Appointments</Text>
          <Text style={styles.arrowIcon}>›</Text>
        </TouchableOpacity>

        <TouchableOpacity
          onPress={() => navigation.navigate('Favorites')}
          style={[styles.actionRow, theme.cardBg]}
        >
          <Text style={styles.actionIcon}>❤️</Text>
          <Text style={[styles.actionLabel, theme.primaryText]}>Saved Favorites</Text>
          <Text style={styles.arrowIcon}>›</Text>
        </TouchableOpacity>

        <TouchableOpacity
          onPress={() => {
            if (!isAuthenticated) {
              setAuthModalVisible(true);
              return;
            }
            navigation.navigate('OrdersList');
          }}
          style={[styles.actionRow, theme.cardBg]}
        >
          <Text style={styles.actionIcon}>📦</Text>
          <Text style={[styles.actionLabel, theme.primaryText]}>Order History</Text>
          <Text style={styles.arrowIcon}>›</Text>
        </TouchableOpacity>

        <TouchableOpacity style={[styles.actionRow, theme.cardBg]}>
          <Text style={styles.actionIcon}>🎫</Text>
          <Text style={[styles.actionLabel, theme.primaryText]}>Support Tickets</Text>
          <Text style={styles.arrowIcon}>›</Text>
        </TouchableOpacity>

        {isAuthenticated && (
          <TouchableOpacity onPress={handleLogout} style={[styles.actionRow, theme.cardBg, { marginTop: 14 }]}>
            <Text style={styles.actionIcon}>🚪</Text>
            <Text style={[styles.actionLabel, { color: '#EF4444', fontWeight: '700' }]}>
              Logout Account
            </Text>
          </TouchableOpacity>
        )}
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, paddingHorizontal: 20, paddingTop: 16 },
  profileCard: {
    flexDirection: 'row',
    padding: 20,
    borderRadius: 20,
    alignItems: 'center',
    marginBottom: 16,
    position: 'relative',
  },
  avatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  avatarImage: {
    width: 60,
    height: 60,
    borderRadius: 30,
  },
  userInfo: { flex: 1 },
  userName: { fontSize: 18, fontWeight: '800' },
  userEmail: { fontSize: 13, marginTop: 2 },
  userContact: { fontSize: 12, marginTop: 4 },
  loginBtn: {
    backgroundColor: '#6366F1',
    borderRadius: 10,
    paddingHorizontal: 14,
    paddingVertical: 8,
    marginTop: 8,
    alignSelf: 'flex-start',
  },
  loginBtnText: { color: '#FFF', fontSize: 13, fontWeight: '700' },
  actionSection: { paddingBottom: 40 },
  sectionTitle: { fontSize: 16, fontWeight: '800', marginBottom: 12 },
  actionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 14,
    marginBottom: 10,
  },
  actionIcon: { fontSize: 18, marginRight: 12 },
  actionLabel: { flex: 1, fontSize: 14, fontWeight: '600' },
  arrowIcon: { fontSize: 18, color: '#94A3B8' },
});

const lightTheme = StyleSheet.create({
  background: { backgroundColor: '#F8FAFC' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
  cardBg: { backgroundColor: '#FFFFFF' },
});

const darkTheme = StyleSheet.create({
  background: { backgroundColor: '#0F172A' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
});

export default ProfileScreen;
