import React, { useEffect, useState, useRef } from 'react';
import {
  Animated,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import NetInfo, { NetInfoState } from '@react-native-community/netinfo';
import Svg, { Path, Line } from 'react-native-svg';

const WifiOffIcon: React.FC<{ size?: number; color?: string }> = ({
  size = 18,
  color = '#FFFFFF',
}) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Line x1="1" y1="1" x2="23" y2="23" stroke={color} strokeWidth="2.2" strokeLinecap="round" />
    <Path
      d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"
      stroke={color}
      strokeWidth="2.2"
      strokeLinecap="round"
    />
    <Path
      d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"
      stroke={color}
      strokeWidth="2.2"
      strokeLinecap="round"
    />
    <Path
      d="M10.71 5.05A16 16 0 0 1 22.58 9"
      stroke={color}
      strokeWidth="2.2"
      strokeLinecap="round"
    />
    <Path
      d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"
      stroke={color}
      strokeWidth="2.2"
      strokeLinecap="round"
    />
    <Path
      d="M8.53 16.11a6 6 0 0 1 6.95 0"
      stroke={color}
      strokeWidth="2.2"
      strokeLinecap="round"
    />
    <Line x1="12" y1="20" x2="12.01" y2="20" stroke={color} strokeWidth="2.5" strokeLinecap="round" />
  </Svg>
);

const CheckCircleIcon: React.FC<{ size?: number; color?: string }> = ({
  size = 18,
  color = '#FFFFFF',
}) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none">
    <Path
      d="M22 11.08V12a10 10 0 1 1-5.93-9.14"
      stroke={color}
      strokeWidth="2.2"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
    <Path
      d="M22 4L12 14.01l-3-3"
      stroke={color}
      strokeWidth="2.2"
      strokeLinecap="round"
      strokeLinejoin="round"
    />
  </Svg>
);

export const OfflineBanner: React.FC = () => {
  const [isOffline, setIsOffline] = useState(false);
  const [showRestored, setShowRestored] = useState(false);
  const [isMounted, setIsMounted] = useState(false);

  const slideAnim = useRef(new Animated.Value(-80)).current;
  const opacityAnim = useRef(new Animated.Value(0)).current;
  const restoredTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    // Initial fetch of network state
    NetInfo.fetch().then((state: NetInfoState) => {
      const offline = state.isConnected === false || state.isInternetReachable === false;
      setIsOffline(offline);
    });

    // Subscribe to network changes
    const unsubscribe = NetInfo.addEventListener((state: NetInfoState) => {
      const offline = state.isConnected === false || state.isInternetReachable === false;

      setIsOffline((prevOffline) => {
        if (prevOffline && !offline) {
          // Came back online
          setShowRestored(true);

          if (restoredTimeoutRef.current) {
            clearTimeout(restoredTimeoutRef.current);
          }
          restoredTimeoutRef.current = setTimeout(() => {
            setShowRestored(false);
          }, 2500);
        }
        return offline;
      });
    });

    return () => {
      unsubscribe();
      if (restoredTimeoutRef.current) {
        clearTimeout(restoredTimeoutRef.current);
      }
    };
  }, []);

  const shouldShow = isOffline || showRestored;

  useEffect(() => {
    if (shouldShow) {
      setIsMounted(true);
      Animated.parallel([
        Animated.timing(slideAnim, {
          toValue: 0,
          duration: 250,
          useNativeDriver: true,
        }),
        Animated.timing(opacityAnim, {
          toValue: 1,
          duration: 250,
          useNativeDriver: true,
        }),
      ]).start();
    } else {
      Animated.parallel([
        Animated.timing(slideAnim, {
          toValue: -80,
          duration: 200,
          useNativeDriver: true,
        }),
        Animated.timing(opacityAnim, {
          toValue: 0,
          duration: 200,
          useNativeDriver: true,
        }),
      ]).start(({ finished }) => {
        if (finished) {
          setIsMounted(false);
        }
      });
    }
  }, [shouldShow, slideAnim, opacityAnim]);

  if (!isMounted) {
    return null;
  }

  const isRestoredState = !isOffline && showRestored;

  return (
    <Animated.View
      pointerEvents={isMounted ? 'auto' : 'none'}
      style={[
        styles.container,
        isRestoredState ? styles.restoredContainer : styles.offlineContainer,
        {
          transform: [{ translateY: slideAnim }],
          opacity: opacityAnim,
        },
      ]}
    >
      <View style={styles.content}>
        <View style={styles.iconWrapper}>
          {isRestoredState ? (
            <CheckCircleIcon size={18} color="#FFFFFF" />
          ) : (
            <WifiOffIcon size={18} color="#FFFFFF" />
          )}
        </View>
        <View style={styles.textContainer}>
          <Text style={styles.titleText}>
            {isRestoredState ? 'Back Online' : 'No Internet Connection'}
          </Text>
          <Text style={styles.subtitleText}>
            {isRestoredState
              ? 'Your connection has been restored'
              : 'Please check your network settings'}
          </Text>
        </View>
        {!isRestoredState && (
          <TouchableOpacity
            style={styles.retryButton}
            activeOpacity={0.8}
            onPress={() => {
              NetInfo.fetch().then((state: NetInfoState) => {
                const offline =
                  state.isConnected === false || state.isInternetReachable === false;
                setIsOffline(offline);
              });
            }}
          >
            <Text style={styles.retryText}>Retry</Text>
          </TouchableOpacity>
        )}
      </View>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    width: '100%',
    paddingVertical: 9,
    paddingHorizontal: 16,
    zIndex: 99999,
    elevation: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
  },
  offlineContainer: {
    backgroundColor: '#EF4444', // Danger Red
  },
  restoredContainer: {
    backgroundColor: '#10B981', // Success Green
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  iconWrapper: {
    marginRight: 10,
    justifyContent: 'center',
    alignItems: 'center',
  },
  textContainer: {
    flex: 1,
  },
  titleText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#FFFFFF',
    letterSpacing: 0.2,
  },
  subtitleText: {
    fontSize: 11,
    color: 'rgba(255, 255, 255, 0.9)',
    marginTop: 1,
  },
  retryButton: {
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    paddingHorizontal: 12,
    paddingVertical: 5,
    borderRadius: 6,
    marginLeft: 8,
  },
  retryText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#FFFFFF',
  },
});

export default OfflineBanner;
