
<template>
  <div class="analytics-dashboard">
    <!-- Header with controls -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">Analytics Dashboard</h1>
      <div class="dashboard-controls">
        <div class="timeframe-selector">
          <label>Timeframe:</label>
          <select v-model="selectedTimeframe" @change="refreshData" class="timeframe-select">
            <option value="24h">Last 24 Hours</option>
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
            <option value="90d">Last 90 Days</option>
          </select>
        </div>
        <button @click="exportData" class="export-btn" :disabled="loading">
          <svg class="icon">
            <use href="#download-icon"></use>
          </svg>
          Export Data
        </button>
        <button @click="refreshData" class="refresh-btn" :disabled="loading">
          <svg class="icon" :class="{ spinning: loading }">
            <use href="#refresh-icon"></use>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Overview metrics -->
    <div class="metrics-overview">
      <MetricCard
        v-for="metric in overviewMetrics"
        :key="metric.key"
        :title="metric.title"
        :value="metric.value"
        :change="metric.change"
        :trend="metric.trend"
        :icon="metric.icon"
        :color="metric.color"
      />
    </div>

    <!-- Charts grid -->
    <div class="charts-grid">
      <!-- Ticket trends chart -->
      <div class="chart-container">
        <div class="chart-header">
          <h3>Ticket Discovery Trends</h3>
          <div class="chart-controls">
            <select v-model="ticketTrendsGroupBy" @change="updateTicketTrendsChart">
              <option value="day">Daily</option>
              <option value="hour">Hourly</option>
            </select>
          </div>
        </div>
        <canvas ref="ticketTrendsChart" class="chart-canvas"></canvas>
        <div v-if="loading" class="chart-loading">
          <div class="loading-spinner"></div>
        </div>
      </div>

      <!-- Platform performance chart -->
      <div class="chart-container">
        <div class="chart-header">
          <h3>Platform Performance</h3>
        </div>
        <canvas ref="platformPerformanceChart" class="chart-canvas"></canvas>
        <div v-if="loading" class="chart-loading">
          <div class="loading-spinner"></div>
        </div>
      </div>

      <!-- Success rates chart -->
      <div class="chart-container">
        <div class="chart-header">
          <h3>Success Rates by Platform</h3>
        </div>
        <canvas ref="successRatesChart" class="chart-canvas"></canvas>
        <div v-if="loading" class="chart-loading">
          <div class="loading-spinner"></div>
        </div>
      </div>

      <!-- Price analysis chart -->
      <div class="chart-container">
        <div class="chart-header">
          <h3>Price Distribution</h3>
          <div class="chart-controls">
            <select v-model="priceAnalysisEventType" @change="updatePriceAnalysisChart">
              <option value="">All Events</option>
              <option value="sports">Sports</option>
              <option value="concerts">Concerts</option>
              <option value="theater">Theater</option>
            </select>
          </div>
        </div>
        <canvas ref="priceAnalysisChart" class="chart-canvas"></canvas>
        <div v-if="loading" class="chart-loading">
          <div class="loading-spinner"></div>
        </div>
      </div>

      <!-- Demand patterns heatmap -->
      <div class="chart-container full-width">
        <div class="chart-header">
          <h3>Activity Heatmap by Hour of Day</h3>
        </div>
        <canvas ref="demandPatternsChart" class="chart-canvas"></canvas>
        <div v-if="loading" class="chart-loading">
          <div class="loading-spinner"></div>
        </div>
      </div>

      <!-- Top events table -->
      <div class="chart-container full-width">
        <div class="chart-header">
          <h3>Top Performing Events</h3>
        </div>
        <div class="top-events-table">
          <table>
            <thead>
              <tr>
                <th>Event</th>
                <th>Venue</th>
                <th>Tickets Found</th>
                <th>Avg Price</th>
                <th>Success Rate</th>
                <th>Last Activity</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="event in topEvents" :key="event.id" class="table-row">
                <td class="event-name">{{ event.event_title }}</td>
                <td class="venue-name">{{ event.venue }}</td>
                <td class="ticket-count">{{ formatNumber(event.ticket_count) }}</td>
                <td class="avg-price">{{ formatCurrency(event.avg_price) }}</td>
                <td class="success-rate">
                  <span class="rate-badge" :class="getSuccessRateClass(event.success_rate)">
                    {{ event.success_rate }}%
                  </span>
                </td>
                <td class="last-activity">{{ formatDateTime(event.last_activity) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="loading" class="table-loading">
            <div class="loading-spinner"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Export modal -->
    <ExportModal
      v-if="showExportModal"
      :analytics-data="analyticsData"
      :timeframe="selectedTimeframe"
      @close="showExportModal = false"
      @export="handleExport"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import MetricCard from './components/MetricCard.vue'
import ExportModal from './components/ExportModal.vue'

Chart.register(...registerables)

// Reactive state
const loading = ref(false)
const selectedTimeframe = ref('7d')
const ticketTrendsGroupBy = ref('day')
const priceAnalysisEventType = ref('')
const showExportModal = ref(false)

const overviewMetrics = ref([])
const analyticsData = ref({})
const topEvents = ref([])

// Chart refs
const ticketTrendsChart = ref(null)
const platformPerformanceChart = ref(null)
const successRatesChart = ref(null)
const priceAnalysisChart = ref(null)
const demandPatternsChart = ref(null)

// Chart instances
let ticketTrendsChartInstance = null
let platformPerformanceChartInstance = null
let successRatesChartInstance = null
let priceAnalysisChartInstance = null
let demandPatternsChartInstance = null

// Auto-refresh interval
let refreshInterval = null

// Methods
const refreshData = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchOverviewData(),
      fetchChartData(),
      fetchTopEvents()
    ])
  } catch (error) {
    console.error('Error refreshing analytics data:', error)
    showNotification('Failed to refresh analytics data', 'error')
  } finally {
    loading.value = false
  }
}

