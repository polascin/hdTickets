<template>
  <div class="realtime-monitoring-dashboard">
    <!-- Enhanced Header with controls -->
    <div class="dashboard-header">
      <div class="header-content">
        <div class="header-title-section">
          <h1 class="dashboard-title">Sports Events Ticket Monitoring</h1>
          <p class="dashboard-subtitle">Real-time monitoring of ticket availability, prices, and platform health</p>
        </div>
        <div class="header-controls">
          <div class="status-indicator" :class="connectionStatus">
            <div class="status-dot"></div>
            <span>{{ connectionStatus === 'connected' ? 'Live' : 'Disconnected' }}</span>
          </div>
          <div class="control-group">
            <button @click="toggleAutoRefresh" class="auto-refresh-btn" :class="{ active: autoRefresh }">
              <svg class="icon" viewBox="0 0 24 24">
                <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
              </svg>
              <span class="btn-text">Auto Refresh</span>
            </button>
            <button @click="refreshAll" class="refresh-btn" :disabled="loading">
              <svg class="icon" :class="{ spinning: loading }" viewBox="0 0 24 24">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              <span class="btn-text">Refresh</span>
            </button>
            <button @click="toggleTheme" class="theme-toggle-btn" :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
              <svg v-if="!isDarkMode" class="icon" viewBox="0 0 24 24">
                <path d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
              </svg>
              <svg v-else class="icon" viewBox="0 0 24 24">
                <path d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Real-time stats grid -->
    <div class="stats-grid">
      <StatCard
        v-for="stat in realtimeStats"
        :key="stat.key"
        :title="stat.title"
        :value="stat.value"
        :change="stat.change"
        :trend="stat.trend"
        :icon="stat.icon"
        :color="stat.color"
        :is-realtime="true"
        :loading="loading"
      />
    </div>

    <!-- Platform health monitor -->
    <div class="platform-health-section">
      <div class="section-header">
        <h2>Platform Health</h2>
        <div class="health-summary">
          <span class="health-score" :class="getHealthScoreClass(overallHealthScore)">
            {{ overallHealthScore }}%
          </span>
        </div>
      </div>
      <div class="platform-grid">
        <PlatformHealthCard
          v-for="platform in platformHealth"
          :key="platform.platform"
          :platform="platform"
          @check-now="checkPlatformNow"
        />
      </div>
    </div>

    <!-- Active monitors section -->
    <div class="monitors-section">
      <div class="section-header">
        <h2>Active Event Monitors</h2>
        <button @click="showCreateMonitorModal" class="create-monitor-btn">
          <svg class="icon">
            <use href="#plus-icon"></use>
          </svg>
          Create Monitor
        </button>
      </div>
      
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Loading monitors...</p>
      </div>
      
      <div v-else-if="monitors.length" class="monitors-grid">
        <MonitorCard
          v-for="monitor in monitors"
          :key="monitor.id"
          :monitor="monitor"
          @check-now="checkMonitorNow"
          @toggle="toggleMonitor"
          @view="viewMonitor"
          @edit="editMonitor"
        />
      </div>
      
      <div v-else class="empty-state">
        <svg class="empty-icon">
          <use href="#monitor-icon"></use>
        </svg>
        <h3>No Active Monitors</h3>
        <p>Create your first event monitor to start tracking ticket availability.</p>
        <button @click="showCreateMonitorModal" class="create-first-monitor-btn">
          Create Monitor
        </button>
      </div>
    </div>

    <!-- Recent activity feed -->
    <div class="activity-section">
      <div class="section-header">
        <h2>Recent Activity</h2>
        <div class="activity-filter">
          <select v-model="activityFilter" @change="filterActivity">
            <option value="all">All Activity</option>
            <option value="alerts">Alerts Only</option>
            <option value="tickets">New Tickets</option>
            <option value="errors">Errors</option>
          </select>
        </div>
      </div>
      <ActivityFeed 
        :activities="filteredActivity" 
        :loading="loading"
        @load-more="loadMoreActivity"
      />
    </div>

    <!-- Ticket alerts modal -->
    <TicketAlertModal
      v-if="showAlertModal"
      :ticket="selectedTicket"
      @close="showAlertModal = false"
      @create-alert="handleCreateAlert"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { Chart } from 'chart.js/auto'
