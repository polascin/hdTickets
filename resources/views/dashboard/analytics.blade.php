@extends('layouts.modern')

@section('title', 'Dashboard Analytics')
@section('description', 'Advanced analytics and insights for your sports ticket monitoring and purchasing activities')

@push('styles')
  <style>
    /* Analytics Dashboard Styles */
    .analytics-hero {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%);
      position: relative;
      overflow: hidden;
    }

    .analytics-hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
      opacity: 0.2;
    }

    .analytics-card {
      @apply bg-white rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300 overflow-hidden;
    }

    .chart-container {
      position: relative;
      height: 400px;
      width: 100%;
    }

    .mini-chart-container {
      position: relative;
      height: 200px;
      width: 100%;
    }

    .metric-card {
      @apply p-6 bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:shadow-md transition-shadow;
    }

    .metric-value {
      @apply text-3xl font-bold mb-1;
    }

    .metric-trend {
      @apply flex items-center text-sm font-medium;
    }

    .trend-up {
      @apply text-green-600;
    }

    .trend-down {
      @apply text-red-600;
    }

    .trend-stable {
      @apply text-gray-600;
    }

    .activity-item {
      @apply flex items-center p-4 bg-white rounded-lg border border-gray-100 hover:shadow-sm transition-shadow;
    }

    .activity-icon {
      @apply w-10 h-10 rounded-full flex items-center justify-center mr-4;
    }

    .filter-tabs {
      @apply flex space-x-1 bg-gray-100 p-1 rounded-lg;
    }

    .filter-tab {
      @apply px-4 py-2 text-sm font-medium rounded-md transition-colors cursor-pointer;
    }

    .filter-tab-active {
      @apply bg-white text-blue-600 shadow-sm;
    }

    .filter-tab-inactive {
      @apply text-gray-600 hover:text-gray-900;
    }

    .progress-ring {
      transform: rotate(-90deg);
    }

    .progress-ring-circle {
      transition: stroke-dashoffset 0.35s;
      transform: rotate(90deg);
      transform-origin: 50% 50%;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .insights-panel {
      @apply bg-gradient-to-br from-blue-50 to-indigo-100 p-6 rounded-2xl border border-blue-200;
    }

    .recommendation-card {
      @apply bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow;
    }

    @media (max-width: 768px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }

      .chart-container {
        height: 300px;
      }
    }
  </style>
@endpush