const fetchOverviewData = async () => {
  try {
    const response = await axios.get('/api/v1/analytics/overview', {
      params: { timeframe: selectedTimeframe.value }
    })
    
    const data = response.data.data
    overviewMetrics.value = [
      {
        key: 'total_tickets',
        title: 'Total Tickets Found',
        value: data.summary.total_tickets_found.toLocaleString(),
        change: '+12%',
        trend: 'up',
        icon: 'ticket',
        color: 'blue'
      },
      {
        key: 'unique_events',
        title: 'Unique Events',
        value: data.summary.unique_events.toLocaleString(),
        change: '+8%',
        trend: 'up',
        icon: 'calendar',
        color: 'purple'
      },
      {
        key: 'platforms',
        title: 'Platforms Monitored',
        value: data.summary.platforms_monitored,
        change: 'Stable',
        trend: 'stable',
        icon: 'server',
        color: 'gray'
      },
      {
        key: 'avg_price',
        title: 'Average Price',
        value: formatCurrency(data.summary.avg_price),
        change: '-5%',
        trend: 'down',
        icon: 'currency',
        color: 'green'
      }
    ]
    
    analyticsData.value = data
  } catch (error) {
    console.error('Error fetching overview data:', error)
  }
}

const fetchChartData = async () => {
  try {
    const [trendsResponse, platformResponse, successResponse, priceResponse, demandResponse] = await Promise.all([
      axios.get('/api/v1/analytics/ticket-trends', {
        params: { 
          timeframe: selectedTimeframe.value,
          group_by: ticketTrendsGroupBy.value
        }
      }),
      axios.get('/api/v1/analytics/platform-performance', {
        params: { timeframe: selectedTimeframe.value }
      }),
      axios.get('/api/v1/analytics/success-rates', {
        params: { timeframe: selectedTimeframe.value }
      }),
      axios.get('/api/v1/analytics/price-analysis', {
        params: { 
          timeframe: selectedTimeframe.value,
          event_type: priceAnalysisEventType.value
        }
      }),
      axios.get('/api/v1/analytics/demand-patterns', {
        params: { timeframe: selectedTimeframe.value }
      })
    ])

    await nextTick()
    
    updateTicketTrendsChart(trendsResponse.data.data)
    updatePlatformPerformanceChart(platformResponse.data.data)
    updateSuccessRatesChart(successResponse.data.data)
    updatePriceAnalysisChart(priceResponse.data.data)
    updateDemandPatternsChart(demandResponse.data.data)
    
  } catch (error) {
    console.error('Error fetching chart data:', error)
  }
}

