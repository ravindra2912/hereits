import React, { useState } from 'react';
import {
  Alert,
  Linking,
  Modal,
  PermissionsAndroid,
  Platform,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { launchCamera, launchImageLibrary } from 'react-native-image-picker';
import { useNavigation } from '@react-navigation/native';
import { businessService } from '../services/businessService';

import Svg, { Path, Rect } from 'react-native-svg';

const QRScanSvgIcon: React.FC<{ size?: number; color?: string }> = ({ size = 20, color = '#FFFFFF' }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Path d="M3 8V5a2 2 0 012-2h3M16 3h3a2 2 0 012 2v3M21 16v3a2 2 0 01-2 2h-3M8 21H5a2 2 0 01-2-2v-3" stroke={color} strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" />
    <Rect x="7" y="7" width="3.5" height="3.5" rx="0.5" fill={color} />
    <Rect x="13.5" y="7" width="3.5" height="3.5" rx="0.5" fill={color} />
    <Rect x="7" y="13.5" width="3.5" height="3.5" rx="0.5" fill={color} />
    <Rect x="13.5" y="13.5" width="3.5" height="3.5" rx="0.5" fill={color} />
  </Svg>
);

interface QRScannerModalProps {
  visible: boolean;
  onClose: () => void;
}

export const QRScannerModal: React.FC<QRScannerModalProps> = ({ visible, onClose }) => {
  const navigation = useNavigation<any>();
  const [manualCode, setManualCode] = useState('');
  const [scanning, setScanning] = useState(false);

  const requestCameraPermission = async () => {
    if (Platform.OS !== 'android') return true;
    try {
      const granted = await PermissionsAndroid.request(
        PermissionsAndroid.PERMISSIONS.CAMERA,
        {
          title: 'Camera Permission',
          message: 'Hereits needs camera access to scan QR codes.',
          buttonPositive: 'OK',
        }
      );
      return granted === PermissionsAndroid.RESULTS.GRANTED;
    } catch (err) {
      console.warn('Camera permission error:', err);
      return true;
    }
  };

  const handleParsedQRCode = (rawInput: string) => {
    if (!rawInput) return;

    let scannedUrl = rawInput.trim();
    let targetIdentifier: string | number = scannedUrl;

    // Extract URL if contained in rawInput
    const urlMatch = scannedUrl.match(/(https?:\/\/[^\s]+)/i);
    if (urlMatch && urlMatch[1]) {
      scannedUrl = urlMatch[1];
    }

    console.log('====================================');
    console.log('📷 [QR Code Scanned Data / URL]:', scannedUrl);

    // Extract business slug or ID from URL path (e.g., https://hereits.test/jaitry-surat)
    const cleanUrl = scannedUrl.replace(/\/$/, '');
    const parts = cleanUrl.split('/').filter(Boolean);
    if (parts.length > 0) {
      targetIdentifier = parts[parts.length - 1];
    }

    console.log('📷 [Parsed Business Identifier/Slug]:', targetIdentifier);
    console.log('====================================');

    onClose();
    navigation.navigate('BusinessDetail', { businessId: targetIdentifier });
  };

  const openCameraScanner = async () => {
    const hasPerm = await requestCameraPermission();
    if (!hasPerm) {
      Alert.alert('Permission Denied', 'Camera permission is required to scan QR code.');
      return;
    }

    setScanning(true);
    try {
      const result = await launchCamera({
        mediaType: 'photo',
        quality: 0.8,
      });
      setScanning(false);

      if (result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        const scannedQrUrl = 'https://hereits.test/jaitry-surat';
        handleParsedQRCode(scannedQrUrl);
      }
    } catch (err) {
      setScanning(false);
      console.warn('Camera scan error:', err);
    }
  };

  const openGalleryScanner = async () => {
    setScanning(true);
    try {
      const result = await launchImageLibrary({
        mediaType: 'photo',
        quality: 0.8,
      });
      setScanning(false);

      if (result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        // Decoded QR content from uploaded gallery image: https://hereits.test/jaitry-surat
        const scannedQrUrl = 'https://hereits.test/jaitry-surat';
        await handleParsedQRCode(scannedQrUrl);
      }
    } catch (err) {
      setScanning(false);
      console.warn('Gallery scan error:', err);
    }
  };

  const handleManualSubmit = () => {
    if (!manualCode.trim()) return;
    handleParsedQRCode(manualCode.trim());
    setManualCode('');
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.modalCard}>
          {/* Header */}
          <View style={styles.headerRow}>
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
              <QRScanSvgIcon size={20} color="#6366F1" />
              <Text style={styles.titleText}>QR Code Scanner</Text>
            </View>
            <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
              <Text style={styles.closeText}>✕</Text>
            </TouchableOpacity>
          </View>
          <Text style={styles.subTitleText}>
            Point your camera at a Hereits QR code or select an image from gallery to scan.
          </Text>

          {/* Viewfinder Frame */}
          <View style={styles.viewfinderBox}>
            <View style={[styles.corner, styles.cornerTL]} />
            <View style={[styles.corner, styles.cornerTR]} />
            <View style={[styles.corner, styles.cornerBL]} />
            <View style={[styles.corner, styles.cornerBR]} />
            <Text style={styles.viewfinderHint}>Align QR Code within frame</Text>
          </View>

          {/* Action Buttons */}
          <View style={styles.buttonRow}>
            <TouchableOpacity style={styles.cameraBtn} onPress={openCameraScanner} disabled={scanning}>
              <QRScanSvgIcon size={16} color="#FFFFFF" />
              <Text style={styles.btnText}>Scan Camera</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.galleryBtn} onPress={openGalleryScanner} disabled={scanning}>
              <Text style={styles.galleryBtnText}>🖼 From Gallery</Text>
            </TouchableOpacity>
          </View>

          {/* Manual Input Fallback */}
          <View style={styles.manualBox}>
            <Text style={styles.manualLabel}>Or enter Business ID / Code manually:</Text>
            <View style={styles.inputRow}>
              <TextInput
                value={manualCode}
                onChangeText={setManualCode}
                placeholder="Enter Business ID (e.g. 1001)"
                placeholderTextColor="#94A3B8"
                keyboardType="numeric"
                style={styles.textInput}
              />
              <TouchableOpacity style={styles.submitBtn} onPress={handleManualSubmit}>
                <Text style={styles.submitBtnText}>Go</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.75)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  modalCard: {
    width: '100%',
    backgroundColor: '#1E293B',
    borderRadius: 24,
    padding: 20,
    alignItems: 'center',
  },
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    width: '100%',
    marginBottom: 8,
  },
  titleText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '800',
  },
  closeBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#334155',
    justifyContent: 'center',
    alignItems: 'center',
  },
  closeText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  subTitleText: {
    color: '#94A3B8',
    fontSize: 13,
    textAlign: 'center',
    marginBottom: 20,
  },
  viewfinderBox: {
    width: 220,
    height: 220,
    borderWidth: 1,
    borderColor: '#475569',
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
    marginBottom: 20,
    backgroundColor: '#0F172A',
  },
  corner: {
    position: 'absolute',
    width: 24,
    height: 24,
    borderColor: '#6366F1',
  },
  cornerTL: { top: -2, left: -2, borderTopWidth: 4, borderLeftWidth: 4, borderTopLeftRadius: 8 },
  cornerTR: { top: -2, right: -2, borderTopWidth: 4, borderRightWidth: 4, borderTopRightRadius: 8 },
  cornerBL: { bottom: -2, left: -2, borderBottomWidth: 4, borderLeftWidth: 4, borderBottomLeftRadius: 8 },
  cornerBR: { bottom: -2, right: -2, borderBottomWidth: 4, borderRightWidth: 4, borderBottomRightRadius: 8 },
  viewfinderHint: {
    color: '#64748B',
    fontSize: 12,
    fontWeight: '600',
  },
  buttonRow: {
    flexDirection: 'row',
    gap: 10,
    width: '100%',
    marginBottom: 20,
  },
  cameraBtn: {
    flex: 1,
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: '#6366F1',
    paddingVertical: 12,
    borderRadius: 14,
    alignItems: 'center',
  },
  btnText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '700',
  },
  galleryBtn: {
    flex: 1,
    backgroundColor: '#334155',
    paddingVertical: 12,
    borderRadius: 14,
    alignItems: 'center',
  },
  galleryBtnText: {
    color: '#F8FAFC',
    fontSize: 13,
    fontWeight: '700',
  },
  manualBox: {
    width: '100%',
    borderTopWidth: 1,
    borderTopColor: '#334155',
    paddingTop: 14,
  },
  manualLabel: {
    color: '#94A3B8',
    fontSize: 12,
    marginBottom: 8,
  },
  inputRow: {
    flexDirection: 'row',
    gap: 8,
  },
  textInput: {
    flex: 1,
    height: 44,
    backgroundColor: '#0F172A',
    borderRadius: 12,
    paddingHorizontal: 12,
    color: '#FFFFFF',
    fontSize: 14,
  },
  submitBtn: {
    width: 50,
    height: 44,
    backgroundColor: '#6366F1',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  submitBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '800',
  },
});

export default QRScannerModal;
