import React, { useState } from 'react';
import { Image, ImageProps } from 'react-native';

interface FallbackImageProps extends Omit<ImageProps, 'source'> {
  source: any;
  fallbackSource: any;
}

export const FallbackImage: React.FC<FallbackImageProps> = ({
  source,
  fallbackSource,
  style,
  ...props
}) => {
  const [error, setError] = useState(false);

  const isUriObject = typeof source === 'object' && source !== null && 'uri' in source;
  const hasValidUri = isUriObject ? !!source.uri && String(source.uri).trim() !== '' : !!source;
  const sourceKey = isUriObject ? source.uri : source;

  React.useEffect(() => {
    setError(false);
  }, [sourceKey]);

  const imageSource = error || !hasValidUri ? fallbackSource : source;

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