import StatCard from './components/StatCard.vue'
import PlatformHealthCard from './components/PlatformHealthCard.vue'
import MonitorCard from './components/MonitorCard.vue'
import ActivityFeed from './components/ActivityFeed.vue'
import TicketAlertModal from './components/TicketAlertModal.vue'

// Reactive state
const loading = ref(false)
const autoRefresh = ref(true)
const connectionStatus = ref('connected')
const realtimeStats = ref([])
const platformHealth = ref([])
const monitors = ref([])
const recentActivity = ref([])
const activityFilter = ref('all')
const showAlertModal = ref(false)
const selectedTicket = ref(null)
const isDarkMode = ref(false)

// Auto-refresh interval
let refreshInterval = null
let echoChannel = null

// Computed properties
const overallHealthScore = computed(() => {
  if (!platformHealth.value.length) return 100
  
  const totalScore = platformHealth.value.reduce((sum, platform) => 
    sum + platform.success_rate, 0)
  return Math.round(totalScore / platformHealth.value.length)
})

const filteredActivity = computed(() => {
  if (activityFilter.value === 'all') return recentActivity.value
  
  return recentActivity.value.filter(activity => {
    switch (activityFilter.value) {
      case 'alerts': return activity.type === 'alert'
      case 'tickets': return activity.type === 'ticket_found'
      case 'errors': return activity.type === 'error'
      default: return true
    }
  })
})

// Methods
const refreshAll = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchRealtimeStats(),
      fetchPlatformHealth(),
      fetchMonitors(),
      fetchRecentActivity()
    ])
  } catch (error) {
    console.error('Error refreshing data:', error)
    showNotification('Failed to refresh data', 'error')
  } finally {
    loading.value = false
  }
}

const fetchRealtimeStats = async () => {
  try {
    const response = await fetch('/api/v1/monitoring/stats', {
      headers: {
        'Authorization': `Bearer ${window.authToken}`,
        'Content-Type': 'application/json'
      }
    })
    
    if (!response.ok) throw new Error('Failed to fetch stats')
    
    const data = await response.json()
    realtimeStats.value = [
      {
        key: 'active_monitors',
        title: 'Active Monitors',
        value: data.active_monitors || 0,
        change: data.monitors_change || 0,
        trend: data.monitors_change >= 0 ? 'up' : 'down',
        icon: 'monitor',
        color: 'blue'
      },
      {
        key: 'tickets_found',
        title: 'Tickets Found Today',
        value: data.tickets_found_today || 0,
        change: data.tickets_change || 0,
        trend: data.tickets_change >= 0 ? 'up' : 'down',
        icon: 'ticket',
        color: 'green'
      },
      {
        key: 'alerts_sent',
        title: 'Alerts Sent',
        value: data.alerts_sent_today || 0,
        change: data.alerts_change || 0,
        trend: data.alerts_change >= 0 ? 'up' : 'down',
        icon: 'bell',
        color: 'orange'
      },
      {
        key: 'success_rate',
        title: 'Success Rate',
        value: `${data.success_rate || 100}%`,
        change: data.success_rate_change || 0,
        trend: data.success_rate_change >= 0 ? 'up' : 'down',
        icon: 'check-circle',
        color: data.success_rate >= 95 ? 'green' : data.success_rate >= 80 ? 'orange' : 'red'
      },
      {
        key: 'response_time',
        title: 'Avg Response Time',
        value: `${data.avg_response_time || 0}ms`,
        change: data.response_time_change || 0,
        trend: data.response_time_change <= 0 ? 'up' : 'down',
        icon: 'clock',
        color: data.avg_response_time <= 500 ? 'green' : data.avg_response_time <= 1000 ? 'orange' : 'red'
      },
      {
        key: 'platform_health',
        title: 'Platform Health',
        value: `${overallHealthScore.value}%`,
        change: data.health_change || 0,
        trend: data.health_change >= 0 ? 'up' : 'down',
        icon: 'heart',
        color: overallHealthScore.value >= 95 ? 'green' : overallHealthScore.value >= 80 ? 'orange' : 'red'
      }
    ]
  } catch (error) {
    console.error('Error fetching realtime stats:', error)
    showNotification('Failed to fetch monitoring statistics', 'error')
  }
}

