<x-unified-layout title="Analytics Dashboard" subtitle="Track your ticket activity, savings, and spending insights">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Date Range Selector -->
      <div class="flex items-center space-x-2">
        <button @click="setDateRange('7d')" 
                :class="dateRange === '7d' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          7 Days
        </button>
        <button @click="setDateRange('30d')" 
                :class="dateRange === '30d' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          30 Days
        </button>
        <button @click="setDateRange('90d')" 
                :class="dateRange === '90d' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          90 Days
        </button>
        <button @click="setDateRange('1y')" 
                :class="dateRange === '1y' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          1 Year
        </button>
      </div>

      <!-- Export Data -->
      <button @click="exportData()" 
              class="flex items-center bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Export Data
      </button>
    </div>
  </x-slot>

  <div x-data="analyticsManager()" x-init="init()" class="space-y-8">
    
    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Purchases</p>
              <p class="text-2xl font-bold text-gray-900" x-text="metrics.totalPurchases">--</p>
              <div class="flex items-center mt-1" x-show="metrics.purchaseGrowth !== null">
                <svg :class="metrics.purchaseGrowth >= 0 ? 'text-green-500' : 'text-red-500'" 
                     x-show="metrics.purchaseGrowth >= 0" 
                     class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <svg :class="metrics.purchaseGrowth >= 0 ? 'text-green-500' : 'text-red-500'" 
                     x-show="metrics.purchaseGrowth < 0" 
                     class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
                <span :class="metrics.purchaseGrowth >= 0 ? 'text-green-600' : 'text-red-600'" 
                      class="text-xs font-medium" 
                      x-text="Math.abs(metrics.purchaseGrowth) + '% vs prev period'"></span>
              </div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Spent</p>
              <p class="text-2xl font-bold text-gray-900" x-text="formatCurrency(metrics.totalSpent)">--</p>
              <div class="flex items-center mt-1" x-show="metrics.spendingGrowth !== null">
                <svg :class="metrics.spendingGrowth >= 0 ? 'text-red-500' : 'text-green-500'" 
                     x-show="metrics.spendingGrowth >= 0" 
                     class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <svg :class="metrics.spendingGrowth >= 0 ? 'text-red-500' : 'text-green-500'" 
                     x-show="metrics.spendingGrowth < 0" 
                     class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
                <span :class="metrics.spendingGrowth >= 0 ? 'text-red-600' : 'text-green-600'" 
                      class="text-xs font-medium" 
                      x-text="Math.abs(metrics.spendingGrowth) + '% vs prev period'"></span>
              </div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Savings</p>
              <p class="text-2xl font-bold text-purple-600" x-text="formatCurrency(metrics.totalSavings)">--</p>
              <div class="flex items-center mt-1">
                <svg class="w-4 h-4 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-xs text-purple-600 font-medium" x-text="metrics.averageSavingsPercent + '% avg discount'"></span>
              </div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Watchlist Items</p>
              <p class="text-2xl font-bold text-gray-900" x-text="metrics.watchlistItems">--</p>
              <div class="flex items-center mt-1">
                <span class="text-xs text-yellow-600 font-medium" x-text="metrics.activeAlerts + ' active alerts'"></span>
              </div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Spending Over Time Chart -->
      <x-ui.card>
        <x-ui.card-header title="Spending Over Time">
          <div class="flex items-center space-x-2">
            <button @click="chartType = 'line'" 
                    :class="chartType === 'line' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-2 py-1 rounded text-xs">Line</button>
            <button @click="chartType = 'bar'" 
                    :class="chartType === 'bar' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-2 py-1 rounded text-xs">Bar</button>
          </div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="h-64">
            <canvas id="spendingChart" class="w-full h-full"></canvas>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Sports Distribution Chart -->
      <x-ui.card>
        <x-ui.card-header title="Sports Distribution">
          <div class="text-sm text-gray-500">Your ticket purchases by sport</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="h-64">
            <canvas id="sportsChart" class="w-full h-full"></canvas>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Recent Activity and Top Savings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Recent Purchase Activity -->
      <x-ui.card>
        <x-ui.card-header title="Recent Activity">
          <a href="{{ route('user.purchase-history') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            View All
          </a>
        </x-ui.card-header>
        <x-ui.card-content class="p-0">
          <div class="divide-y divide-gray-200">
            <template x-for="activity in recentActivity" :key="activity.id">
              <div class="p-4 hover:bg-gray-50 transition">
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0">
                    <div :class="getActivityIcon(activity.type).bgColor" class="w-8 h-8 rounded-full flex items-center justify-center">
                      <svg class="w-4 h-4" :class="getActivityIcon(activity.type).textColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.type).path"></path>
                      </svg>
                    </div>
                  </div>
                  
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-medium text-gray-900 truncate" x-text="activity.title"></p>
                      <p class="text-sm text-gray-500" x-text="formatTimeAgo(activity.created_at)"></p>
                    </div>
                    <p class="text-sm text-gray-600" x-text="activity.description"></p>
                    <div class="mt-1 flex items-center space-x-2" x-show="activity.amount">
                      <span class="text-sm font-medium text-gray-900" x-text="formatCurrency(activity.amount)"></span>
                      <span x-show="activity.savings > 0" class="text-xs text-green-600 font-medium" x-text="'Saved ' + formatCurrency(activity.savings)"></span>
                    </div>
                  </div>
                </div>
              </div>
            </template>
            
            <div x-show="recentActivity.length === 0" class="p-8 text-center text-gray-500">
              <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <p>No recent activity</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Top Savings Opportunities -->
      <x-ui.card>
        <x-ui.card-header title="Top Savings">
          <div class="text-sm text-gray-500">Your biggest deals</div>
        </x-ui.card-header>
        <x-ui.card-content class="p-0">
          <div class="divide-y divide-gray-200">
            <template x-for="saving in topSavings" :key="saving.id">
              <div class="p-4 hover:bg-gray-50 transition">
                <div class="flex items-center space-x-4">
                  <img :src="saving.image || '/images/default-event.jpg'" 
                       :alt="saving.event_title"
                       class="w-12 h-12 rounded-lg object-cover bg-gray-200">
                  
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-medium text-gray-900 truncate" x-text="saving.event_title"></p>
                      <div class="flex items-center">
                        <span class="text-lg font-bold text-green-600" x-text="formatCurrency(saving.savings)"></span>
                      </div>
                    </div>
                    <p class="text-sm text-gray-500" x-text="saving.venue"></p>
                    <div class="mt-1 flex items-center space-x-4">
                      <span class="text-xs text-gray-500" x-text="formatDate(saving.event_date)"></span>
                      <span class="text-xs text-green-600 font-medium" x-text="saving.savings_percent + '% off'"></span>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <div x-show="topSavings.length === 0" class="p-8 text-center text-gray-500">
              <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              <p>No savings tracked yet</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Detailed Analytics Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Purchase Breakdown by Month -->
      <x-ui.card>
        <x-ui.card-header title="Monthly Breakdown">
          <select x-model="selectedYear" @change="loadMonthlyBreakdown()" class="text-sm border border-gray-300 rounded px-2 py-1">
            <template x-for="year in availableYears" :key="year">
              <option :value="year" x-text="year"></option>
            </template>
          </select>
        </x-ui.card-header>
        <x-ui.card-content class="p-0">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchases</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spent</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Savings</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="month in monthlyBreakdown" :key="month.month">
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="month.month_name"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="month.purchases"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatCurrency(month.spent)"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium" x-text="formatCurrency(month.savings)"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Price Alert Performance -->
      <x-ui.card>
        <x-ui.card-header title="Price Alert Performance">
          <div class="text-sm text-gray-500">Your watchlist effectiveness</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-4">
            <!-- Performance Metrics -->
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <div class="text-2xl font-bold text-blue-600" x-text="alertStats.totalAlerts">--</div>
                <div class="text-xs text-gray-500">Total Alerts</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-green-600" x-text="alertStats.triggered">--</div>
                <div class="text-xs text-gray-500">Triggered</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-purple-600" x-text="alertStats.successRate + '%'">--</div>
                <div class="text-xs text-gray-500">Success Rate</div>
              </div>
            </div>

            <!-- Recent Triggered Alerts -->
            <div class="space-y-3">
              <h4 class="text-sm font-medium text-gray-900">Recent Triggers</h4>
              <template x-for="trigger in recentTriggers.slice(0, 3)" :key="trigger.id">
                <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                  <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate" x-text="trigger.event_title"></p>
                    <div class="flex items-center space-x-2 mt-1">
                      <span class="text-xs text-gray-500" x-text="formatCurrency(trigger.target_price) + ' target'"></span>
                      <span class="text-xs text-green-600 font-medium" x-text="formatCurrency(trigger.actual_price) + ' actual'"></span>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-gray-500" x-text="formatTimeAgo(trigger.triggered_at)"></p>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Export Modal -->
    <div x-show="showExportModal" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showExportModal = false">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Export Analytics Data</h3>
          <p class="text-sm text-gray-600">Download your ticket activity and spending data</p>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
              <select x-model="exportFormat" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="csv">CSV (Excel Compatible)</option>
                <option value="pdf">PDF Report</option>
                <option value="json">JSON Data</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
              <select x-model="exportDateRange" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="30d">Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
                <option value="1y">Last Year</option>
                <option value="all">All Time</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">Include Data</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" x-model="exportIncludes.purchases" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">Purchase History</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" x-model="exportIncludes.watchlist" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">Watchlist Activity</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" x-model="exportIncludes.alerts" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">Price Alert Data</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" x-model="exportIncludes.savings" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">Savings Analysis</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
          <button @click="showExportModal = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
            Cancel
          </button>
          <button @click="processExport()" 
                  :disabled="exportProcessing"
                  class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            <span x-show="!exportProcessing">Download Export</span>
            <span x-show="exportProcessing" class="flex items-center">
              <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Generating...
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
@vite('resources/js/vendor/chart.js')
    <script>
      function analyticsManager() {
        return {
          // State
          loading: true,
          dateRange: '30d',
          chartType: 'line',
          selectedYear: new Date().getFullYear(),
          showExportModal: false,
          exportProcessing: false,

          // Data
          metrics: {
            totalPurchases: 0,
            totalSpent: 0,
            totalSavings: 0,
            watchlistItems: 0,
            activeAlerts: 0,
            purchaseGrowth: null,
            spendingGrowth: null,
            averageSavingsPercent: 0
          },
          
          recentActivity: [],
          topSavings: [],
          monthlyBreakdown: [],
          availableYears: [],
          
          alertStats: {
            totalAlerts: 0,
            triggered: 0,
            successRate: 0
          },
          
          recentTriggers: [],

          // Charts
          spendingChart: null,
          sportsChart: null,

          // Export
          exportFormat: 'csv',
          exportDateRange: '30d',
          exportIncludes: {
            purchases: true,
            watchlist: true,
            alerts: true,
            savings: true
          },

          async init() {
            this.loading = true;
            await Promise.all([
              this.loadMetrics(),
              this.loadRecentActivity(),
              this.loadTopSavings(),
              this.loadMonthlyBreakdown(),
              this.loadAlertStats(),
              this.loadChartData()
            ]);
            this.loading = false;
            this.initializeCharts();
          },

          async setDateRange(range) {
            this.dateRange = range;
            await this.loadMetrics();
            await this.loadChartData();
            this.updateCharts();
          },

          async loadMetrics() {
            try {
              const response = await fetch(`/api/user/analytics/metrics?range=${this.dateRange}`);
              const data = await response.json();
              
              if (data.success) {
                this.metrics = { ...this.metrics, ...data.metrics };
              }
            } catch (error) {
              console.error('Failed to load metrics:', error);
            }
          },

          async loadRecentActivity() {
            try {
              const response = await fetch('/api/user/analytics/activity?limit=10');
              const data = await response.json();
              
              if (data.success) {
                this.recentActivity = data.activity || [];
              }
            } catch (error) {
              console.error('Failed to load recent activity:', error);
            }
          },

          async loadTopSavings() {
            try {
              const response = await fetch('/api/user/analytics/top-savings?limit=5');
              const data = await response.json();
              
              if (data.success) {
                this.topSavings = data.savings || [];
              }
            } catch (error) {
              console.error('Failed to load top savings:', error);
            }
          },

          async loadMonthlyBreakdown() {
            try {
              const response = await fetch(`/api/user/analytics/monthly?year=${this.selectedYear}`);
              const data = await response.json();
              
              if (data.success) {
                this.monthlyBreakdown = data.breakdown || [];
                this.availableYears = data.available_years || [new Date().getFullYear()];
              }
            } catch (error) {
              console.error('Failed to load monthly breakdown:', error);
            }
          },

          async loadAlertStats() {
            try {
              const response = await fetch('/api/user/analytics/alert-performance');
              const data = await response.json();
              
              if (data.success) {
                this.alertStats = { ...this.alertStats, ...data.stats };
                this.recentTriggers = data.recent_triggers || [];
              }
            } catch (error) {
              console.error('Failed to load alert stats:', error);
            }
          },

          async loadChartData() {
            try {
              const [spendingResponse, sportsResponse] = await Promise.all([
                fetch(`/api/user/analytics/spending-chart?range=${this.dateRange}`),
                fetch(`/api/user/analytics/sports-distribution?range=${this.dateRange}`)
              ]);

              const spendingData = await spendingResponse.json();
              const sportsData = await sportsResponse.json();

              this.spendingChartData = spendingData.success ? spendingData.data : [];
              this.sportsChartData = sportsData.success ? sportsData.data : [];
            } catch (error) {
              console.error('Failed to load chart data:', error);
            }
          },

          initializeCharts() {
            this.initSpendingChart();
            this.initSportsChart();
          },

          initSpendingChart() {
            const ctx = document.getElementById('spendingChart').getContext('2d');
            this.spendingChart = new Chart(ctx, {
              type: this.chartType,
              data: {
                labels: this.spendingChartData?.labels || [],
                datasets: [{
                  label: 'Amount Spent ($)',
                  data: this.spendingChartData?.values || [],
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
                      callback: (value) => '$' + value.toLocaleString()
                    }
                  }
                }
              }
            });
          },

          initSportsChart() {
            const ctx = document.getElementById('sportsChart').getContext('2d');
            this.sportsChart = new Chart(ctx, {
              type: 'doughnut',
              data: {
                labels: this.sportsChartData?.labels || [],
                datasets: [{
                  data: this.sportsChartData?.values || [],
                  backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'
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
                    position: 'right'
                  }
                }
              }
            });
          },

          updateCharts() {
            if (this.spendingChart) {
              this.spendingChart.type = this.chartType;
              this.spendingChart.data.labels = this.spendingChartData?.labels || [];
              this.spendingChart.data.datasets[0].data = this.spendingChartData?.values || [];
              this.spendingChart.update();
            }

            if (this.sportsChart) {
              this.sportsChart.data.labels = this.sportsChartData?.labels || [];
              this.sportsChart.data.datasets[0].data = this.sportsChartData?.values || [];
              this.sportsChart.update();
            }
          },

          exportData() {
            this.showExportModal = true;
          },

          async processExport() {
            this.exportProcessing = true;
            
            try {
              const response = await fetch('/api/user/analytics/export', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  format: this.exportFormat,
                  date_range: this.exportDateRange,
                  includes: this.exportIncludes
                })
              });

              if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `ticket-analytics-${new Date().toISOString().split('T')[0]}.${this.exportFormat}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                this.showExportModal = false;
                this.showNotification('Export Complete', 'Your analytics data has been downloaded', 'success');
              } else {
                throw new Error('Export failed');
              }
            } catch (error) {
              this.showNotification('Export Failed', 'Unable to export data at this time', 'error');
            } finally {
              this.exportProcessing = false;
            }
          },

          getActivityIcon(type) {
            const icons = {
              purchase: {
                bgColor: 'bg-green-100',
                textColor: 'text-green-600',
                path: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'
              },
              watchlist_add: {
                bgColor: 'bg-blue-100',
                textColor: 'text-blue-600',
                path: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'
              },
              price_alert: {
                bgColor: 'bg-yellow-100',
                textColor: 'text-yellow-600',
                path: 'M15 17h5l-5 5v-5zM4.343 12.344l1.414-1.414L6.5 11.5'
              }
            };
            return icons[type] || icons.purchase;
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
              year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
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
