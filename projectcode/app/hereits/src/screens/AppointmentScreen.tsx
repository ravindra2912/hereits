import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Modal,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  useColorScheme,
  View,
} from 'react-native';
import { appointmentService } from '../services/appointmentService';
import { useAuth } from '../context/AuthContext';
import { useNavigation, useRoute } from '@react-navigation/native';

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

  // Booking Modal States
  const [modalVisible, setModalVisible] = useState<boolean>(false);
  const [businessId, setBusinessId] = useState<number | null>(null);
  const [businessName, setBusinessName] = useState<string | null>(null);
  const [experts, setExperts] = useState<any[]>([]);
  const [selectedExpert, setSelectedExpert] = useState<any>(null);
  const [bookingDate, setBookingDate] = useState<string>('2026-07-22');
  const [slots, setSlots] = useState<any[]>([]);
  const [selectedSlot, setSelectedSlot] = useState<any>(null);
  const [userName, setUserName] = useState<string>(user ? `${user.first_name} ${user.last_name}` : '');
  const [userContact, setUserContact] = useState<string>(user?.contact || '');
  const [note, setNote] = useState<string>('');
  const [submitting, setSubmitting] = useState(false);

  // Reactively trigger modal when params are updated
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
    // Clear route parameters so modal doesn't open on re-clicks
    navigation.setParams({ businessId: null, businessName: null });
  };

  const fetchMyAppointments = async () => {
    setLoading(true);
    const res = await appointmentService.getMyAppointments();
    if (res.success && res.data) {
      setAppointments(res.data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchMyAppointments();
  }, []);



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

  return (
    <View style={[styles.container, theme.background]}>
      <View style={styles.header}>
        <Text style={[styles.title, theme.primaryText]}>My Appointments</Text>
        <Text style={[styles.subtitle, theme.secondaryText]}>
          Track ongoing bookings and time slot statuses
        </Text>
      </View>

      {loading ? (
        <ActivityIndicator size="large" color="#6366F1" style={{ marginTop: 40 }} />
      ) : (
        <FlatList
          data={appointments}
          keyExtractor={item => String(item.id)}
          contentContainerStyle={styles.listContent}
          renderItem={({ item }) => (
            <View style={[styles.bookingCard, theme.cardBg]}>
              <View style={styles.cardHeader}>
                <Text style={[styles.bizName, theme.primaryText]}>
                  {item.business?.name || 'Local Business'}
                </Text>
                <Text style={styles.tokenBadge}>Token #{item.token_number}</Text>
              </View>
              <Text style={[styles.expertText, theme.secondaryText]}>
                Expert: {item.expert?.expert_name || 'Assigned Staff'}
              </Text>
              <View style={styles.timeRow}>
                <Text style={styles.timeText}>
                  📅 {item.booking_date} | ⏰ {item.slot_start_time} - {item.slot_end_time}
                </Text>
              </View>
              <View style={styles.statusRow}>
                <Text style={[styles.statusTag, getStatusStyle(item.status)]}>
                  {getStatusText(item.status)}
                </Text>
              </View>
            </View>
          )}
          ListEmptyComponent={
            <View style={styles.emptyView}>
              <Text style={{ fontSize: 36, marginBottom: 8 }}>📅</Text>
              <Text style={[styles.emptyText, theme.secondaryText]}>
                No upcoming appointments found.
              </Text>
            </View>
          }
        />
      )}

      {/* Booking Modal */}
      <Modal visible={modalVisible} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, theme.cardBg]}>
            <View style={styles.modalHeader}>
              <Text style={[styles.modalTitle, theme.primaryText]}>
                Book Appointment ({businessName || 'Shop'})
              </Text>
              <TouchableOpacity
                onPress={handleClose}
              >
                <Text style={{ fontSize: 18, color: '#64748B' }}>✕</Text>
              </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.modalScroll}>
              {/* Select Expert */}
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

              {/* Time Slots */}
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

              {/* User Details */}
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

function getStatusText(status: number) {
  switch (status) {
    case 0: return 'Pending Confirmation';
    case 1: return 'Confirmed';
    case 2: return 'Completed';
    case 3: return 'Cancelled';
    default: return 'Pending';
  }
}

function getStatusStyle(status: number) {
  switch (status) {
    case 1: return { backgroundColor: '#D1FAE5', color: '#10B981' };
    case 2: return { backgroundColor: '#DBEAFE', color: '#2563EB' };
    case 3: return { backgroundColor: '#FEE2E2', color: '#EF4444' };
    default: return { backgroundColor: '#FEF3C7', color: '#D97706' };
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingHorizontal: 20,
    paddingTop: 16,
  },
  header: {
    marginBottom: 16,
  },
  title: {
    fontSize: 24,
    fontWeight: '800',
  },
  subtitle: {
    fontSize: 13,
    marginTop: 2,
  },
  listContent: {
    paddingBottom: 40,
  },
  bookingCard: {
    padding: 16,
    borderRadius: 16,
    marginBottom: 12,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  bizName: {
    fontSize: 16,
    fontWeight: '700',
  },
  tokenBadge: {
    fontSize: 11,
    fontWeight: '700',
    color: '#6366F1',
  },
  expertText: {
    fontSize: 13,
    marginBottom: 6,
  },
  timeRow: {
    marginBottom: 8,
  },
  timeText: {
    fontSize: 13,
    color: '#6366F1',
    fontWeight: '600',
  },
  statusRow: {
    flexDirection: 'row',
  },
  statusTag: {
    fontSize: 11,
    fontWeight: '700',
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderRadius: 10,
  },
  emptyView: {
    alignItems: 'center',
    marginTop: 60,
  },
  emptyText: {
    fontSize: 14,
  },
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
  modalTitle: {
    fontSize: 18,
    fontWeight: '800',
  },
  modalScroll: {
    paddingBottom: 20,
  },
  fieldLabel: {
    fontSize: 13,
    fontWeight: '700',
    marginBottom: 6,
    marginTop: 8,
  },
  expertChip: {
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 12,
    marginRight: 10,
  },
  selChip: { backgroundColor: '#6366F1' },
  unselChip: { backgroundColor: '#E2E8F0' },
  chipTitle: { fontSize: 13, fontWeight: '700' },
  slotsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 12,
  },
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
  confirmBtnText: {
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

export default AppointmentScreen;
