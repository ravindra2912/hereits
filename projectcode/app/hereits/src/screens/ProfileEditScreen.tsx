import React, { useState, useEffect } from 'react';
import {
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  TextInput,
  ActivityIndicator,
  Alert,
  Modal,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import { authService } from '../services/authService';
import FallbackImage from '../components/FallbackImage';
import { launchImageLibrary } from 'react-native-image-picker';

export const ProfileEditScreen: React.FC = () => {
  const navigation = useNavigation<any>();
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const { user, refreshProfile } = useAuth();

  // Edit Profile State
  const [editFirstName, setEditFirstName] = useState('');
  const [editLastName, setEditLastName] = useState('');
  const [editContact, setEditContact] = useState('');
  const [editDob, setEditDob] = useState('');
  const [isSavingProfile, setIsSavingProfile] = useState(false);

  // Profile Image State
  const [selectedImage, setSelectedImage] = useState<any>(null);
  const [selectedImageUri, setSelectedImageUri] = useState<string | null>(null);

  // Change Password Modal State
  const [changePasswordVisible, setChangePasswordVisible] = useState(false);
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmNewPassword, setConfirmNewPassword] = useState('');
  const [isSavingPassword, setIsSavingPassword] = useState(false);

  useEffect(() => {
    if (user) {
      setEditFirstName(user.first_name || '');
      setEditLastName(user.last_name || '');
      setEditContact(user.contact ? String(user.contact) : '');
      setEditDob(user.dob || '');
    }
  }, [user]);

  const handlePickImage = () => {
    launchImageLibrary(
      {
        mediaType: 'photo',
        quality: 0.8,
      },
      (response) => {
        if (response.didCancel) return;
        if (response.errorCode) {
          Alert.alert('Error', response.errorMessage || 'Failed to select image.');
          return;
        }
        if (response.assets && response.assets.length > 0) {
          const asset = response.assets[0];
          setSelectedImage(asset);
          setSelectedImageUri(asset.uri || null);
        }
      }
    );
  };

  const handleUpdateProfile = async () => {
    if (!editFirstName || !editLastName) {
      Alert.alert('Required', 'First name and last name are required.');
      return;
    }
    setIsSavingProfile(true);

    const formData = new FormData();
    formData.append('first_name', editFirstName);
    formData.append('last_name', editLastName);
    if (editContact) formData.append('contact', editContact);
    if (editDob) formData.append('dob', editDob);

    if (selectedImage) {
      formData.append('profile', {
        uri: selectedImage.uri,
        name: selectedImage.fileName || 'profile.jpg',
        type: selectedImage.type || 'image/jpeg',
      } as any);
    }

    const res = await authService.updateProfile(formData);
    setIsSavingProfile(false);
    if (res.success) {
      Alert.alert('Success', 'Profile updated successfully.');
      setSelectedImage(null);
      setSelectedImageUri(null);
      refreshProfile();
      navigation.goBack();
    } else {
      Alert.alert('Error', res.message || 'Failed to update profile.');
    }
  };

  const handleUpdatePassword = async () => {
    if (!currentPassword || !newPassword || !confirmNewPassword) {
      Alert.alert('Required', 'Please fill all password fields.');
      return;
    }
    if (newPassword !== confirmNewPassword) {
      Alert.alert('Error', 'New password and confirm password do not match.');
      return;
    }
    setIsSavingPassword(true);
    const res = await authService.updatePassword({
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: confirmNewPassword,
    });
    setIsSavingPassword(false);
    if (res.success) {
      Alert.alert('Success', 'Password changed successfully.');
      setChangePasswordVisible(false);
      setCurrentPassword('');
      setNewPassword('');
      setConfirmNewPassword('');
    } else {
      Alert.alert('Error', res.message || 'Failed to update password.');
    }
  };

  return (
    <ScrollView style={[styles.container, theme.background]}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Text style={[styles.backText, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.title, theme.primaryText]}>Edit Profile</Text>
        <View style={{ width: 60 }} />
      </View>

      {/* Profile Image Select */}
      <View style={{ alignItems: 'center', marginBottom: 24, marginTop: 16 }}>
        <View style={{ position: 'relative' }}>
          <TouchableOpacity onPress={handlePickImage} style={styles.avatarContainer}>
            <FallbackImage
              source={selectedImageUri ? { uri: selectedImageUri } : (user && user.profile ? { uri: user.profile } : null)}
              type="user"
              style={styles.avatar}
            />
          </TouchableOpacity>
          <TouchableOpacity onPress={handlePickImage} style={styles.editImageBadge}>
            <Text style={{ fontSize: 12, color: '#FFF' }}>📷</Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* Form Fields */}
      <View style={[styles.card, theme.cardBg]}>
        <Text style={[styles.label, theme.primaryText]}>First Name</Text>
        <TextInput
          value={editFirstName}
          onChangeText={setEditFirstName}
          placeholder="First Name"
          placeholderTextColor="#94A3B8"
          style={[styles.input, theme.primaryText]}
        />

        <Text style={[styles.label, theme.primaryText]}>Last Name</Text>
        <TextInput
          value={editLastName}
          onChangeText={setEditLastName}
          placeholder="Last Name"
          placeholderTextColor="#94A3B8"
          style={[styles.input, theme.primaryText]}
        />

        <Text style={[styles.label, theme.primaryText]}>Contact Number</Text>
        <TextInput
          value={editContact}
          onChangeText={setEditContact}
          keyboardType="phone-pad"
          placeholder="10 digit contact"
          placeholderTextColor="#94A3B8"
          style={[styles.input, theme.primaryText]}
        />

        <Text style={[styles.label, theme.primaryText]}>Date of Birth</Text>
        <TextInput
          value={editDob}
          onChangeText={setEditDob}
          placeholder="YYYY-MM-DD"
          placeholderTextColor="#94A3B8"
          style={[styles.input, theme.primaryText]}
        />

        <TouchableOpacity
          disabled={isSavingProfile}
          onPress={handleUpdateProfile}
          style={styles.submitBtn}
        >
          {isSavingProfile ? (
            <ActivityIndicator color="#FFF" />
          ) : (
            <Text style={styles.submitBtnText}>Save Changes</Text>
          )}
        </TouchableOpacity>
      </View>

      {/* Settings Options inside Profile Edit Screen */}
      <View style={{ marginTop: 24, marginBottom: 40 }}>
        <TouchableOpacity
          onPress={() => setChangePasswordVisible(true)}
          style={[styles.actionRow, theme.cardBg]}
        >
          <Text style={styles.actionIcon}>🔑</Text>
          <Text style={[styles.actionLabel, theme.primaryText]}>Change Password</Text>
          <Text style={styles.arrowIcon}>›</Text>
        </TouchableOpacity>
      </View>

      {/* Change Password Modal */}
      <Modal visible={changePasswordVisible} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, theme.cardBg]}>
            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
              <Text style={[styles.modalTitle, theme.primaryText]}>Change Password</Text>
              <TouchableOpacity onPress={() => setChangePasswordVisible(false)}>
                <Text style={{ fontSize: 18, color: '#64748B' }}>✕</Text>
              </TouchableOpacity>
            </View>

            <Text style={[styles.modalLabel, theme.primaryText]}>Current Password</Text>
            <TextInput
              value={currentPassword}
              onChangeText={setCurrentPassword}
              secureTextEntry
              placeholder="••••••••"
              placeholderTextColor="#94A3B8"
              style={[styles.modalInput, theme.primaryText]}
            />

            <Text style={[styles.modalLabel, theme.primaryText]}>New Password</Text>
            <TextInput
              value={newPassword}
              onChangeText={setNewPassword}
              secureTextEntry
              placeholder="••••••••"
              placeholderTextColor="#94A3B8"
              style={[styles.modalInput, theme.primaryText]}
            />

            <Text style={[styles.modalLabel, theme.primaryText]}>Confirm New Password</Text>
            <TextInput
              value={confirmNewPassword}
              onChangeText={setConfirmNewPassword}
              secureTextEntry
              placeholder="••••••••"
              placeholderTextColor="#94A3B8"
              style={[styles.modalInput, theme.primaryText]}
            />

            <TouchableOpacity
              disabled={isSavingPassword}
              onPress={handleUpdatePassword}
              style={styles.modalSubmitBtn}
            >
              {isSavingPassword ? (
                <ActivityIndicator color="#FFF" />
              ) : (
                <Text style={styles.modalSubmitBtnText}>Update Password</Text>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, paddingHorizontal: 20, paddingTop: 16 },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 16,
  },
  backBtn: {
    paddingVertical: 8,
    width: 60,
  },
  backText: {
    fontSize: 14,
    fontWeight: '700',
  },
  title: {
    fontSize: 18,
    fontWeight: '800',
    flex: 1,
    textAlign: 'center',
  },
  avatarContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
    overflow: 'hidden',
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
  },
  avatarPlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    justifyContent: 'center',
    alignItems: 'center',
  },
  editImageBadge: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: '#6366F1',
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#FFF',
  },
  card: {
    padding: 20,
    borderRadius: 20,
    marginBottom: 16,
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
  actionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 14,
  },
  actionIcon: { fontSize: 18, marginRight: 12 },
  actionLabel: { flex: 1, fontSize: 14, fontWeight: '600' },
  arrowIcon: { fontSize: 18, color: '#94A3B8' },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 20,
    maxHeight: '85%',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  modalLabel: {
    fontSize: 13,
    fontWeight: '700',
    marginBottom: 6,
    marginTop: 8,
  },
  modalInput: {
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 12,
    paddingHorizontal: 14,
    height: 46,
    marginBottom: 10,
  },
  modalSubmitBtn: {
    backgroundColor: '#6366F1',
    borderRadius: 14,
    height: 50,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 16,
  },
  modalSubmitBtnText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '800',
  },
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

export default ProfileEditScreen;
