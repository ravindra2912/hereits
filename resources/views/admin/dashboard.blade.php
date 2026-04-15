@extends('admin.layouts.main')
@section('title', 'Dashboard')
@section('content')

<h2 class="mb-4 text-gray-800">Dashboard</h2>

<!-- Stats Grid -->
<div class="row g-4 mb-4">
  <!-- Total Users -->
  <div class="col-md-4 col-sm-6">
    <div class="card h-100 py-2 border-left-secondary">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Users</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userCount }}</div>
          </div>
          <div class="col-auto">
            <i class="bi bi-person-slash dash-icon text-gray-300"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0 pt-0">
        <a href="{{ route('admin.user.index') }}" class="text-secondary small stretched-link text-decoration-none">View Details <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </div>

  <!-- Active Subscriptions -->
  <div class="col-md-4 col-sm-6">
    <div class="card h-100 py-2 border-left-success">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Business</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sellerCount }}</div>
          </div>
          <div class="col-auto">
            <i class="bi bi-check-circle dash-icon text-gray-300"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0 pt-0">
        <a href="{{ route('admin.user.index') }}" class="text-success small stretched-link text-decoration-none">View Details <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </div>

  <!-- Total Plans -->
  <div class="col-md-4 col-sm-6">
    <div class="card h-100 py-2 border-left-warning">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Business</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeBusinessCount }}</div>
          </div>
          <div class="col-auto">
            <i class="bi bi-tags dash-icon text-gray-300"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0 pt-0">
        <a href="{{ route('admin.business.index') }}" class="text-warning small stretched-link text-decoration-none">View Details <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </div>

  <!-- Total Revenue -->
  <div class="col-md-4 col-sm-6">
    <div class="card h-100 py-2 border-left-primary">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Business</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingBusinessCount }}</div>
          </div>
          <div class="col-auto">
            <i class="bi bi-currency-dollar dash-icon text-gray-300"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent border-0 pt-0">
        <a href="{{ route('admin.business.index') }}" class="text-primary small stretched-link text-decoration-none">View Details <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </div>



  <!-- Charts Row -->
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card shadow mb-4 h-100">
        <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue ({{ date('Y') }})</h6>
        </div>
        <div class="card-body">
          <div style="height: 300px;">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow mb-4 h-100">
        <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-success">Monthly Purchases ({{ date('Y') }})</h6>
        </div>
        <div class="card-body">
          <div style="height: 300px;">
            <canvas id="purchaseChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  @endsection

  @push('js')
  <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var chartLabels = @json($chartLabels);

      // Revenue Chart
      var revenueCtx = document.getElementById('revenueChart').getContext('2d');
      var revenueData = @json($revenueChartData);
      new Chart(revenueCtx, {
        type: 'bar', // changed to bar for consistency or keep line if preferred
        data: {
          labels: chartLabels,
          datasets: [{
            label: 'Revenue',
            data: revenueData,
            backgroundColor: '#4e73df',
            hoverBackgroundColor: '#2e59d9',
            borderColor: '#4e73df',
            borderRadius: 4,
            maxBarThickness: 30
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              grid: {
                display: false,
                drawBorder: false
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: "rgb(234, 236, 244)",
                borderDash: [2],
                drawBorder: false,
                zeroLineBorderDash: [2]
              },
              ticks: {
                padding: 10
              }
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });

      // Purchase Chart
      var purchaseCtx = document.getElementById('purchaseChart').getContext('2d');
      var purchaseData = @json($purchaseChartData);
      new Chart(purchaseCtx, {
        type: 'bar',
        data: {
          labels: chartLabels,
          datasets: [{
            label: 'Purchases',
            data: purchaseData,
            backgroundColor: '#1cc88a',
            hoverBackgroundColor: '#17a673',
            borderColor: '#1cc88a',
            borderRadius: 4,
            maxBarThickness: 30
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              grid: {
                display: false,
                drawBorder: false
              }
            },
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0,
                padding: 10
              },
              grid: {
                color: "rgb(234, 236, 244)",
                borderDash: [2],
                drawBorder: false
              }
            }
          },
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
    });
  </script>
  @endpush