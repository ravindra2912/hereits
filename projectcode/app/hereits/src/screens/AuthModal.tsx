import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { useAuth } from '../context/AuthContext';

interface AuthModalProps {
  visible: boolean;
  onClose: () => void;
}

export const AuthModal: React.FC<AuthModalProps> = ({ visible, onClose }) => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const { login, register, isLoading } = useAuth();

  const [mode, setMode] = useState<'login' | 'register'>('login');

  // Form Fields
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [contact, setContact] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const handleSubmit = async () => {
    if (mode === 'login') {
      if (!email || !password) {
        Alert.alert('Required', 'Please enter email and password.');
        return;
      }
      const res = await login(email, password);
      if (res.success) {
        Alert.alert('Welcome!', 'Logged in successfully.');
        onClose();
      } else {
        Alert.alert('Login Error', res.message || 'Invalid credentials');
      }
    } else {
      if (!firstName || !lastName || !email || !contact || !password) {
        Alert.alert('Required', 'Please fill all required registration fields.');
        return;
      }
      const res = await register({
        first_name: firstName,
        last_name: lastName,
        email,
        contact,
        password,
        confirm_password: confirmPassword,
      });
      if (res.success) {
        Alert.alert('Success', 'Registered successfully!');
        onClose();
      } else {
        Alert.alert('Registration Error', res.message || 'Could not register.');
      }
    }
  };

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.overlay}>
        <View style={[styles.content, theme.cardBg]}>
          <View style={styles.topRow}>
            <Text style={[styles.title, theme.primaryText]}>
              {mode === 'login' ? 'Welcome Back' : 'Create Account'}
            </Text>
            <TouchableOpacity onPress={onClose}>
              <Text style={{ fontSize: 18, color: '#64748B' }}>✕</Text>
            </TouchableOpacity>
          </View>

          <ScrollView contentContainerStyle={styles.scroll}>
            {mode === 'register' && (
              <>
                <Text style={[styles.label, theme.primaryText]}>First Name</Text>
                <TextInput
                  value={firstName}
                  onChangeText={setFirstName}
                  placeholder="First Name"
                  style={[styles.input, theme.primaryText]}
                />

                <Text style={[styles.label, theme.primaryText]}>Last Name</Text>
                <TextInput
                  value={lastName}
                  onChangeText={setLastName}
                  placeholder="Last Name"
                  style={[styles.input, theme.primaryText]}
                />

                <Text style={[styles.label, theme.primaryText]}>Contact Number</Text>
                <TextInput
                  value={contact}
                  onChangeText={setContact}
                  keyboardType="phone-pad"
                  placeholder="10 digit contact"
                  style={[styles.input, theme.primaryText]}
                />
              </>
            )}

            <Text style={[styles.label, theme.primaryText]}>Email Address</Text>
            <TextInput
              value={email}
              onChangeText={setEmail}
              keyboardType="email-address"
              autoCapitalize="none"
              placeholder="user@example.com"
              style={[styles.input, theme.primaryText]}
            />

            <Text style={[styles.label, theme.primaryText]}>Password</Text>
            <TextInput
              value={password}
              onChangeText={setPassword}
              secureTextEntry
              placeholder="••••••••"
              style={[styles.input, theme.primaryText]}
            />

            {mode === 'register' && (
              <>
                <Text style={[styles.label, theme.primaryText]}>Confirm Password</Text>
                <TextInput
                  value={confirmPassword}
                  onChangeText={setConfirmPassword}
                  secureTextEntry
                  placeholder="••••••••"
                  style={[styles.input, theme.primaryText]}
                />
              </>
            )}

            <TouchableOpacity
              disabled={isLoading}
              onPress={handleSubmit}
              style={styles.submitBtn}
            >
              {isLoading ? (
                <ActivityIndicator color="#FFF" />
              ) : (
                <Text style={styles.submitBtnText}>
                  {mode === 'login' ? 'Sign In' : 'Register Account'}
                </Text>
              )}
            </TouchableOpacity>

            <TouchableOpacity
              onPress={() => setMode(mode === 'login' ? 'register' : 'login')}
              style={styles.toggleRow}
            >
              <Text style={[styles.toggleText, theme.secondaryText]}>
                {mode === 'login'
                  ? "Don't have an account? Sign Up"
                  : 'Already have an account? Sign In'}
              </Text>
            </TouchableOpacity>
          </ScrollView>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  content: {
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 20,
    maxHeight: '85%',
  },
  topRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  title: {
    fontSize: 20,
    fontWeight: '800',
  },
  scroll: {
    paddingBottom: 20,
  },
  label: {
    fontSize: 13,
    fontWeight: '700',
    marginBottom: 6,
    marginTop: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 12,
    paddingHorizontal: 14,
    height: 46,
    marginBottom: 10,
  },
  submitBtn: {
    backgroundColor: '#6366F1',
    borderRadius: 14,
    height: 50,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 16,
  },
  submitBtnText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '800',
  },
  toggleRow: {
    alignItems: 'center',
    marginTop: 14,
  },
  toggleText: {
    fontSize: 13,
    fontWeight: '600',
  },
});

const lightTheme = StyleSheet.create({
  cardBg: { backgroundColor: '#FFFFFF' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
});

const darkTheme = StyleSheet.create({
  cardBg: { backgroundColor: '#1E293B' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
});

export default AuthModal;