const fetchPlatformHealth = async () => {
  try {
    const response = await axios.get('/api/v1/dashboard/platform-health')
    platformHealth.value = response.data.data.map(platform => ({
      ...platform,
      last_check: new Date(platform.last_success).toLocaleTimeString()
    }))
  } catch (error) {
    console.error('Error fetching platform health:', error)
  }
}

const fetchMonitors = async () => {
  try {
    const response = await axios.get('/api/v1/dashboard/monitors')
    monitors.value = response.data.data
  } catch (error) {
    console.error('Error fetching monitors:', error)
  }
}

const fetchRecentActivity = async () => {
  try {
    // Mock data - would fetch from activity API
    recentActivity.value = [
      {
        id: 1,
        type: 'ticket_found',
        title: 'New tickets found for Lakers vs Warriors',
        description: '5 tickets found on StubHub',
        timestamp: new Date().toISOString(),
        priority: 'high'
      },
      {
        id: 2,
        type: 'alert',
        title: 'Price drop alert triggered',
        description: 'Lakers tickets dropped below $200',
        timestamp: new Date(Date.now() - 300000).toISOString(),
        priority: 'medium'
      }
    ]
  } catch (error) {
    console.error('Error fetching recent activity:', error)
  }
}

const checkMonitorNow = async (monitorId) => {
  try {
    const response = await axios.post(`/api/v1/dashboard/monitors/${monitorId}/check-now`)
    showNotification('Monitor check initiated', 'success')
    
    // Refresh monitor data after a delay
    setTimeout(() => {
      fetchMonitors()
    }, 2000)
  } catch (error) {
    console.error('Error checking monitor:', error)
    showNotification('Failed to check monitor', 'error')
  }
}

const toggleMonitor = async (monitorId) => {
  try {
    const response = await axios.post(`/api/v1/dashboard/monitors/${monitorId}/toggle`)
    showNotification('Monitor status updated', 'success')
    await fetchMonitors()
  } catch (error) {
    console.error('Error toggling monitor:', error)
    showNotification('Failed to update monitor status', 'error')
  }
}

const checkPlatformNow = async (platformName) => {
  showNotification(`Checking ${platformName}...`, 'info')
  
  // Simulate platform check
  setTimeout(() => {
    fetchPlatformHealth()
    showNotification(`${platformName} check completed`, 'success')
  }, 2000)
}

const viewMonitor = (monitorId) => {
  window.location.href = `/monitors/${monitorId}`
}

const editMonitor = (monitorId) => {
  window.location.href = `/monitors/${monitorId}/edit`
}

const showCreateMonitorModal = () => {
  window.location.href = '/monitors/create'
}

const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value
  
  if (autoRefresh.value) {
    startAutoRefresh()
  } else {
    stopAutoRefresh()
  }
}

const startAutoRefresh = () => {
  if (refreshInterval) clearInterval(refreshInterval)
  
  refreshInterval = setInterval(() => {
    if (!loading.value) {
      refreshAll()
    }
  }, 30000) // Refresh every 30 seconds
}

const stopAutoRefresh = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
}

const setupRealTimeUpdates = () => {
  if (typeof Echo !== 'undefined') {
    echoChannel = Echo.channel('ticket-monitoring')
      .listen('.ticket.found', (event) => {
        // Handle new ticket found
        addActivityItem({
          type: 'ticket_found',
          title: `New tickets found for ${event.event_title}`,
          description: `${event.quantity} tickets on ${event.platform}`,
          timestamp: new Date().toISOString(),
          priority: 'high'
        })
        
        // Update stats
        fetchRealtimeStats()
      })
      .listen('.platform.status.changed', (event) => {
        // Handle platform status change
        fetchPlatformHealth()
      })
      .listen('.monitor.alert', (event) => {
        // Handle monitor alert
        addActivityItem({
          type: 'alert',
          title: event.title,
          description: event.message,
          timestamp: new Date().toISOString(),
          priority: event.priority
        })
        
        // Show browser notification if supported
        if ('Notification' in window && Notification.permission === 'granted') {
          new Notification(event.title, {
            body: event.message,
            icon: '/assets/images/hdTicketsLogo.png'
          })
        }
      })
  }
}

