import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {
  GoogleSignin,
  statusCodes,
} from '@react-native-google-signin/google-signin';
import { useAuth } from '../context/AuthContext';

GoogleSignin.configure({
  webClientId: '1034832913243-lhms3o79iis7ld1r0pjma2cehikfvahl.apps.googleusercontent.com',
  offlineAccess: true,
  scopes: ['profile', 'email'],
});

interface AuthModalProps {
  visible: boolean;
  onClose: () => void;
}

export const AuthModal: React.FC<AuthModalProps> = ({ visible, onClose }) => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const { login, googleLogin, register, isLoading } = useAuth();

  const [mode, setMode] = useState<'login' | 'register'>('login');
  const [googleLoading, setGoogleLoading] = useState(false);

  // Form Fields
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [contact, setContact] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const handleGoogleSignIn = async () => {
    try {
      setGoogleLoading(true);
      await GoogleSignin.hasPlayServices({ showPlayServicesUpdateDialog: true });

      let userInfo: any = null;
      try {
        GoogleSignin.configure({
          webClientId: '1034832913243-lhms3o79iis7ld1r0pjma2cehikfvahl.apps.googleusercontent.com',
        });
        const response = await GoogleSignin.signIn();
        userInfo = (response as any).data || response;
      } catch (err: any) {
        console.warn('SignIn with webClientId failed, trying fallback configuration:', err);
        GoogleSignin.configure({});
        const response = await GoogleSignin.signIn();
        userInfo = (response as any).data || response;
      }
      console.log('User Info', userInfo);
      const user = userInfo?.user || userInfo;

      if (user && user.email) {
        const res = await googleLogin({
          email: user.email,
          first_name: user.givenName || user.name || '',
          last_name: user.familyName || '',
          google_id: user.id || '',
          profile: user.photo || '',
        });

        setGoogleLoading(false);
        if (res.success) {
          Alert.alert('Welcome!', 'Signed in with Google successfully.');
          onClose();
        } else {
          Alert.alert('Google Auth Failed', res.message || 'Could not authenticate with Google.');
        }
      } else {
        setGoogleLoading(false);
        Alert.alert('Google Sign-In Error', 'Unable to retrieve user details from Google.');
      }
    } catch (error: any) {
      setGoogleLoading(false);
      if (error.code === statusCodes.SIGN_IN_CANCELLED) {
        // User cancelled the login flow
      } else if (error.code === statusCodes.IN_PROGRESS) {
        // Operation in progress
      } else if (error.code === statusCodes.PLAY_SERVICES_NOT_AVAILABLE) {
        Alert.alert('Play Services Error', 'Google Play Services are not available or out of date.');
      } else {
        console.error('Google Sign-In Error details:', error);
        Alert.alert(
          'Google Sign-In Error',
          error.message || 'Developer Error: Please verify SHA-1 fingerprint and package name in Google Console.'
        );
      }
    }
  };

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
            {/* Google Sign In Button */}
            <TouchableOpacity
              disabled={googleLoading || isLoading}
              onPress={handleGoogleSignIn}
              style={styles.googleBtn}
            >
              {googleLoading ? (
                <ActivityIndicator color="#0F172A" />
              ) : (
                <View style={styles.googleBtnContent}>
                  <Text style={styles.googleIconText}>G</Text>
                  <Text style={styles.googleBtnText}>Continue with Google</Text>
                </View>
              )}
            </TouchableOpacity>

            <View style={styles.dividerRow}>
              <View style={styles.dividerLine} />
              <Text style={[styles.dividerText, theme.secondaryText]}>OR</Text>
              <View style={styles.dividerLine} />
            </View>

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
              disabled={isLoading || googleLoading}
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
  googleBtn: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderRadius: 14,
    height: 48,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  googleBtnContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  googleIconText: {
    fontSize: 18,
    fontWeight: '900',
    color: '#4285F4',
    marginRight: 10,
  },
  googleBtnText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#1E293B',
  },
  dividerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 12,
  },
  dividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: '#E2E8F0',
  },
  dividerText: {
    marginHorizontal: 12,
    fontSize: 12,
    fontWeight: '700',
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
