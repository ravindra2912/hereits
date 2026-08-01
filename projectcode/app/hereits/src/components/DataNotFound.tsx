import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

interface DataNotFoundProps {
  title?: string;
  description?: string;
  icon?: string;
  theme: {
    background: { backgroundColor: string };
    primaryText: { color: string };
    secondaryText: { color: string };
    cardBg: { backgroundColor: string };
  };
}

export const DataNotFound: React.FC<DataNotFoundProps> = ({
  title = "No Data Found",
  description = "There is no information available at the moment.",
  icon = "📂",
  theme,
}) => {
  return (
    <View style={[styles.container, theme.cardBg]}>
      <View style={styles.iconContainer}>
        <Text style={styles.icon}>{icon}</Text>
      </View>
      <Text style={[styles.title, theme.primaryText]}>{title}</Text>
      <Text style={[styles.description, theme.secondaryText]}>{description}</Text>
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
