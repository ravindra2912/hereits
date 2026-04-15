@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    
    <!-- Static Pages -->
    <url>
        <loc>{{ url('') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <url>
        <loc>{{ route('register.business') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc>{{ route('blog.index') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Dynamic Content: Businesses -->
    @foreach ($businesses as $val)
        <url>
            <loc>{{ route('business-details', $val->slug) }}</loc>
            <lastmod>{{ $val->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach
    
    <!-- Dynamic Content: Experts -->
    @foreach ($experts as $val)
        @if($val->business)
        <url>
            <loc>{{ route('expert', [$val->business->slug, $val->slug]) }}</loc>
            <lastmod>{{ $val->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
        @endif
    @endforeach

    <!-- Dynamic Content: Blogs -->
    @foreach($blogs as $val)
    <url>
        <loc>{{ route('blog.detail', $val->slug) }}</loc>
        <lastmod>{{ $val->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    <!-- Dynamic Content: Products -->
    @foreach($products as $val)
        @if($val->business)
        <url>
            <loc>{{ route('product-detail', [$val->business->slug, $val->slug]) }}</loc>
            <lastmod>{{ $val->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
        @endif
    @endforeach

    <!-- Dynamic Content: Services -->
    @foreach($services as $val)
        @if($val->business)
        <url>
            <loc>{{ route('service-details', [$val->business->slug, $val->slug]) }}</loc>
            <lastmod>{{ $val->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
        @endif
    @endforeach

    <!-- Legal & Static Info Pages -->
    <url>
        <loc>{{ route('aboutUs') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
    <url>
        <loc>{{ route('contactUs') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
    <url>
        <loc>{{ route('termAndCondition') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <url>
        <loc>{{ route('privacyPolicy') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <url>
        <loc>{{ route('VendorPolicy') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>

    <url>
        <loc>{{ route('CancellationAndRefundPolicy') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <url>
        <loc>{{ route('CopyRight') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.3</priority>
    </url>
    
    <url>
        <loc>{{ route('faq') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
    
</urlset>