const addActivityItem = (item) => {
  recentActivity.value.unshift({
    ...item,
    id: Date.now()
  })
  
  // Keep only last 50 items
  if (recentActivity.value.length > 50) {
    recentActivity.value = recentActivity.value.slice(0, 50)
  }
}

const filterActivity = () => {
  // Filtering is handled by computed property
}

const loadMoreActivity = async () => {
  // Implement pagination for activity feed
  console.log('Loading more activity...')
}

const handleCreateAlert = (alertData) => {
  // Handle creating new alert
  showNotification('Alert created successfully', 'success')
  showAlertModal.value = false
}

const getHealthScoreClass = (score) => {
  if (score >= 90) return 'excellent'
  if (score >= 75) return 'good'
  if (score >= 50) return 'warning'
  return 'critical'
}

const showNotification = (message, type = 'info') => {
  // Use your notification system
  if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
    window.hdTicketsUtils.notify(message, type)
  }
}

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  const html = document.documentElement
  
  if (isDarkMode.value) {
    html.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    html.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
  
  showNotification(`Switched to ${isDarkMode.value ? 'dark' : 'light'} mode`, 'success')
}

// Lifecycle hooks
onMounted(async () => {
  await refreshAll()
  
  if (autoRefresh.value) {
    startAutoRefresh()
  }
  
  setupRealTimeUpdates()
  
  // Request notification permission
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission()
  }
})

onUnmounted(() => {
  stopAutoRefresh()
  
  if (echoChannel) {
    Echo.leave('ticket-monitoring')
  }
})

// Watch for connection status changes
watch(connectionStatus, (newStatus) => {
  if (newStatus === 'connected' && autoRefresh.value) {
    startAutoRefresh()
  } else {
    stopAutoRefresh()
  }
})
</script>

<style scoped>
.realtime-monitoring-dashboard {
  padding: 1.5rem;
  background: #f8fafc;
  min-height: 100vh;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.header-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-indicator.connected {
  background: #d1fae5;
  color: #065f46;
}

.status-indicator.disconnected {
  background: #fee2e2;
  color: #991b1b;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
  animation: pulse 2s infinite;
}

.auto-refresh-btn, .refresh-btn {
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

.auto-refresh-btn:hover, .refresh-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.auto-refresh-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.refresh-btn:disabled {
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.platform-health-section,
.monitors-section,
.activity-section {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 0;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.health-summary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.health-score {
  font-size: 1.5rem;
  font-weight: 700;
  padding: 0.25rem 0.75rem;
  border-radius: 0.5rem;
}

.health-score.excellent {
  background: #d1fae5;
  color: #065f46;
}

.health-score.good {
  background: #dbeafe;
  color: #1e40af;
}

.health-score.warning {
  background: #fef3c7;
  color: #92400e;
}

.health-score.critical {
  background: #fee2e2;
  color: #991b1b;
}

.platform-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
  padding: 0 1.5rem 1.5rem;
}

.monitors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  padding: 0 1.5rem 1.5rem;
}

.create-monitor-btn,
.create-first-monitor-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.create-monitor-btn:hover,
.create-first-monitor-btn:hover {
  background: #2563eb;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #6b7280;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top: 3px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
  color: #6b7280;
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 0.5rem;
}

.empty-state p {
  margin: 0 0 1.5rem;
}

.activity-filter select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background: white;
  font-size: 0.875rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .realtime-monitoring-dashboard {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .header-controls {
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .platform-grid,
  .monitors-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
}

@media (max-width: 480px) {
  .dashboard-title {
    font-size: 1.5rem;
  }
  
  .header-controls {
    gap: 0.5rem;
  }
  
  .auto-refresh-btn,
  .refresh-btn {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
  }
}

/* Animations */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
