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
import { AuthModal } from './AuthModal';
import { authService } from '../services/authService';

export const ProfileScreen: React.FC = () => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const { user, isAuthenticated, logout, refreshProfile } = useAuth();

  const [authModalVisible, setAuthModalVisible] = useState(false);
  const [favorites, setFavorites] = useState<any[]>([]);
  const [orders, setOrders] = useState<any[]>([]);

  useEffect(() => {
    if (isAuthenticated) {
      refreshProfile();
      fetchUserData();
    }
  }, [isAuthenticated]);

  const fetchUserData = async () => {
    const fRes = await authService.getFavorites();
    if (fRes.success && fRes.data) setFavorites(fRes.data);

    const oRes = await authService.getOrders();
    if (oRes.success && oRes.data) setOrders(oRes.data);
  };

  return (
    <ScrollView style={[styles.container, theme.background]}>
      {/* Profile Header */}
      <View style={[styles.profileCard, theme.cardBg]}>
        <View style={styles.avatar}>
          <Text style={{ fontSize: 32 }}>👤</Text>
        </View>
        {isAuthenticated && user ? (
          <View style={styles.userInfo}>
            <Text style={[styles.userName, theme.primaryText]}>
              {user.first_name} {user.last_name}
            </Text>
            <Text style={[styles.userEmail, theme.secondaryText]}>{user.email}</Text>
            <Text style={[styles.userContact, theme.secondaryText]}>
              📞 {user.contact || 'No contact added'}
            </Text>
          </View>
        ) : (
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
        )}
      </View>

      {/* Wallet / Credit Balance */}
      {isAuthenticated && (
        <View style={styles.walletCard}>
          <View>
            <Text style={styles.walletLabel}>WALLET CREDIT BALANCE</Text>
            <Text style={styles.walletBalance}>₹{user?.credit_balance ?? 0}</Text>
          </View>
          <TouchableOpacity style={styles.addCreditBtn}>
            <Text style={styles.addCreditText}>+ Add Credits</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* Quick Statistics / Links */}
      {isAuthenticated && (
        <View style={styles.statsRow}>
          <View style={[styles.statBox, theme.cardBg]}>
            <Text style={styles.statNumber}>{favorites.length}</Text>
            <Text style={[styles.statLabel, theme.secondaryText]}>Favorites</Text>
          </View>
          <View style={[styles.statBox, theme.cardBg]}>
            <Text style={styles.statNumber}>{orders.length}</Text>
            <Text style={[styles.statLabel, theme.secondaryText]}>Orders</Text>
          </View>
        </View>
      )}

      {/* Account Actions */}
      <View style={styles.actionSection}>
        <Text style={[styles.sectionTitle, theme.primaryText]}>Account Settings</Text>

        <TouchableOpacity style={[styles.actionRow, theme.cardBg]}>
          <Text style={styles.actionIcon}>❤️</Text>
          <Text style={[styles.actionLabel, theme.primaryText]}>Saved Favorites</Text>
          <Text style={styles.arrowIcon}>›</Text>
        </TouchableOpacity>

        <TouchableOpacity style={[styles.actionRow, theme.cardBg]}>
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
          <TouchableOpacity onPress={logout} style={[styles.actionRow, theme.cardBg, { marginTop: 14 }]}>
            <Text style={styles.actionIcon}>🚪</Text>
            <Text style={[styles.actionLabel, { color: '#EF4444', fontWeight: '700' }]}>
              Logout Account
            </Text>
          </TouchableOpacity>
        )}
      </View>

      <AuthModal
        visible={authModalVisible}
        onClose={() => setAuthModalVisible(false)}
      />
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
  walletCard: {
    backgroundColor: '#6366F1',
    borderRadius: 18,
    padding: 20,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  walletLabel: { color: '#EEF2FF', fontSize: 10, fontWeight: '800', letterSpacing: 1 },
  walletBalance: { color: '#FFF', fontSize: 26, fontWeight: '800', marginTop: 4 },
  addCreditBtn: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 10,
  },
  addCreditText: { color: '#FFF', fontSize: 12, fontWeight: '700' },
  statsRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  statBox: {
    width: '48%',
    padding: 16,
    borderRadius: 16,
    alignItems: 'center',
  },
  statNumber: { fontSize: 22, fontWeight: '800', color: '#6366F1' },
  statLabel: { fontSize: 12, marginTop: 2 },
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
