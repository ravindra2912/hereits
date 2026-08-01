import React from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useLocation } from '../context/LocationContext';

interface ComingSoonProps {
  theme: {
    background: { backgroundColor: string };
    primaryText: { color: string };
    secondaryText: { color: string };
    cardBg: { backgroundColor: string };
  };
}

export const ComingSoon: React.FC<ComingSoonProps> = ({ theme }) => {
  const { location, setLocationModalVisible } = useLocation();

  return (
    <View style={[styles.container, theme.cardBg]}>
      <View style={styles.iconContainer}>
        <Text style={styles.icon}>🚀</Text>
      </View>
      <Text style={[styles.title, theme.primaryText]}>
        We are coming soon!
      </Text>
      <Text style={[styles.description, theme.secondaryText]}>
        We are not active in {location?.location_name || 'this area'} yet, but we are expanding rapidly. Stay tuned!
      </Text>
      <TouchableOpacity
        style={styles.button}
        onPress={() => setLocationModalVisible(true)}
        activeOpacity={0.8}
      >
        <Text style={styles.buttonText}>Change Location</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    padding: 24,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
    marginVertical: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.05,
    shadowRadius: 12,
    elevation: 3,
  },
  iconContainer: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#EEF2FF',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  icon: {
    fontSize: 40,
  },
  title: {
    fontSize: 20,
    fontWeight: '800',
    textAlign: 'center',
    marginBottom: 8,
  },
  description: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 20,
    paddingHorizontal: 10,
  },
  button: {
    backgroundColor: '#6366F1',
    paddingVertical: 12,
    paddingHorizontal: 24,
    borderRadius: 14,
    shadowColor: '#6366F1',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  buttonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
  },
});

export default ComingSoon;
