@extends('business.layouts.main')
@section('title', 'Visitor Analytics')

@push('style')
<style>
  .visitor-card {
    border-radius: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }
  .visitor-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
  }
  .visitor-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
  }
  .chart-card {
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }
  .table-ranking-badge {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
  }
  .item-thumbnail {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    object-fit: cover;
    background-color: #f1f5f9;
  }
  .stat-label {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
</style>
@endpush

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-3 align-items-center">
      <div class="col-sm-12">
        <x-business-alert :businessDetails="$businessDetails" />
      </div>
      <div class="col-sm-6">
        <h2 class="m-0 fw-bold text-dark">
          <i class="bi bi-people-fill text-primary me-2"></i>Visitor Analytics
        </h2>
        <p class="text-muted small mb-0">Track customer traffic, page views, and visitor demographics in real time.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end mb-0">
          <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active">Visitors</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- KPI Metrics Cards Row -->
    <div class="row g-3 mb-4">
      <!-- 👥 Total Visitors -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card visitor-card shadow-sm h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="stat-label text-primary">Total Visitors</span>
              <div class="visitor-icon-box bg-primary bg-opacity-10 text-primary">
                👥
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($totalVisitors) }}</h3>
            <small class="text-muted">Lifetime Page Views</small>
          </div>
        </div>
      </div>

      <!-- 👤 Unique Visitors -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card visitor-card shadow-sm h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="stat-label text-success">Unique Visitors</span>
              <div class="visitor-icon-box bg-success bg-opacity-10 text-success">
                👤
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($uniqueVisitors) }}</h3>
            <small class="text-muted">Distinct Visitors</small>
          </div>
        </div>
      </div>

      <!-- 📅 Today's Visitors -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card visitor-card shadow-sm h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="stat-label text-info">Today's Visitors</span>
              <div class="visitor-icon-box bg-info bg-opacity-10 text-info">
                📅
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($todayVisitors) }}</h3>
            <small class="text-muted">{{ now()->format('d M Y') }}</small>
          </div>
        </div>
      </div>

      <!-- 📈 Last 7 Days -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card visitor-card shadow-sm h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="stat-label text-warning">Last 7 Days</span>
              <div class="visitor-icon-box bg-warning bg-opacity-10 text-warning">
                📈
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($last7DaysVisitors) }}</h3>
            <small class="text-muted">Past Week Traffic</small>
          </div>
        </div>
      </div>

      <!-- 📆 Last 30 Days -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card visitor-card shadow-sm h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="stat-label text-danger">Last 30 Days</span>
              <div class="visitor-icon-box bg-danger bg-opacity-10 text-danger">
                📆
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($last30DaysVisitors) }}</h3>
            <small class="text-muted">Past Month Traffic</small>
          </div>
        </div>
      </div>

      <!-- 🔄 Returning Visitors -->
      <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card visitor-card shadow-sm h-100 bg-white">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="stat-label text-secondary">Returning</span>
              <div class="visitor-icon-box bg-secondary bg-opacity-10 text-secondary">
                🔄
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($returningVisitors) }}</h3>
            <small class="text-muted">Repeat Visitors</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 1: Daily & Monthly Visitors -->
    <div class="row g-3 mb-4">
      <!-- Visitors by Day (Last 30 Days) -->
      <div class="col-lg-8">
        <div class="card chart-card shadow-sm h-100 bg-white">
          <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
              <h6 class="m-0 fw-bold text-dark">
                <i class="bi bi-graph-up text-primary me-2"></i>Daily Visitors (Last 30 Days)
              </h6>
              <small class="text-muted">Trend of daily page views and unique visitors</small>
            </div>
          </div>
          <div class="card-body pt-0">
            <div style="height: 300px;">
              <canvas id="dailyVisitorsChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Visitors by Month -->
      <div class="col-lg-4">
        <div class="card chart-card shadow-sm h-100 bg-white">
          <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark">
              <i class="bi bi-bar-chart-fill text-success me-2"></i>Monthly Visitors
            </h6>
            <small class="text-muted">Monthly traffic volume (last 12 months)</small>
          </div>
          <div class="card-body pt-0">
            <div style="height: 300px;">
              <canvas id="monthlyVisitorsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 2: Referrals, Device Breakdown & Browser Breakdown -->
    <div class="row g-3 mb-4">
      <!-- Top Referral Sources -->
      <div class="col-lg-4">
        <div class="card chart-card shadow-sm h-100 bg-white">
          <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark">
              <i class="bi bi-signpost-split text-info me-2"></i>Top Referral Sources
            </h6>
            <small class="text-muted">Traffic source origin (Google, WhatsApp, etc.)</small>
          </div>
          <div class="card-body pt-0">
            <div style="height: 250px;">
              <canvas id="referralChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Device Breakdown -->
      <div class="col-lg-4">
        <div class="card chart-card shadow-sm h-100 bg-white">
          <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark">
              <i class="bi bi-phone text-warning me-2"></i>Device Breakdown
            </h6>
            <small class="text-muted">Visitors by device category (Mobile/Desktop)</small>
          </div>
          <div class="card-body pt-0">
            <div style="height: 250px;">
              <canvas id="deviceChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Browser Breakdown -->
      <div class="col-lg-4">
        <div class="card chart-card shadow-sm h-100 bg-white">
          <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark">
              <i class="bi bi-browser-chrome text-primary me-2"></i>Browser Breakdown
            </h6>
            <small class="text-muted">Top web browsers used by visitors</small>
          </div>
          <div class="card-body pt-0">
            <div style="height: 250px;">
              <canvas id="browserChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Row 3: Top Viewed Products & Top Viewed Services -->
    <div class="row g-3 mb-4">
      <!-- Top Viewed Products -->
      @if ($businessSettings->is_ecommerce_system)
      <div class="col-lg-6">
        <div class="card chart-card shadow-sm h-100 bg-white">
          <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
              <h6 class="m-0 fw-bold text-dark">
                <i class="bi bi-box-seam text-primary me-2"></i>Top Viewed Products
              </h6>
              <small class="text-muted">Most popular product items by view count</small>
            </div>
            <a href="{{ route('business.product.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
          </div>
          <div class="card-body pt-0">
            @if ($topProducts->isNotEmpty())
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th class="text-end">Views</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($topProducts as $index => $product)
                  <tr>
                    <td>
                      <span class="table-ranking-badge {{ $index == 0 ? 'bg-warning text-dark' : ($index == 1 ? 'bg-secondary text-white' : 'bg-light text-muted border') }}">
                        {{ $index + 1 }}
                      </span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="{{ getImage($product->firstImage?->image_url, 'product') }}" alt="{{ $product->name }}" class="item-thumbnail me-2" onerror="this.src='{{ getImage(null, 'product') }}'">
                        <div>
                          <span class="fw-semibold text-dark d-block">{{ $product->name }}</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-secondary border">{{ $product->category?->name ?? 'General' }}</span>
                    </td>
                    <td>
                      @if ($product->price_type === 'PriceInRange')
                        <span class="fw-bold text-success">₹{{ $product->min_price }} - ₹{{ $product->max_price }}</span>
                      @elseif ($product->price_type === 'FixPrice')
                        <span class="fw-bold text-success">₹{{ $product->sell_price ?: $product->price }}</span>
                      @else
                        <span class="text-muted small">Contact</span>
                      @endif
                    </td>
                    <td class="text-end">
                      <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fw-bold">
                        <i class="bi bi-eye me-1"></i>{{ number_format($product->views_count) }}
                      </span>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="text-center py-4 text-muted">
              <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
              <p class="mb-0">No product view analytics recorded yet.</p>
            </div>
            @endif
          </div>
        </div>
      </div>
      @endif

      <!-- Top Viewed Services -->
      @if ($businessSettings->is_service_system)
      <div class="col-lg-6">
        <div class="card chart-card shadow-sm h-100 bg-white">
          <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
              <h6 class="m-0 fw-bold text-dark">
                <i class="bi bi-tools text-success me-2"></i>Top Viewed Services
              </h6>
              <small class="text-muted">Most viewed service offerings</small>
            </div>
            <a href="{{ route('business.service.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">View All</a>
          </div>
          <div class="card-body pt-0">
            @if ($topServices->isNotEmpty())
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 50px;">#</th>
                    <th>Service</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th class="text-end">Views</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($topServices as $index => $service)
                  <tr>
                    <td>
                      <span class="table-ranking-badge {{ $index == 0 ? 'bg-warning text-dark' : ($index == 1 ? 'bg-secondary text-white' : 'bg-light text-muted border') }}">
                        {{ $index + 1 }}
                      </span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="{{ getImage($service->image_url, 'service') }}" alt="{{ $service->name }}" class="item-thumbnail me-2" onerror="this.src='{{ getImage(null, 'service') }}'">
                        <div>
                          <span class="fw-semibold text-dark d-block">{{ $service->name }}</span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-secondary border">{{ $service->category?->name ?? 'General' }}</span>
                    </td>
                    <td>
                      @if ($service->price_type === 'PriceInRange')
                        <span class="fw-bold text-success">₹{{ $service->min_price }} - ₹{{ $service->max_price }}</span>
                      @elseif ($service->price_type === 'FixPrice')
                        <span class="fw-bold text-success">₹{{ $service->price }}</span>
                      @else
                        <span class="text-muted small">Contact</span>
                      @endif
                    </td>
                    <td class="text-end">
                      <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 fw-bold">
                        <i class="bi bi-eye me-1"></i>{{ number_format($service->views_count) }}
                      </span>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="text-center py-4 text-muted">
              <i class="bi bi-tools fs-1 d-block mb-2 text-secondary"></i>
              <p class="mb-0">No service view analytics recorded yet.</p>
            </div>
            @endif
          </div>
        </div>
      </div>
      @endif
    </div>

  </div>
