<x-unified-layout title="Analytics Dashboard" subtitle="Comprehensive insights into your ticket monitoring and purchasing">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Time Range Selector -->
      <div class="flex items-center bg-white border border-gray-300 rounded-lg">
        <select x-model="timeRange" @change="updateTimeRange()" class="border-0 rounded-l-lg px-3 py-2 text-sm focus:ring-0">
          <option value="7d">Last 7 Days</option>
          <option value="30d">Last 30 Days</option>
          <option value="90d">Last 90 Days</option>
          <option value="1y">Last Year</option>
          <option value="custom">Custom Range</option>
        </select>
      </div>
      
      <button @click="refreshData()" :disabled="loading" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
        <svg x-show="!loading" class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <svg x-show="loading" class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Refresh
      </button>
    </div>
  </x-slot>

  <div x-data="analyticsManager()" x-init="init()" class="space-y-8">
    
    <!-- Key Metrics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-100 text-sm font-medium">Total Savings</p>
            <p class="text-3xl font-bold" x-text="formatCurrency(metrics.totalSavings)">$0</p>
            <div class="flex items-center mt-2">
              <span class="text-green-200 text-sm font-medium" x-text="metrics.savingsChange + '% vs last period'"></span>
            </div>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-100 text-sm font-medium">Tickets Purchased</p>
            <p class="text-3xl font-bold" x-text="metrics.ticketsPurchased">0</p>
            <div class="flex items-center mt-2">
              <span class="text-green-200 text-sm font-medium" x-text="metrics.ticketsChange + '% vs last period'"></span>
            </div>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-100 text-sm font-medium">Price Alerts</p>
            <p class="text-3xl font-bold" x-text="metrics.priceAlerts">0</p>
            <div class="flex items-center mt-2">
              <span class="text-purple-200 text-sm font-medium" x-text="metrics.alertsChange + '% vs last period'"></span>
            </div>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.343 12.344l1.414-1.414L6.5 11.5M6.5 18L4 20.5 6.5 23M20.5 20.5L18 18 20.5 15.5"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-orange-100 text-sm font-medium">Avg. Savings</p>
            <p class="text-3xl font-bold" x-text="metrics.avgSavingsPercent + '%'">0%</p>
            <div class="flex items-center mt-2">
              <span class="text-orange-200 text-sm font-medium">per ticket purchase</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Savings Over Time Chart -->
      <x-ui.card>
        <x-ui.card-header title="Savings Over Time">
          <div class="flex items-center space-x-2">
            <button @click="savingsChartType = 'line'" 
                    :class="savingsChartType === 'line' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-3 py-1 rounded text-sm">Line</button>
            <button @click="savingsChartType = 'bar'" 
                    :class="savingsChartType === 'bar' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-3 py-1 rounded text-sm">Bar</button>
          </div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="h-80">
            <canvas id="savingsChart" class="w-full h-full"></canvas>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Sports Distribution Chart -->
      <x-ui.card>
        <x-ui.card-header title="Sports Activity Distribution"></x-ui.card-header>
        <x-ui.card-content>
          <div class="h-80">
            <canvas id="sportsChart" class="w-full h-full"></canvas>
          </div>
          
          <!-- Legend -->
          <div class="grid grid-cols-2 gap-4 mt-4">
            <template x-for="sport in sportsData" :key="sport.name">
              <div class="flex items-center">
                <div class="w-3 h-3 rounded-full mr-2" :style="`background-color: ${sport.color}`"></div>
                <span class="text-sm text-gray-600" x-text="sport.name"></span>
                <span class="text-sm font-medium text-gray-900 ml-auto" x-text="sport.count"></span>
              </div>
            </template>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Detailed Analytics Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Top Performing Events -->
      <x-ui.card>
        <x-ui.card-header title="Top Performing Events">
          <div class="text-sm text-gray-500">Best savings opportunities</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-4">
            <template x-for="event in topEvents" :key="event.id">
              <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex-1">
                  <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full mr-3 flex items-center justify-center"
                         :style="`background: linear-gradient(135deg, ${event.color}40, ${event.color}60)`">
                      <span class="text-lg" x-text="event.emoji"></span>
                    </div>
                    <div>
                      <h4 class="font-medium text-gray-900" x-text="event.title"></h4>
                      <p class="text-sm text-gray-500" x-text="event.venue + ' • ' + formatDate(event.date)"></p>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-bold text-green-600" x-text="formatCurrency(event.savings)"></div>
                  <div class="text-sm text-gray-500" x-text="event.savingsPercent + '% saved'"></div>
                </div>
              </div>
            </template>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Price Alert Performance -->
      <x-ui.card>
        <x-ui.card-header title="Price Alert Performance">
          <div class="text-sm text-gray-500">Alert effectiveness metrics</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-6">
            <!-- Alert Success Rate -->
            <div>
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600">Alert Success Rate</span>
                <span class="font-medium text-gray-900" x-text="alertMetrics.successRate + '%'">85%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                     :style="`width: ${alertMetrics.successRate}%`"></div>
              </div>
            </div>

            <!-- Average Response Time -->
            <div>
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600">Avg. Response Time</span>
                <span class="font-medium text-gray-900" x-text="alertMetrics.avgResponseTime">2.3m</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" 
                     style="width: 75%"></div>
              </div>
            </div>

            <!-- Price Drop Frequency -->
            <div>
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600">Price Drop Frequency</span>
                <span class="font-medium text-gray-900" x-text="alertMetrics.dropFrequency">3.2/week</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-500 h-2 rounded-full transition-all duration-500" 
                     style="width: 60%"></div>
              </div>
            </div>

            <!-- Alert Breakdown -->
            <div class="pt-4 border-t border-gray-200">
              <h4 class="font-medium text-gray-900 mb-3">Alert Types</h4>
              <div class="space-y-3">
                <template x-for="type in alertTypes" :key="type.name">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-3 h-3 rounded-full mr-3" :style="`background-color: ${type.color}`"></div>
                      <span class="text-sm text-gray-600" x-text="type.name"></span>
                    </div>
                    <div class="text-sm font-medium text-gray-900" x-text="type.count"></div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Insights and Recommendations -->
    <x-ui.card>
      <x-ui.card-header title="Insights & Recommendations">
        <div class="text-sm text-gray-500">AI-powered analysis of your ticket activity</div>
      </x-ui.card-header>
      <x-ui.card-content>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <template x-for="insight in insights" :key="insight.id">
            <div class="p-4 rounded-lg border-l-4" :class="`border-${insight.type === 'success' ? 'green' : insight.type === 'warning' ? 'yellow' : 'blue'}-500 bg-${insight.type === 'success' ? 'green' : insight.type === 'warning' ? 'yellow' : 'blue'}-50`">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="w-5 h-5 mt-0.5" :class="`text-${insight.type === 'success' ? 'green' : insight.type === 'warning' ? 'yellow' : 'blue'}-500`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="insight.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    <path x-show="insight.type === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    <path x-show="insight.type === 'info'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h4 class="font-medium" :class="`text-${insight.type === 'success' ? 'green' : insight.type === 'warning' ? 'yellow' : 'blue'}-900`" x-text="insight.title"></h4>
                  <p class="text-sm mt-1" :class="`text-${insight.type === 'success' ? 'green' : insight.type === 'warning' ? 'yellow' : 'blue'}-700`" x-text="insight.message"></p>
                  <button x-show="insight.action" 
                          @click="handleInsightAction(insight)"
                          class="text-xs font-medium mt-2 underline hover:no-underline"
                          :class="`text-${insight.type === 'success' ? 'green' : insight.type === 'warning' ? 'yellow' : 'blue'}-600`"
                          x-text="insight.action"></button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Activity Timeline -->
    <x-ui.card>
      <x-ui.card-header title="Recent Activity Timeline">
        <button @click="loadMoreActivity()" 
                :disabled="loadingActivity"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium">
          <span x-show="!loadingActivity">Load More</span>
          <span x-show="loadingActivity">Loading...</span>
        </button>
      </x-ui.card-header>
      <x-ui.card-content>
        <div class="space-y-4">
          <template x-for="activity in recentActivity" :key="activity.id">
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                     :class="{
                       'bg-green-100 text-green-600': activity.type === 'purchase',
                       'bg-blue-100 text-blue-600': activity.type === 'alert',
                       'bg-purple-100 text-purple-600': activity.type === 'watchlist',
                       'bg-orange-100 text-orange-600': activity.type === 'price_change'
                     }">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="activity.type === 'purchase'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    <path x-show="activity.type === 'alert'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.343 12.344l1.414-1.414L6.5 11.5"></path>
                    <path x-show="activity.type === 'watchlist'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    <path x-show="activity.type === 'price_change'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900" x-text="activity.title"></p>
                <p class="text-sm text-gray-500" x-text="activity.description"></p>
                <p class="text-xs text-gray-400 mt-1" x-text="formatTimeAgo(activity.timestamp)"></p>
              </div>
              <div x-show="activity.value" class="flex-shrink-0 text-right">
                <p class="text-sm font-medium" 
                   :class="activity.value_type === 'savings' ? 'text-green-600' : 'text-gray-900'"
                   x-text="formatValue(activity.value, activity.value_type)"></p>
              </div>
            </div>
          </template>
        </div>
      </x-ui.card-content>
    </x-ui.card>
  </div>

  @push('scripts')
