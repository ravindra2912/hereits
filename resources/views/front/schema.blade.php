@php
if($type == 'home'){
$schema = [
"@context" => "https://schema.org",
"@type" => "WebSite",
"name" => config('const.site_setting.name'),
"url" => url('/')
];
}else if($type == 'organization'){
$schema = [
"@context" => "https://schema.org",
"@type" => "Organization",
"name" => config('const.site_setting.name'),
"url" => url('/'),
"logo" => config('const.site_setting.logo'),
"sameAs" => [
config('const.social_links.facebook'),
config('const.social_links.twitter'),
config('const.social_links.instagram'),
config('const.social_links.linkedin'),
config('const.social_links.youtube')
]
];
}else if($type == 'blog'){
$schema = [
"@context" => "https://schema.org",
"@type" => "BlogPosting",
"headline" => $blog->title,
"image" => getImage($blog->image),
"author" => [
"@type" => "Organization",
"name" => config('const.site_setting.name')
],
"publisher" => [
"@type" => "Organization",
"name" => config('const.site_setting.name'),
"logo" => [
"@type" => "ImageObject",
"url" => config('const.site_setting.logo')
]
],
"datePublished" => $blog->published_at ? $blog->published_at->toIso8601String() : $blog->created_at->toIso8601String(),
"description" => Str::limit(strip_tags($blog->short_description ?? $blog->content), 160)
];
}
@endphp
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}