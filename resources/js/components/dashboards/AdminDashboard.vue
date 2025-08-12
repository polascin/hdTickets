<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Enhanced Header -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:20px_20px]"></div>
      
      <div class="relative z-10 px-6 py-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
          <div class="flex items-center space-x-4">
            <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg">
              <ChartBarIcon class="w-10 h-10 text-white" />
            </div>
            <div>
              <h1 class="text-4xl font-bold text-white mb-2">
                Welcome back, {{ userStore.user?.name }}!
              </h1>
              <p class="text-white/90 text-lg">Sports Ticket Management Dashboard</p>
              <div class="flex items-center space-x-6 mt-3 text-sm text-white/80">
                <div class="flex items-center">
                  <CalendarIcon class="w-4 h-4 mr-2" />
                  {{ formatDate(new Date(), 'EEEE, MMMM d, yyyy') }}
                </div>
                <div class="flex items-center">
                  <ClockIcon class="w-4 h-4 mr-2" />
                  <span>{{ currentTime }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <!-- System Health Indicator -->
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 min-w-[200px] text-center border border-white/20">
              <div class="text-white/80 text-sm mb-2">System Health</div>
              <div class="relative w-20 h-20 mx-auto mb-3">
                <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 36 36">
                  <path 
                    class="text-white/20" 
                    stroke="currentColor" 
                    stroke-width="3" 
                    fill="none" 
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  />
                  <path 
                    class="text-green-400" 
                    stroke="currentColor" 
                    stroke-width="3" 
                    fill="none" 
                    :stroke-dasharray="`${systemHealth}, 100`" 
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-2xl font-bold text-white">{{ systemHealth }}%</span>
                </div>
              </div>
              <div class="flex items-center justify-center">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                <span class="text-xs text-green-200">{{ systemStatus }}</span>
              </div>
            </div>
            
            <!-- Refresh Button -->
            <button 
              @click="refreshDashboard"
              :disabled="isRefreshing"
              class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-6 py-3 text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50"
              :title="isRefreshing ? 'Refreshing...' : 'Refresh Dashboard'"
            >
              <ArrowPathIcon 
                :class="['w-5 h-5 inline mr-2', { 'animate-spin': isRefreshing }]"
              />
              <span class="hidden sm:inline">{{ isRefreshing ? 'Refreshing...' : 'Refresh' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-8 space-y-8">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          v-for="stat in mainStats"
          :key="stat.id"
          :title="stat.title"
          :value="stat.value"
          :change="stat.change"
          :trend="stat.trend"
          :icon="stat.icon"
          :color="stat.color"
          :loading="isLoading"
          @click="handleStatClick(stat)"
        />
      </div>

      <!-- Management Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <ManagementCard
          v-for="card in managementCards"
          :key="card.id"
          :title="card.title"
          :description="card.description"
          :stats="card.stats"
          :actions="card.actions"
          :color="card.color"
          :icon="card.icon"
        />
      </div>

      <!-- Analytics Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Real-time Scraping Statistics -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Real-time Scraping Statistics</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Live ticket scraping performance</p>
              </div>
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <ChartBarIcon class="w-5 h-5 text-white" />
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <ScrapingMetrics :metrics="scrapingMetrics" :loading="isLoading" />
          </div>
        </div>

        <!-- Platform Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-gray-700 dark:to-gray-600">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Platform Performance</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Ticketmaster, StubHub, SeatGeek metrics</p>
              </div>
              <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">
                <BoltIcon class="w-5 h-5 text-white" />
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <PlatformMetrics :platforms="platformMetrics" :loading="isLoading" />
          </div>
        </div>
      </div>

      <!-- User Activity & Revenue Analytics -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Analytics -->
        <RevenueChart :data="revenueData" :loading="isLoading" />
        
        <!-- User Activity Heatmap -->
        <UserActivityHeatmap :data="activityData" :loading="isLoading" />
      </div>

      <!-- Alert Management System -->
      <AlertManagement 
        :alerts="alertsData" 
        :loading="isLoading"
        @refresh="fetchAlerts"
        @dismiss="handleAlertDismiss"
        @escalate="handleAlertEscalate"
      />

      <!-- Recent Activity Log -->
      <RecentActivity 
        v-if="recentActivity.length > 0"
        :activities="recentActivity"
        :loading="isLoading"
      />
    </div>

    <!-- Loading Overlay -->
    <LoadingOverlay v-if="isInitialLoading" />

    <!-- Error Notifications -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuery, useQueryClient } from '@tanstack/vue-query'
import { formatDate } from 'date-fns'
import { 
  ChartBarIcon, 
  CalendarIcon, 
  ClockIcon, 
  ArrowPathIcon,
  BoltIcon
} from '@heroicons/vue/24/outline'

// Composables
import { useUserStore } from '@stores/user'
import { useNotifications } from '@composables/useNotifications'
import { useWebSocket } from '@composables/useWebSocket'
import { useTheme } from '@composables/useTheme'
import { useDashboardData } from '@composables/useDashboardData'

// Components
import StatCard from '@components/ui/StatCard.vue'
import ManagementCard from '@components/ui/ManagementCard.vue'
import ScrapingMetrics from '@components/admin/ScrapingMetrics.vue'
import PlatformMetrics from '@components/admin/PlatformMetrics.vue'
import RevenueChart from '@components/charts/RevenueChart.vue'
import UserActivityHeatmap from '@components/charts/UserActivityHeatmap.vue'
import AlertManagement from '@components/admin/AlertManagement.vue'
import RecentActivity from '@components/admin/RecentActivity.vue'
import LoadingOverlay from '@components/ui/LoadingOverlay.vue'
import NotificationContainer from '@components/ui/NotificationContainer.vue'

// Stores
const userStore = useUserStore()
const { user } = storeToRefs(userStore)

// Composables
const { showNotification } = useNotifications()
const { socket } = useWebSocket()
const { isDarkMode } = useTheme()
const queryClient = useQueryClient()

// Dashboard data composable
const {
  mainStats,
  managementCards,
  scrapingMetrics,
  platformMetrics,
  revenueData,
  activityData,
  alertsData,
  recentActivity,
  systemHealth,
  systemStatus
} = useDashboardData()

// Reactive state
const currentTime = ref('')
const isRefreshing = ref(false)
const isInitialLoading = ref(true)

// Computed
const isLoading = computed(() => isInitialLoading.value || isRefreshing.value)

// Dashboard data queries
const { data: dashboardData, isLoading: dashboardLoading, refetch: refetchDashboard } = useQuery({
  queryKey: ['admin-dashboard'],
  queryFn: async () => {
    const response = await fetch('/api/admin/dashboard')
    if (!response.ok) throw new Error('Failed to fetch dashboard data')
    return response.json()
  },
  refetchInterval: 30000, // Refetch every 30 seconds
  staleTime: 10000 // Consider data stale after 10 seconds
})

const { data: alertsQuery, refetch: fetchAlerts } = useQuery({
  queryKey: ['admin-alerts'],
  queryFn: async () => {
    const response = await fetch('/api/admin/alerts')
    if (!response.ok) throw new Error('Failed to fetch alerts')
    return response.json()
  },
  refetchInterval: 15000
})

// Methods
const updateCurrentTime = () => {
  currentTime.value = new Date().toLocaleTimeString()
}

const refreshDashboard = async () => {
  isRefreshing.value = true
  try {
    await Promise.all([
      refetchDashboard(),
      fetchAlerts(),
      queryClient.invalidateQueries({ queryKey: ['admin-dashboard'] })
    ])
    showNotification('Dashboard refreshed successfully', 'success')
  } catch (error) {
    console.error('Failed to refresh dashboard:', error)
    showNotification('Failed to refresh dashboard', 'error')
  } finally {
    isRefreshing.value = false
  }
}

const handleStatClick = (stat) => {
  // Navigate to detailed view based on stat type
  console.log('Stat clicked:', stat)
  // Implementation for navigation
}

const handleAlertDismiss = async (alertId) => {
  try {
    await fetch(`/api/admin/alerts/${alertId}/dismiss`, { method: 'POST' })
    await fetchAlerts()
    showNotification('Alert dismissed successfully', 'success')
  } catch (error) {
    showNotification('Failed to dismiss alert', 'error')
  }
}

const handleAlertEscalate = async (alertId) => {
  try {
    await fetch(`/api/admin/alerts/${alertId}/escalate`, { method: 'POST' })
    await fetchAlerts()
    showNotification('Alert escalated successfully', 'success')
  } catch (error) {
    showNotification('Failed to escalate alert', 'error')
  }
}

// WebSocket event handlers
const setupWebSocketListeners = () => {
  if (socket.value) {
    socket.value.on('dashboard-update', (data) => {
      // Update dashboard data from WebSocket
      queryClient.setQueryData(['admin-dashboard'], data)
    })

    socket.value.on('system-health-update', (data) => {
      systemHealth.value = data.health
      systemStatus.value = data.status
    })

    socket.value.on('alert-created', () => {
      fetchAlerts()
    })
  }
}

// Lifecycle hooks
onMounted(async () => {
  updateCurrentTime()
  const timeInterval = setInterval(updateCurrentTime, 1000)
  
  setupWebSocketListeners()
  
  // Wait for initial data to load
  try {
    await refetchDashboard()
  } finally {
    isInitialLoading.value = false
  }
  
  onUnmounted(() => {
    clearInterval(timeInterval)
  })
})

// Keyboard shortcuts
const handleKeydown = (event) => {
  if ((event.ctrlKey || event.metaKey) && event.key === 'r') {
    event.preventDefault()
    refreshDashboard()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.bg-grid-white\/\[0\.05\] {
  background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
}

/* Loading shimmer effect for cards */
.loading-shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Hover effects */
.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Dark mode adjustments */
.dark .bg-grid-white\/\[0\.05\] {
  background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
}
</style>
