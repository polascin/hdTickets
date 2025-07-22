<template>
  <div class="admin-dashboard">
    <!-- Header with Real-time Stats -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Administrative Dashboard</h1>
        <div class="flex items-center space-x-4">
          <div class="text-sm text-gray-500">
            Last updated: {{ lastUpdated }}
          </div>
          <button @click="refreshData" 
                  :disabled="loading"
                  class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            <svg v-if="loading" class="animate-spin h-4 w-4 mr-2 inline" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/>
              <path fill="currentColor" opacity="0.75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            {{ loading ? 'Refreshing...' : 'Refresh' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Real-time Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatCard 
        v-for="stat in stats" 
        :key="stat.key"
        :title="stat.title"
        :value="stat.value"
        :change="stat.change"
        :icon="stat.icon"
        :color="stat.color"
        :trend="stat.trend"
      />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Ticket Status Distribution</h3>
        <canvas ref="statusChart"></canvas>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Monthly Ticket Trends</h3>
        <canvas ref="trendChart"></canvas>
      </div>
    </div>

    <!-- Management Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <ManagementCard
        v-for="module in managementModules"
        :key="module.key"
        :title="module.title"
        :description="module.description"
        :stats="module.stats"
        :route="module.route"
        :color="module.color"
        :icon="module.icon"
        :permissions="module.permissions"
        @action="handleModuleAction"
      />
    </div>

    <!-- System Health Monitor -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
      <h3 class="text-lg font-semibold mb-4">System Health Monitor</h3>
      <SystemHealth :health-data="systemHealth" />
    </div>

    <!-- Recent Activity Feed -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold mb-4">Recent System Activity</h3>
      <ActivityFeed :activities="recentActivities" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Chart, registerables } from 'chart.js'
import StatCard from './components/StatCard.vue'
import ManagementCard from './components/ManagementCard.vue'
import SystemHealth from './components/SystemHealth.vue'
import ActivityFeed from './components/ActivityFeed.vue'

Chart.register(...registerables)

// Reactive data
const loading = ref(false)
const lastUpdated = ref(new Date().toLocaleString())
const stats = ref([])
const systemHealth = ref({})
const recentActivities = ref([])
const statusChart = ref(null)
const trendChart = ref(null)

// Management modules configuration
const managementModules = ref([
  {
    key: 'users',
    title: 'User Management',
    description: 'Manage users, roles, and permissions',
    route: '/admin/users',
    color: 'blue',
    icon: 'users',
    permissions: ['manage_users'],
    stats: { total: 0, active: 0, new: 0 }
  },
  {
    key: 'tickets',
    title: 'Ticket Management',
    description: 'Manage tickets, assignments, and workflows',
    route: '/admin/tickets',
    color: 'green',
    icon: 'ticket',
    permissions: ['manage_all_tickets'],
    stats: { total: 0, open: 0, overdue: 0 }
  },
  {
    key: 'scraping',
    title: 'Scraping Operations',
    description: 'Control ticket scraping and platform management',
    route: '/admin/scraping',
    color: 'purple',
    icon: 'cog',
    permissions: ['access_scraping'],
    stats: { platforms: 0, active: 0, errors: 0 }
  },
  {
    key: 'analytics',
    title: 'Analytics & Reports',
    description: 'System insights and performance metrics',
    route: '/admin/reports',
    color: 'yellow',
    icon: 'chart',
    permissions: ['access_monitoring'],
    stats: { reports: 0, exports: 0, alerts: 0 }
  },
  {
    key: 'system',
    title: 'System Configuration',
    description: 'Configure system settings and preferences',
    route: '/admin/system',
    color: 'red',
    icon: 'settings',
    permissions: ['manage_system'],
    stats: { configs: 0, services: 0, logs: 0 }
  },
  {
    key: 'api',
    title: 'API Management',
    description: 'Manage API access and integrations',
    route: '/admin/api',
    color: 'indigo',
    icon: 'code',
    permissions: ['manage_api_access'],
    stats: { endpoints: 0, requests: 0, keys: 0 }
  }
])

// Methods
const refreshData = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchStats(),
      fetchSystemHealth(),
      fetchRecentActivities(),
      updateCharts()
    ])
    lastUpdated.value = new Date().toLocaleString()
  } catch (error) {
    console.error('Error refreshing data:', error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/admin/stats.json')
    stats.value = [
      {
        key: 'total_tickets',
        title: 'Total Tickets',
        value: response.data.tickets.total,
        change: response.data.tickets.change,
        icon: 'document',
        color: 'blue',
        trend: response.data.tickets.trend
      },
      {
        key: 'open_tickets',
        title: 'Open Tickets',
        value: response.data.tickets.open,
        change: response.data.tickets.open_change,
        icon: 'clock',
        color: 'yellow',
        trend: response.data.tickets.open_trend
      },
      {
        key: 'active_users',
        title: 'Active Users',
        value: response.data.users.active,
        change: response.data.users.change,
        icon: 'users',
        color: 'green',
        trend: response.data.users.trend
      },
      {
        key: 'system_health',
        title: 'System Health',
        value: response.data.system.health + '%',
        change: response.data.system.change,
        icon: 'heart',
        color: response.data.system.health > 90 ? 'green' : 'red',
        trend: response.data.system.trend
      }
    ]
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const fetchSystemHealth = async () => {
  try {
    const response = await axios.get('/admin/system/health')
    systemHealth.value = response.data
  } catch (error) {
    console.error('Error fetching system health:', error)
  }
}

const fetchRecentActivities = async () => {
  try {
    const response = await axios.get('/admin/activities/recent')
    recentActivities.value = response.data
  } catch (error) {
    console.error('Error fetching activities:', error)
  }
}

const updateCharts = async () => {
  try {
    const [statusData, trendData] = await Promise.all([
      axios.get('/admin/chart/status.json'),
      axios.get('/admin/chart/monthly-trend.json')
    ])

    // Update status chart
    if (statusChart.value) {
      statusChart.value.destroy()
    }
    createStatusChart(statusData.data)

    // Update trend chart
    if (trendChart.value) {
      trendChart.value.destroy()
    }
    createTrendChart(trendData.data)
  } catch (error) {
    console.error('Error updating charts:', error)
  }
}

const createStatusChart = (data) => {
  const ctx = document.querySelector('canvas[ref="statusChart"]')
  statusChart.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.map(item => item.label),
      datasets: [{
        data: data.map(item => item.value),
        backgroundColor: data.map(item => item.color),
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })
}

const createTrendChart = (data) => {
  const ctx = document.querySelector('canvas[ref="trendChart"]')
  trendChart.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => item.month),
      datasets: [{
        label: 'Tickets',
        data: data.map(item => item.tickets),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  })
}

const handleModuleAction = (module) => {
  window.location.href = module.route
}

// Lifecycle
onMounted(() => {
  refreshData()
  
  // Set up auto-refresh every 30 seconds
  setInterval(refreshData, 30000)
})
</script>

<style scoped>
.admin-dashboard {
  @apply min-h-screen bg-gray-50 p-6;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
