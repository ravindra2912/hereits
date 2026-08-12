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
import { SafeAreaView } from 'react-native-safe-area-context';
import { useLocation } from '../context/LocationContext';
import { LocationPayload } from '../services/locationService';
import { GOOGLE_MAP_KEY } from '@env';

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

  useEffect(() => {
    console.log("LocationModal: GOOGLE_MAP_KEY =", GOOGLE_MAP_KEY);
  }, []);

  const fetchPlaces = async (query: string) => {
    try {
      const response = await fetch(
        `https://maps.googleapis.com/maps/api/place/autocomplete/json?input=${encodeURIComponent(
          query
        )}&key=${GOOGLE_MAP_KEY}&components=country:in&language=en`
      );
      const json = await response.json();
      if (json.status === 'OK' && json.predictions) {
        return json.predictions;
      } else {
        console.warn('Places autocomplete status:', json.status, json.error_message);
        return [];
      }
    } catch (error) {
      console.error('Places autocomplete fetch error:', error);
      return [];
    }
  };

  const fetchPlaceDetails = async (placeId: string) => {
    try {
      const response = await fetch(
        `https://maps.googleapis.com/maps/api/place/details/json?place_id=${placeId}&key=${GOOGLE_MAP_KEY}&fields=geometry,formatted_address,address_components`
      );
      const json = await response.json();
      if (json.status === 'OK' && json.result) {
        return json.result;
      } else {
        console.warn('Place details status:', json.status, json.error_message);
        return null;
      }
    } catch (error) {
      console.error('Place details fetch error:', error);
      return null;
    }
  };

  useEffect(() => {
    if (!searchQuery.trim()) {
      setCityResults([]);
      setIsSearching(false);
      return;
    }

    const timer = setTimeout(async () => {
      setIsSearching(true);
      const predictions = await fetchPlaces(searchQuery);
      setCityResults(predictions);
      setIsSearching(false);
    }, 400);

    return () => clearTimeout(timer);
  }, [searchQuery]);

  const handleSelectLocation = async (loc: LocationPayload) => {
    await setLocationData(loc);
    setSearchQuery('');
    onClose();
  };

  const handleSelectPrediction = async (prediction: any) => {
    setIsSearching(true);
    const details = await fetchPlaceDetails(prediction.place_id);
    setIsSearching(false);
    if (!details) {
      console.warn('Failed to get location details.');
      return;
    }

    const lat = details.geometry.location.lat;
    const lng = details.geometry.location.lng;

    // Extract Area and City from address components
    let area = '';
    let city = '';
    if (details.address_components) {
      for (const component of details.address_components) {
        if (component.types.includes('sublocality_level_1')) {
          area = component.long_name;
        }
        if (component.types.includes('locality')) {
          city = component.long_name;
        }
        if (!area && component.types.includes('sublocality')) {
          area = component.long_name;
        }
      }
    }

    const locationName = area && city ? `${area}, ${city}` : (city || area || prediction.structured_formatting?.main_text || prediction.description);
    const fullAddress = details.formatted_address || prediction.description;

    let areaLatLong = undefined;
    if (details.geometry && details.geometry.viewport) {
      const sw = details.geometry.viewport.southwest;
      const ne = details.geometry.viewport.northeast;
      areaLatLong = `${sw.lat},${sw.lng},${ne.lat},${ne.lng}`;
    }

    handleSelectLocation({
      type: 'search',
      location_name: locationName,
      full_address: fullAddress,
      latitude: lat,
      longitude: lng,
      area_lat_long: areaLatLong,
    });
  };

  const handleGPSDetect = async () => {
    const loc = await detectCurrentLocation();
    if (loc) {
      onClose();
    }
  };

  return (
    <Modal visible={visible} animationType="slide" transparent={false}>
      <SafeAreaView style={[styles.container, theme.cardBg]}>
        <View style={styles.content}>
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
          ) : searchQuery.trim().length > 0 ? (
            <View style={{ flex: 1 }}>
              <Text style={[styles.sectionHeading, theme.secondaryText]}>SEARCH RESULTS</Text>
              {cityResults.length === 0 ? (
                <Text style={{ textAlign: 'center', marginVertical: 20, color: '#64748B' }}>No results found</Text>
              ) : (
                <FlatList
                  data={cityResults}
                  keyExtractor={item => item.place_id}
                  keyboardShouldPersistTaps="handled"
                  renderItem={({ item }) => (
                    <TouchableOpacity
                      onPress={() => handleSelectPrediction(item)}
                      style={[styles.cityRow, theme.borderBottom]}
                    >
                      <Text style={{ fontSize: 16, marginRight: 10 }}>📍</Text>
                      <View style={{ flex: 1 }}>
                        <Text style={[styles.cityName, theme.primaryText]}>
                          {item.structured_formatting?.main_text || item.description}
                        </Text>
                        <Text style={[styles.stateName, theme.secondaryText]}>
                          {item.structured_formatting?.secondary_text || ''}
                        </Text>
                      </View>
                    </TouchableOpacity>
                  )}
                />
              )}
            </View>
          ) : (
            <View style={{ flex: 1 }}>
              {/* Recent Locations if any */}
              {recentLocations.length > 0 && (
                <View style={{ marginBottom: 16 }}>
                  <Text style={[styles.sectionHeading, theme.secondaryText]}>
                    RECENT LOCATIONS
                  </Text>
                  <View>
                    {recentLocations.map((loc, idx) => (
                      <TouchableOpacity
                        key={idx}
                        onPress={() => handleSelectLocation(loc)}
                        style={[styles.cityRow, theme.borderBottom]}
                      >
                        <Text style={{ fontSize: 16, marginRight: 10 }}>🕒</Text>
                        <View style={{ flex: 1 }}>
                          <Text style={[styles.cityName, theme.primaryText]}>
                            {loc.location_name}
                          </Text>
                          {loc.full_address ? (
                            <Text style={[styles.stateName, theme.secondaryText]}>
                              {loc.full_address}
                            </Text>
                          ) : null}
                        </View>
                      </TouchableOpacity>
                    ))}
                  </View>
                </View>
              )}
            </View>
          )}
        </View>
      </SafeAreaView>
    </Modal>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  content: {
    flex: 1,
    padding: 20,
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