const fetchTopEvents = async () => {
  try {
    const response = await axios.get('/api/v1/analytics/overview', {
      params: { timeframe: selectedTimeframe.value }
    })
    
    topEvents.value = response.data.data.top_events.map(event => ({
      ...event,
      success_rate: Math.floor(Math.random() * 40) + 60, // Mock success rate
      last_activity: new Date(Date.now() - Math.random() * 86400000 * 7).toISOString()
    }))
  } catch (error) {
    console.error('Error fetching top events:', error)
  }
}

const updateTicketTrendsChart = (data) => {
  if (ticketTrendsChartInstance) {
    ticketTrendsChartInstance.destroy()
  }

  const ctx = ticketTrendsChart.value?.getContext('2d')
  if (!ctx) return

  ticketTrendsChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => {
        const date = new Date(item.period)
        return ticketTrendsGroupBy.value === 'hour' 
          ? date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
          : date.toLocaleDateString()
      }),
      datasets: [
        {
          label: 'Tickets Found',
          data: data.map(item => item.tickets_found),
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4
        },
        {
          label: 'Unique Events',
          data: data.map(item => item.unique_events),
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          fill: false,
          tension: 0.4
        }
      ]
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

const updatePlatformPerformanceChart = (data) => {
  if (platformPerformanceChartInstance) {
    platformPerformanceChartInstance.destroy()
  }

  const ctx = platformPerformanceChart.value?.getContext('2d')
  if (!ctx) return

  platformPerformanceChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.map(item => item.platform),
      datasets: [
        {
          label: 'Success Rate (%)',
          data: data.map(item => item.success_rate),
          backgroundColor: data.map(item => {
            if (item.success_rate >= 80) return '#10b981'
            if (item.success_rate >= 60) return '#f59e0b'
            return '#ef4444'
          }),
          borderRadius: 4
        }
      ]
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
          max: 100
        }
      }
    }
  })
}

const updateSuccessRatesChart = (data) => {
  if (successRatesChartInstance) {
    successRatesChartInstance.destroy()
  }

  const ctx = successRatesChart.value?.getContext('2d')
  if (!ctx) return

  successRatesChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.by_platform.map(item => item.platform),
      datasets: [{
        data: data.by_platform.map(item => item.success_rate),
        backgroundColor: [
          '#3b82f6',
          '#10b981',
          '#f59e0b',
          '#ef4444',
          '#8b5cf6',
          '#06b6d4'
        ],
        borderWidth: 2
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

const updatePriceAnalysisChart = (data) => {
  if (priceAnalysisChartInstance) {
    priceAnalysisChartInstance.destroy()
  }

  const ctx = priceAnalysisChart.value?.getContext('2d')
  if (!ctx) return

  priceAnalysisChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.price_ranges.map(range => range.range),
      datasets: [{
        label: 'Number of Tickets',
        data: data.price_ranges.map(range => range.count),
        backgroundColor: '#3b82f6',
        borderRadius: 4
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
          beginAtZero: true
        }
      }
    }
  })
}

const updateDemandPatternsChart = (data) => {
  if (demandPatternsChartInstance) {
    demandPatternsChartInstance.destroy()
  }

  const ctx = demandPatternsChart.value?.getContext('2d')
  if (!ctx) return

  // Generate mock heatmap data
  const hours = Array.from({ length: 24 }, (_, i) => i)
  const daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  
  demandPatternsChartInstance = new Chart(ctx, {
    type: 'scatter',
    data: {
      datasets: [{
        label: 'Activity Level',
        data: hours.flatMap(hour => 
          daysOfWeek.map((day, dayIndex) => ({
            x: hour,
            y: dayIndex,
            v: Math.floor(Math.random() * 100) // Mock activity level
          }))
        ),
        backgroundColor: (context) => {
          const value = context.parsed.v || 0
          const alpha = value / 100
          return `rgba(59, 130, 246, ${alpha})`
        },
        pointRadius: 8
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
        x: {
          type: 'linear',
          min: 0,
          max: 23,
          ticks: {
            stepSize: 1,
            callback: (value) => `${value}:00`
          },
          title: {
            display: true,
            text: 'Hour of Day'
          }
        },
        y: {
          type: 'linear',
          min: -0.5,
          max: 6.5,
          ticks: {
            stepSize: 1,
            callback: (value) => daysOfWeek[value] || ''
          },
          title: {
            display: true,
            text: 'Day of Week'
          }
        }
      }
    }
  })
}

const exportData = () => {
  showExportModal.value = true
}

const handleExport = async (exportConfig) => {
  try {
    const response = await axios.get(`/api/v1/analytics/export/${exportConfig.type}`, {
      params: {
        timeframe: selectedTimeframe.value,
        format: exportConfig.format
      }
    })
    
    // Create download link
    const blob = new Blob([JSON.stringify(response.data, null, 2)], {
      type: 'application/json'
    })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `analytics-${exportConfig.type}-${selectedTimeframe.value}.json`
    link.click()
    window.URL.revokeObjectURL(url)
    
    showNotification('Analytics data exported successfully', 'success')
  } catch (error) {
    console.error('Error exporting data:', error)
    showNotification('Failed to export data', 'error')
  }
}

// Utility functions
const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num)
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleString()
}

