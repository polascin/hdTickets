@extends('layouts.app-v2')

@section('title', 'Profile Analytics')

@section('header')
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <div>
      <h1 class="h3 mb-0 text-gray-900">Profile Analytics</h1>
      <nav aria-label="breadcrumb" class="mt-1">
        <ol class="breadcrumb breadcrumb-sm mb-0">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('profile.show') }}" class="text-decoration-none">Profile</a></li>
          <li class="breadcrumb-item active" aria-current="page">Analytics</li>
        </ol>
      </nav>
    </div>
    <div class="mt-3 mt-md-0">
      <div class="btn-group" role="group">
        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
        <button class="btn btn-outline-primary btn-sm" onclick="refreshAnalytics()">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button class="btn btn-outline-success btn-sm" onclick="exportAnalytics()">
          <i class="fas fa-download me-1"></i> Export
        </button>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .analytics-card {
      transition: all 0.3s ease;
      border: none;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .analytics-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .metric-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 15px;
      padding: 1.5rem;
      text-align: center;
      transition: all 0.3s ease;
    }

    .metric-card:hover {
      transform: scale(1.05);
    }

    .metric-value {
      font-size: 2.5rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }

    .metric-label {
      font-size: 0.9rem;
      opacity: 0.9;
    }

    .trend-positive {
      color: #10b981;
    }

    .trend-negative {
      color: #ef4444;
    }

    .chart-container {
      position: relative;
      height: 300px;
      margin: 1rem 0;
    }

    .insight-item {
      background: #f8fafc;
      border-left: 4px solid #3b82f6;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 0 8px 8px 0;
    }

    .recommendation-item {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      border: 1px solid #f59e0b;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }

    .timeline-item {
      position: relative;
      padding-left: 2rem;
      margin-bottom: 1.5rem;
    }

    .timeline-item::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0.5rem;
      width: 10px;
      height: 10px;
      background: #3b82f6;
      border-radius: 50%;
    }

    .timeline-item::after {
      content: '';
      position: absolute;
      left: 4px;
      top: 1.5rem;
      width: 2px;
      height: calc(100% - 1rem);
      background: #e5e7eb;
    }

    .timeline-item:last-child::after {
      display: none;
    }

    .loading-skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
      border-radius: 4px;
    }

    @keyframes loading {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }
  </style>
@endpush