@section('content')
  <div class="py-6" x-data="analyticsData()" x-init="initializeAnalytics()">
    <!-- Hero Section -->
    <div class="analytics-hero text-white py-8 px-6 rounded-2xl mb-8 relative z-10">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h1 class="text-4xl font-bold mb-2">Dashboard Analytics</h1>
          <p class="text-white/90 text-lg mb-4">Advanced insights into your sports ticket monitoring and purchasing
            activities</p>
          <div class="flex items-center space-x-6 text-sm">
            <div class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
              </svg>
              <span x-text="analytics.totalEvents">{{ $totalEvents ?? 45 }}</span> Events Tracked
            </div>
            <div class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                </path>
              </svg>
              $<span x-text="analytics.totalSavings">{{ $totalSavings ?? 1247 }}</span> Total Savings
            </div>
            <div class="flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4">
                </path>
              </svg>
              <span x-text="analytics.successRate">{{ $successRate ?? 94 }}%</span> Success Rate
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-3 mt-6 lg:mt-0">
          <div class="filter-tabs">
            <button @click="setTimeRange('7d')"
              :class="timeRange === '7d' ? 'filter-tab filter-tab-active' : 'filter-tab filter-tab-inactive'">
              7 Days
            </button>
            <button @click="setTimeRange('30d')"
              :class="timeRange === '30d' ? 'filter-tab filter-tab-active' : 'filter-tab filter-tab-inactive'">
              30 Days
            </button>
            <button @click="setTimeRange('90d')"
              :class="timeRange === '90d' ? 'filter-tab filter-tab-active' : 'filter-tab filter-tab-inactive'">
              90 Days
            </button>
            <button @click="setTimeRange('1y')"
              :class="timeRange === '1y' ? 'filter-tab filter-tab-active' : 'filter-tab filter-tab-inactive'">
              1 Year
            </button>
          </div>

          <button @click="exportAnalytics()"
            class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-4 py-2 text-white rounded-xl font-medium transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
              </path>
            </svg>
            Export
          </button>
        </div>
      </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="stats-grid mb-8">
      <div class="metric-card">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-600">Active Alerts</h3>
            <div class="metric-value text-blue-600" x-text="analytics.activeAlerts">12</div>
            <div class="metric-trend trend-up">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4">
                </path>
              </svg>
              <span>+8.3% from last month</span>
            </div>
          </div>
          <div class="w-16 h-16">
            <svg class="w-full h-full" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8" />
              <circle cx="50" cy="50" r="40" fill="none" stroke="#3b82f6" stroke-width="8"
                stroke-dasharray="251.2" stroke-dashoffset="75.36" class="progress-ring-circle" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-600">Money Saved</h3>
            <div class="metric-value text-green-600">$<span x-text="analytics.totalSavings">1,247</span></div>
            <div class="metric-trend trend-up">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
              </svg>
              <span>+23.1% vs target prices</span>
            </div>
          </div>
          <div class="w-16 h-16 text-green-600">
            <svg class="w-full h-full" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-600">Tickets Purchased</h3>
            <div class="metric-value text-purple-600" x-text="analytics.ticketsPurchased">27</div>
            <div class="metric-trend trend-up">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
              </svg>
              <span>+41% this month</span>
            </div>
          </div>
          <div class="w-16 h-16 text-purple-600">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-600">Avg Response Time</h3>
            <div class="metric-value text-orange-600">2.4s</div>
            <div class="metric-trend trend-down">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 17h8m0 0V9m0 8l-8-8-4 4-4-4"></path>
              </svg>
              <span>-12% improvement</span>
            </div>
          </div>
          <div class="w-16 h-16 text-orange-600">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Price Trends Chart -->
      <div class="analytics-card">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Price Trends</h3>
          <p class="text-sm text-gray-600">Average ticket prices over time across monitored events</p>
        </div>
        <div class="p-6">
          <div class="chart-container" id="priceTrendsChart">
            <!-- Chart will be rendered here by Chart.js -->
            <div class="flex items-center justify-center h-full text-gray-500">
              <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p>Loading price trends...</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Savings Analysis Chart -->
      <div class="analytics-card">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Savings Analysis</h3>
          <p class="text-sm text-gray-600">Money saved through smart monitoring and alerts</p>
        </div>
        <div class="p-6">
          <div class="chart-container" id="savingsChart">
            <div class="flex items-center justify-center h-full text-gray-500">
              <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
                <p>Loading savings analysis...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity & Insights Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Recent Activity -->
      <div class="lg:col-span-2 analytics-card">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Recent Activity</h3>
              <p class="text-sm text-gray-600">Your latest monitoring and purchasing activities</p>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">View All</button>
          </div>
        </div>
        <div class="p-6">
          <div class="space-y-4" id="recentActivity">
            <!-- Activity items will be populated by JavaScript -->
            <template x-for="activity in analytics.recentActivities" :key="activity.id">
              <div class="activity-item">
                <div class="activity-icon" :class="activity.iconClass">
                  <i :class="activity.icon"></i>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900" x-text="activity.title"></p>
                  <p class="text-xs text-gray-500" x-text="activity.description"></p>
                  <p class="text-xs text-gray-400 mt-1" x-text="activity.time"></p>
                </div>
                <div class="text-right">
                  <span class="text-sm font-semibold" :class="activity.amountClass" x-text="activity.amount"></span>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Smart Insights Panel -->
      <div class="insights-panel">
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Smart Insights</h3>
          <p class="text-sm text-gray-600">AI-powered recommendations based on your activity</p>
        </div>

        <div class="space-y-4">
          <div class="recommendation-card">
            <div class="flex items-start">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
                </svg>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">Optimal Alert Timing</h4>
                <p class="text-xs text-gray-600 mt-1">Set alerts 3-5 days before events for 23% better savings</p>
              </div>
            </div>
          </div>

          <div class="recommendation-card">
            <div class="flex items-start">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                  </path>
                </svg>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">Popular Categories</h4>
                <p class="text-xs text-gray-600 mt-1">NBA and NFL games show highest savings potential this month</p>
              </div>
            </div>
          </div>

          <div class="recommendation-card">
            <div class="flex items-start">
              <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">Best Purchase Times</h4>
                <p class="text-xs text-gray-600 mt-1">Tuesday-Thursday show 15% lower average prices</p>
              </div>
            </div>
          </div>

          <div class="recommendation-card">
            <div class="flex items-start">
              <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3 mt-1">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                </svg>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-900">Alert Optimization</h4>
                <p class="text-xs text-gray-600 mt-1">Consider increasing your Lakers game alert threshold by $25</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 pt-6 border-t border-blue-200">
          <button
            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
            Get More Insights
          </button>
        </div>
      </div>
    </div>

    <!-- Performance Breakdown -->
    <div class="analytics-card">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Performance Breakdown</h3>
        <p class="text-sm text-gray-600">Detailed analysis of your monitoring effectiveness</p>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="text-center">
            <div class="mini-chart-container mb-4" id="alertSuccessChart">
              <div class="flex items-center justify-center h-full">
                <div class="text-center">
                  <div class="text-3xl font-bold text-green-600">94%</div>
                  <div class="text-sm text-gray-600">Alert Success</div>
                </div>
              </div>
            </div>
            <h4 class="text-sm font-medium text-gray-900">Alert Effectiveness</h4>
            <p class="text-xs text-gray-600 mt-1">Alerts that resulted in savings</p>
          </div>

          <div class="text-center">
            <div class="mini-chart-container mb-4" id="responseTimeChart">
              <div class="flex items-center justify-center h-full">
                <div class="text-center">
                  <div class="text-3xl font-bold text-blue-600">2.4s</div>
                  <div class="text-sm text-gray-600">Avg Response</div>
                </div>
              </div>
            </div>
            <h4 class="text-sm font-medium text-gray-900">System Performance</h4>
            <p class="text-xs text-gray-600 mt-1">Average notification delivery time</p>
          </div>

          <div class="text-center">
            <div class="mini-chart-container mb-4" id="platformDistributionChart">
              <div class="flex items-center justify-center h-full">
                <div class="text-center">
                  <div class="text-3xl font-bold text-purple-600">6</div>
                  <div class="text-sm text-gray-600">Platforms</div>
                </div>
              </div>
            </div>
            <h4 class="text-sm font-medium text-gray-900">Platform Coverage</h4>
            <p class="text-xs text-gray-600 mt-1">Active monitoring sources</p>
          </div>

          <div class="text-center">
            <div class="mini-chart-container mb-4" id="savingsRateChart">
              <div class="flex items-center justify-center h-full">
                <div class="text-center">
                  <div class="text-3xl font-bold text-orange-600">23%</div>
                  <div class="text-sm text-gray-600">Avg Savings</div>
                </div>
              </div>
            </div>
            <h4 class="text-sm font-medium text-gray-900">Savings Rate</h4>
            <p class="text-xs text-gray-600 mt-1">Average discount from list price</p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <!-- Chart.js CDN -->
