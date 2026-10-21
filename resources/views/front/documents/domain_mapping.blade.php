@php
    $hasBusiness = !empty($business);
    $businessSlug = $hasBusiness ? $business->slug : ($slug ?: 'YOUR_STORE_SLUG');
    $pageTitle = $hasBusiness 
        ? ('Custom Domain Setup Guide for ' . $business->name . ' | ' . config('app.name'))
        : ('Custom Domain Setup Guide | ' . config('app.name'));
@endphp

@extends('front.layouts.main', ['seo' => [
    'title' => $pageTitle,
    'description' => 'Complete step-by-step documentation to connect your own custom domain with Cloudflare Worker reverse proxy on ' . config('app.name') . '.',
    'keywords' => 'custom domain, cloudflare worker, reverse proxy, domain mapping, ' . ($hasBusiness ? $business->name . ', ' : '') . config('app.name')
]])

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-primary-dark text-white py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d6efd 100%);">
    <div class="container py-4 position-relative" style="z-index: 2;">
        <div class="row align-items-center justify-content-between g-4">
            <div class="{{ $hasBusiness ? 'col-lg-8' : 'col-lg-7' }}">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-10 text-white mb-3 small border border-white border-opacity-10 backdrop-blur">
                    <i class="bi bi-hdd-network text-info"></i>
                    <span>Custom Domain Integration Guide</span>
                </div>
                <h1 class="display-5 fw-bold text-white mb-3">
                    @if($hasBusiness)
                        Connect Custom Domain to <span class="text-info">{{ $business->name }}</span>
                    @else
                        Connect Your Custom Domain
                    @endif
                </h1>
                <p class="lead text-light text-opacity-75 mb-4 mb-lg-0" style="max-width: 650px;">
                    Follow this 3-step guide using Cloudflare Workers to link your custom domain (e.g., <code>yourbrand.com</code>) directly to your store on {{ config('app.name') }}.
                </p>
            </div>
            <div class="{{ $hasBusiness ? 'col-lg-4' : 'col-lg-5' }}">
                @if($hasBusiness)
                <div class="card bg-white bg-opacity-10 border border-white border-opacity-20 rounded-4 p-4 text-white shadow-lg backdrop-blur">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if($business->business_logo || $business->business_image)
                        <img src="{{ getImage($business->business_logo ?: $business->business_image) }}" class="rounded-circle border border-2 border-white p-1 bg-white flex-shrink-0" style="width: 54px; height: 54px; min-width: 54px; min-height: 54px; object-fit: cover;" alt="{{ $business->name }}">
                        @else
                        <div class="rounded-circle border border-2 border-white p-2 bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 54px; height: 54px; min-width: 54px; min-height: 54px; font-size: 1.25rem;">
                            {{ strtoupper(substr($business->name, 0, 1)) }}
                        </div>
                        @endif
                        <div class="min-w-0">
                            <h6 class="fw-bold mb-0 text-white text-truncate">{{ $business->name }}</h6>
                            <span class="badge bg-success bg-opacity-75 text-white small">Active Storefront</span>
                        </div>
                    </div>
                    <div class="small text-light text-opacity-75 mb-2">Primary Store URL:</div>
                    <a href="{{ url($business->slug) }}" target="_blank" class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-black bg-opacity-25 text-white text-decoration-none border border-white border-opacity-10 small hover-opacity">
                        <span class="text-truncate text-info">{{ url($business->slug) }}</span>
                        <i class="fas fa-external-link-alt ms-2 flex-shrink-0"></i>
                    </a>
                </div>
                @else
                <div class="card bg-white bg-opacity-10 border border-white border-opacity-20 rounded-4 p-4 text-white shadow-lg backdrop-blur">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-info bg-opacity-25 border border-2 border-white border-opacity-50 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 54px; height: 54px; min-width: 54px; min-height: 54px;">
                            <i class="fas fa-globe fs-4 text-white"></i>
                        </div>
                        <div class="min-w-0">
                            <h6 class="fw-bold mb-0 text-white">Custom Domain Proxy</h6>
                            <span class="badge bg-primary bg-opacity-75 text-white small">Cloudflare Worker</span>
                        </div>
                    </div>
                    <ul class="list-unstyled small text-light text-opacity-75 mb-0 d-flex flex-column gap-2">
                        <li class="d-flex align-items-center"><i class="fas fa-check-circle text-success me-2 flex-shrink-0"></i> <span>Free Automated SSL / HTTPS</span></li>
                        <li class="d-flex align-items-center"><i class="fas fa-check-circle text-success me-2 flex-shrink-0"></i> <span>Global Cloudflare Edge Caching</span></li>
                        <li class="d-flex align-items-center"><i class="fas fa-check-circle text-success me-2 flex-shrink-0"></i> <span>Clean Branded URLs for Customers</span></li>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-5 bg-light">
    <div class="container py-lg-3">
        <div class="row g-4">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 90px;">
                    <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
                        <h6 class="fw-bold px-3 pt-2 mb-3 text-dark">
                            <i class="bi bi-list-nested me-1 text-primary"></i> Steps Overview
                        </h6>
                        <nav class="nav nav-pills flex-column gap-1">
                            <a class="nav-link py-2 px-3 rounded-3 active" href="#step-1">
                                <span class="badge bg-primary me-2">1</span> Nameservers &amp; DNS
                            </a>
                            <a class="nav-link py-2 px-3 rounded-3 text-secondary" href="#step-2">
                                <span class="badge bg-secondary me-2">2</span> Cloudflare Worker
                            </a>
                            <a class="nav-link py-2 px-3 rounded-3 text-secondary" href="#step-3">
                                <span class="badge bg-secondary me-2">3</span> Route Mapping
                            </a>
                            <a class="nav-link py-2 px-3 rounded-3 text-secondary" href="#step-4">
                                <span class="badge bg-secondary me-2">4</span> SSL/TLS &amp; Security
                            </a>
                        </nav>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary text-white">
                        <h6 class="fw-bold mb-2"><i class="bi bi-shield-check me-1"></i> Security Key</h6>
                        <p class="small text-white text-opacity-75 mb-2">The tenant proxy authentication key is pre-configured in the code snippet.</p>
                        <div class="p-2 rounded bg-black bg-opacity-25 text-white small font-monospace text-truncate">
                            {{ Str::limit($proxyHashKey, 20) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Documentation Content -->
            <div class="col-lg-9">
                <!-- STEP 1: Nameservers & DNS Records -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" id="step-1">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill bg-primary px-3 py-2 fw-bold">Step 1</span>
                            <span class="text-muted small fw-semibold">Nameservers &amp; DNS Configuration</span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Point Your Domain to Cloudflare</h3>
                        <p class="text-muted small mb-0">First, update your domain's nameservers at your registrar, then add the DNS CNAME records in Cloudflare.</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- 1.1 Nameserver Setup -->
                        <div class="mb-4 pb-3 border-bottom">
                            <h5 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white rounded-pill px-2 py-1 small">1.1</span>
                                Add Cloudflare Nameservers to Your Domain
                            </h5>
                            <p class="text-secondary small mb-3">
                                Log in to your domain registrar (where you bought your domain, e.g. GoDaddy, Namecheap, Hostinger, BigRock, etc.), open your domain's <strong>DNS / Nameserver Settings</strong>, and set the nameservers to:
                            </p>

                            <div class="table-responsive rounded-3 border mb-3">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3" style="width: 140px;">Record</th>
                                            <th>Nameserver</th>
                                            <th class="text-end pe-3" style="width: 180px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-primary">Nameserver 1</span></td>
                                            <td><code class="text-primary fw-bold font-monospace fs-6">lindsey.ns.cloudflare.com</code></td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill copy-btn" data-clipboard-text="lindsey.ns.cloudflare.com">
                                                    <i class="bi bi-clipboard me-1"></i> Copy NS 1
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-primary">Nameserver 2</span></td>
                                            <td><code class="text-primary fw-bold font-monospace fs-6">roman.ns.cloudflare.com</code></td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill copy-btn" data-clipboard-text="roman.ns.cloudflare.com">
                                                    <i class="bi bi-clipboard me-1"></i> Copy NS 2
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 1.2 CNAME DNS Records -->
                        <div>
                            <h5 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white rounded-pill px-2 py-1 small">1.2</span>
                                Add CNAME Records in Cloudflare DNS
                            </h5>
                            <ol class="text-secondary ps-3 mb-3 small">
                                <li class="mb-1">Log in to your <strong>Cloudflare Dashboard</strong> and select your domain (e.g., <code>yourbrand.com</code>).</li>
                                <li class="mb-1">Navigate to <strong>DNS &gt; Records</strong> from the left sidebar.</li>
                                <li class="mb-1">Add or update the following two <strong>CNAME</strong> records with <strong>Proxy status: Proxied (Orange Cloud ON)</strong>:</li>
                            </ol>

                            <div class="table-responsive rounded-3 border mb-3">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Type</th>
                                            <th>Name</th>
                                            <th>Target</th>
                                            <th>Proxy Status</th>
                                            <th class="text-end pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-secondary">CNAME</span></td>
                                            <td><code>@</code> <small class="text-muted">(root)</small></td>
                                            <td><code class="text-primary fw-bold">{{ $targetDomain }}</code></td>
                                            <td><span class="badge bg-warning text-dark"><i class="bi bi-cloud-fill me-1"></i> Proxied</span></td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill copy-btn" data-clipboard-text="{{ $targetDomain }}">
                                                    <i class="bi bi-clipboard me-1"></i> Copy Target
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-secondary">CNAME</span></td>
                                            <td><code>www</code></td>
                                            <td><code class="text-primary fw-bold">{{ $targetDomain }}</code></td>
                                            <td><span class="badge bg-warning text-dark"><i class="bi bi-cloud-fill me-1"></i> Proxied</span></td>
                                            <td class="text-end pe-3">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill copy-btn" data-clipboard-text="{{ $targetDomain }}">
                                                    <i class="bi bi-clipboard me-1"></i> Copy Target
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-info border-0 rounded-3 mb-0 d-flex gap-3 align-items-start">
                                <i class="bi bi-info-circle-fill fs-5 text-info flex-shrink-0 mt-1"></i>
                                <div class="small">
                                    <strong>Important:</strong> Ensure the <strong>Orange Cloud (Proxy enabled)</strong> is turned <strong>ON</strong>. Cloudflare Workers only execute on traffic that passes through the Cloudflare Proxy.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Cloudflare Worker Setup -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" id="step-2">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill bg-primary px-3 py-2 fw-bold">Step 2</span>
                            <span class="text-muted small fw-semibold">Cloudflare Worker Script</span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Create the Reverse Proxy Worker</h3>
                        <p class="text-muted small mb-0">The Cloudflare Worker routes incoming requests from your custom domain to your store slug seamlessly.</p>
                    </div>
                    <div class="card-body p-4">
                        <ol class="text-secondary ps-3 mb-4">
                            <li class="mb-2">In Cloudflare, go to <strong>Workers &amp; Pages</strong> &gt; Click <strong>Create Application</strong> (or <strong>Create Worker</strong>).</li>
                            <li class="mb-2">Name your worker (e.g. <code>{{ $hasBusiness ? $business->slug : 'your-store-slug' }}-proxy</code>) and click <strong>Deploy</strong>.</li>
                            <li class="mb-2">Click <strong>Edit Code</strong>, replace the default code with the script below, and click <strong>Save and Deploy</strong>.</li>
                        </ol>

                        <!-- Code Box with Copy Button -->
                        <div class="position-relative rounded-4 overflow-hidden border shadow-sm mb-3">
                            <div class="d-flex justify-content-between align-items-center bg-dark text-white px-3 py-2 border-bottom border-secondary border-opacity-25">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary small font-monospace">worker.js</span>
                                    <span class="small text-muted">
                                        @if($hasBusiness)
                                            Auto-configured for {{ $business->name }}
                                        @else
                                            Cloudflare Reverse Proxy Script
                                        @endif
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 copy-btn" id="copy-worker-code-btn" data-clipboard-target="#worker-code-block">
                                    <i class="bi bi-clipboard me-1"></i> Copy Full Script
                                </button>
                            </div>
                            <pre class="m-0 p-3 bg-dark text-light font-monospace small" style="max-height: 480px; overflow-y: auto;"><code id="worker-code-block">export default {
  async fetch(request) {
    // ----------------------------------------------------
    // Configuration Variables
    // ----------------------------------------------------
    const TENANT_SLUG = '{{ $businessSlug }}';
    const TARGET_DOMAIN = '{{ $targetDomain }}';
    const HASH_KEY = '{{ $proxyHashKey }}';

    const url = new URL(request.url);
    const path = url.pathname;
    const slugPrefix = `/${TENANT_SLUG}`;

    // 1. Only redirect GET and HEAD requests to clean URLs.
    // NEVER redirect POST / PUT / DELETE requests (avoids 405 Method Not Allowed on form submits)
    if ((request.method === 'GET' || request.method === 'HEAD') && path.startsWith(slugPrefix)) {
      const cleanPath = path.replace(slugPrefix, '') || '/';
      return Response.redirect(`https://${url.hostname}${cleanPath}${url.search}`, 301);
    }

    // 2. Normalize the path (strip slug prefix if present on POST/AJAX to prevent duplicate slugs)
    let normalizedPath = path;
    if (normalizedPath.startsWith(slugPrefix)) {
      normalizedPath = normalizedPath.replace(slugPrefix, '') || '/';
    }

    // 3. Handle static assets & root-level routes (auth, account, appointments, payments, etc.)
    const isAsset = normalizedPath.match(/\.(css|js|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)$/i) || 
                    normalizedPath.startsWith('/build/') || 
                    normalizedPath.startsWith('/storage/') ||
                    normalizedPath.startsWith('/assets/');

    const isRootRoute = /^\/(login|register|logout|forgot-password|password-reset|auth|account|favorite|payment|\.well-known|sitemap\.xml)(\/|$)/i.test(normalizedPath);

    let targetUrl;
    if (isAsset || isRootRoute) {
      targetUrl = `https://${TARGET_DOMAIN}${normalizedPath}${url.search}`;
    } else {
      targetUrl = `https://${TARGET_DOMAIN}${slugPrefix}${normalizedPath}${url.search}`;
    }

    // 4. Clone and modify headers
    const modifiedHeaders = new Headers(request.headers);
    modifiedHeaders.set("Host", TARGET_DOMAIN);
    modifiedHeaders.set("X-Forwarded-Host", url.hostname);
    modifiedHeaders.set("X-Proxy-Hash-Key", HASH_KEY);

    // 5. Fetch options (do not pass body on GET/HEAD to prevent runtime errors)
    const fetchOptions = {
      method: request.method,
      headers: modifiedHeaders,
      redirect: "manual",
    };

    if (request.method !== 'GET' && request.method !== 'HEAD') {
      fetchOptions.body = request.body;
    }

    // 6. Fetch response from SaaS primary domain
    const response = await fetch(targetUrl, fetchOptions);

    // 7. If the backend issues a redirect, rewrite Location header back to custom domain
    if ([301, 302, 307, 308].includes(response.status)) {
      const location = response.headers.get("Location");
      if (location) {
        let redirectUrl = location
          .replace(`https://${TARGET_DOMAIN}${slugPrefix}`, `https://${url.hostname}`)
          .replace(`https://${TARGET_DOMAIN}`, `https://${url.hostname}`)
          .replace(`http://${TARGET_DOMAIN}${slugPrefix}`, `https://${url.hostname}`)
          .replace(`http://${TARGET_DOMAIN}`, `https://${url.hostname}`);

        const newHeaders = new Headers(response.headers);
        newHeaders.set("Location", redirectUrl);

        return new Response(response.body, {
          status: response.status,
          statusText: response.statusText,
          headers: newHeaders,
        });
      }
    }

    return response;
  },
};</code></pre>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Route Mapping -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" id="step-3">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill bg-primary px-3 py-2 fw-bold">Step 3</span>
                            <span class="text-muted small fw-semibold">Worker Routing</span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Map Your Custom Domain to the Worker</h3>
                        <p class="text-muted small mb-0">Bind your domain routes so Cloudflare routes visitors through the proxy worker.</p>
                    </div>
                    <div class="card-body p-4">
                        <ol class="text-secondary ps-3 mb-4">
                            <li class="mb-2">Navigate back to your domain dashboard in Cloudflare (e.g. <code>yourbrand.com</code>).</li>
                            <li class="mb-2">Click on <strong>Workers Routes</strong> (under <i>Rules</i> or <i>Settings</i>).</li>
                            <li class="mb-2">Click <strong>Add Route</strong> and create the following two routes:</li>
                        </ol>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card border rounded-3 p-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary">Route 1 (Apex Domain)</span>
                                    </div>
                                    <div class="font-monospace fw-bold text-dark mb-1">yourbrand.com/*</div>
                                    <div class="small text-muted">Worker: <span class="text-primary fw-semibold">{{ $hasBusiness ? $business->slug : 'your-store-slug' }}-proxy</span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border rounded-3 p-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary">Route 2 (WWW Subdomain)</span>
                                    </div>
                                    <div class="font-monospace fw-bold text-dark mb-1">www.yourbrand.com/*</div>
                                    <div class="small text-muted">Worker: <span class="text-primary fw-semibold">{{ $hasBusiness ? $business->slug : 'your-store-slug' }}-proxy</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: SSL/TLS & Security Settings -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" id="step-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill bg-success px-3 py-2 fw-bold">Step 4</span>
                            <span class="text-muted small fw-semibold">SSL/TLS &amp; Security Settings</span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">SSL/TLS, Security &amp; Verification</h3>
                        <p class="text-muted small mb-0">Ensure HTTPS encryption is active, configure country restriction (optional), and verify your storefront.</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- SSL/TLS Core Settings -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="d-flex gap-3 align-items-start p-3 rounded-3 bg-light border h-100">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                        <i class="fas fa-lock fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Set SSL/TLS Mode to Full</h6>
                                        <p class="text-muted small mb-0">Under <strong>SSL/TLS &gt; Overview</strong>, select <strong>Full</strong> or <strong>Full (strict)</strong> to ensure secure end-to-end HTTPS encryption.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 align-items-start p-3 rounded-3 bg-light border h-100">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                        <i class="fas fa-shield-alt fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Always Use HTTPS</h6>
                                        <p class="text-muted small mb-0">Under <strong>SSL/TLS &gt; Edge Certificates</strong>, enable <strong>Always Use HTTPS</strong> for automatic HTTP to HTTPS redirection.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Country Restriction (Geo-Blocking WAF) -->
                        <div class="p-4 rounded-4 bg-white border border-2 border-primary border-opacity-25 mb-4 shadow-sm position-relative overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="fas fa-globe-asia fs-5 text-dark"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0">Restrict Access by Country (Geo-Blocking)</h5>
                                        <span class="text-muted small">Protect your store from foreign bot traffic &amp; unwanted regions</span>
                                    </div>
                                </div>
                                <span class="badge bg-warning bg-opacity-75 text-dark rounded-pill px-3 py-2 fw-semibold">Cloudflare WAF</span>
                            </div>

                            <p class="text-secondary small mb-3">
                                You can restrict store traffic to your target operating country (e.g., allow only users from India) using Cloudflare's free Web Application Firewall (WAF):
                            </p>

                            <ol class="text-secondary ps-3 mb-3 small">
                                <li class="mb-2">In your Cloudflare Dashboard for your domain, go to <strong>Security &gt; WAF</strong> (under <i>Security</i> in left sidebar).</li>
                                <li class="mb-2">Click on the <strong>Custom Rules</strong> tab &gt; click <strong>Create Rule</strong>.</li>
                                <li class="mb-2">Enter a <strong>Rule Name</strong> (e.g. <code>Allow India Only</code> or <code>Country Restriction</code>).</li>
                                <li class="mb-2">
                                    Under <strong>When incoming requests match...</strong>, configure:
                                    <div class="table-responsive rounded-3 border mt-2 mb-2 bg-light">
                                        <table class="table table-sm table-bordered align-middle mb-0 small">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Field</th>
                                                    <th>Operator</th>
                                                    <th>Value</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>Country</code></td>
                                                    <td><code>does not equal</code> (or <code>does not match</code>)</td>
                                                    <td><strong class="text-primary">India</strong> (or your primary market)</td>
                                                    <td><span class="badge bg-danger">Block</span> <small class="text-muted">or</small> <span class="badge bg-warning text-dark">Managed Challenge</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                                <li class="mb-0">Click <strong>Deploy Rule</strong> at the bottom. Cloudflare will now automatically block or challenge any traffic originating from outside your selected country.</li>
                            </ol>
                        </div>

                        <!-- Verification Box -->
                        <div class="p-4 rounded-4 bg-light border">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i> Testing Your Setup
                            </h6>
                            <p class="small text-secondary mb-3">Once saved, visit your custom domain in an Incognito/Private window. Your storefront should load seamlessly under your own branded domain name.</p>
                            <div class="d-flex flex-wrap gap-2">
                                @if($hasBusiness)
                                <a href="{{ url($business->slug) }}" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                                    <i class="fas fa-store me-1"></i> Open {{ $business->name }} Store
                                </a>
                                @endif
                                <a href="{{ route('contactUs') }}" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                                    <i class="fas fa-headset me-1"></i> Need Help? Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('css')
<style>
    .backdrop-blur {
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
    }
    .hover-opacity:hover {
        opacity: 0.85;
    }
    pre, pre code {
        white-space: pre !important;
        word-break: normal !important;
        word-wrap: normal !important;
        line-height: 1.6;
    }
</style>
@endpush

@push('js')
<script>
    function copyTextToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            return new Promise(function(resolve, reject) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.top = "-9999px";
                textArea.style.left = "-9999px";
                textArea.style.opacity = "0";
                textArea.setAttribute("readonly", "");
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    var successful = document.execCommand('copy');
                    document.body.removeChild(textArea);
                    if (successful) {
                        resolve();
                    } else {
                        reject(new Error('Copy command failed'));
                    }
                } catch (err) {
                    document.body.removeChild(textArea);
                    reject(err);
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Copy to clipboard handler
        document.querySelectorAll('.copy-btn').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                var targetSelector = this.getAttribute('data-clipboard-target');
                var textToCopy = this.getAttribute('data-clipboard-text');

                if (targetSelector) {
                    var targetEl = document.querySelector(targetSelector);
                    if (targetEl) {
                        textToCopy = targetEl.innerText || targetEl.textContent;
                    }
                }

                if (textToCopy) {
                    copyTextToClipboard(textToCopy).then(function() {
                        var originalHtml = button.innerHTML;
                        button.innerHTML = '<i class="fas fa-check me-1 text-success"></i> Copied!';
                        button.classList.add('btn-success');
                        button.classList.remove('btn-outline-light', 'btn-outline-secondary');
                        
                        setTimeout(function() {
                            button.innerHTML = originalHtml;
                            button.classList.remove('btn-success');
                            if (button.id === 'copy-worker-code-btn') {
                                button.classList.add('btn-outline-light');
                            } else {
                                button.classList.add('btn-outline-secondary');
                            }
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Failed to copy: ', err);
                    });
                }
            });
        });
    });
</script>
@endpush