</section>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  $(function() {
    // Fetch Chart Analytics Data
    $.ajax({
      url: "{{ route('business.visitors.data') }}",
      type: "GET",
      dataType: "json",
      success: function(response) {
        if (response.success && response.data) {
          initDailyVisitorsChart(response.data.daily);
          initMonthlyVisitorsChart(response.data.monthly);
          initReferralChart(response.data.referrals);
          initDeviceChart(response.data.devices);
          initBrowserChart(response.data.browsers);
        }
      },
      error: function(err) {
        console.error('Failed to load visitor chart analytics:', err);
      }
    });

    // 1. Daily Visitors Area / Line Chart
    function initDailyVisitorsChart(dailyData) {
      var ctx = document.getElementById('dailyVisitorsChart').getContext('2d');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: dailyData.labels,
          datasets: [
            {
              label: 'Total Views',
              data: dailyData.total,
              borderColor: '#4f46e5',
              backgroundColor: 'rgba(79, 70, 229, 0.1)',
              borderWidth: 2.5,
              tension: 0.35,
              fill: true,
              pointRadius: 2,
              pointHoverRadius: 5,
            },
            {
              label: 'Unique Visitors',
              data: dailyData.unique,
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.05)',
              borderWidth: 2,
              borderDash: [5, 5],
              tension: 0.35,
              fill: true,
              pointRadius: 2,
              pointHoverRadius: 5,
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(0, 0, 0, 0.04)' },
              ticks: { precision: 0 }
            },
            x: {
              grid: { display: false },
              ticks: { maxTicksLimit: 10 }
            }
          },
          plugins: {
            legend: { position: 'top', align: 'end' }
          }
        }
      });
    }

    // 2. Monthly Visitors Bar Chart
    function initMonthlyVisitorsChart(monthlyData) {
      var ctx = document.getElementById('monthlyVisitorsChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: monthlyData.labels,
          datasets: [{
            label: 'Monthly Views',
            data: monthlyData.views,
            backgroundColor: 'rgba(16, 185, 129, 0.85)',
            borderRadius: 6,
            barThickness: 16,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(0, 0, 0, 0.04)' },
              ticks: { precision: 0 }
            },
            x: {
              grid: { display: false }
            }
          },
          plugins: {
            legend: { display: false }
          }
        }
      });
    }

    // 3. Top Referral Sources Doughnut Chart
    function initReferralChart(refData) {
      var ctx = document.getElementById('referralChart').getContext('2d');
      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: refData.labels,
          datasets: [{
            data: refData.data,
            backgroundColor: [
              '#8B5CF6', // Hereits App Purple
              '#4285F4', // Google Blue
              '#1877F2', // Facebook Blue
              '#25D366', // WhatsApp Green
              '#E1306C', // Instagram Pink
              '#6366F1', // Direct Purple
              '#94A3B8'  // Other Gray
            ],
            borderWidth: 2,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }

    // 4. Device Breakdown Doughnut Chart
    function initDeviceChart(deviceData) {
      var ctx = document.getElementById('deviceChart').getContext('2d');
      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: deviceData.labels,
          datasets: [{
            data: deviceData.data,
            backgroundColor: [
              '#6366F1', // Mobile Indigo
              '#3B82F6', // Desktop Blue
              '#EC4899', // Tablet Pink
              '#94A3B8'  // Unknown Gray
            ],
            borderWidth: 2,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }

    // 5. Browser Breakdown Horizontal Bar Chart
    function initBrowserChart(browserData) {
      var ctx = document.getElementById('browserChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: browserData.labels,
          datasets: [{
            label: 'Visitors',
            data: browserData.data,
            backgroundColor: [
              '#3B82F6',
              '#10B981',
              '#F59E0B',
              '#6366F1',
              '#EC4899',
              '#8B5CF6'
            ],
            borderRadius: 6,
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: 'rgba(0, 0, 0, 0.04)' },
              ticks: { precision: 0 }
            },
            y: {
              grid: { display: false }
            }
          },
          plugins: {
            legend: { display: false }
          }
        }
      });
    }
  });
</script>
@endpush
