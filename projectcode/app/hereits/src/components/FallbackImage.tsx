import React, { useState } from 'react';
import { Image, ImageProps } from 'react-native';
import DEFAULT_IMAGES from '../constants/assets';

interface FallbackImageProps extends Omit<ImageProps, 'source'> {
  source: any;
  fallbackSource?: any;
  type?: 'business' | 'product' | 'service' | 'specialist' | 'user';
}

export const FallbackImage: React.FC<FallbackImageProps> = ({
  source,
  fallbackSource,
  type = 'business',
  style,
  ...props
}) => {
  const [error, setError] = useState(false);

  const defaultFallback = DEFAULT_IMAGES[type] || fallbackSource || DEFAULT_IMAGES.business;

  const isUriObject = typeof source === 'object' && source !== null && 'uri' in source;
  
  const isDefaultOrInvalidUri = isUriObject
    ? !source.uri ||
      String(source.uri).trim() === '' ||
      String(source.uri).includes('default.png') ||
      String(source.uri).toLowerCase().includes('default')
    : !source;

  const hasValidUri = !isDefaultOrInvalidUri;
  const sourceKey = isUriObject ? source.uri : source;

  React.useEffect(() => {
    setError(false);
  }, [sourceKey]);

  const imageSource = error || !hasValidUri ? defaultFallback : source;

  return (
    <Image
      {...props}
      source={imageSource}
      style={style}
      onError={() => {
        setError(true);
      }}
    />
  );
};

export default FallbackImage;
