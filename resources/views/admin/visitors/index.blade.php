@extends('admin.layouts.main')
@section('title', 'Visitor Analytics')
@section('content')

<!-- Page Header & Breadcrumb -->
<div class="row align-items-center mb-4">
  <div class="col-md-7">
    <h2 class="mb-1 text-gray-800 fw-bold">
      <i class="bi bi-graph-up-arrow text-primary me-2"></i>Visitor Analytics
    </h2>
    <p class="text-muted small mb-0">Platform-wide visitor traffic, user engagement, and multi-entity SaaS analytics.</p>
  </div>
  <div class="col-md-5 text-md-end mt-3 mt-md-0">
    <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
      <i class="bi bi-calendar3 me-1"></i> {{ $dateRangeLabel }}
    </span>
  </div>
</div>

<!-- Validation Error Alert -->
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    {{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<!-- Date Filters Bar -->
<div class="visitor-filter-card">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="visitor-stat-title me-2 mb-0">
        <i class="bi bi-funnel-fill text-primary me-1"></i>Date Filter:
      </span>
      <div class="visitor-pills">
        <a href="{{ route('admin.visitors.index', ['filter' => 'today']) }}" 
           class="visitor-pill-btn {{ $filter === 'today' ? 'active' : '' }}">
          <i class="bi bi-clock-history"></i> Today
        </a>
        <a href="{{ route('admin.visitors.index', ['filter' => 'week']) }}" 
           class="visitor-pill-btn {{ $filter === 'week' ? 'active' : '' }}">
          <i class="bi bi-calendar-week"></i> This Week
        </a>
        <a href="{{ route('admin.visitors.index', ['filter' => 'this_month']) }}" 
           class="visitor-pill-btn {{ $filter === 'this_month' ? 'active' : '' }}">
          <i class="bi bi-calendar-month"></i> This Month
        </a>
        <a href="{{ route('admin.visitors.index', ['filter' => 'last_month']) }}" 
           class="visitor-pill-btn {{ $filter === 'last_month' ? 'active' : '' }}">
          <i class="bi bi-calendar-range"></i> Last Month
        </a>
        <a href="{{ route('admin.visitors.index', ['filter' => 'all']) }}" 
           class="visitor-pill-btn {{ $filter === 'all' ? 'active' : '' }}">
          <i class="bi bi-infinity"></i> All Time
        </a>
        <button type="button" id="btnCustomDateToggle" 
                class="visitor-pill-btn {{ $filter === 'custom' ? 'active' : '' }}">
          <i class="bi bi-calendar2-range"></i> Custom Date Range
        </button>
      </div>
    </div>
    <div>
      <span class="visitor-stat-subtext">
        All-Time Platform Views: <strong class="text-dark">{{ number_format($allTimeViews) }}</strong>
      </span>
    </div>
  </div>

  <!-- Custom Date Selection Drawer/Form -->
  <div id="customDateDrawer" class="custom-date-box {{ $filter === 'custom' ? '' : 'd-none' }}">
    <form action="{{ route('admin.visitors.index') }}" method="GET" class="row g-3 align-items-end">
      <input type="hidden" name="filter" value="custom">
      <div class="col-md-4 col-sm-6">
        <label for="from_date" class="form-label small fw-bold text-muted">From Date</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
          <input type="date" class="form-control" id="from_date" name="from_date" 
                 value="{{ $fromDate ?? $filterStartDate->format('Y-m-d') }}" required>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <label for="to_date" class="form-label small fw-bold text-muted">To Date</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
          <input type="date" class="form-control" id="to_date" name="to_date" 
                 value="{{ $toDate ?? $filterEndDate->format('Y-m-d') }}" required>
        </div>
      </div>
      <div class="col-md-4 col-sm-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm px-4">
          <i class="bi bi-filter me-1"></i> Apply Filter
        </button>
        <a href="{{ route('admin.visitors.index') }}" class="btn btn-outline-secondary btn-sm px-3">
          Reset
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Primary Metric KPI Cards -->
<div class="row g-3 mb-4">
  <!-- Total Views -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">Total Views</div>
            <div class="visitor-stat-value text-primary">{{ number_format($totalViews) }}</div>
            <div class="visitor-stat-subtext">All entities in period</div>
          </div>
          <div class="stat-icon-box stat-icon-primary">
            <i class="bi bi-eye"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Unique Visitors -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">Unique Visitors</div>
            <div class="visitor-stat-value text-success">{{ number_format($uniqueVisitors) }}</div>
            <div class="visitor-stat-subtext">Distinct visitor sessions</div>
          </div>
          <div class="stat-icon-box stat-icon-success">
            <i class="bi bi-people"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Returning Visitors -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">Returning Visitors</div>
            <div class="visitor-stat-value text-info">{{ number_format($returningVisitorsCount) }}</div>
            <div class="visitor-stat-subtext">Visited &gt; 1 time</div>
          </div>
          <div class="stat-icon-box stat-icon-info">
            <i class="bi bi-arrow-repeat"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Total All-Time Visitors -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">All-Time Visitors</div>
            <div class="visitor-stat-value text-dark">{{ number_format($allTimeVisitors) }}</div>
            <div class="visitor-stat-subtext">Platform cumulative total</div>
          </div>
          <div class="stat-icon-box stat-icon-dark">
            <i class="bi bi-globe2"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Secondary Metric Entity Cards -->
<div class="row g-3 mb-4">
  <!-- Business Views -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">Business Storefronts</div>
            <div class="visitor-stat-value">{{ number_format($businessViews) }}</div>
            <div class="visitor-stat-subtext">
              <span class="badge-entity badge-entity-business">Storefront Views</span>
            </div>
          </div>
          <div class="stat-icon-box stat-icon-primary">
            <i class="bi bi-shop"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Product Views -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">Product Views</div>
            <div class="visitor-stat-value">{{ number_format($productViews) }}</div>
            <div class="visitor-stat-subtext">
              <span class="badge-entity badge-entity-product">Catalog Items</span>
            </div>
          </div>
          <div class="stat-icon-box stat-icon-success">
            <i class="bi bi-box-seam"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Service Views -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">Service Views</div>
            <div class="visitor-stat-value">{{ number_format($serviceViews) }}</div>
            <div class="visitor-stat-subtext">
              <span class="badge-entity badge-entity-service">Service Offerings</span>
            </div>
          </div>
          <div class="stat-icon-box stat-icon-info">
            <i class="bi bi-briefcase"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Expert Views -->
  <div class="col-xl-3 col-md-6">
    <div class="visitor-stat-card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="visitor-stat-title">Expert Views</div>
            <div class="visitor-stat-value">{{ number_format($expertViews) }}</div>
            <div class="visitor-stat-subtext">
              <span class="badge-entity badge-entity-expert">Specialist Profiles</span>
            </div>
          </div>
          <div class="stat-icon-box stat-icon-warning">
            <i class="bi bi-person-badge"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Primary Charts Row (Traffic Trend & Entity Distribution) -->
<div class="row g-4 mb-4">
  <!-- Traffic Timeline Trend -->
  <div class="col-lg-8">
    <div class="visitor-chart-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-activity text-primary me-2"></i>{{ $chartTitle }}
        </h6>
        <span class="badge bg-light text-dark border">Timeline</span>
      </div>
      <div class="card-body">
        <div style="height: 320px; position: relative;">
          <canvas id="trendChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Entity Type Distribution -->
  <div class="col-lg-4">
    <div class="visitor-chart-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-pie-chart text-success me-2"></i>Traffic by Module
        </h6>
        <span class="badge bg-light text-dark border">Breakdown</span>
      </div>
      <div class="card-body">
        <div style="height: 240px; position: relative;">
          <canvas id="entityChart"></canvas>
        </div>
        <div class="row text-center mt-3 g-2 pt-2 border-top">
          @foreach ($entityBreakdown as $entity)
            <div class="col-6 col-sm-3 col-lg-6">
              <span class="small text-muted d-block">{{ $entity['name'] }}</span>
              <strong class="fs-6">{{ number_format($entity['total']) }}</strong>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Secondary Charts Row (Device, Browser, Referral Source) -->
<div class="row g-4 mb-4">
  <!-- Devices -->
  <div class="col-lg-4 col-md-6">
    <div class="visitor-chart-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-laptop text-info me-2"></i>Device Types
        </h6>
      </div>
      <div class="card-body">
        <div style="height: 240px; position: relative;">
          <canvas id="deviceChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Browsers -->
  <div class="col-lg-4 col-md-6">
    <div class="visitor-chart-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-browser-chrome text-primary me-2"></i>Top Browsers
        </h6>
      </div>
      <div class="card-body">
        <div style="height: 240px; position: relative;">
          <canvas id="browserChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Traffic Referrals -->
  <div class="col-lg-4 col-md-12">
    <div class="visitor-chart-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-diagram-3 text-warning me-2"></i>Referral Sources
        </h6>
      </div>
      <div class="card-body">
        <div style="height: 240px; position: relative;">
          <canvas id="referralChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Leaderboards: Top Businesses & Top Products -->
<div class="row g-4 mb-4">
  <!-- Top Businesses -->
  <div class="col-lg-6">
    <div class="visitor-table-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-trophy text-warning me-2"></i>Top 10 Visited Businesses
        </h6>
        <span class="badge bg-primary-subtle text-primary">By Views</span>
      </div>
      <div class="table-responsive">
        <table class="table visitor-table align-middle">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Business</th>
              <th>Owner</th>
              <th class="text-end">Unique</th>
              <th class="text-end">Views</th>
              <th class="text-center" style="width: 70px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($topBusinesses as $index => $business)
              <tr>
                <td>
                  <span class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">
                    {{ $index + 1 }}
                  </span>
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ $business->name }}</div>
                  @if ($business->contact)
                    <div class="small text-muted">{{ $business->contact }}</div>
                  @endif
                </td>
                <td>
                  <span class="small text-muted">{{ $business->owner_name }}</span>
                </td>
                <td class="text-end">
                  <span class="badge bg-light text-dark border">{{ number_format($business->unique_count) }}</span>
                </td>
                <td class="text-end fw-bold text-primary">
                  {{ number_format($business->views_count) }}
                </td>
                <td class="text-center">
                  @if ($business->slug)
                    <a href="{{ route('admin.business.index') }}?search={{ urlencode($business->name) }}" 
                       class="btn btn-sm btn-outline-primary" title="View Business">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  @else
                    -
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                  No business traffic recorded in this date range.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top Products -->
  <div class="col-lg-6">
    <div class="visitor-table-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-bag-check text-success me-2"></i>Top 10 Visited Products
        </h6>
        <span class="badge bg-success-subtle text-success">Catalog</span>
      </div>
      <div class="table-responsive">
        <table class="table visitor-table align-middle">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Product Name</th>
              <th>Business</th>
              <th class="text-end">Price</th>
              <th class="text-end">Views</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($topProducts as $index => $product)
              <tr>
                <td>
                  <span class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">
                    {{ $index + 1 }}
                  </span>
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ $product->name }}</div>
                </td>
                <td>
                  <span class="small text-muted">{{ $product->business_name }}</span>
                </td>
                <td class="text-end text-muted">
                  {{ $product->price ? '₹' . number_format((float) $product->price, 2) : 'N/A' }}
                </td>
                <td class="text-end fw-bold text-success">
                  {{ number_format($product->views_count) }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                  No product views recorded in this date range.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Leaderboards: Top Services & Top Experts -->
<div class="row g-4 mb-4">
  <!-- Top Services -->
  <div class="col-lg-6">
    <div class="visitor-table-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-gear text-info me-2"></i>Top 10 Visited Services
        </h6>
        <span class="badge bg-info-subtle text-info">Services</span>
      </div>
      <div class="table-responsive">
        <table class="table visitor-table align-middle">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Service Name</th>
              <th>Business</th>
              <th class="text-end">Price</th>
              <th class="text-end">Views</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($topServices as $index => $service)
              <tr>
                <td>
                  <span class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">
                    {{ $index + 1 }}
                  </span>
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ $service->name }}</div>
                </td>
                <td>
                  <span class="small text-muted">{{ $service->business_name }}</span>
                </td>
                <td class="text-end text-muted">
                  {{ $service->price ? '₹' . number_format((float) $service->price, 2) : 'N/A' }}
                </td>
                <td class="text-end fw-bold text-info">
                  {{ number_format($service->views_count) }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                  No service views recorded in this date range.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top Experts -->
  <div class="col-lg-6">
    <div class="visitor-table-card">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-star text-warning me-2"></i>Top 10 Visited Experts
        </h6>
        <span class="badge bg-warning-subtle text-warning">Specialists</span>
      </div>
      <div class="table-responsive">
        <table class="table visitor-table align-middle">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Expert Name</th>
              <th>Designation</th>
              <th>Business</th>
              <th class="text-end">Views</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($topExperts as $index => $expert)
              <tr>
                <td>
                  <span class="rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : '')) }}">
                    {{ $index + 1 }}
                  </span>
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ $expert->name }}</div>
                </td>
                <td>
                  <span class="small text-muted">{{ $expert->title }}</span>
                </td>
                <td>
                  <span class="small text-muted">{{ $expert->business_name }}</span>
                </td>
                <td class="text-end fw-bold text-warning">
                  {{ number_format($expert->views_count) }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                  No expert views recorded in this date range.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Location & Recent Activity Row -->
<div class="row g-4 mb-4">
  <!-- Top Locations -->
  <div class="col-lg-4">
    <div class="visitor-table-card h-100">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-geo-alt text-danger me-2"></i>Top Locations
        </h6>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse ($locationData as $loc)
            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
              <span class="text-dark fw-medium">
                <i class="bi bi-pin-map text-danger me-2"></i>{{ $loc->location }}
              </span>
              <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                {{ number_format($loc->total) }} views
              </span>
            </li>
          @empty
            <li class="list-group-item text-center py-4 text-muted">
              No geographical data recorded in this period.
            </li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>

  <!-- Recent Activity Stream -->
  <div class="col-lg-8">
    <div class="visitor-table-card h-100">
      <div class="card-header">
        <h6 class="card-title">
          <i class="bi bi-clock-history text-secondary me-2"></i>Recent Visitor Activity Stream
        </h6>
        <span class="badge bg-light text-dark border">Latest 15 Visits</span>
      </div>
      <div class="table-responsive">
        <table class="table visitor-table align-middle">
          <thead>
            <tr>
              <th>Time</th>
              <th>Module</th>
              <th>Entity Visited</th>
              <th>Business</th>
              <th>Device &amp; Browser</th>
              <th>Location</th>
              <th>Referrer</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentActivities as $item)
              <tr>
                <td class="text-nowrap small text-muted">
                  {{ $item->created_at->format('d M, H:i') }}
                </td>
                <td>
                  <span class="badge-entity badge-entity-{{ $item->page_type }}">
                    {{ $item->page_type }}
                  </span>
                </td>
                <td class="fw-semibold text-dark">
                  {{ $item->entity_title }}
                </td>
                <td class="small text-muted">
                  {{ $item->business_name }}
                </td>
                <td class="small text-nowrap">
                  <i class="bi {{ strtolower($item->device) === 'mobile' ? 'bi-phone' : (strtolower($item->device) === 'tablet' ? 'bi-tablet' : 'bi-laptop') }} me-1"></i>
                  {{ $item->device }} · {{ $item->browser }}
                </td>
                <td class="small text-muted text-nowrap">
                  {{ $item->location }}
                </td>
                <td class="small text-muted text-truncate" style="max-width: 120px;">
                  {{ $item->referer }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                  No visitor activity events recorded in this period.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle Custom Date Drawer
    const customToggleBtn = document.getElementById('btnCustomDateToggle');
    const customDrawer = document.getElementById('customDateDrawer');

    if (customToggleBtn && customDrawer) {
      customToggleBtn.addEventListener('click', function() {
        customDrawer.classList.toggle('d-none');
        if (!customDrawer.classList.contains('d-none')) {
          document.getElementById('from_date')?.focus();
        }
      });
    }

    // Chart.js Theme Awareness
    let activeCharts = [];

    function getChartTheme() {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      return {
        isDark: isDark,
        textColor: isDark ? '#adb5bd' : '#64748b',
        gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)',
        cardBg: isDark ? '#2d2d2d' : '#ffffff'
      };
    }

    function initCharts() {
      // Clean up previous instances
      activeCharts.forEach(chart => chart.destroy());
      activeCharts = [];

      const theme = getChartTheme();

      // 1. Timeline Trend Chart (Line/Area)
      const trendCanvas = document.getElementById('trendChart');
      if (trendCanvas) {
        const trendCtx = trendCanvas.getContext('2d');
        const trendLabels = @json($trendLabels);
        const trendTotalViews = @json($trendTotalViews);
        const trendUniqueVisitors = @json($trendUniqueVisitors);

        const trendChart = new Chart(trendCtx, {
          type: 'line',
          data: {
            labels: trendLabels,
            datasets: [
              {
                label: 'Total Views',
                data: trendTotalViews,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.12)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 3,
                pointHoverRadius: 6
              },
              {
                label: 'Unique Visitors',
                data: trendUniqueVisitors,
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 2,
                borderDash: [5, 5],
                pointRadius: 2,
                pointHoverRadius: 5
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'top',
                labels: { color: theme.textColor }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: { precision: 0, color: theme.textColor },
                grid: { color: theme.gridColor }
              },
              x: {
                ticks: { color: theme.textColor },
                grid: { display: false }
              }
            }
          }
        });
        activeCharts.push(trendChart);
      }

      // 2. Entity Module Distribution (Doughnut)
      const entityCanvas = document.getElementById('entityChart');
      if (entityCanvas) {
        const entityCtx = entityCanvas.getContext('2d');
        const rawEntity = @json($entityBreakdown);
        const entityLabels = rawEntity.map(item => item.name);
        const entityValues = rawEntity.map(item => item.total);
        const entityColors = rawEntity.map(item => item.color);

        const entityChart = new Chart(entityCtx, {
          type: 'doughnut',
          data: {
            labels: entityLabels,
            datasets: [{
              data: entityValues,
              backgroundColor: entityColors,
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { color: theme.textColor }
              }
            },
            cutout: '70%'
          }
        });
        activeCharts.push(entityChart);
      }

      // 3. Device Types Breakdown (Doughnut)
      const deviceCanvas = document.getElementById('deviceChart');
      if (deviceCanvas) {
        const deviceCtx = deviceCanvas.getContext('2d');
        const rawDevice = @json($deviceData);
        const deviceLabels = rawDevice.map(item => item.device_type);
        const deviceValues = rawDevice.map(item => item.total);

        const deviceChart = new Chart(deviceCtx, {
          type: 'doughnut',
          data: {
            labels: deviceLabels.length ? deviceLabels : ['Desktop', 'Mobile'],
            datasets: [{
              data: deviceValues.length ? deviceValues : [0, 0],
              backgroundColor: ['#4e73df', '#36b9cc', '#f6c23e', '#e74a3b'],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { color: theme.textColor }
              }
            },
            cutout: '65%'
          }
        });
        activeCharts.push(deviceChart);
      }

      // 4. Browser Breakdown (Bar)
      const browserCanvas = document.getElementById('browserChart');
      if (browserCanvas) {
        const browserCtx = browserCanvas.getContext('2d');
        const rawBrowser = @json($browserData);
        const browserLabels = rawBrowser.map(item => item.browser_name);
        const browserValues = rawBrowser.map(item => item.total);

        const browserChart = new Chart(browserCtx, {
          type: 'bar',
          data: {
            labels: browserLabels.length ? browserLabels : ['Other'],
            datasets: [{
              label: 'Visitors',
              data: browserValues.length ? browserValues : [0],
              backgroundColor: '#4e73df',
              borderRadius: 6
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false }
            },
            scales: {
              x: {
                beginAtZero: true,
                ticks: { precision: 0, color: theme.textColor },
                grid: { color: theme.gridColor }
              },
              y: {
                ticks: { color: theme.textColor },
                grid: { display: false }
              }
            }
          }
        });
        activeCharts.push(browserChart);
      }

      // 5. Referral Sources (Doughnut)
      const referralCanvas = document.getElementById('referralChart');
      if (referralCanvas) {
        const referralCtx = referralCanvas.getContext('2d');
        const rawReferral = @json($referralData);
        const refLabels = rawReferral.map(item => item.source);
        const refValues = rawReferral.map(item => item.total);

        const referralChart = new Chart(referralCtx, {
          type: 'doughnut',
          data: {
            labels: refLabels.length ? refLabels : ['Direct'],
            datasets: [{
              data: refValues.length ? refValues : [1],
              backgroundColor: ['#4e73df', '#1877f2', '#25d366', '#e1306c', '#1da1f2', '#858796'],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { color: theme.textColor }
              }
            },
            cutout: '65%'
          }
        });
        activeCharts.push(referralChart);
      }
    }

    // Initialize all dashboard charts
    initCharts();

    // Re-render charts when dark mode toggles
    const darkToggleBtn = document.getElementById('dark-mode-toggle');
    if (darkToggleBtn) {
      darkToggleBtn.addEventListener('click', function() {
        setTimeout(initCharts, 80);
      });
    }
  });
</script>
@endpush