const getSuccessRateClass = (rate) => {
  if (rate >= 80) return 'excellent'
  if (rate >= 60) return 'good'
  if (rate >= 40) return 'fair'
  return 'poor'
}

const showNotification = (message, type = 'info') => {
  if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
    window.hdTicketsUtils.notify(message, type)
  }
}

// Lifecycle hooks
onMounted(async () => {
  await refreshData()
  
  // Set up auto-refresh every 5 minutes
  refreshInterval = setInterval(refreshData, 300000)
})

onUnmounted(() => {
  // Cleanup chart instances
  if (ticketTrendsChartInstance) ticketTrendsChartInstance.destroy()
  if (platformPerformanceChartInstance) platformPerformanceChartInstance.destroy()
  if (successRatesChartInstance) successRatesChartInstance.destroy()
  if (priceAnalysisChartInstance) priceAnalysisChartInstance.destroy()
  if (demandPatternsChartInstance) demandPatternsChartInstance.destroy()
  
  // Clear intervals
  if (refreshInterval) clearInterval(refreshInterval)
})

// Watchers
watch(selectedTimeframe, refreshData)
watch(ticketTrendsGroupBy, () => fetchChartData())
watch(priceAnalysisEventType, () => fetchChartData())
</script>

<style scoped>
.analytics-dashboard {
  padding: 1.5rem;
  background: #f8fafc;
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.dashboard-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.dashboard-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.timeframe-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.timeframe-select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.export-btn, .refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background: white;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.export-btn:hover, .refresh-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.export-btn:disabled, .refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.icon {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.icon.spinning {
  animation: spin 1s linear infinite;
}

.metrics-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.chart-container {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  position: relative;
}

.chart-container.full-width {
  grid-column: 1 / -1;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 0.75rem;
}

.chart-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.chart-controls select {
  padding: 0.25rem 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.75rem;
}

.chart-canvas {
  height: 300px;
  padding: 0 1.5rem 1.5rem;
}

.chart-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top: 3px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.top-events-table {
  padding: 0 1.5rem 1.5rem;
  position: relative;
}

.top-events-table table {
  width: 100%;
  border-collapse: collapse;
}

.top-events-table th {
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  padding: 0.75rem 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.top-events-table td {
  padding: 0.75rem 0.5rem;
  border-bottom: 1px solid #f3f4f6;
  font-size: 0.875rem;
}

.table-row:hover {
  background: #f9fafb;
}

.event-name {
  font-weight: 600;
  color: #1f2937;
}

.venue-name {
  color: #6b7280;
}

.ticket-count, .avg-price {
  font-weight: 600;
}

.rate-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.rate-badge.excellent {
  background: #d1fae5;
  color: #065f46;
}

.rate-badge.good {
  background: #dbeafe;
  color: #1e40af;
}

.rate-badge.fair {
  background: #fef3c7;
  color: #92400e;
}

.rate-badge.poor {
  background: #fee2e2;
  color: #991b1b;
}

.last-activity {
  color: #6b7280;
  font-size: 0.75rem;
}

.table-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Responsive design */
@media (max-width: 768px) {
  .analytics-dashboard {
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .dashboard-controls {
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .charts-grid {
    grid-template-columns: 1fr;
  }
  
  .chart-canvas {
    height: 250px;
  }
  
  .top-events-table {
    overflow-x: auto;
  }
}

/* Animations */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
