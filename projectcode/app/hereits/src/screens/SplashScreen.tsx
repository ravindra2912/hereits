import React, { useEffect, useRef } from 'react';
import {
  Animated,
  Easing,
  Image,
  StyleSheet,
  Text,
  useColorScheme,
  View,
} from 'react-native';

interface SplashScreenProps {
  onFinish?: () => void;
  duration?: number;
}

export const SplashScreen: React.FC<SplashScreenProps> = ({
  onFinish,
  duration = 2500,
}) => {
  const isDarkMode = false;

  // Animation values
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.85)).current;
  const logoSpinAnim = useRef(new Animated.Value(0)).current;
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const textTranslateY = useRef(new Animated.Value(20)).current;
  const progressAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // 1. Parallel entry animation: Fade in logo, scale up, slide up text
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 800,
        useNativeDriver: true,
        easing: Easing.out(Easing.cubic),
      }),
      Animated.spring(scaleAnim, {
        toValue: 1,
        friction: 6,
        tension: 40,
        useNativeDriver: true,
      }),
      Animated.timing(textTranslateY, {
        toValue: 0,
        duration: 800,
        useNativeDriver: true,
        easing: Easing.out(Easing.cubic),
      }),
      Animated.timing(progressAnim, {
        toValue: 1,
        duration: duration - 300,
        useNativeDriver: false,
        easing: Easing.inOut(Easing.quad),
      }),
    ]).start();

    // Subtle pulsing animation on logo badge
    const pulseLoop = Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 1.06,
          duration: 1000,
          useNativeDriver: true,
          easing: Easing.inOut(Easing.ease),
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: true,
          easing: Easing.inOut(Easing.ease),
        }),
      ])
    );
    pulseLoop.start();

    // Timer to finish splash
    const timer = setTimeout(() => {
      // Exit fade out animation
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 400,
        useNativeDriver: true,
        easing: Easing.in(Easing.cubic),
      }).start(() => {
        pulseLoop.stop();
        if (onFinish) {
          onFinish();
        }
      });
    }, duration);

    return () => clearTimeout(timer);
  }, []);

  const themeStyles = isDarkMode ? darkTheme : lightTheme;

  const progressWidth = progressAnim.interpolate({
    inputRange: [0, 1],
    outputRange: ['0%', '100%'],
  });

  return (
    <View style={[styles.container, themeStyles.background]}>
      <Animated.View
        style={[
          styles.content,
          {
            opacity: fadeAnim,
            transform: [{ scale: scaleAnim }],
          },
        ]}
      >
        {/* Animated Brand Badge & Logo */}
        <Animated.View
          style={[
            styles.logoWrapper,
            themeStyles.logoShadow,
            { transform: [{ scale: pulseAnim }] },
          ]}
        >
          <View style={styles.outerRing}>
            <Image
              source={require('../assets/app_icon.png')}
              style={{ width: 90, height: 90, borderRadius: 20 }}
              resizeMode="cover"
            />
          </View>
        </Animated.View>

        {/* Brand Text Content */}
        <Animated.View
          style={[
            styles.textContainer,
            {
              transform: [{ translateY: textTranslateY }],
            },
          ]}
        >
          <Text style={[styles.appName, themeStyles.primaryText]}>
            Here<Text style={styles.accentText}>its</Text>
          </Text>
          <Text style={[styles.tagline, themeStyles.secondaryText]}>
            Discover Local Businesses & Services
          </Text>
        </Animated.View>
      </Animated.View>

      {/* Progress Bar Footer */}
      <Animated.View style={[styles.footer, { opacity: fadeAnim }]}>
        <View style={[styles.progressBarTrack, themeStyles.progressTrack]}>
          <Animated.View
            style={[
              styles.progressBarFill,
              themeStyles.progressFill,
              { width: progressWidth },
            ]}
          />
        </View>
        <Text style={[styles.versionText, themeStyles.secondaryText]}>
          v1.0.0 • Connecting You Locally
        </Text>
      </Animated.View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
  },
  logoWrapper: {
    marginBottom: 28,
  },
  outerRing: {
    width: 110,
    height: 110,
    borderRadius: 55,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 8,
  },
  innerCircle: {
    width: 90,
    height: 90,
    borderRadius: 45,
    justifyContent: 'center',
    alignItems: 'center',
  },
  logoIcon: {
    fontSize: 44,
  },
  textContainer: {
    alignItems: 'center',
  },
  appName: {
    fontSize: 42,
    fontWeight: '800',
    letterSpacing: 0.5,
    fontFamily: 'System',
  },
  accentText: {
    color: '#6366F1', // Indigo Accent
  },
  tagline: {
    fontSize: 15,
    marginTop: 8,
    fontWeight: '500',
    textAlign: 'center',
    letterSpacing: 0.3,
  },
  footer: {
    position: 'absolute',
    bottom: 48,
    width: '70%',
    alignItems: 'center',
  },
  progressBarTrack: {
    height: 4,
    width: '100%',
    borderRadius: 2,
    overflow: 'hidden',
    marginBottom: 14,
  },
  progressBarFill: {
    height: '100%',
    borderRadius: 2,
  },
  versionText: {
    fontSize: 12,
    fontWeight: '400',
    letterSpacing: 0.5,
  },
});

const lightTheme = StyleSheet.create({
  background: {
    backgroundColor: '#F8FAFC',
  },
  primaryText: {
    color: '#0F172A',
  },
  secondaryText: {
    color: '#64748B',
  },
  logoShadow: {
    shadowColor: '#6366F1',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.25,
    shadowRadius: 18,
    elevation: 10,
  },
  outerRingStyle: {
    backgroundColor: '#EEF2FF',
  },
  innerCircleStyle: {
    backgroundColor: '#6366F1',
  },
  progressTrack: {
    backgroundColor: '#E2E8F0',
  },
  progressFill: {
    backgroundColor: '#6366F1',
  },
});

const darkTheme = StyleSheet.create({
  background: {
    backgroundColor: '#0F172A',
  },
  primaryText: {
    color: '#F8FAFC',
  },
  secondaryText: {
    color: '#94A3B8',
  },
  logoShadow: {
    shadowColor: '#818CF8',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.35,
    shadowRadius: 20,
    elevation: 12,
  },
  outerRingStyle: {
    backgroundColor: '#1E293B',
  },
  innerCircleStyle: {
    backgroundColor: '#6366F1',
  },
  progressTrack: {
    backgroundColor: '#334155',
  },
  progressFill: {
    backgroundColor: '#818CF8',
  },
});

export default SplashScreen;
