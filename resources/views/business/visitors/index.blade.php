@extends('business.layouts.main')
@section('title', 'Visitor Analytics')
@section('content')

<style>
  .filter-bar {
    border: none;
    border-radius: 16px;
    background: var(--bg-card, #ffffff);
    box-shadow: var(--card-shadow, 0 4px 20px rgba(0, 0, 0, 0.05));
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
  }
  .filter-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .filter-pill-btn {
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #ffffff);
    color: var(--secondary-color, #64748b);
    padding: 0.45rem 1.1rem;
    border-radius: 50rem;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
    text-decoration: none;
    cursor: pointer;
  }
  .filter-pill-btn:hover {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
    border-color: #3b82f6;
  }
  .filter-pill-btn.active {
    background: #3b82f6;
    color: #ffffff !important;
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
  }
  .visitor-card {
    border: none;
    border-radius: 16px;
    background: var(--bg-card, #ffffff);
    box-shadow: var(--card-shadow, 0 4px 20px rgba(0, 0, 0, 0.05));
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    overflow: hidden;
  }
  .visitor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  }
  .stat-icon-wrapper {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }
  .stat-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--secondary-color, #6c757d);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-color, #1e293b);
    line-height: 1.2;
  }
  .chart-card {
    border: none;
    border-radius: 18px;
    background: var(--bg-card, #ffffff);
    box-shadow: var(--card-shadow, 0 4px 20px rgba(0, 0, 0, 0.05));
    margin-bottom: 1.5rem;
  }
  .chart-card .card-header {
    background: transparent;
    border-bottom: 1px solid var(--border-color, #f1f5f9);
    padding: 1.25rem 1.5rem;
  }
  .chart-card .card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-color, #0f172a);
    margin: 0;
  }
  .table-custom th {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--secondary-color, #64748b);
    border-top: none;
    background: var(--table-header-bg, #f8fafc);
  }
  .table-custom td {
    vertical-align: middle;
    font-size: 0.9rem;
    color: var(--text-color, #334155);
    border-color: var(--border-color, #e2e8f0);
  }
  .badge-page-type {
    font-size: 0.75rem;
    padding: 0.35em 0.75em;
    border-radius: 50rem;
    font-weight: 600;
    text-transform: capitalize;
  }

  /* Dark mode overrides */
  [data-theme="dark"] .filter-bar {
    background: var(--bg-card, #252525) !important;
  }
  [data-theme="dark"] .filter-pill-btn {
    background: #1e1e1e !important;
    border-color: #383838 !important;
    color: #94a3b8 !important;
  }
  [data-theme="dark"] .filter-pill-btn:hover {
    background: rgba(59, 130, 246, 0.2) !important;
    color: #60a5fa !important;
    border-color: #60a5fa !important;
  }
  [data-theme="dark"] .filter-pill-btn.active {
    background: #3b82f6 !important;
    color: #ffffff !important;
    border-color: #3b82f6 !important;
  }
  [data-theme="dark"] .table-custom th {
    background: var(--table-header-bg, #383838) !important;
    color: #94a3b8 !important;
  }
  [data-theme="dark"] .table-custom td {
    color: #e2e8f0 !important;
  }
  [data-theme="dark"] .progress {
    background-color: #334155 !important;
  }
</style>

<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-3 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 fw-bold text-dark"><i class="bi bi-eye text-primary me-2"></i>Visitor Analytics</h1>
        <p class="text-muted mb-0 small">Real-time engagement insights and traffic distribution across your business storefront.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0 bg-transparent p-0">
          <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active">Visitor Analytics</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- Filter Toolbar -->
    <div class="filter-bar">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <span class="fw-bold text-dark small text-uppercase tracking-wider me-1">
            <i class="bi bi-funnel-fill text-primary me-1"></i>Filter:
          </span>
          <div class="filter-pills">
            <a href="{{ route('business.visitors', ['filter' => 'today']) }}" 
               class="filter-pill-btn {{ $filter === 'today' ? 'active' : '' }}">
              <i class="bi bi-clock-history"></i> Today
            </a>
            <a href="{{ route('business.visitors', ['filter' => 'daily']) }}" 
               class="filter-pill-btn {{ $filter === 'daily' ? 'active' : '' }}">
              <i class="bi bi-calendar-day"></i> Daily (30 Days)
            </a>
            <a href="{{ route('business.visitors', ['filter' => 'monthly']) }}" 
               class="filter-pill-btn {{ $filter === 'monthly' ? 'active' : '' }}">
              <i class="bi bi-calendar-month"></i> Monthly (12 Months)
            </a>
            <a href="{{ route('business.visitors', ['filter' => 'yearly']) }}" 
               class="filter-pill-btn {{ $filter === 'yearly' ? 'active' : '' }}">
              <i class="bi bi-calendar-range"></i> Yearly (12 Years)
            </a>
            <button type="button" id="btnCustomToggle" 
                    class="filter-pill-btn {{ $filter === 'custom' ? 'active' : '' }}">
              <i class="bi bi-calendar-check"></i> Custom Range
            </button>
          </div>
        </div>
        
        <!-- Custom Date Range Form -->
        <form method="GET" action="{{ route('business.visitors') }}" id="customDateForm" 
              class="d-flex flex-wrap align-items-center gap-2 {{ $filter === 'custom' ? '' : 'd-none' }}">
          <input type="hidden" name="filter" value="custom">
          <div class="input-group input-group-sm" style="width: auto;">
            <span class="input-group-text"><i class="bi bi-calendar-event me-1"></i>From</span>
            <input type="date" name="from_date" id="from_date" class="form-control form-control-sm" 
                   value="{{ $fromDate ?? now()->subDays(29)->format('Y-m-d') }}" required>
          </div>
          <div class="input-group input-group-sm" style="width: auto;">
            <span class="input-group-text"><i class="bi bi-calendar-event me-1"></i>To</span>
            <input type="date" name="to_date" id="to_date" class="form-control form-control-sm" 
                   value="{{ $toDate ?? now()->format('Y-m-d') }}" required>
          </div>
          <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bi bi-funnel me-1"></i>Apply
          </button>
        </form>
      </div>
    </div>

    <!-- 6 Metric Cards -->
    <div class="row g-3 mb-4">
      
      <!-- Total Visitors -->
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="card visitor-card p-3 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Total Visitors</span>
            <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
              <i class="bi bi-people-fill"></i>
            </div>
          </div>
          <div class="stat-value">{{ number_format($totalVisitors) }}</div>
          <small class="text-muted mt-1"><i class="bi bi-graph-up text-success me-1"></i>All storefront views</small>
        </div>
      </div>

      <!-- Unique Visitors -->
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="card visitor-card p-3 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Unique Visitors</span>
            <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
              <i class="bi bi-person-badge-fill"></i>
            </div>
          </div>
          <div class="stat-value">{{ number_format($uniqueVisitors) }}</div>
          <small class="text-muted mt-1"><i class="bi bi-fingerprint text-info me-1"></i>Distinct devices</small>
        </div>
      </div>

      <!-- Today's Visitors -->
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="card visitor-card p-3 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Today's Visitors</span>
            <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
              <i class="bi bi-calendar-event-fill"></i>
            </div>
          </div>
          <div class="stat-value">{{ number_format($todayVisitors) }}</div>
          <small class="text-muted mt-1"><i class="bi bi-clock-history text-success me-1"></i>Since midnight</small>
        </div>
      </div>

      <!-- Last 7 Days -->
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="card visitor-card p-3 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Last 7 Days</span>
            <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
              <i class="bi bi-bar-chart-line-fill"></i>
            </div>
          </div>
          <div class="stat-value">{{ number_format($last7DaysVisitors) }}</div>
          <small class="text-muted mt-1"><i class="bi bi-calendar-week me-1"></i>Past week views</small>
        </div>
      </div>

      <!-- Last 30 Days -->
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="card visitor-card p-3 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Last 30 Days</span>
            <div class="stat-icon-wrapper bg-purple bg-opacity-10 text-purple" style="background-color: rgba(147, 51, 234, 0.1); color: #9333ea;">
              <i class="bi bi-calendar-month-fill"></i>
            </div>
          </div>
          <div class="stat-value">{{ number_format($last30DaysVisitors) }}</div>
          <small class="text-muted mt-1"><i class="bi bi-calendar3 me-1"></i>Past month views</small>
        </div>
      </div>

      <!-- Returning Visitors -->
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="card visitor-card p-3 h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="stat-label">Returning</span>
            <div class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger">
              <i class="bi bi-arrow-repeat"></i>
            </div>
          </div>
          <div class="stat-value">{{ number_format($returningVisitors) }}</div>
          <small class="text-muted mt-1"><i class="bi bi-heart-fill text-danger me-1"></i>Repeat visitors</small>
        </div>
      </div>

    </div>

    <!-- Main Visitors Trend Chart (Full Width) -->
    <div class="row g-3 mb-4">
      <div class="col-12">
        <div class="card chart-card">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title"><i class="bi bi-graph-up me-2 text-primary"></i>{{ $chartTitle }}</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ $chartBadge }}</span>
          </div>
          <div class="card-body">
            <div style="height: 350px; position: relative;">
              <canvas id="trendVisitorsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Breakdown Charts Row: Device, Referral Sources & Browser Breakdown -->
    <div class="row g-3 mb-4">
      
      <!-- Device Breakdown -->
      <div class="col-lg-4 col-md-6">
        <div class="card chart-card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title"><i class="bi bi-phone me-2 text-info"></i>Device Breakdown</h5>
            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">Share</span>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <div style="height: 280px; width: 100%; position: relative;">
              <canvas id="deviceChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Referral Sources -->
      <div class="col-lg-4 col-md-6">
        <div class="card chart-card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title"><i class="bi bi-share me-2 text-warning"></i>Top Referral Sources</h5>
            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Traffic Source</span>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <div style="height: 280px; width: 100%; position: relative;">
              <canvas id="referralChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Browser Breakdown -->
      <div class="col-lg-4 col-md-12">
        <div class="card chart-card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title"><i class="bi bi-globe me-2 text-secondary"></i>Browser Breakdown</h5>
            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Browsers</span>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <div style="height: 280px; width: 100%; position: relative;">
              <canvas id="browserChart"></canvas>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Top Viewed Pages Table -->
    <div class="row g-3 mb-4">
      <div class="col-12">
        <div class="card chart-card h-100">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Top Viewed Pages</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Distribution</span>
          </div>
          <div class="card-body">
            @if(count($topPages) > 0)
              <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Page Type</th>
                      <th>Total Views</th>
                      <th>Share</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $totalPageViews = $topPages->sum('views_count'); @endphp
                    @foreach($topPages as $page)
                      @php $percentage = $totalPageViews > 0 ? round(($page->views_count / $totalPageViews) * 100, 1) : 0; @endphp
                      <tr>
                        <td class="fw-bold">
                          @if($page->page_type === 'business')
                            <span class="badge badge-page-type bg-primary text-white me-2">Business</span> Storefront Home
                          @elseif($page->page_type === 'product')
                            <span class="badge badge-page-type bg-success text-white me-2">Product</span> Product Detail Pages
                          @elseif($page->page_type === 'service')
                            <span class="badge badge-page-type bg-info text-white me-2">Service</span> Service Detail Pages
                          @elseif($page->page_type === 'expert')
                            <span class="badge badge-page-type bg-warning text-dark me-2">Expert</span> Expert Profile Pages
                          @else
                            <span class="badge badge-page-type bg-secondary text-white me-2">{{ $page->page_type }}</span> Page
                          @endif
                        </td>
                        <td class="fw-bold">{{ number_format($page->views_count) }}</td>
                        <td style="width: 40%;">
                          <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                              <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small fw-semibold text-muted" style="min-width: 45px;">{{ $percentage }}%</span>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <div class="text-center py-4 text-muted">
                <i class="bi bi-info-circle fs-3 mb-2 d-block text-secondary"></i>
                No page view events recorded yet.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    @php
      $isEcommerceEnabled = !empty($businessSettings->is_ecommerce_system);
      $isServiceEnabled = !empty($businessSettings->is_service_system);
      $gridClass = ($isEcommerceEnabled && $isServiceEnabled) ? 'col-lg-6' : 'col-12';
    @endphp

    @if($isEcommerceEnabled || $isServiceEnabled)
      <!-- Top Products & Top Services Tables -->
      <div class="row g-3 mb-4">
        @if($isEcommerceEnabled)
          <!-- Top Viewed Products -->
          <div class="{{ $gridClass }}">
            <div class="card chart-card h-100">
              <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title"><i class="bi bi-box-seam me-2 text-primary"></i>Top Viewed Products</h5>
                <a href="{{ route('business.product.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
              </div>
              <div class="card-body p-0">
                @if(count($topProducts) > 0)
                  <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th class="ps-4">Product Name</th>
                          <th>Price</th>
                          <th class="text-end pe-4">Views</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($topProducts as $item)
                          <tr>
                            <td class="ps-4 fw-semibold">{{ $item['name'] }}</td>
                            <td>
                              @if($item['price'] !== null)
                                <span class="fw-bold text-success">₹{{ number_format($item['price'], 2) }}</span>
                              @else
                                <span class="text-muted small">N/A</span>
                              @endif
                            </td>
                            <td class="text-end pe-4">
                              <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">{{ number_format($item['views']) }} views</span>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @else
                  <div class="text-center py-5 text-muted">
                    <i class="bi bi-box-seam fs-2 mb-2 d-block text-secondary"></i>
                    No product views recorded yet.
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endif

        @if($isServiceEnabled)
          <!-- Top Viewed Services -->
          <div class="{{ $gridClass }}">
            <div class="card chart-card h-100">
              <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title"><i class="bi bi-tools me-2 text-info"></i>Top Viewed Services</h5>
                <a href="{{ route('business.service.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">View All</a>
              </div>
              <div class="card-body p-0">
                @if(count($topServices) > 0)
                  <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                      <thead>
                        <tr>
                          <th class="ps-4">Service Name</th>
                          <th>Price</th>
                          <th class="text-end pe-4">Views</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($topServices as $item)
                          <tr>
                            <td class="ps-4 fw-semibold">{{ $item['name'] }}</td>
                            <td>
                              @if($item['price'] !== null)
                                <span class="fw-bold text-info">₹{{ number_format($item['price'], 2) }}</span>
                              @else
                                <span class="text-muted small">N/A</span>
                              @endif
                            </td>
                            <td class="text-end pe-4">
                              <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-bold">{{ number_format($item['views']) }} views</span>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @else
                  <div class="text-center py-5 text-muted">
                    <i class="bi bi-tools fs-2 mb-2 d-block text-secondary"></i>
                    No service views recorded yet.
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endif
      </div>
    @endif

  </div>
</section>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  $(function() {
    let charts = [];

    // Custom range toggle
    $('#btnCustomToggle').on('click', function() {
      const form = $('#customDateForm');
      if (form.hasClass('d-none')) {
        form.removeClass('d-none');
        $('#from_date').focus();
      } else {
        form.addClass('d-none');
      }
    });

    function getThemeColors() {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      return {
        isDark: isDark,
        textColor: isDark ? '#cbd5e1' : '#64748b',
        gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)'
      };
    }

    function initCharts() {
      const theme = getThemeColors();

      // Destroy existing charts if re-initializing on theme toggle
      charts.forEach(chart => chart.destroy());
      charts = [];

      // 1. Visitors Trend Chart (Full Width)
      const trendCanvas = document.getElementById('trendVisitorsChart');
      if (trendCanvas) {
        const trendCtx = trendCanvas.getContext('2d');
        const trendChart = new Chart(trendCtx, {
          type: 'line',
          data: {
            labels: @json($trendLabels),
            datasets: [
              {
                label: 'Total Views',
                data: @json($trendTotalViews),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.15)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 3,
                pointHoverRadius: 6
              },
              {
                label: 'Unique Visitors',
                data: @json($trendUniqueVisitors),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.08)',
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
        charts.push(trendChart);
      }

      // 2. Device Breakdown Chart (Doughnut)
      const deviceCanvas = document.getElementById('deviceChart');
      if (deviceCanvas) {
        const deviceCtx = deviceCanvas.getContext('2d');
        const rawDevice = @json($deviceData);
        const deviceLabels = rawDevice.map(item => item.device_type.charAt(0).toUpperCase() + item.device_type.slice(1));
        const deviceValues = rawDevice.map(item => item.total);

        const deviceChart = new Chart(deviceCtx, {
          type: 'doughnut',
          data: {
            labels: deviceLabels.length ? deviceLabels : ['Desktop', 'Mobile'],
            datasets: [{
              data: deviceValues.length ? deviceValues : [0, 0],
              backgroundColor: ['#3b82f6', '#ec4899', '#f59e0b', '#8b5cf6'],
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
        charts.push(deviceChart);
      }

      // 3. Referral Sources Chart (Doughnut)
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
              backgroundColor: ['#3b82f6', '#1877f2', '#25d366', '#e1306c', '#1da1f2', '#64748b'],
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
        charts.push(referralChart);
      }

      // 4. Browser Breakdown Chart (Bar)
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
              backgroundColor: '#6366f1',
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
        charts.push(browserChart);
      }
    }

    initCharts();

    // Re-initialize charts dynamically when dark mode toggle is clicked
    const toggleButton = document.getElementById('dark-mode-toggle');
    if (toggleButton) {
      toggleButton.addEventListener('click', function() {
        setTimeout(initCharts, 50);
      });
    }
  });
</script>
@endpush
@endsection
