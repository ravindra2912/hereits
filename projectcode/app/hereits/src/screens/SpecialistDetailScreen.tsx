import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  ScrollView,
  Share,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import Toast from 'react-native-toast-message';
import { useFocusEffect, useNavigation, useRoute } from '@react-navigation/native';
import { businessService } from '../services/businessService';
import FallbackImage from '../components/FallbackImage';
import DataNotFound from '../components/DataNotFound';
import { useAuth } from '../context/AuthContext';

export const SpecialistDetailScreen: React.FC = () => {
  const route = useRoute<any>();
  const navigation = useNavigation<any>();
  const specialistId = route.params?.specialistId;
  const { user, isAuthenticated, setAuthModalVisible } = useAuth();

  const [loading, setLoading] = useState<boolean>(true);
  const [expert, setExpert] = useState<any>(null);

  // Booking states
  const [selectedDate, setSelectedDate] = useState<string>('');
  const [slots, setSlots] = useState<any[]>([]);
  const [selectedSlot, setSelectedSlot] = useState<any>(null);
  const [loadingSlots, setLoadingSlots] = useState<boolean>(false);
  const [userName, setUserName] = useState<string>('');
  const [userContact, setUserContact] = useState<string>('');
  const [bookingNote, setBookingNote] = useState<string>('');
  const [isBooking, setIsBooking] = useState<boolean>(false);
  const [showSlotDropdown, setShowSlotDropdown] = useState<boolean>(false);
  const [appointmentFor, setAppointmentFor] = useState<'self' | 'other'>('self');
  const [isFavorited, setIsFavorited] = useState<boolean>(false);

  const isDarkMode = false;
  const theme = isDarkMode ? darkTheme : lightTheme;

  // Generate next 7 days for horizontal picker
  const getNextSevenDays = () => {
    const days = [];
    for (let i = 0; i < 7; i++) {
      const date = new Date();
      date.setDate(date.getDate() + i);
      days.push(date);
    }
    return days;
  };

  const formatDateString = (date: Date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  };

  const formatDisplayDayName = (date: Date) => {
    return date.toLocaleDateString('en-US', { weekday: 'short' });
  };

  const formatDisplayDayNum = (date: Date) => {
    return date.toLocaleDateString('en-US', { day: 'numeric' });
  };

  const daysList = getNextSevenDays();

  useFocusEffect(
    useCallback(() => {
      fetchSpecialistDetail();
    }, [specialistId])
  );

  useEffect(() => {
    // Default selected date to today
    if (daysList.length > 0) {
      setSelectedDate(formatDateString(daysList[0]));
    }
  }, [specialistId]);

  useEffect(() => {
    if (user) {
      setUserName(`${user.first_name || ''} ${user.last_name || ''}`.trim());
      setUserContact(user.contact ? String(user.contact) : '');
    }
  }, [user]);

  // Reset guest fields when switching back to 'self'
  useEffect(() => {
    if (appointmentFor === 'self' && user) {
      setUserName(`${user.first_name || ''} ${user.last_name || ''}`.trim());
      setUserContact(user.contact ? String(user.contact) : '');
    } else if (appointmentFor === 'other') {
      setUserName('');
      setUserContact('');
    }
  }, [appointmentFor]);

  useEffect(() => {
    if (expert && selectedDate && expert.is_appointment_book_with_time_slot) {
      fetchSlots(selectedDate);
    }
  }, [selectedDate, expert]);

  const fetchSpecialistDetail = async () => {
    setLoading(true);
    try {
      const res = await businessService.getExpertDetail(specialistId);
      if (res.success && res.data) {
        setExpert(res.data);
        setIsFavorited(!!res.data.is_favorited);
      }
    } catch (e) {
      console.warn('Failed to load specialist details:', e);
    } finally {
      setLoading(false);
    }
  };

  const handleToggleFavorite = async () => {
    if (!isAuthenticated) {
      setAuthModalVisible(true);
      return;
    }
    const prev = isFavorited;
    setIsFavorited(!prev);
    try {
      const res = await businessService.toggleFavorite(expert.business_id, 'expert', expert.id);
      if (res.success) {
        Toast.show({
          type: 'success',
          text1: !prev ? 'Added to Favorites ❤️' : 'Removed from Favorites',
        });
      } else {
        setIsFavorited(prev);
      }
    } catch (e) {
      setIsFavorited(prev);
    }
  };

  const handleShare = async () => {
    try {
      await Share.share({
        message: `Check out ${expert?.expert_name || 'Specialist'} (${expert?.title || 'Specialist'}) on HereIts!`,
      });
    } catch (e) {
      console.warn('Share error:', e);
    }
  };

  const fetchSlots = async (dateStr: string) => {
    setLoadingSlots(true);
    setSelectedSlot(null);
    setShowSlotDropdown(false);
    try {
      const res = await businessService.getExpertTiming(expert.id, dateStr);
      if (res.success && res.data?.slots) {
        setSlots(res.data.slots);
      } else {
        setSlots([]);
      }
    } catch (e) {
      console.warn('Failed to load slots:', e);
      setSlots([]);
    } finally {
      setLoadingSlots(false);
    }
  };

  const handleBookAppointment = async () => {
    if (!isAuthenticated) {
      setAuthModalVisible(true);
      return;
    }

    if (expert.is_appointment_book_with_time_slot && !selectedSlot) {
      Toast.show({ type: 'error', text1: 'Selection Required', text2: 'Please select a time slot for your appointment.' });
      return;
    }

    if (!userName.trim()) {
      Toast.show({ type: 'error', text1: 'Required Info', text2: 'Please enter your name.' });
      return;
    }

    if (!userContact.trim()) {
      Toast.show({ type: 'error', text1: 'Required Info', text2: 'Please enter your contact number.' });
      return;
    }

    setIsBooking(true);
    try {
      const payload: any = {
        business_id: expert.business_id,
        expert_id: expert.id,
        booking_date: selectedDate,
        appointment_for: appointmentFor,
        user_name: userName,
        user_contact: userContact,
        note: bookingNote,
      };

      if (expert.is_appointment_book_with_time_slot && selectedSlot) {
        payload.slot_start_time = selectedSlot.start_time;
        payload.slot_end_time = selectedSlot.end_time;
      }

      const res = await businessService.bookAppointment(payload);

      if (res.success) {
        if (expert.is_appointment_book_with_time_slot) {
          Toast.show({ type: 'success', text1: 'Booking Confirmed! 🎉', text2: 'Your appointment has been booked successfully.' });
        } else {
          Toast.show({
            type: 'success',
            text1: 'Booking Confirmed! 🎉',
            text2: `Queue token #${res.data?.token_number || ''} issued for your selected date.`,
          });
        }
        setBookingNote('');
        setSelectedSlot(null);
        if (expert.is_appointment_book_with_time_slot) {
          fetchSlots(selectedDate);
        }
      } else {
        Toast.show({ type: 'error', text1: 'Booking Failed', text2: res.message || 'Unable to book appointment.' });
      }
    } catch (e) {
      console.warn('Failed to book appointment:', e);
      Toast.show({ type: 'error', text1: 'Error', text2: 'An error occurred while booking. Please try again.' });
    } finally {
      setIsBooking(false);
    }
  };

  if (loading) {
    return (
      <View style={[styles.centered, theme.background]}>
        <ActivityIndicator size="large" color="#6366F1" />
      </View>
    );
  }

  if (!expert) {
    return (
      <View style={[styles.container, theme.background]}>
        <View style={styles.header}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
            <Text style={[styles.backText, theme.primaryText]}>← Back</Text>
          </TouchableOpacity>
        </View>
        <DataNotFound
          title="Specialist Not Found"
          description="We couldn't retrieve the details for this specialist."
          theme={theme}
        />
      </View>
    );
  }

  return (
    <ScrollView style={[styles.container, theme.background]} showsVerticalScrollIndicator={false}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Text style={[styles.backText, theme.primaryText]}>← Back</Text>
        </TouchableOpacity>
        <Text style={[styles.headerTitle, theme.primaryText]}>Specialist Details</Text>
        <View style={{ width: 60 }} />
      </View>

      {/* Profile Card */}
      <View style={[styles.profileCard, theme.cardBg]}>
        {/* Top: Left Rectangular Image + Right Name/Specialization */}
        <View style={styles.profileTopRow}>
          <FallbackImage
            source={expert.expert_image ? { uri: expert.expert_image } : null}
            fallbackSource={require('../assets/business_icon.png')}
            style={styles.avatarRect}
            resizeMode="cover"
          />
          <View style={styles.profileInfoRight}>
            <Text style={[styles.name, theme.primaryText]}>{expert.expert_name}</Text>
            <Text style={[styles.titleText, theme.secondaryText]}>
              {expert.title || 'Professional Specialist'}
            </Text>

            {/* Rating right after Specialist title */}
            {expert.rating >= 0 && (
              <View style={styles.ratingRowInline}>
                <Text style={styles.starIcon}>⭐</Text>
                <Text style={[styles.ratingValueText, theme.primaryText]}>
                  {Number(expert.rating).toFixed(1)}
                </Text>
              </View>
            )}

            <View style={styles.badgeRow}>
              {expert.experience_years !== undefined && expert.experience_years !== null && (
                <View style={styles.expBadge}>
                  <Text style={styles.badgeText}>{expert.experience_years} Yrs Exp</Text>
                </View>
              )}
              {expert.charge_amount !== undefined && expert.charge_amount !== null && (
                <View style={styles.priceBadge}>
                  <Text style={styles.badgeText}>₹{expert.charge_amount} Fee</Text>
                </View>
              )}
            </View>
          </View>
        </View>

        {/* Bottom: Description */}
        {expert.description ? (
          <Text style={[styles.descriptionLeft, theme.secondaryText]}>{expert.description}</Text>
        ) : null}

        {/* Bottom: Favorite & Share Buttons */}
        <View style={styles.actionButtonsRow}>
          <TouchableOpacity
            style={[
              styles.actionBtn,
              isFavorited ? styles.actionBtnFavActive : styles.actionBtnOutline,
            ]}
            onPress={handleToggleFavorite}
            activeOpacity={0.8}
          >
            <Text style={styles.actionBtnIcon}>{isFavorited ? '❤️' : '🤍'}</Text>
            <Text style={[styles.actionBtnText, isFavorited ? styles.textFavActive : theme.primaryText]}>
              {isFavorited ? 'Saved' : 'Favorite'}
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.actionBtn, styles.actionBtnOutline]}
            onPress={handleShare}
            activeOpacity={0.8}
          >
            <Text style={styles.actionBtnIcon}>🔗</Text>
            <Text style={[styles.actionBtnText, theme.primaryText]}>Share</Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* Booking Widget */}
      <View style={styles.section}>
        <Text style={[styles.sectionTitle, theme.primaryText]}>Book an Appointment</Text>
        <View style={[styles.bookingCard, theme.cardBg]}>
          {/* ── Appointment For — Self / Other ── */}
          <Text style={[styles.bookingSubLabel, theme.secondaryText]}>Who is this for?</Text>
          <View style={styles.apptForRow}>
            <TouchableOpacity
              style={[styles.apptForBtn, appointmentFor === 'self' && styles.apptForBtnActive]}
              onPress={() => setAppointmentFor('self')}
              activeOpacity={0.8}
            >
              <View style={[styles.radioCircle, appointmentFor === 'self' && styles.radioCircleActive]}>
                {appointmentFor === 'self' && <View style={styles.radioInner} />}
              </View>
              <Text style={[styles.apptForText, appointmentFor === 'self' && styles.apptForTextActive]}>
                👤 Myself
              </Text>
            </TouchableOpacity>

            <View style={{ width: 10 }} />

            <TouchableOpacity
              style={[styles.apptForBtn, appointmentFor === 'other' && styles.apptForBtnActive]}
              onPress={() => setAppointmentFor('other')}
              activeOpacity={0.8}
            >
              <View style={[styles.radioCircle, appointmentFor === 'other' && styles.radioCircleActive]}>
                {appointmentFor === 'other' && <View style={styles.radioInner} />}
              </View>
              <Text style={[styles.apptForText, appointmentFor === 'other' && styles.apptForTextActive]}>
                👥 Someone Else
              </Text>
            </TouchableOpacity>
          </View>

          {/* ── Select Date ── */}
          <Text style={[styles.bookingSubLabel, theme.secondaryText]}>Select Date</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.calendarRow}>
            {daysList.map((day, idx) => {
              const formatted = formatDateString(day);
              const isSelected = selectedDate === formatted;
              return (
                <TouchableOpacity
                  key={`day-${idx}`}
                  style={[
                    styles.datePill,
                    isSelected ? styles.datePillActive : theme.background,
                    { borderColor: isSelected ? '#6366F1' : '#E2E8F0' },
                  ]}
                  onPress={() => setSelectedDate(formatted)}
                >
                  <Text style={[styles.dateDayName, isSelected ? styles.textWhite : theme.secondaryText]}>
                    {formatDisplayDayName(day)}
                  </Text>
                  <Text style={[styles.dateDayNum, isSelected ? styles.textWhite : theme.primaryText]}>
                    {formatDisplayDayNum(day)}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </ScrollView>

          {/* Time Slot Dropdown */}
          {expert.is_appointment_book_with_time_slot ? (
            <>
              <Text style={[styles.bookingSubLabel, theme.secondaryText, { marginTop: 16 }]}>Select Time Slot</Text>
              {loadingSlots ? (
                <ActivityIndicator size="small" color="#6366F1" style={{ marginVertical: 20 }} />
              ) : slots.length > 0 ? (
                <View>
                  {/* Dropdown trigger */}
                  <TouchableOpacity
                    style={[styles.dropdownTrigger, theme.background, { borderColor: showSlotDropdown ? '#6366F1' : '#E2E8F0' }]}
                    onPress={() => setShowSlotDropdown(prev => !prev)}
                    activeOpacity={0.8}
                  >
                    <Text style={[styles.dropdownTriggerText, selectedSlot ? theme.primaryText : { color: '#94A3B8' }]}>
                      {selectedSlot ? selectedSlot.display_time : 'Choose a time slot...'}
                    </Text>
                    <Text style={{ color: '#6366F1', fontSize: 14 }}>{showSlotDropdown ? '▲' : '▼'}</Text>
                  </TouchableOpacity>

                  {/* Dropdown list */}
                  {showSlotDropdown && (
                    <ScrollView
                      style={[styles.dropdownList, theme.cardBg ?? { backgroundColor: '#FFFFFF' }]}
                      nestedScrollEnabled
                      keyboardShouldPersistTaps="handled"
                      showsVerticalScrollIndicator={false}
                    >
                      {slots.map((slot, idx) => {
                        const isSelected = selectedSlot?.start_time === slot.start_time;
                        return (
                          <TouchableOpacity
                            key={`slot-${idx}`}
                            style={[
                              styles.dropdownItem,
                              isSelected && styles.dropdownItemActive,
                              idx < slots.length - 1 && { borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
                            ]}
                            onPress={() => {
                              setSelectedSlot(slot);
                              setShowSlotDropdown(false);
                            }}
                          >
                            <Text style={{ fontSize: 11, marginRight: 8 }}>🕐</Text>
                            <Text style={[styles.dropdownItemText, isSelected && styles.textWhite]}>
                              {slot.display_time}
                            </Text>
                            {isSelected && <Text style={[styles.dropdownItemCheck]}>✓</Text>}
                          </TouchableOpacity>
                        );
                      })}
                    </ScrollView>
                  )}
                </View>
              ) : (
                <Text style={[styles.emptySlotsText, theme.secondaryText]}>
                  No available slots for this date.
                </Text>
              )}
            </>
          ) : (
            <View style={[styles.queueInfoCard, theme.background, { marginTop: 16, marginBottom: 8 }]}>
              <Text style={styles.queueInfoTitle}>🎟️ Queue-based Booking</Text>
              <Text style={[styles.queueInfoDesc, theme.secondaryText]}>
                This specialist operates on a first-come, first-served queue system.
                A queue token number will be issued to you for your selected date.
              </Text>
            </View>
          )}

          {/* Form Fields */}
          <Text style={[styles.bookingSubLabel, theme.secondaryText, { marginTop: 16 }]}>Contact Information</Text>

          <TextInput
            placeholder={appointmentFor === 'other' ? "Guest Full Name" : "Your Full Name"}
            placeholderTextColor="#94A3B8"
            style={[styles.inputField, theme.background, theme.primaryText]}
            value={userName}
            onChangeText={setUserName}
          />

          <TextInput
            placeholder={appointmentFor === 'other' ? "Guest Contact Number" : "Your Contact Number"}
            placeholderTextColor="#94A3B8"
            keyboardType="phone-pad"
            style={[styles.inputField, theme.background, theme.primaryText, { marginTop: 10 }]}
            value={userContact}
            onChangeText={setUserContact}
          />

          <TextInput
            placeholder="Add special notes or symptoms (optional)"
            placeholderTextColor="#94A3B8"
            multiline
            numberOfLines={3}
            style={[styles.inputField, theme.background, theme.primaryText, { marginTop: 10, height: 80, textAlignVertical: 'top' }]}
            value={bookingNote}
            onChangeText={setBookingNote}
          />

          {/* Confirm Button */}
          <TouchableOpacity
            style={[styles.confirmBtn, isBooking && { backgroundColor: '#A5B4FC' }]}
            onPress={handleBookAppointment}
            disabled={isBooking}
          >
            {isBooking ? (
              <ActivityIndicator size="small" color="#FFF" />
            ) : (
              <Text style={styles.confirmBtnText}>Confirm Appointment Booking</Text>
            )}
          </TouchableOpacity>
        </View>
      </View>

      {/* Business Association */}
      {expert.business && (
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, theme.primaryText]}>Assigned Business</Text>
          <TouchableOpacity
            style={[styles.businessCard, theme.cardBg]}
            onPress={() => navigation.navigate('BusinessDetail', { businessId: expert.business.id })}
            activeOpacity={0.9}
          >
            <FallbackImage
              source={expert.business.business_logo ? { uri: expert.business.business_logo } : null}
              fallbackSource={require('../assets/business_icon.png')}
              style={styles.businessLogo}
              resizeMode="cover"
            />
            <View style={styles.businessInfo}>
              <Text style={[styles.businessName, theme.primaryText]} numberOfLines={1}>
                {expert.business.name}
              </Text>
              <Text style={[styles.businessAddress, theme.secondaryText]} numberOfLines={2}>
                📍 {expert.business.address || 'Address not listed'}
              </Text>
              {expert.business.contact && (
                <Text style={[styles.businessContact, theme.secondaryText]}>
                  📞 {expert.business.contact}
                </Text>
              )}
            </View>
            <Text style={styles.arrowIcon}>›</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* Timings */}
      {expert.timings && expert.timings.length > 0 && (
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, theme.primaryText]}>Availability Schedule</Text>
          <View style={[styles.timingsCard, theme.cardBg]}>
            {(() => {
              const currentDayName = new Date().toLocaleDateString('en-US', { weekday: 'long' });
              return Object.entries(
                expert.timings.reduce((acc: Record<string, any[]>, item: any) => {
                  if (!acc[item.day]) acc[item.day] = [];
                  acc[item.day].push(item);
                  return acc;
                }, {})
              ).map(([day, slots], idx: number, arr: any[]) => {
                const isToday = day.toLowerCase() === currentDayName.toLowerCase();
                return (
                  <View
                    key={`day-${idx}`}
                    style={[
                      styles.timingRow,
                      isToday && styles.todayTimingRow,
                      idx === arr.length - 1 && !isToday && { borderBottomWidth: 0 },
                    ]}
                  >
                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                      <Text style={[styles.dayText, isToday ? styles.todayDayText : theme.primaryText]}>
                        {day}
                      </Text>
                      {isToday && (
                        <View style={styles.todayBadge}>
                          <Text style={styles.todayBadgeText}>Today</Text>
                        </View>
                      )}
                    </View>
                    <View style={{ alignItems: 'flex-end' }}>
                      {slots.map((t: any, sIdx: number) => (
                        <Text
                          key={`slot-${sIdx}`}
                          style={[
                            styles.timeText,
                            isToday && styles.todayTimeText,
                            sIdx > 0 && { marginTop: 4 },
                          ]}
                        >
                          {t.start_time.substring(0, 5)} - {t.end_time.substring(0, 5)}
                        </Text>
                      ))}
                    </View>
                  </View>
                );
              });
            })()}
          </View>
        </View>
      )}

      {/* Reviews */}
      <View style={[styles.section, { paddingBottom: 40 }]}>
        <Text style={[styles.sectionTitle, theme.primaryText]}>Customer Reviews</Text>
        {expert.reviews && expert.reviews.length > 0 ? (
          expert.reviews.map((rev: any, idx: number) => (
            <View key={`rev-${idx}`} style={[styles.reviewCard, theme.cardBg]}>
              <View style={styles.reviewHeader}>
                <FallbackImage
                  source={rev.user?.profile ? { uri: rev.user.profile } : null}
                  fallbackSource={require('../assets/business_icon.png')}
                  style={styles.reviewerAvatar}
                />
                <View style={styles.reviewerInfo}>
                  <Text style={[styles.reviewerName, theme.primaryText]}>
                    {rev.user?.first_name} {rev.user?.last_name || ''}
                  </Text>
                  <Text style={styles.reviewRating}>⭐ {rev.rating} / 5</Text>
                </View>
              </View>
              {rev.review ? (
                <Text style={[styles.reviewComment, theme.secondaryText]}>{rev.review}</Text>
              ) : null}
            </View>
          ))
        ) : (
          <View style={[styles.emptyReviewsCard, theme.cardBg]}>
            <Text style={[styles.emptyReviewsText, theme.secondaryText]}>
              No reviews listed for this specialist yet.
            </Text>
          </View>
        )}
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, paddingTop: 16 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    marginBottom: 16,
  },
  backBtn: { paddingVertical: 8, width: 60 },
  backText: { fontSize: 14, fontWeight: '700' },
  headerTitle: { fontSize: 18, fontWeight: '800', flex: 1, textAlign: 'center' },
  profileCard: {
    marginHorizontal: 20,
    padding: 16,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#F1F5F9',
    marginBottom: 20,
  },
  profileTopRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  avatarRect: {
    width: 95,
    height: 115,
    borderRadius: 16,
    marginRight: 14,
  },
  profileInfoRight: {
    flex: 1,
    justifyContent: 'center',
  },
  name: { fontSize: 19, fontWeight: '800', lineHeight: 24 },
  titleText: { fontSize: 13, fontWeight: '600', marginTop: 4 },
  ratingRowInline: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
  },
  starIcon: {
    fontSize: 13,
    marginRight: 4,
  },
  ratingValueText: {
    fontSize: 13,
    fontWeight: '700',
  },
  badgeRow: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 8 },
  ratingBadge: { backgroundColor: '#F59E0B', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8, marginRight: 6, marginBottom: 4 },
  expBadge: { backgroundColor: '#3B82F6', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8, marginRight: 6, marginBottom: 4 },
  priceBadge: { backgroundColor: '#10B981', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8, marginBottom: 4 },
  badgeText: { color: '#FFF', fontSize: 11, fontWeight: '800' },
  descriptionLeft: { fontSize: 13, lineHeight: 20, marginTop: 14 },
  actionButtonsRow: {
    flexDirection: 'row',
    marginTop: 16,
    gap: 10,
  },
  actionBtn: {
    flex: 1,
    height: 44,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  actionBtnOutline: {
    borderWidth: 1.5,
    borderColor: '#E2E8F0',
    backgroundColor: '#F8FAFC',
  },
  actionBtnFavActive: {
    backgroundColor: '#FEE2E2',
    borderWidth: 1.5,
    borderColor: '#EF4444',
  },
  actionBtnIcon: {
    fontSize: 15,
    marginRight: 6,
  },
  actionBtnText: {
    fontSize: 13,
    fontWeight: '700',
  },
  textFavActive: {
    color: '#EF4444',
  },
  section: { marginHorizontal: 20, marginTop: 16 },
  sectionTitle: { fontSize: 15, fontWeight: '800', marginBottom: 12 },
  bookingCard: {
    padding: 16,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  bookingSubLabel: {
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 10,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  calendarRow: {
    paddingVertical: 4,
  },
  datePill: {
    width: 60,
    height: 70,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    marginRight: 10,
  },
  datePillActive: {
    backgroundColor: '#6366F1',
  },
  dateDayName: {
    fontSize: 11,
    fontWeight: '600',
  },
  dateDayNum: {
    fontSize: 16,
    fontWeight: '800',
    marginTop: 4,
  },
  textWhite: {
    color: '#FFFFFF',
  },
  dropdownTrigger: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    height: 48,
    borderRadius: 12,
    borderWidth: 1.5,
    paddingHorizontal: 14,
  },
  dropdownTriggerText: {
    fontSize: 13,
    fontWeight: '600',
    flex: 1,
  },
  dropdownList: {
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    marginTop: 4,
    maxHeight: 200,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 4,
  },
  dropdownItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 14,
  },
  dropdownItemActive: {
    backgroundColor: '#6366F1',
  },
  dropdownItemText: {
    fontSize: 13,
    fontWeight: '600',
    flex: 1,
    color: '#0F172A',
  },
  dropdownItemCheck: {
    color: '#FFFFFF',
    fontWeight: '800',
    fontSize: 14,
  },
  emptySlotsText: {
    fontSize: 13,
    textAlign: 'center',
    paddingVertical: 14,
  },
  inputField: {
    height: 48,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    paddingHorizontal: 16,
    fontSize: 13,
    fontWeight: '600',
  },
  confirmBtn: {
    backgroundColor: '#6366F1',
    height: 52,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: 20,
    shadowColor: '#6366F1',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  confirmBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '800',
  },
  // appointment_for buttons
  apptForRow: {
    flexDirection: 'row',
    marginBottom: 16,
  },
  apptForBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 11,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: '#E2E8F0',
    backgroundColor: '#F8FAFC',
  },
  apptForBtnActive: {
    borderColor: '#6366F1',
    backgroundColor: '#EEF2FF',
  },
  radioCircle: {
    width: 18,
    height: 18,
    borderRadius: 9,
    borderWidth: 2,
    borderColor: '#CBD5E1',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
  },
  radioCircleActive: { borderColor: '#6366F1' },
  radioInner: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#6366F1',
  },
  apptForText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#64748B',
  },
  apptForTextActive: { color: '#6366F1' },
  queueInfoCard: {
    padding: 14,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  queueInfoTitle: {
    fontSize: 13,
    fontWeight: '800',
    color: '#6366F1',
  },
  queueInfoDesc: {
    fontSize: 12,
    marginTop: 6,
    lineHeight: 16,
  },
  businessCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  businessLogo: { width: 50, height: 50, borderRadius: 10, marginRight: 14 },
  businessInfo: { flex: 1 },
  businessName: { fontSize: 15, fontWeight: '700' },
  businessAddress: { fontSize: 12, marginTop: 2 },
  businessContact: { fontSize: 11, marginTop: 2, fontWeight: '600' },
  arrowIcon: { fontSize: 18, color: '#94A3B8', marginLeft: 8 },
  timingsCard: {
    padding: 16,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  timingRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  todayTimingRow: {
    backgroundColor: '#EEF2FF',
    marginHorizontal: -10,
    paddingHorizontal: 10,
    borderRadius: 12,
    borderBottomWidth: 0,
  },
  dayText: { fontSize: 13, fontWeight: '700' },
  todayDayText: { color: '#6366F1', fontWeight: '800' },
  todayBadge: {
    backgroundColor: '#6366F1',
    paddingHorizontal: 7,
    paddingVertical: 2,
    borderRadius: 6,
    marginLeft: 8,
  },
  todayBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  timeText: { fontSize: 13, color: '#6366F1', fontWeight: '800' },
  todayTimeText: { color: '#4F46E5', fontWeight: '800' },
  reviewCard: {
    padding: 16,
    borderRadius: 18,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#F1F5F9',
  },
  reviewHeader: { flexDirection: 'row', alignItems: 'center' },
  reviewerAvatar: { width: 36, height: 36, borderRadius: 18, marginRight: 12 },
  reviewerInfo: { flex: 1 },
  reviewerName: { fontSize: 13, fontWeight: '700' },
  reviewRating: { fontSize: 11, color: '#F59E0B', fontWeight: '600', marginTop: 2 },
  reviewComment: { fontSize: 12, marginTop: 10, lineHeight: 16 },
  emptyReviewsCard: { padding: 20, borderRadius: 18, alignItems: 'center', borderStyle: 'dashed', borderWidth: 1, borderColor: '#CBD5E1' },
  emptyReviewsText: { fontSize: 13, textAlign: 'center' },
});

const lightTheme = StyleSheet.create({
  background: { backgroundColor: '#F8FAFC' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
  cardBg: { backgroundColor: '#FFFFFF' },
  skeletonBg: { backgroundColor: '#E2E8F0' },
});

const darkTheme = StyleSheet.create({
  background: { backgroundColor: '#0F172A' },
  primaryText: { color: '#F8FAFC' },
  secondaryText: { color: '#94A3B8' },
  cardBg: { backgroundColor: '#1E293B' },
  skeletonBg: { backgroundColor: '#334155' },
});

export default SpecialistDetailScreen;
