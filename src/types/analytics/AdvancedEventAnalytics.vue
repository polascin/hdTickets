<!--
/**
 * Advanced Event Analytics Component
 * 
 * Lazy-loaded Vue.js component for complex sports event analytics
 * with advanced data visualization and real-time updates.
 * 
 * @category analytics
 * @lazy true
 * @dependencies ['Chart.js', 'date-fns']
 * @props events: array - Events data for analysis
 * @props dateRange: object - Date range for analysis
 * @props userRole: string - Current user role for permissions
 * @events analytics-loaded: Emitted when component is fully loaded
 * @events data-export: Emitted when data export is requested
 * @events filter-changed: Emitted when analytics filter changes
 */
-->

<template>
  <div class="advanced-event-analytics">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-500 mx-auto"></div>
      <p class="text-center mt-4 text-gray-600 dark:text-gray-300">
        Loading advanced analytics...
      </p>
    </div>

    <!-- Error State -->
    <div v-else-if="hasError" class="error-state">
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <strong class="font-bold">Error loading analytics:</strong>
        <span class="block sm:inline">{{ errorMessage }}</span>
      </div>
      <button @click="retryLoad" 
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Retry
      </button>
    </div>

    <!-- Main Analytics Interface -->
    <div v-else class="analytics-interface">
      <!-- Header with Controls -->
      <div class="analytics-header bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
              Sports Event Analytics
            </h2>
            <p class="text-gray-600 dark:text-gray-300">
              Advanced insights for {{ totalEvents }} events across {{ uniqueVenues }} venues
            </p>
          </div>
          
          <div class="flex items-center space-x-4">
            <!-- Date Range Selector -->
            <div class="relative">
              <select v-model="selectedTimeframe" 
                      @change="updateAnalytics"
                      class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2">
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
                <option value="3m">Last 3 Months</option>
                <option value="1y">Last Year</option>
              </select>
            </div>
            
            <!-- Export Button -->
            <button @click="exportData" 
                    :disabled="isExporting"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50">
              <i class="fas fa-download mr-2"></i>
              {{ isExporting ? 'Exporting...' : 'Export' }}
            </button>
            
            <!-- Refresh Button -->
            <button @click="refreshData" 
                    :disabled="isRefreshing"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50">
              <i class="fas fa-sync-alt mr-2" :class="{ 'animate-spin': isRefreshing }"></i>
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Key Metrics Grid -->
      <div class="metrics-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <MetricCard
          v-for="metric in keyMetrics"
          :key="metric.id"
          :title="metric.title"
          :value="metric.value"
          :change="metric.change"
          :trend="metric.trend"
          :color="metric.color"
          :format="metric.format"
        />
      </div>

      <!-- Charts Section -->
      <div class="charts-section space-y-8">
        <!-- Price Trends Chart -->
        <div class="chart-container bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Price Trends Analysis
            </h3>
            <div class="chart-controls flex items-center space-x-2">
              <button
                v-for="period in chartPeriods"
                :key="period.value"
                @click="setPriceTrendPeriod(period.value)"
                :class="[
                  'px-3 py-1 rounded text-sm font-medium transition-colors',
                  priceTrendPeriod === period.value
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                ]"
              >
                {{ period.label }}
              </button>
            </div>
          </div>
          <div class="chart-wrapper">
            <canvas ref="priceTrendChart" class="w-full h-64"></canvas>
          </div>
        </div>

        <!-- Category Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="chart-container bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Sport Categories Distribution
            </h3>
            <div class="chart-wrapper">
              <canvas ref="categoryChart" class="w-full h-64"></canvas>
            </div>
          </div>

          <div class="chart-container bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Platform Performance
            </h3>
            <div class="chart-wrapper">
              <canvas ref="platformChart" class="w-full h-64"></canvas>
            </div>
          </div>
        </div>

        <!-- Venue Heatmap -->
        <div class="chart-container bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Venue Activity Heatmap
            </h3>
            <div class="flex items-center space-x-2">
              <label class="text-sm text-gray-600 dark:text-gray-400">Metric:</label>
              <select v-model="heatmapMetric" 
                      @change="updateHeatmap"
                      class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm">
                <option value="events">Event Count</option>
                <option value="revenue">Revenue</option>
                <option value="tickets">Ticket Sales</option>
              </select>
            </div>
          </div>
          <VenueHeatmap 
            :venues="venueData" 
            :metric="heatmapMetric"
            @venue-selected="onVenueSelected"
          />
        </div>

        <!-- Advanced Analytics Tables -->
        <div class="tables-section grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Top Performing Events -->
          <div class="table-container bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Top Performing Events
            </h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-4 py-2 text-left text-gray-900 dark:text-white">Event</th>
                    <th class="px-4 py-2 text-left text-gray-900 dark:text-white">Revenue</th>
                    <th class="px-4 py-2 text-left text-gray-900 dark:text-white">Tickets</th>
                    <th class="px-4 py-2 text-left text-gray-900 dark:text-white">Avg Price</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="event in topEvents" 
                      :key="event.id" 
                      class="border-t border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-2">
                      <div class="font-medium text-gray-900 dark:text-white">{{ event.name }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ event.venue }}</div>
                    </td>
                    <td class="px-4 py-2 text-green-600 font-medium">
                      £{{ formatCurrency(event.revenue) }}
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                      {{ event.ticketsSold.toLocaleString() }}
                    </td>
                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                      £{{ formatCurrency(event.avgPrice) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Market Insights -->
          <div class="insights-container bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Market Insights
            </h3>
            <div class="space-y-4">
              <div v-for="insight in marketInsights" 
                   :key="insight.id"
                   class="insight-item border-l-4 pl-4 py-2"
                   :class="getInsightColorClass(insight.type)">
                <div class="flex items-center justify-between">
                  <span class="font-medium text-gray-900 dark:text-white">
                    {{ insight.title }}
                  </span>
                  <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ insight.confidence }}% confidence
                  </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                  {{ insight.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Real-time Updates Indicator -->
      <div v-if="isRealTimeEnabled" 
           class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-full shadow-lg">
        <div class="flex items-center space-x-2">
          <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
          <span class="text-sm">Live Updates</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineAsyncComponent, ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import { format, subDays, subMonths, subYears } from 'date-fns'
import { useAnalyticsStore } from '@stores/analytics'
import { useWebSocket } from '@composables/useWebSocket'
import { useNotifications } from '@composables/useNotifications'

// Register Chart.js components
Chart.register(...registerables)

// Lazy-load heavy components
const MetricCard = defineAsyncComponent(() => import('@components/ui/MetricCard.vue'))
const VenueHeatmap = defineAsyncComponent(() => import('@components/analytics/VenueHeatmap.vue'))

export default {
  name: 'AdvancedEventAnalytics',
  
  components: {
    MetricCard,
    VenueHeatmap
  },

  props: {
    events: {
      type: Array,
      default: () => []
    },
    dateRange: {
      type: Object,
      default: () => ({
        start: subDays(new Date(), 30),
        end: new Date()
      })
    },
    userRole: {
      type: String,
      default: 'guest'
    },
    realTimeUpdates: {
      type: Boolean,
      default: false
    }
  },

  emits: ['analytics-loaded', 'data-export', 'filter-changed'],

  setup(props, { emit }) {
    // Store and composables
    const analyticsStore = useAnalyticsStore()
    const { socket } = useWebSocket()
    const { showNotification } = useNotifications()

    // Reactive state
    const isLoading = ref(true)
    const hasError = ref(false)
    const errorMessage = ref('')
    const isRefreshing = ref(false)
    const isExporting = ref(false)
    const isRealTimeEnabled = ref(props.realTimeUpdates)

    // Chart refs
    const priceTrendChart = ref(null)
    const categoryChart = ref(null)
    const platformChart = ref(null)

    // Chart instances
    let priceChart = null
    let categoryChartInstance = null
    let platformChartInstance = null

    // Analytics state
    const selectedTimeframe = ref('30d')
    const priceTrendPeriod = ref('daily')
    const heatmapMetric = ref('events')

    // Chart configuration
    const chartPeriods = [
      { value: 'hourly', label: 'Hourly' },
      { value: 'daily', label: 'Daily' },
      { value: 'weekly', label: 'Weekly' },
      { value: 'monthly', label: 'Monthly' }
    ]

    // Computed properties
    const processedEvents = computed(() => {
      return analyticsStore.processEvents(props.events, selectedTimeframe.value)
    })

    const totalEvents = computed(() => processedEvents.value.length)

    const uniqueVenues = computed(() => {
      return [...new Set(processedEvents.value.map(e => e.venue))].length
    })

    const keyMetrics = computed(() => [
      {
        id: 'total-revenue',
        title: 'Total Revenue',
        value: analyticsStore.totalRevenue,
        change: analyticsStore.revenueChange,
        trend: analyticsStore.revenueChange > 0 ? 'up' : 'down',
        color: 'green',
        format: 'currency'
      },
      {
        id: 'avg-price',
        title: 'Average Ticket Price',
        value: analyticsStore.averagePrice,
        change: analyticsStore.priceChange,
        trend: analyticsStore.priceChange > 0 ? 'up' : 'down',
        color: 'blue',
        format: 'currency'
      },
      {
        id: 'events-count',
        title: 'Total Events',
        value: totalEvents.value,
        change: analyticsStore.eventsChange,
        trend: analyticsStore.eventsChange > 0 ? 'up' : 'down',
        color: 'purple',
        format: 'number'
      },
      {
        id: 'conversion-rate',
        title: 'Conversion Rate',
        value: analyticsStore.conversionRate,
        change: analyticsStore.conversionChange,
        trend: analyticsStore.conversionChange > 0 ? 'up' : 'down',
        color: 'orange',
        format: 'percentage'
      }
    ])

    const topEvents = computed(() => {
      return analyticsStore.getTopPerformingEvents(10)
    })

    const marketInsights = computed(() => {
      return analyticsStore.getMarketInsights()
    })

    const venueData = computed(() => {
      return analyticsStore.getVenueAnalytics(heatmapMetric.value)
    })

    // Methods
    const initializeCharts = async () => {
      try {
        await Promise.all([
          initializePriceTrendChart(),
          initializeCategoryChart(),
          initializePlatformChart()
        ])
        
        emit('analytics-loaded', {
          chartsLoaded: true,
          eventsCount: totalEvents.value
        })
      } catch (error) {
        console.error('Failed to initialize charts:', error)
        hasError.value = true
        errorMessage.value = 'Failed to load analytics charts'
      }
    }

    const initializePriceTrendChart = () => {
      const ctx = priceTrendChart.value?.getContext('2d')
      if (!ctx) return

      const data = analyticsStore.getPriceTrendData(priceTrendPeriod.value)
      
      priceChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Average Price',
            data: data.prices,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
          }, {
            label: 'Volume',
            data: data.volumes,
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: {
              position: 'top',
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  if (context.datasetIndex === 0) {
                    return `Price: £${context.parsed.y}`
                  } else {
                    return `Volume: ${context.parsed.y} tickets`
                  }
                }
              }
            }
          },
          scales: {
            x: {
              display: true,
              title: {
                display: true,
                text: 'Time Period'
              }
            },
            y: {
              display: true,
              title: {
                display: true,
                text: 'Price (£)'
              },
              position: 'left'
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              title: {
                display: true,
                text: 'Volume'
              },
              grid: {
                drawOnChartArea: false,
              }
            }
          }
        }
      })
    }

    const initializeCategoryChart = () => {
      const ctx = categoryChart.value?.getContext('2d')
      if (!ctx) return

      const data = analyticsStore.getCategoryDistribution()
      
      categoryChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: data.labels,
          datasets: [{
            data: data.values,
            backgroundColor: [
              '#3B82F6', '#10B981', '#F59E0B', '#EF4444', 
              '#8B5CF6', '#F97316', '#06B6D4', '#84CC16'
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
      })
    }

    const initializePlatformChart = () => {
      const ctx = platformChart.value?.getContext('2d')
      if (!ctx) return

      const data = analyticsStore.getPlatformPerformance()
      
      platformChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Revenue',
            data: data.revenues,
            backgroundColor: 'rgba(59, 130, 246, 0.8)'
          }, {
            label: 'Event Count',
            data: data.counts,
            backgroundColor: 'rgba(16, 185, 129, 0.8)'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top'
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      })
    }

    const updateAnalytics = async () => {
      isRefreshing.value = true
      
      try {
        await analyticsStore.refreshAnalytics(selectedTimeframe.value)
        
        // Update charts
        updateChartData()
        
        showNotification('Analytics updated successfully', 'success')
      } catch (error) {
        console.error('Failed to update analytics:', error)
        showNotification('Failed to update analytics', 'error')
      } finally {
        isRefreshing.value = false
      }
    }

    const updateChartData = () => {
      if (priceChart) {
        const data = analyticsStore.getPriceTrendData(priceTrendPeriod.value)
        priceChart.data.labels = data.labels
        priceChart.data.datasets[0].data = data.prices
        priceChart.data.datasets[1].data = data.volumes
        priceChart.update()
      }

      if (categoryChartInstance) {
        const data = analyticsStore.getCategoryDistribution()
        categoryChartInstance.data.labels = data.labels
        categoryChartInstance.data.datasets[0].data = data.values
        categoryChartInstance.update()
      }

      if (platformChartInstance) {
        const data = analyticsStore.getPlatformPerformance()
        platformChartInstance.data.labels = data.labels
        platformChartInstance.data.datasets[0].data = data.revenues
        platformChartInstance.data.datasets[1].data = data.counts
        platformChartInstance.update()
      }
    }

    const setPriceTrendPeriod = (period) => {
      priceTrendPeriod.value = period
      
      if (priceChart) {
        const data = analyticsStore.getPriceTrendData(period)
        priceChart.data.labels = data.labels
        priceChart.data.datasets[0].data = data.prices
        priceChart.data.datasets[1].data = data.volumes
        priceChart.update()
      }
    }

    const updateHeatmap = () => {
      // Heatmap will reactively update via computed property
    }

    const onVenueSelected = (venue) => {
      emit('filter-changed', {
        type: 'venue',
        value: venue
      })
    }

    const exportData = async () => {
      isExporting.value = true
      
      try {
        const exportData = {
          metrics: keyMetrics.value,
          events: topEvents.value,
          insights: marketInsights.value,
          venues: venueData.value,
          dateRange: {
            start: format(subDays(new Date(), parseInt(selectedTimeframe.value)), 'yyyy-MM-dd'),
            end: format(new Date(), 'yyyy-MM-dd')
          }
        }
        
        emit('data-export', {
          data: exportData,
          format: 'json',
          filename: `event-analytics-${format(new Date(), 'yyyy-MM-dd')}.json`
        })
        
        showNotification('Analytics data exported successfully', 'success')
      } catch (error) {
        console.error('Failed to export data:', error)
        showNotification('Failed to export analytics data', 'error')
      } finally {
        isExporting.value = false
      }
    }

    const refreshData = () => {
      updateAnalytics()
    }

    const retryLoad = () => {
      hasError.value = false
      errorMessage.value = ''
      isLoading.value = true
      loadAnalytics()
    }

    const getInsightColorClass = (type) => {
      const classes = {
        'positive': 'border-green-500',
        'negative': 'border-red-500',
        'neutral': 'border-blue-500',
        'warning': 'border-yellow-500'
      }
      return classes[type] || classes.neutral
    }

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('en-GB', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(value)
    }

    const loadAnalytics = async () => {
      try {
        isLoading.value = true
        await analyticsStore.loadAnalytics(props.events, selectedTimeframe.value)
        await initializeCharts()
        isLoading.value = false
      } catch (error) {
        console.error('Failed to load analytics:', error)
        hasError.value = true
        errorMessage.value = error.message || 'Failed to load analytics'
        isLoading.value = false
      }
    }

    // Setup real-time updates
    const setupRealTimeUpdates = () => {
      if (socket.value && isRealTimeEnabled.value) {
        socket.value.on('analytics-update', (data) => {
          analyticsStore.updateRealTimeData(data)
          updateChartData()
        })
      }
    }

    // Lifecycle hooks
    onMounted(async () => {
      await loadAnalytics()
      setupRealTimeUpdates()
    })

    onUnmounted(() => {
      // Cleanup chart instances
      if (priceChart) priceChart.destroy()
      if (categoryChartInstance) categoryChartInstance.destroy()
      if (platformChartInstance) platformChartInstance.destroy()
      
      // Cleanup WebSocket listeners
      if (socket.value) {
        socket.value.off('analytics-update')
      }
    })

    // Watch for prop changes
    watch(() => props.events, () => {
      updateAnalytics()
    }, { deep: true })

    return {
      // State
      isLoading,
      hasError,
      errorMessage,
      isRefreshing,
      isExporting,
      isRealTimeEnabled,
      
      // UI state
      selectedTimeframe,
      priceTrendPeriod,
      heatmapMetric,
      
      // Chart refs
      priceTrendChart,
      categoryChart,
      platformChart,
      
      // Chart config
      chartPeriods,
      
      // Computed
      totalEvents,
      uniqueVenues,
      keyMetrics,
      topEvents,
      marketInsights,
      venueData,
      
      // Methods
      updateAnalytics,
      setPriceTrendPeriod,
      updateHeatmap,
      onVenueSelected,
      exportData,
      refreshData,
      retryLoad,
      getInsightColorClass,
      formatCurrency
    }
  }
}
</script>

<style scoped>
.advanced-event-analytics {
  @apply space-y-6;
}

.loading-state,
.error-state {
  @apply flex flex-col items-center justify-center min-h-96 p-8;
}

.chart-wrapper {
  @apply relative h-64;
}

.insight-item {
  @apply transition-colors hover:bg-gray-50 dark:hover:bg-gray-700 rounded p-2;
}

/* Chart responsive adjustments */
@media (max-width: 768px) {
  .chart-wrapper {
    @apply h-48;
  }
  
  .charts-section .grid {
    @apply grid-cols-1;
  }
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .chart-container {
    @apply bg-gray-800 border-gray-700;
  }
}
</style>