@vite('resources/js/vendor/chart.js')
    <script>
      function analyticsManager() {
        return {
          loading: false,
          loadingActivity: false,
          timeRange: '30d',
          savingsChartType: 'line',
          
          // Charts
          savingsChart: null,
          sportsChart: null,
          
          // Data
          metrics: {
            totalSavings: 0,
            savingsChange: 0,
            ticketsPurchased: 0,
            ticketsChange: 0,
            priceAlerts: 0,
            alertsChange: 0,
            avgSavingsPercent: 0
          },
          
          sportsData: [],
          topEvents: [],
          recentActivity: [],
          insights: [],
          
          alertMetrics: {
            successRate: 85,
            avgResponseTime: '2.3m',
            dropFrequency: '3.2/week'
          },
          
          alertTypes: [
            { name: 'Price Drops', count: 45, color: '#10b981' },
            { name: 'Availability', count: 32, color: '#3b82f6' },
            { name: 'New Listings', count: 28, color: '#8b5cf6' },
            { name: 'Event Updates', count: 15, color: '#f59e0b' }
          ],

          async init() {
            await this.loadAnalyticsData();
            this.initializeCharts();
            this.setupRealTimeUpdates();
          },

          async loadAnalyticsData() {
            this.loading = true;
            try {
              const response = await fetch(`/api/analytics/dashboard?range=${this.timeRange}`, {
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });
              
              const data = await response.json();
              
              this.metrics = data.metrics || this.metrics;
              this.sportsData = data.sportsData || [];
              this.topEvents = data.topEvents || [];
              this.recentActivity = data.recentActivity || [];
              this.insights = data.insights || [];
              
              this.updateCharts(data.chartData || {});
              
            } catch (error) {
              console.error('Failed to load analytics data:', error);
              this.showNotification('Error', 'Failed to load analytics data', 'error');
            } finally {
              this.loading = false;
            }
          },

          initializeCharts() {
            this.initSavingsChart();
            this.initSportsChart();
          },

          initSavingsChart() {
            const ctx = document.getElementById('savingsChart').getContext('2d');
            this.savingsChart = new Chart(ctx, {
              type: this.savingsChartType,
              data: {
                labels: [],
                datasets: [{
                  label: 'Savings ($)',
                  data: [],
                  borderColor: '#3b82f6',
                  backgroundColor: 'rgba(59, 130, 246, 0.1)',
                  borderWidth: 2,
                  fill: true,
                  tension: 0.4
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
                    ticks: {
                      callback: function(value) {
                        return '$' + value.toLocaleString();
                      }
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

          initSportsChart() {
            const ctx = document.getElementById('sportsChart').getContext('2d');
            this.sportsChart = new Chart(ctx, {
              type: 'doughnut',
              data: {
                labels: [],
                datasets: [{
                  data: [],
                  backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                    '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'
                  ],
                  borderWidth: 2,
                  borderColor: '#ffffff'
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false
                  }
                }
              }
            });
          },

          updateCharts(chartData) {
            if (chartData.savings && this.savingsChart) {
              this.savingsChart.data.labels = chartData.savings.labels || [];
              this.savingsChart.data.datasets[0].data = chartData.savings.data || [];
              this.savingsChart.update();
            }

            if (chartData.sports && this.sportsChart) {
              this.sportsChart.data.labels = chartData.sports.labels || [];
              this.sportsChart.data.datasets[0].data = chartData.sports.data || [];
              this.sportsChart.update();
            }
          },

          async updateTimeRange() {
            await this.loadAnalyticsData();
          },

          async refreshData() {
            await this.loadAnalyticsData();
            this.showNotification('Success', 'Analytics data refreshed', 'success');
          },

          async loadMoreActivity() {
            this.loadingActivity = true;
            try {
              const response = await fetch(`/api/analytics/activity?offset=${this.recentActivity.length}`, {
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });
              
              const data = await response.json();
              this.recentActivity.push(...(data.activities || []));
              
            } catch (error) {
              this.showNotification('Error', 'Failed to load more activity', 'error');
            } finally {
              this.loadingActivity = false;
            }
          },

          handleInsightAction(insight) {
            switch (insight.actionType) {
              case 'set_alert':
                window.location.href = '/alerts/create';
                break;
              case 'view_deals':
                window.location.href = '/tickets/browse?filter=deals';
                break;
              case 'upgrade_subscription':
                window.location.href = '/subscription/upgrade';
                break;
              default:
                console.log('Insight action:', insight);
            }
          },

          setupRealTimeUpdates() {
            if (window.Echo) {
              window.Echo.channel('user-analytics.' + window.userId)
                .listen('AnalyticsUpdated', (e) => {
                  this.updateMetrics(e.metrics);
                });
            }
          },

          updateMetrics(newMetrics) {
            Object.keys(newMetrics).forEach(key => {
              if (this.metrics.hasOwnProperty(key)) {
                this.metrics[key] = newMetrics[key];
              }
            });
          },

          formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
              style: 'currency',
              currency: 'USD',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
            }).format(value);
          },

          formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
              month: 'short', 
              day: 'numeric',
              year: 'numeric'
            });
          },

          formatTimeAgo(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffInHours < 1) return 'Just now';
            if (diffInHours < 24) return `${diffInHours}h ago`;
            if (diffInHours < 168) return `${Math.floor(diffInHours / 24)}d ago`;
            return this.formatDate(timestamp);
          },

          formatValue(value, type) {
            switch (type) {
              case 'savings':
                return this.formatCurrency(value);
              case 'percentage':
                return value + '%';
              case 'count':
                return value.toLocaleString();
              default:
                return value;
            }
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }
    </script>
  @endpush
</x-unified-layout>
