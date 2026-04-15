@php
if($type == 'business'){
$schema = [
"@context" => "https://schema.org",
"@type" => "LocalBusiness",
"name" => $business->name,
"image" => getImage($business->business_image),
"@id" => url()->current(),
"url" => url()->current(),
"telephone" => $business->contact,
"address" => [
"@type" => "PostalAddress",
"streetAddress" => $business->address,
"addressLocality" => $business->city->name ?? '',
"postalCode" => $business->pincode ?? '',
"addressRegion" => $business->state->name ?? '',
"addressCountry" => "IN"
],
"geo" => [
"@type" => "GeoCoordinates",
"latitude" => $business->latitude,
"longitude" => $business->longitude
],
"openingHoursSpecification" => [],
"sameAs" => array_values(array_filter([
$business->facebook,
$business->twitter,
$business->instagram,
$business->linkedin,
$business->youtube
]))
];
}else if($type == 'product'){
$schema = [
"@context" => "https://schema.org/",
"@type" => "Product",
"name" => $product->name,
"image" => getImage($product->images->first()?->image_url),
"description" => $product->description,
"sku" => "PROD-".$product->id,
"brand" => [
"@type" => "Brand",
"name" => $business->name
],
"offers" => [
"@type" => "Offer",
"url" => url()->current(),
"priceCurrency" => "INR",
"price" => $product->sell_price ?? $product->price,
"availability" => "https://schema.org/InStock"
]
];
}else if($type == 'service'){
$schema = [
"@context" => "https://schema.org/",
"@type" => "Service",
"name" => $service->name,
"description" => $service->description,
"provider" => [
"@type" => "LocalBusiness",
"name" => $business->name
],
"areaServed" => $business->city->name ?? '',
"hasOfferCatalog" => [
"@type" => "OfferCatalog",
"name" => "Services",
"itemListElement" => [
[
"@type" => "Offer",
"itemOffered" => [
"@type" => "Service",
"name" => $service->name
]
]
]
]
];
}else if($type == 'expert'){
$schema = [
"@context" => "https://schema.org/",
"@type" => "Person",
"name" => $expert->expert_name,
"jobTitle" => $expert->title,
"image" => getImage($expert->expert_image),
"description" => $expert->description,
"worksFor" => [
"@type" => "LocalBusiness",
"name" => $business->name
],
"address" => [
"@type" => "PostalAddress",
"streetAddress" => $business->address,
"addressLocality" => $business->city->name ?? '',
"addressRegion" => $business->state->name ?? '',
"addressCountry" => "IN"
]
];
}
@endphp
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}