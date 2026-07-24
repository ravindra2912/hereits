import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Modal,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { useLocation } from '../context/LocationContext';
import { LocationPayload, locationService } from '../services/locationService';

interface LocationModalProps {
  visible: boolean;
  onClose: () => void;
  allowClose?: boolean;
}

export const LocationModal: React.FC<LocationModalProps> = ({
  visible,
  onClose,
  allowClose = true,
}) => {
  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  const {
    location,
    recentLocations,
    setLocationData,
    detectCurrentLocation,
    isDetectingGPS,
  } = useLocation();

  const [searchQuery, setSearchQuery] = useState('');
  const [cityResults, setCityResults] = useState<any[]>([]);
  const [isSearching, setIsSearching] = useState(false);

  const defaultCities: LocationPayload[] = [
    { type: 'search', location_name: 'Surat', full_address: 'Surat, Gujarat, India', latitude: 21.1702, longitude: 72.8311 },
    { type: 'search', location_name: 'Ahmedabad', full_address: 'Ahmedabad, Gujarat, India', latitude: 23.0225, longitude: 72.5714 },
    { type: 'search', location_name: 'Vadodara', full_address: 'Vadodara, Gujarat, India', latitude: 22.3072, longitude: 73.1812 },
    { type: 'search', location_name: 'Mumbai', full_address: 'Mumbai, Maharashtra, India', latitude: 19.076, longitude: 72.8777 },
    { type: 'search', location_name: 'Delhi', full_address: 'Delhi, India', latitude: 28.7041, longitude: 77.1025 },
  ];

  useEffect(() => {
    if (!searchQuery.trim()) {
      setCityResults([]);
      setIsSearching(false);
      return;
    }

    const timer = setTimeout(async () => {
      setIsSearching(true);
      const res = await locationService.searchCities(searchQuery);
      if (res.success && res.data) {
        setCityResults(res.data);
      }
      setIsSearching(false);
    }, 300);

    return () => clearTimeout(timer);
  }, [searchQuery]);

  const handleSelectLocation = async (loc: LocationPayload) => {
    await setLocationData(loc);
    onClose();
  };

  const handleGPSDetect = async () => {
    const loc = await detectCurrentLocation();
    if (loc) {
      onClose();
    }
  };

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.overlay}>
        <View style={[styles.content, theme.cardBg]}>
          {/* Header */}
          <View style={styles.header}>
            <View>
              <Text style={[styles.title, theme.primaryText]}>Select Location</Text>
              <Text style={[styles.subtitle, theme.secondaryText]}>
                Find businesses & services near you
              </Text>
            </View>
            {allowClose && (
              <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
                <Text style={{ fontSize: 18, color: '#64748B' }}>✕</Text>
              </TouchableOpacity>
            )}
          </View>

          {/* Current GPS Location Button */}
          <TouchableOpacity
            disabled={isDetectingGPS}
            onPress={handleGPSDetect}
            style={styles.gpsBtn}
          >
            {isDetectingGPS ? (
              <ActivityIndicator color="#FFFFFF" size="small" />
            ) : (
              <>
                <Text style={styles.gpsIcon}>🎯</Text>
                <View style={{ flex: 1, marginLeft: 10 }}>
                  <Text style={styles.gpsTitle}>Use Current Location</Text>
                  <Text style={styles.gpsSubtitle}>Detect via GPS & Device Location</Text>
                </View>
              </>
            )}
          </TouchableOpacity>

          {/* Search Box */}
          <View style={[styles.searchBox, theme.searchBg]}>
            <Text style={{ fontSize: 16, marginRight: 8 }}>🔍</Text>
            <TextInput
              value={searchQuery}
              onChangeText={setSearchQuery}
              placeholder="Search city, area, or Google location..."
              placeholderTextColor={isDarkMode ? '#64748B' : '#94A3B8'}
              style={[styles.searchInput, theme.primaryText]}
            />
            {searchQuery !== '' && (
              <TouchableOpacity onPress={() => setSearchQuery('')}>
                <Text style={{ fontSize: 16, color: '#64748B' }}>✕</Text>
              </TouchableOpacity>
            )}
          </View>

          {/* Search Results / Default Cities */}
          {isSearching ? (
            <ActivityIndicator size="small" color="#6366F1" style={{ marginVertical: 20 }} />
          ) : cityResults.length > 0 ? (
            <View style={{ maxHeight: 220 }}>
              <Text style={[styles.sectionHeading, theme.secondaryText]}>SEARCH RESULTS</Text>
              <FlatList
                data={cityResults}
                keyExtractor={item => String(item.id)}
                renderItem={({ item }) => (
                  <TouchableOpacity
                    onPress={() =>
                      handleSelectLocation({
                        type: 'search',
                        location_name: item.name,
                        full_address: `${item.name}, ${item.state?.name || 'India'}`,
                        latitude: 21.1702,
                        longitude: 72.8311,
                        city_id: item.id,
                      })
                    }
                    style={[styles.cityRow, theme.borderBottom]}
                  >
                    <Text style={{ fontSize: 16, marginRight: 10 }}>📍</Text>
                    <View>
                      <Text style={[styles.cityName, theme.primaryText]}>{item.name}</Text>
                      <Text style={[styles.stateName, theme.secondaryText]}>
                        {item.state?.name || 'India'}
                      </Text>
                    </View>
                  </TouchableOpacity>
                )}
              />
            </View>
          ) : (
            <View>
              {/* Recent Locations if any */}
              {recentLocations.length > 0 && (
                <View style={{ marginBottom: 16 }}>
                  <Text style={[styles.sectionHeading, theme.secondaryText]}>
                    RECENT LOCATIONS
                  </Text>
                  <View style={styles.chipRow}>
                    {recentLocations.map((loc, idx) => (
                      <TouchableOpacity
                        key={idx}
                        onPress={() => handleSelectLocation(loc)}
                        style={[styles.chip, theme.chipBg]}
                      >
                        <Text style={[styles.chipText, theme.primaryText]}>
                          🕒 {loc.location_name}
                        </Text>
                      </TouchableOpacity>
                    ))}
                  </View>
                </View>
              )}

              {/* Popular Cities */}
              <Text style={[styles.sectionHeading, theme.secondaryText]}>POPULAR CITIES</Text>
              <View style={styles.chipRow}>
                {defaultCities.map((loc, idx) => (
                  <TouchableOpacity
                    key={idx}
                    onPress={() => handleSelectLocation(loc)}
                    style={[styles.chip, theme.chipBg]}
                  >
                    <Text style={[styles.chipText, theme.primaryText]}>
                      🏢 {loc.location_name}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
            </View>
          )}
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
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  title: {
    fontSize: 20,
    fontWeight: '800',
  },
  subtitle: {
    fontSize: 13,
    marginTop: 2,
  },
  closeBtn: {
    padding: 4,
  },
  gpsBtn: {
    backgroundColor: '#6366F1',
    borderRadius: 16,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  gpsIcon: {
    fontSize: 24,
  },
  gpsTitle: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '800',
  },
  gpsSubtitle: {
    color: '#EEF2FF',
    fontSize: 12,
    marginTop: 2,
  },
  searchBox: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    borderRadius: 14,
    height: 48,
    marginBottom: 16,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
  },
  sectionHeading: {
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 1,
    marginBottom: 10,
  },
  chipRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 10,
  },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    marginRight: 8,
    marginBottom: 8,
  },
  chipText: {
    fontSize: 13,
    fontWeight: '600',
  },
  cityRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
  },
  cityName: {
    fontSize: 15,
    fontWeight: '700',
  },
  stateName: {
    fontSize: 12,
  },
});

const lightTheme = StyleSheet.create({
  cardBg: { backgroundColor: '#FFFFFF' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
  searchBg: { backgroundColor: '#F1F5F9' },
  chipBg: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0' },
  borderBottom: { borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
});

const darkTheme = StyleSheet.create({
  cardBg: { backgroundColor: '#1E293B' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  searchBg: { backgroundColor: '#0F172A' },
  chipBg: { backgroundColor: '#334155' },
  borderBottom: { borderBottomWidth: 1, borderBottomColor: '#334155' },
});

export default LocationModal;