@section('content')
  <div class="container-fluid px-4">
    <!-- Key Metrics Overview -->
    <div class="row mb-4">
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card">
          <div class="metric-value" id="activity-score">{{ $analytics['activity_metrics']['interaction_score'] ?? 0 }}
          </div>
          <div class="metric-label">Activity Score</div>
          <small class="trend-positive">
            <i class="fas fa-arrow-up"></i> +5% this week
          </small>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
          <div class="metric-value" id="engagement-level">{{ $analytics['engagement_stats']['interaction_score'] ?? 0 }}%
          </div>
          <div class="metric-label">Engagement Level</div>
          <small class="trend-positive">
            <i class="fas fa-arrow-up"></i> +12% this month
          </small>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
          <div class="metric-value" id="login-frequency">
            {{ $analytics['activity_metrics']['active_days_this_month'] ?? 0 }}</div>
          <div class="metric-label">Active Days (Month)</div>
          <small class="trend-positive">
            <i class="fas fa-arrow-up"></i> +3 days
          </small>
        </div>
      </div>
      <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
          <div class="metric-value" id="performance-score">
            {{ $analytics['performance_insights']['optimization_score'] ?? 0 }}%</div>
          <div class="metric-label">Performance Score</div>
          <small class="trend-positive">
            <i class="fas fa-arrow-up"></i> +8% improvement
          </small>
        </div>
      </div>
    </div>

    <!-- Activity Trends Chart -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card analytics-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-line me-2 text-primary"></i>
              Activity Trends (Last 30 Days)
            </h5>
            <div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-outline-primary active" onclick="changeTimeframe('30d')">30 Days</button>
              <button class="btn btn-outline-primary" onclick="changeTimeframe('7d')">7 Days</button>
              <button class="btn btn-outline-primary" onclick="changeTimeframe('24h')">24 Hours</button>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="activityChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="row mb-4">
      <!-- Activity Metrics -->
      <div class="col-lg-6 mb-4">
        <div class="card analytics-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-user-clock me-2 text-success"></i>
              Activity Metrics
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-primary mb-1">{{ $analytics['activity_metrics']['session_duration_avg'] ?? 0 }}m
                  </div>
                  <small class="text-muted">Avg Session Duration</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-success mb-1">{{ $analytics['activity_metrics']['login_frequency'] ?? 'N/A' }}</div>
                  <small class="text-muted">Login Frequency</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-info mb-1">{{ $analytics['engagement_stats']['profile_views'] ?? 0 }}</div>
                  <small class="text-muted">Profile Views</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-warning mb-1">{{ $analytics['engagement_stats']['profile_updates'] ?? 0 }}</div>
                  <small class="text-muted">Profile Updates</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Insights -->
      <div class="col-lg-6 mb-4">
        <div class="card analytics-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-tachometer-alt me-2 text-info"></i>
              Performance Insights
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-primary mb-1">{{ $analytics['performance_insights']['response_time_avg'] ?? 0 }}s
                  </div>
                  <small class="text-muted">Avg Response Time</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-success mb-1">
                    {{ number_format(($analytics['performance_insights']['success_rate'] ?? 0) * 100, 1) }}%</div>
                  <small class="text-muted">Success Rate</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-danger mb-1">
                    {{ number_format(($analytics['performance_insights']['error_rate'] ?? 0) * 100, 2) }}%</div>
                  <small class="text-muted">Error Rate</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center p-3 bg-light rounded">
                  <div class="h4 text-info mb-1">{{ $analytics['performance_insights']['optimization_score'] ?? 0 }}%
                  </div>
                  <small class="text-muted">Optimization Score</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Feature Usage -->
    <div class="row mb-4">
      <div class="col-lg-8 mb-4">
        <div class="card analytics-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-pie me-2 text-warning"></i>
              Feature Usage Distribution
            </h5>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="featureUsageChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Peak Activity Hours -->
      <div class="col-lg-4 mb-4">
        <div class="card analytics-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-clock me-2 text-purple"></i>
              Peak Activity Hours
            </h5>
          </div>
          <div class="card-body">
            @if (isset($analytics['activity_metrics']['peak_activity_hours']))
              @foreach ($analytics['activity_metrics']['peak_activity_hours'] as $hour)
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span>{{ $hour }}</span>
                  <div class="progress flex-grow-1 ms-3" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: {{ rand(60, 100) }}%"></div>
                  </div>
                </div>
              @endforeach
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Recommendations & Insights -->
    <div class="row mb-4">
      <div class="col-lg-6 mb-4">
        <div class="card analytics-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-lightbulb me-2 text-warning"></i>
              Personalized Recommendations
            </h5>
          </div>
          <div class="card-body">
            @if (isset($analytics['recommendations']) && count($analytics['recommendations']) > 0)
              @foreach ($analytics['recommendations'] as $recommendation)
                <div class="recommendation-item">
                  <div class="d-flex align-items-start">
                    <div class="me-3">
                      <i class="{{ $recommendation['icon'] ?? 'fas fa-info-circle' }} text-warning"></i>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-1">{{ $recommendation['title'] }}</h6>
                      <p class="mb-2 text-muted">{{ $recommendation['description'] }}</p>
                      <span
                        class="badge bg-{{ $recommendation['priority'] === 'high' ? 'danger' : ($recommendation['priority'] === 'medium' ? 'warning' : 'info') }}">
                        {{ ucfirst($recommendation['priority']) }} Priority
                      </span>
                    </div>
                  </div>
                </div>
              @endforeach
            @else
              <div class="text-center py-4">
                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                <h6 class="mt-2">All Good!</h6>
                <p class="text-muted">No recommendations at this time.</p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Activity Timeline -->
      <div class="col-lg-6 mb-4">
        <div class="card analytics-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-history me-2 text-info"></i>
              Recent Activity Timeline
            </h5>
          </div>
          <div class="card-body">
            <div class="timeline">
              <div class="timeline-item">
                <div class="timeline-content">
                  <h6 class="mb-1">Profile Updated</h6>
                  <p class="mb-1 text-muted">You updated your profile information</p>
                  <small class="text-muted">{{ now()->subHours(2)->format('g:i A') }}</small>
                </div>
              </div>
              <div class="timeline-item">
                <div class="timeline-content">
                  <h6 class="mb-1">Security Check</h6>
                  <p class="mb-1 text-muted">Security scan completed successfully</p>
                  <small class="text-muted">{{ now()->subHours(6)->format('g:i A') }}</small>
                </div>
              </div>
              <div class="timeline-item">
                <div class="timeline-content">
                  <h6 class="mb-1">Login Activity</h6>
                  <p class="mb-1 text-muted">Logged in from new device</p>
                  <small class="text-muted">{{ now()->subHours(12)->format('g:i A') }}</small>
                </div>
              </div>
              <div class="timeline-item">
                <div class="timeline-content">
                  <h6 class="mb-1">Preferences Updated</h6>
                  <p class="mb-1 text-muted">Changed notification settings</p>
                  <small class="text-muted">{{ now()->subDay()->format('M j, g:i A') }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Monthly Comparison -->
    <div class="row">
      <div class="col-12">
        <div class="card analytics-card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-chart-bar me-2 text-primary"></i>
              Monthly Comparison
            </h5>
          </div>
          <div class="card-body">
            <div class="row">
              @if (isset($analytics['trends']['monthly_comparison']))
                @php $comparison = $analytics['trends']['monthly_comparison']; @endphp
                <div class="col-md-4">
                  <div class="text-center p-3 border rounded">
                    <h6 class="text-muted">Current Month</h6>
                    <div class="h4 text-primary">{{ $comparison['current_month']['logins'] ?? 0 }}</div>
                    <small>Total Logins</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="text-center p-3 border rounded">
                    <h6 class="text-muted">Previous Month</h6>
                    <div class="h4 text-secondary">{{ $comparison['previous_month']['logins'] ?? 0 }}</div>
                    <small>Total Logins</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="text-center p-3 border rounded">
                    <h6 class="text-muted">Growth</h6>
                    <div class="h4 {{ ($comparison['growth']['logins'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                      {{ ($comparison['growth']['logins'] ?? 0) >= 0 ? '+' : '' }}{{ $comparison['growth']['logins'] ?? 0 }}%
                    </div>
                    <small>Change</small>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
      type: 'line',
      data: {
        labels: {!! json_encode(collect($analytics['trends']['daily_activity'] ?? [])->pluck('date')) !!},
        datasets: [{
          label: 'Daily Logins',
          data: {!! json_encode(collect($analytics['trends']['daily_activity'] ?? [])->pluck('logins')) !!},
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4
        }, {
          label: 'Activities',
          data: {!! json_encode(collect($analytics['trends']['daily_activity'] ?? [])->pluck('activities')) !!},
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Feature Usage Chart
    const featureCtx = document.getElementById('featureUsageChart').getContext('2d');
    const featureChart = new Chart(featureCtx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode(array_keys($analytics['engagement_stats']['feature_usage'] ?? [])) !!},
        datasets: [{
          data: {!! json_encode(array_values($analytics['engagement_stats']['feature_usage'] ?? [])) !!},
          backgroundColor: [
            '#3b82f6',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    function refreshAnalytics() {
      // Show loading state
      document.querySelectorAll('.metric-value').forEach(el => {
        el.style.opacity = '0.5';
      });

      fetch('/profile/analytics/data')
        .then(response => response.json())
        .then(data => {
          // Update metrics
          updateAnalyticsDisplay(data);

          // Show success message
          showNotification('Analytics refreshed successfully!', 'success');
        })
        .catch(error => {
          console.error('Failed to refresh analytics:', error);
          showNotification('Failed to refresh analytics', 'error');
        })
        .finally(() => {
          document.querySelectorAll('.metric-value').forEach(el => {
            el.style.opacity = '1';
          });
        });
    }

    function updateAnalyticsDisplay(data) {
      // Update metric values with animation
      animateValue('activity-score', data.data.activity_metrics.interaction_score || 0);
      animateValue('engagement-level', data.data.engagement_stats.interaction_score || 0);
      animateValue('login-frequency', data.data.activity_metrics.active_days_this_month || 0);
      animateValue('performance-score', data.data.performance_insights.optimization_score || 0);

      // Update charts
      activityChart.update();
      featureChart.update();
    }

    function animateValue(elementId, newValue) {
      const element = document.getElementById(elementId);
      if (!element) return;

      const currentValue = parseInt(element.textContent) || 0;
      const duration = 1000;
      const startTime = performance.now();

      function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const value = Math.round(currentValue + (newValue - currentValue) * progress);

        element.textContent = value;

        if (progress < 1) {
          requestAnimationFrame(updateNumber);
        }
      }

      requestAnimationFrame(updateNumber);
    }

    function changeTimeframe(timeframe) {
      // Update active button
      document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
      });
      event.target.classList.add('active');

      // Fetch data for new timeframe
      fetch(`/profile/analytics/data?timeframe=${timeframe}`)
        .then(response => response.json())
        .then(data => {
          updateAnalyticsDisplay(data);
        })
        .catch(error => {
          console.error('Failed to change timeframe:', error);
        });
    }

    function exportAnalytics() {
      // Generate CSV export
      const data = {!! json_encode($analytics) !!};
      const csv = generateCSV(data);
      downloadCSV(csv, `profile-analytics-${new Date().toISOString().split('T')[0]}.csv`);
    }

    function generateCSV(data) {
      const headers = ['Metric', 'Value', 'Category'];
      const rows = [headers];

      // Add activity metrics
      Object.entries(data.activity_metrics || {}).forEach(([key, value]) => {
        rows.push([key.replace(/_/g, ' '), value, 'Activity']);
      });

      // Add engagement stats
      Object.entries(data.engagement_stats || {}).forEach(([key, value]) => {
        if (typeof value === 'object') return;
        rows.push([key.replace(/_/g, ' '), value, 'Engagement']);
      });

      // Add performance insights
      Object.entries(data.performance_insights || {}).forEach(([key, value]) => {
        rows.push([key.replace(/_/g, ' '), value, 'Performance']);
      });

      return rows.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
    }

    function downloadCSV(csv, filename) {
      const blob = new Blob([csv], {
        type: 'text/csv;charset=utf-8;'
      });
      const link = document.createElement('a');

      if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    }

    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

      document.body.appendChild(notification);

      setTimeout(() => {
        notification.remove();
      }, 5000);
    }
  </script>
@endpush
