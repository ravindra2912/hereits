import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

const defaultTheme = {
  background: { backgroundColor: '#F8FAFC' },
  primaryText: { color: '#0F172A' },
  secondaryText: { color: '#64748B' },
  cardBg: { backgroundColor: '#FFFFFF' },
};

interface DataNotFoundProps {
  title?: string;
  description?: string;
  icon?: string;
  buttonText?: string;
  onButtonPress?: () => void;
  theme?: {
    background?: { backgroundColor: string };
    primaryText?: { color: string };
    secondaryText?: { color: string };
    cardBg?: { backgroundColor: string };
  };
}

export const DataNotFound: React.FC<DataNotFoundProps> = ({
  title = "No Data Found",
  description = "There is no information available at the moment.",
  icon = "📂",
  buttonText,
  onButtonPress,
  theme = defaultTheme,
}) => {
  const activeTheme = theme || defaultTheme;
  return (
    <View style={[styles.container, activeTheme.cardBg]}>
      <View style={styles.iconContainer}>
        <Text style={styles.icon}>{icon}</Text>
      </View>
      <Text style={[styles.title, activeTheme.primaryText]}>{title}</Text>
      <Text style={[styles.description, activeTheme.secondaryText]}>{description}</Text>
      {buttonText && onButtonPress && (
        <View style={{ marginTop: 16 }}>
          <Text
            onPress={onButtonPress}
            style={{
              backgroundColor: '#6366F1',
              color: '#FFFFFF',
              paddingHorizontal: 20,
              paddingVertical: 10,
              borderRadius: 14,
              fontWeight: '700',
              fontSize: 13,
              overflow: 'hidden',
              textAlign: 'center',
            }}
          >
            {buttonText}
          </Text>
        </View>
      )}
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
    backgroundColor: '#F1F5F9',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  icon: {
    fontSize: 40,
  },
  title: {
    fontSize: 18,
    fontWeight: '800',
    textAlign: 'center',
    marginBottom: 8,
  },
  description: {
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 20,
    paddingHorizontal: 10,
  },
});

export default DataNotFound;