@vite('resources/js/vendor/chart.js')
  <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>

  <script>
    function analyticsData() {
      return {
        timeRange: '30d',
        analytics: {
          totalEvents: 45,
          totalSavings: 1247,
          successRate: 94,
          activeAlerts: 12,
          ticketsPurchased: 27,
          recentActivities: []
        },
        charts: {},

        initializeAnalytics() {
          this.loadAnalyticsData();
          this.initializeCharts();
          this.loadRecentActivity();

          // Refresh data every 5 minutes
          setInterval(() => {
            this.refreshAnalytics();
          }, 300000);
        },

        async loadAnalyticsData() {
          try {
            // In production, this would fetch from an API endpoint
            // For demo, simulate data loading
            console.log('Loading analytics data for range:', this.timeRange);

            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Update analytics with time-range specific data
            this.analytics = {
              ...this.analytics,
              totalEvents: this.timeRange === '7d' ? 12 : (this.timeRange === '30d' ? 45 : 120),
              totalSavings: this.timeRange === '7d' ? 347 : (this.timeRange === '30d' ? 1247 : 3890),
              successRate: Math.floor(Math.random() * 10) + 90, // 90-99%
              activeAlerts: Math.floor(Math.random() * 20) + 5, // 5-25
              ticketsPurchased: this.timeRange === '7d' ? 8 : (this.timeRange === '30d' ? 27 : 89)
            };
          } catch (error) {
            console.error('Failed to load analytics data:', error);
            this.showNotification('Failed to load analytics data', 'error');
          }
        },

        loadRecentActivity() {
          // Sample activity data
          this.analytics.recentActivities = [{
              id: 1,
              title: 'Alert Triggered - Lakers vs Celtics',
              description: 'Price dropped to $285 (target: $300)',
              time: '2 minutes ago',
              amount: '-$35',
              amountClass: 'text-green-600',
              icon: 'fas fa-bell',
              iconClass: 'bg-green-100 text-green-600'
            },
            {
              id: 2,
              title: 'Ticket Purchase Completed',
              description: 'Warriors vs Nuggets - Section 118',
              time: '1 hour ago',
              amount: '$425',
              amountClass: 'text-blue-600',
              icon: 'fas fa-ticket-alt',
              iconClass: 'bg-blue-100 text-blue-600'
            },
            {
              id: 3,
              title: 'New Alert Created',
              description: 'Super Bowl LVIII monitoring started',
              time: '3 hours ago',
              amount: '',
              amountClass: '',
              icon: 'fas fa-plus',
              iconClass: 'bg-purple-100 text-purple-600'
            },
            {
              id: 4,
              title: 'Price Target Reached',
              description: 'NHL Finals Game 4 - TD Garden',
              time: '5 hours ago',
              amount: '-$60',
              amountClass: 'text-green-600',
              icon: 'fas fa-chart-line',
              iconClass: 'bg-green-100 text-green-600'
            },
            {
              id: 5,
              title: 'Alert Optimization Suggestion',
              description: 'Consider adjusting NBA alerts threshold',
              time: '1 day ago',
              amount: '',
              amountClass: '',
              icon: 'fas fa-lightbulb',
              iconClass: 'bg-yellow-100 text-yellow-600'
            }
          ];
        },

        setTimeRange(range) {
          this.timeRange = range;
          this.loadAnalyticsData().then(() => {
            this.updateCharts();
          });
        },

        initializeCharts() {
          this.initializePriceTrendsChart();
          this.initializeSavingsChart();
        },

        initializePriceTrendsChart() {
          const ctx = document.getElementById('priceTrendsChart');
          if (!ctx) return;

          const canvas = document.createElement('canvas');
          ctx.innerHTML = '';
          ctx.appendChild(canvas);

          this.charts.priceTrends = new Chart(canvas, {
            type: 'line',
            data: {
              labels: this.generateDateLabels(),
              datasets: [{
                label: 'Average Price',
                data: this.generatePriceData(),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: false
                }
              },
              scales: {
                y: {
                  beginAtZero: false,
                  grid: {
                    color: '#f3f4f6'
                  },
                  ticks: {
                    callback: function(value) {
                      return '$' + value;
                    }
                  }
                },
                x: {
                  grid: {
                    display: false
                  }
                }
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });
        },

        initializeSavingsChart() {
          const ctx = document.getElementById('savingsChart');
          if (!ctx) return;

          const canvas = document.createElement('canvas');
          ctx.innerHTML = '';
          ctx.appendChild(canvas);

          this.charts.savings = new Chart(canvas, {
            type: 'bar',
            data: {
              labels: this.generateDateLabels(),
              datasets: [{
                label: 'Savings',
                data: this.generateSavingsData(),
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: '#22c55e',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: false
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  grid: {
                    color: '#f3f4f6'
                  },
                  ticks: {
                    callback: function(value) {
                      return '$' + value;
                    }
                  }
                },
                x: {
                  grid: {
                    display: false
                  }
                }
              }
            }
          });
        },

        updateCharts() {
          if (this.charts.priceTrends) {
            this.charts.priceTrends.data.labels = this.generateDateLabels();
            this.charts.priceTrends.data.datasets[0].data = this.generatePriceData();
            this.charts.priceTrends.update();
          }

          if (this.charts.savings) {
            this.charts.savings.data.labels = this.generateDateLabels();
            this.charts.savings.data.datasets[0].data = this.generateSavingsData();
            this.charts.savings.update();
          }
        },

        generateDateLabels() {
          const labels = [];
          const days = this.timeRange === '7d' ? 7 : (this.timeRange === '30d' ? 30 : 90);

          for (let i = days - 1; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            labels.push(date.toLocaleDateString('en-US', {
              month: 'short',
              day: 'numeric'
            }));
          }
          return labels;
        },

        generatePriceData() {
          const days = this.timeRange === '7d' ? 7 : (this.timeRange === '30d' ? 30 : 90);
          const data = [];
          let basePrice = 250;

          for (let i = 0; i < days; i++) {
            basePrice += (Math.random() - 0.5) * 50;
            basePrice = Math.max(150, Math.min(500, basePrice));
            data.push(Math.round(basePrice));
          }
          return data;
        },

        generateSavingsData() {
          const days = this.timeRange === '7d' ? 7 : (this.timeRange === '30d' ? 30 : 90);
          const data = [];

          for (let i = 0; i < days; i++) {
            data.push(Math.floor(Math.random() * 100) + 10);
          }
          return data;
        },

        async refreshAnalytics() {
          console.log('Refreshing analytics data...');
          await this.loadAnalyticsData();
          this.updateCharts();
        },

        exportAnalytics() {
          console.log('Exporting analytics data...');
          this.showNotification('Analytics export started. You\'ll receive an email when ready.', 'success');
        },

        showNotification(message, type = 'info') {
          window.dispatchEvent(new CustomEvent('notify', {
            detail: {
              title: type.charAt(0).toUpperCase() + type.slice(1),
              message: message,
              type: type
            }
          }));
        }
      }
    }
  </script>
@endpush
