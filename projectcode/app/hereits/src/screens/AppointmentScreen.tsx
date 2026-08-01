import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Linking,
  Modal,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useFocusEffect, useNavigation, useRoute } from '@react-navigation/native';
import { AppointmentCardSkeleton } from '../components/SkeletonLoader';
import { appointmentService } from '../services/appointmentService';
import { useAuth } from '../context/AuthContext';
import FallbackImage from '../components/FallbackImage';

interface AppointmentScreenProps {
  initialBusinessId?: number | null;
  initialBusinessName?: string | null;
  onCloseBookingModal?: () => void;
}

export const AppointmentScreen: React.FC<AppointmentScreenProps> = ({
  onCloseBookingModal,
}) => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;
  const { user } = useAuth();

  const [appointments, setAppointments] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  // Selected Appointment for Details Modal
  const [selectedAppointment, setSelectedAppointment] = useState<any>(null);
  const [detailsModalVisible, setDetailsModalVisible] = useState<boolean>(false);

  // Booking Modal States
  const [modalVisible, setModalVisible] = useState<boolean>(false);
  const [businessId, setBusinessId] = useState<number | null>(null);
  const [businessName, setBusinessName] = useState<string | null>(null);
  const [experts, setExperts] = useState<any[]>([]);
  const [selectedExpert, setSelectedExpert] = useState<any>(null);
  const [bookingDate, setBookingDate] = useState<string>('2026-07-22');
  const [slots, setSlots] = useState<any[]>([]);
  const [selectedSlot, setSelectedSlot] = useState<any>(null);
  const [userName, setUserName] = useState<string>(user ? `${user.first_name || ''} ${user.last_name || ''}`.trim() : '');
  const [userContact, setUserContact] = useState<string>(user?.contact ? String(user.contact) : '');
  const [note, setNote] = useState<string>('');
  const [submitting, setSubmitting] = useState(false);

  const fetchMyAppointments = async (pageNum: number = 1, isRefresh: boolean = false) => {
    if (pageNum === 1 && !isRefresh) setLoading(true);

    try {
      const res = await appointmentService.getMyAppointments(pageNum, 10);
      if (res.success && res.data) {
        const newItems = Array.isArray(res.data) ? res.data : [];
        if (pageNum === 1) {
          setAppointments(newItems);
        } else {
          setAppointments(prev => [...prev, ...newItems]);
        }

        const paginationInfo = res.pagination;
        if (paginationInfo && typeof paginationInfo.has_more === 'boolean') {
          setHasMore(paginationInfo.has_more);
        } else {
          setHasMore(newItems.length >= 10);
        }
        setPage(pageNum);
      }
    } catch (e) {
      console.warn('Failed to load appointments:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
      setLoadingMore(false);
    }
  };

  const handleRefresh = () => {
    setRefreshing(true);
    fetchMyAppointments(1, true);
  };

  const handleLoadMore = () => {
    if (!loadingMore && hasMore && !loading && !refreshing) {
      setLoadingMore(true);
      fetchMyAppointments(page + 1);
    }
  };

  useFocusEffect(
    useCallback(() => {
      fetchMyAppointments(1, true);
    }, [])
  );

  // Reactively trigger booking modal when params are updated
  useEffect(() => {
    if (route.params?.businessId) {
      const bId = route.params.businessId;
      setBusinessId(bId);
      setBusinessName(route.params.businessName || 'Shop');
      setModalVisible(true);
      loadExperts(bId);
    }
  }, [route.params?.businessId, route.params?.businessName]);

  const handleClose = () => {
    setModalVisible(false);
    if (onCloseBookingModal) {
      onCloseBookingModal();
    }
    navigation.setParams({ businessId: null, businessName: null });
  };

  const loadExperts = async (bId: number) => {
    const res = await appointmentService.getExperts(bId);
    if (res.success && res.data) {
      setExperts(res.data);
      if (res.data.length > 0) {
        setSelectedExpert(res.data[0]);
        loadSlots(res.data[0].id, bookingDate);
      }
    }
  };

  const loadSlots = async (expId: number, dateStr: string) => {
    const res = await appointmentService.getExpertTiming(expId, dateStr);
    if (res.success && res.data?.slots) {
      setSlots(res.data.slots);
    }
  };

  const handleBookSubmit = async () => {
    if (!businessId || !selectedExpert || !selectedSlot) {
      Alert.alert('Incomplete', 'Please select an expert and a time slot.');
      return;
    }
    if (!userName || !userContact) {
      Alert.alert('Missing Details', 'Please provide your name and contact number.');
      return;
    }

    setSubmitting(true);
    const res = await appointmentService.bookAppointment({
      business_id: businessId,
      expert_id: selectedExpert.id,
      booking_date: bookingDate,
      slot_start_time: selectedSlot.start_time,
      slot_end_time: selectedSlot.end_time,
      user_name: userName,
      user_contact: userContact,
      note,
    });
    setSubmitting(false);

    if (res.success) {
      Alert.alert('Success', 'Your appointment has been booked!');
      handleClose();
      fetchMyAppointments();
    } else {
      Alert.alert('Booking Error', res.message || 'Failed to book appointment.');
    }
  };

  const handleOpenDetails = (item: any) => {
    setSelectedAppointment(item);
    setDetailsModalVisible(true);
  };

  const canGoBack = navigation.canGoBack();

  return (
    <View style={[styles.container, theme.background]}>
      {/* Header */}
      <View style={styles.header}>
        <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 4 }}>
          {canGoBack && (
            <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
              <Text style={[styles.backText, theme.primaryText]}>← Back</Text>
            </TouchableOpacity>
          )}
          <Text style={[styles.title, theme.primaryText]}>My Appointments</Text>
        </View>
        <Text style={[styles.subtitle, theme.secondaryText]}>
          Track your booked specialist appointments & queue tokens
        </Text>
      </View>

      {/* Appointments List */}
      {loading ? (
        <View style={styles.listContent}>
          {Array.from({ length: 3 }).map((_, index) => (
            <AppointmentCardSkeleton key={`skeleton-${index}`} theme={theme} />
          ))}
        </View>
      ) : (
        <FlatList
          data={appointments}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.listContent}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} tintColor="#6366F1" />
          }
          onEndReached={handleLoadMore}
          onEndReachedThreshold={0.4}
          ListFooterComponent={
            loadingMore ? (
              <View style={{ paddingVertical: 16, alignItems: 'center' }}>
                <ActivityIndicator color="#6366F1" size="small" />
              </View>
            ) : null
          }
          renderItem={({ item }) => (
            <TouchableOpacity
              style={[styles.bookingCard, theme.cardBg]}
              onPress={() => handleOpenDetails(item)}
              activeOpacity={0.8}
            >
              <View style={styles.cardHeader}>
                <View style={{ flex: 1, marginRight: 8 }}>
                  <Text style={[styles.bizName, theme.primaryText]} numberOfLines={1}>
                    {item.business?.name || 'Local Business'}
                  </Text>
                  <Text style={[styles.expertText, theme.secondaryText]} numberOfLines={1}>
                    👨‍⚕️ {item.expert?.expert_name || 'Specialist'}
                  </Text>
                </View>
                <View style={styles.tokenBadge}>
                  <Text style={styles.tokenBadgeText}>Token #{item.token_number}</Text>
                </View>
              </View>

              <View style={styles.timeRow}>
                <Text style={[styles.timeText, theme.primaryText]}>
                  📅 {item.booking_date} {item.slot_start_time ? `| ⏰ ${String(item.slot_start_time).substring(0, 5)}` : ''}
                </Text>
              </View>

              <View style={styles.cardFooterRow}>
                <Text style={[styles.statusTag, getStatusStyle(item.status)]}>
                  {getStatusText(item.status)}
                </Text>
                <Text style={styles.viewDetailsText}>View Details →</Text>
              </View>
            </TouchableOpacity>
          )}
          ListEmptyComponent={
            <View style={styles.emptyView}>
              <Text style={{ fontSize: 40, marginBottom: 12 }}>📅</Text>
              <Text style={[styles.emptyTitle, theme.primaryText]}>No Booked Appointments</Text>
              <Text style={[styles.emptyText, theme.secondaryText]}>
                Bookings made with specialists will appear here.
              </Text>
            </View>
          }
        />
      )}

      {/* Appointment Details Modal */}
      <Modal visible={detailsModalVisible} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, theme.cardBg]}>
            <View style={styles.modalHeader}>
              <Text style={[styles.modalTitle, theme.primaryText]}>Appointment Details</Text>
              <TouchableOpacity onPress={() => setDetailsModalVisible(false)} style={{ padding: 4 }}>
                <Text style={{ fontSize: 20, color: '#64748B', fontWeight: 'bold' }}>✕</Text>
              </TouchableOpacity>
            </View>

            {selectedAppointment && (
              <ScrollView contentContainerStyle={{ paddingBottom: 24 }} showsVerticalScrollIndicator={false}>
                {/* Token & Status Banner */}
                <View style={styles.detailsHeaderBox}>
                  <View style={styles.tokenLargeBadge}>
                    <Text style={styles.tokenLargeLabel}>TOKEN NUMBER</Text>
                    <Text style={styles.tokenLargeNumber}>#{selectedAppointment.token_number}</Text>
                  </View>

                  <View style={[styles.statusTagLarge, getStatusStyle(selectedAppointment.status)]}>
                    <Text style={[styles.statusTagLargeText, { color: getStatusStyle(selectedAppointment.status).color }]}>
                      {getStatusText(selectedAppointment.status)}
                    </Text>
                  </View>
                </View>

                {/* Specialist Box */}
                <View style={[styles.detailsSectionBox, theme.cardBg]}>
                  <Text style={styles.detailsBoxLabel}>SPECIALIST DETAILS</Text>
                  <View style={{ flexDirection: 'row', alignItems: 'center', marginTop: 8 }}>
                    <FallbackImage
                      source={selectedAppointment.expert?.expert_image ? { uri: selectedAppointment.expert.expert_image } : null}
                      fallbackSource={require('../assets/business_icon.png')}
                      style={{ width: 50, height: 50, borderRadius: 12, marginRight: 12 }}
                    />
                    <View style={{ flex: 1 }}>
                      <Text style={[styles.detailsMainTitle, theme.primaryText]}>
                        {selectedAppointment.expert?.expert_name || 'Specialist'}
                      </Text>
                      <Text style={[styles.detailsSubTitle, theme.secondaryText]}>
                        {selectedAppointment.expert?.title || 'Professional Specialist'}
                      </Text>
                    </View>
                  </View>
                </View>

                {/* Business Box */}
                <View style={[styles.detailsSectionBox, theme.cardBg]}>
                  <Text style={styles.detailsBoxLabel}>BUSINESS LOCATION</Text>
                  <Text style={[styles.detailsMainTitle, theme.primaryText, { marginTop: 6 }]}>
                    {selectedAppointment.business?.name || 'Local Business'}
                  </Text>
                  {selectedAppointment.business?.address && (
                    <Text style={[styles.detailsSubTitle, theme.secondaryText, { marginTop: 4 }]}>
                      📍 {selectedAppointment.business.address}
                    </Text>
                  )}
                  {selectedAppointment.business?.contact && (
                    <Text style={[styles.detailsSubTitle, theme.secondaryText, { marginTop: 2 }]}>
                      📞 {selectedAppointment.business.contact}
                    </Text>
                  )}
                </View>

                {/* Booking Time Box */}
                <View style={[styles.detailsSectionBox, theme.cardBg]}>
                  <Text style={styles.detailsBoxLabel}>DATE & TIME</Text>
                  <Text style={[styles.detailsMainTitle, theme.primaryText, { marginTop: 6 }]}>
                    📅 {selectedAppointment.booking_date}
                  </Text>
                  {selectedAppointment.slot_start_time ? (
                    <Text style={[styles.detailsSubTitle, theme.secondaryText, { marginTop: 4 }]}>
                      ⏰ {String(selectedAppointment.slot_start_time).substring(0, 5)} - {selectedAppointment.slot_end_time ? String(selectedAppointment.slot_end_time).substring(0, 5) : ''}
                    </Text>
                  ) : (
                    <Text style={[styles.detailsSubTitle, theme.secondaryText, { marginTop: 4 }]}>
                      🎟️ Queue System (First-come basis for the day)
                    </Text>
                  )}
                </View>

                {/* Patient / Guest Info */}
                <View style={[styles.detailsSectionBox, theme.cardBg]}>
                  <Text style={styles.detailsBoxLabel}>APPOINTMENT FOR</Text>
                  <Text style={[styles.detailsMainTitle, theme.primaryText, { marginTop: 6 }]}>
                    👤 {selectedAppointment.user_name || 'Customer'}
                    {selectedAppointment.appointment_for === 'other' ? ' (Guest / Someone Else)' : ' (Myself)'}
                  </Text>
                  {selectedAppointment.user_contact && (
                    <Text style={[styles.detailsSubTitle, theme.secondaryText, { marginTop: 4 }]}>
                      📞 {selectedAppointment.user_contact}
                    </Text>
                  )}
                </View>

                {/* Note */}
                {selectedAppointment.note ? (
                  <View style={[styles.detailsSectionBox, theme.cardBg]}>
                    <Text style={styles.detailsBoxLabel}>NOTE / INSTRUCTIONS</Text>
                    <Text style={[styles.detailsSubTitle, theme.primaryText, { marginTop: 6 }]}>
                      {selectedAppointment.note}
                    </Text>
                  </View>
                ) : null}

                {/* Action Buttons */}
                <View style={{ flexDirection: 'row', gap: 10, marginTop: 16 }}>
                  {selectedAppointment.business?.contact && (
                    <TouchableOpacity
                      style={[styles.modalActionBtn, { backgroundColor: '#10B981' }]}
                      onPress={() => Linking.openURL(`tel:${selectedAppointment.business.contact}`)}
                    >
                      <Text style={styles.modalActionBtnText}>📞 Call Business</Text>
                    </TouchableOpacity>
                  )}

                  <TouchableOpacity
                    style={[styles.modalActionBtn, { backgroundColor: '#6366F1' }]}
                    onPress={() => {
                      setDetailsModalVisible(false);
                      navigation.navigate('SpecialistDetail', { specialistId: selectedAppointment.expert_id });
                    }}
                  >
                    <Text style={styles.modalActionBtnText}>👨‍⚕️ Specialist Profile</Text>
                  </TouchableOpacity>
                </View>
              </ScrollView>
            )}
          </View>
        </View>
      </Modal>

      {/* Booking Modal */}
      <Modal visible={modalVisible} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, theme.cardBg]}>
            <View style={styles.modalHeader}>
              <Text style={[styles.modalTitle, theme.primaryText]}>
                Book Appointment ({businessName || 'Shop'})
              </Text>
              <TouchableOpacity onPress={handleClose}>
                <Text style={{ fontSize: 18, color: '#64748B' }}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.modalScroll}>
              <Text style={[styles.fieldLabel, theme.primaryText]}>Select Specialist / Expert</Text>
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 16 }}>
                {experts.map(exp => {
                  const isSel = selectedExpert?.id === exp.id;
                  return (
                    <TouchableOpacity
                      key={exp.id}
                      onPress={() => {
                        setSelectedExpert(exp);
                        loadSlots(exp.id, bookingDate);
                      }}
                      style={[styles.expertChip, isSel ? styles.selChip : styles.unselChip]}
                    >
                      <Text style={[styles.chipTitle, isSel ? { color: '#FFF' } : theme.primaryText]}>
                        {exp.expert_name}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </ScrollView>

              <Text style={[styles.fieldLabel, theme.primaryText]}>Available Time Slots</Text>
              <View style={styles.slotsGrid}>
                {slots.map((s, idx) => {
                  const isSel = selectedSlot?.start_time === s.start_time;
                  return (
                    <TouchableOpacity
                      key={idx}
                      disabled={!s.is_available}
                      onPress={() => setSelectedSlot(s)}
                      style={[
                        styles.slotBox,
                        !s.is_available && styles.disabledSlot,
                        isSel && styles.selectedSlot,
                      ]}
                    >
                      <Text
                        style={[
                          styles.slotText,
                          isSel && { color: '#FFF', fontWeight: '800' },
                          !s.is_available && { color: '#94A3B8' },
                        ]}
                      >
                        {s.display_time}
                      </Text>
                    </TouchableOpacity>
                  );
                })}
              </View>

              <Text style={[styles.fieldLabel, theme.primaryText]}>Your Name</Text>
              <TextInput
                value={userName}
                onChangeText={setUserName}
                style={[styles.input, theme.primaryText]}
                placeholder="Full Name"
              />

              <Text style={[styles.fieldLabel, theme.primaryText]}>Contact Number</Text>
              <TextInput
                value={userContact}
                onChangeText={setUserContact}
                style={[styles.input, theme.primaryText]}
                keyboardType="phone-pad"
                placeholder="10 digit contact"
              />

              <TouchableOpacity
                disabled={submitting}
                onPress={handleBookSubmit}
                style={styles.confirmBtn}
              >
                {submitting ? (
                  <ActivityIndicator color="#FFF" />
                ) : (
                  <Text style={styles.confirmBtnText}>Confirm Booking</Text>
                )}
              </TouchableOpacity>
            </ScrollView>
          </View>
        </View>
      </Modal>
    </View>
  );
};

function getStatusText(status: string | number) {
  const s = String(status).toLowerCase();
  if (s === 'confirmed' || s === '1') return 'Confirmed';
  if (s === 'completed' || s === '2') return 'Completed';
  if (s === 'cancel' || s === 'cancelled' || s === '3') return 'Cancelled';
  if (s === 'in_progress') return 'In Progress';
  return 'Pending Confirmation';
}

function getStatusStyle(status: string | number) {
  const s = String(status).toLowerCase();
  if (s === 'confirmed' || s === '1') return { backgroundColor: '#D1FAE5', color: '#059669' };
  if (s === 'completed' || s === '2') return { backgroundColor: '#DBEAFE', color: '#2563EB' };
  if (s === 'cancel' || s === 'cancelled' || s === '3') return { backgroundColor: '#FEE2E2', color: '#DC2626' };
  if (s === 'in_progress') return { backgroundColor: '#E0E7FF', color: '#4F46E5' };
  return { backgroundColor: '#FEF3C7', color: '#D97706' };
}

const styles = StyleSheet.create({
  container: { flex: 1, paddingHorizontal: 20, paddingTop: 16 },
  header: { marginBottom: 16 },
  backBtn: { marginRight: 10, paddingVertical: 4 },
  backText: { fontSize: 15, fontWeight: '700' },
  title: { fontSize: 22, fontWeight: '800' },
  subtitle: { fontSize: 13, marginTop: 2 },
  listContent: { paddingBottom: 40 },
  bookingCard: {
    padding: 16,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#F1F5F9',
    marginBottom: 12,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 6,
  },
  bizName: { fontSize: 16, fontWeight: '800' },
  expertText: { fontSize: 13, fontWeight: '600', marginTop: 2 },
  tokenBadge: {
    backgroundColor: '#EEF2FF',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  tokenBadgeText: { fontSize: 12, fontWeight: '800', color: '#6366F1' },
  timeRow: { marginBottom: 10 },
  timeText: { fontSize: 13, fontWeight: '600' },
  cardFooterRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: '#F8FAFC',
  },
  statusTag: { fontSize: 11, fontWeight: '800', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
  viewDetailsText: { fontSize: 12, fontWeight: '700', color: '#6366F1' },
  emptyView: { alignItems: 'center', marginTop: 60, paddingHorizontal: 20 },
  emptyTitle: { fontSize: 18, fontWeight: '800', marginBottom: 6 },
  emptyText: { fontSize: 13, textAlign: 'center' },

  // Details Modal
  detailsHeaderBox: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#F8FAFC',
    padding: 16,
    borderRadius: 16,
    marginBottom: 14,
  },
  tokenLargeBadge: { justifyContent: 'center' },
  tokenLargeLabel: { fontSize: 10, fontWeight: '800', color: '#64748B', letterSpacing: 1 },
  tokenLargeNumber: { fontSize: 24, fontWeight: '900', color: '#6366F1', marginTop: 2 },
  statusTagLarge: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 10 },
  statusTagLargeText: { fontSize: 13, fontWeight: '800' },
  detailsSectionBox: {
    borderWidth: 1,
    borderColor: '#F1F5F9',
    padding: 14,
    borderRadius: 14,
    marginBottom: 10,
  },
  detailsBoxLabel: { fontSize: 10, fontWeight: '800', color: '#94A3B8', letterSpacing: 1 },
  detailsMainTitle: { fontSize: 15, fontWeight: '800' },
  detailsSubTitle: { fontSize: 13, fontWeight: '600' },
  modalActionBtn: {
    flex: 1,
    height: 46,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalActionBtnText: { color: '#FFF', fontSize: 13, fontWeight: '800' },

  // Booking Modal
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
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  modalTitle: { fontSize: 18, fontWeight: '800' },
  modalScroll: { paddingBottom: 20 },
  fieldLabel: { fontSize: 13, fontWeight: '700', marginBottom: 6, marginTop: 8 },
  expertChip: { paddingHorizontal: 14, paddingVertical: 10, borderRadius: 12, marginRight: 10 },
  selChip: { backgroundColor: '#6366F1' },
  unselChip: { backgroundColor: '#E2E8F0' },
  chipTitle: { fontSize: 13, fontWeight: '700' },
  slotsGrid: { flexDirection: 'row', flexWrap: 'wrap', marginBottom: 12 },
  slotBox: {
    width: '48%',
    padding: 10,
    borderRadius: 10,
    backgroundColor: '#EEF2FF',
    alignItems: 'center',
    marginRight: '2%',
    marginBottom: 8,
  },
  disabledSlot: { backgroundColor: '#F1F5F9', opacity: 0.5 },
  selectedSlot: { backgroundColor: '#6366F1' },
  slotText: { fontSize: 12, fontWeight: '600', color: '#4338CA' },
  input: {
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 12,
    paddingHorizontal: 14,
    height: 46,
    marginBottom: 10,
  },
  confirmBtn: {
    backgroundColor: '#6366F1',
    borderRadius: 14,
    height: 50,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 16,
  },
  confirmBtnText: { color: '#FFF', fontSize: 16, fontWeight: '800' },
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

export default AppointmentScreen;
