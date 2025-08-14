<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Agent Header -->
    <div class="bg-gradient-to-r from-emerald-500 via-teal-600 to-cyan-600 relative overflow-hidden">
      <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:24px_24px]"></div>
      
      <div class="relative z-10 px-6 py-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
          <div class="flex items-center space-x-4">
            <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg">
              <EyeIcon class="w-10 h-10 text-white" />
            </div>
            <div>
              <h1 class="text-4xl font-bold text-white mb-2">
                Agent Monitoring Hub
              </h1>
              <p class="text-white/90 text-lg">{{ userStore.user?.name }} - Active Monitoring Dashboard</p>
              <div class="flex items-center space-x-6 mt-3 text-sm text-white/80">
                <div class="flex items-center">
                  <ClockIcon class="w-4 h-4 mr-2" />
                  <span>{{ currentTime }}</span>
                </div>
                <div class="flex items-center">
                  <UserIcon class="w-4 h-4 mr-2" />
                  Agent ID: {{ userStore.user?.id }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <!-- Active Status -->
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                <div>
                  <div class="text-white font-medium">Active</div>
                  <div class="text-white/70 text-sm">{{ getActiveTime() }}</div>
                </div>
              </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex space-x-2">
              <button 
                @click="togglePause"
                :class="[
                  'px-4 py-2 rounded-lg font-medium transition-all duration-300',
                  isPaused 
                    ? 'bg-green-500 hover:bg-green-600 text-white' 
                    : 'bg-yellow-500 hover:bg-yellow-600 text-white'
                ]"
              >
                <PlayIcon v-if="isPaused" class="w-4 h-4 inline mr-1" />
                <PauseIcon v-else class="w-4 h-4 inline mr-1" />
                {{ isPaused ? 'Resume' : 'Pause' }}
              </button>
              
              <button 
                @click="refreshData"
                :disabled="isRefreshing"
                class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-4 py-2 text-white rounded-lg transition-all duration-300 disabled:opacity-50"
              >
                <ArrowPathIcon :class="['w-4 h-4 inline', { 'animate-spin': isRefreshing }]" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="px-6 py-8 space-y-8">
      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          v-for="metric in keyMetrics"
          :key="metric.id"
          :title="metric.title"
          :value="metric.value"
          :change="metric.change"
          :trend="metric.trend"
          :icon="metric.icon"
          :color="metric.color"
          :loading="isLoading"
        />
      </div>

      <!-- Monitoring Overview & Purchase Queue -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Active Monitoring Overview -->
        <div class="xl:col-span-2">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-gray-700 dark:to-gray-600">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Monitoring Overview</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Real-time ticket monitoring status</p>
                </div>
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                  <span class="text-sm text-green-600 dark:text-green-400 font-medium">Live</span>
                </div>
              </div>
            </div>
            
            <div class="p-6">
              <ActiveMonitoring 
                :monitors="activeMonitors"
                :loading="isLoading"
                @toggle-monitor="handleToggleMonitor"
                @view-details="handleViewDetails"
              />
            </div>
          </div>
        </div>

        <!-- Purchase Queue Management -->
        <div class="xl:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden h-full">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Purchase Queue</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ purchaseQueue.length }} items</p>
                </div>
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                  <QueueListIcon class="w-5 h-5 text-white" />
                </div>
              </div>
            </div>
            
            <div class="p-6">
              <PurchaseQueue 
                :queue="purchaseQueue"
                :loading="isLoading"
                @process-item="handleProcessQueueItem"
                @remove-item="handleRemoveQueueItem"
                @reorder="handleReorderQueue"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Ticket Availability Heatmap -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-red-50 dark:from-gray-700 dark:to-gray-600">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ticket Availability Heatmap</h3>
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Current availability across all platforms</p>
            </div>
            <div class="flex items-center space-x-2">
              <button 
                @click="toggleHeatmapView"
                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
              >
                {{ heatmapView === 'platform' ? 'View by Category' : 'View by Platform' }}
              </button>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <TicketHeatmap 
            :data="heatmapData"
            :view="heatmapView"
            :loading="isLoading"
            @cell-click="handleHeatmapCellClick"
          />
        </div>
      </div>

      <!-- Performance Metrics & Quick Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Metrics -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-gray-700 dark:to-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance Metrics</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Your monitoring performance today</p>
          </div>
          
          <div class="p-6">
            <PerformanceMetrics 
              :metrics="performanceMetrics"
              :loading="isLoading"
            />
          </div>
        </div>

        <!-- Quick Actions Panel -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-cyan-50 to-blue-50 dark:from-gray-700 dark:to-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Frequently used monitoring tools</p>
          </div>
          
          <div class="p-6">
            <QuickActions 
              :actions="quickActions"
              @action-click="handleQuickAction"
            />
          </div>
        </div>
      </div>

      <!-- Recent Alerts & Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Alerts -->
        <RecentAlerts 
          :alerts="recentAlerts"
          :loading="isLoading"
          @view-all="navigateToAlerts"
          @dismiss="handleDismissAlert"
        />
        
        <!-- Recent Activity -->
        <RecentActivity 
          :activities="recentActivity"
          :loading="isLoading"
        />
      </div>
    </div>

    <!-- Modals -->
    <MonitorDetailsModal 
      v-if="selectedMonitor"
      :monitor="selectedMonitor"
      :visible="showMonitorModal"
      @close="showMonitorModal = false"
      @update="handleUpdateMonitor"
    />

    <!-- Notifications -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuery, useQueryClient } from '@tanstack/vue-query'
import { 
  EyeIcon,
  ClockIcon,
  UserIcon,
  PlayIcon,
  PauseIcon,
  ArrowPathIcon,
  QueueListIcon
} from '@heroicons/vue/24/outline'

// Composables
import { useUserStore } from '@stores/user'
import { useNotifications } from '@composables/useNotifications'
import { useWebSocket } from '@composables/useWebSocket'
import { useAgentData } from '@composables/useAgentData'

// Components
import MetricCard from '@components/ui/MetricCard.vue'
import ActiveMonitoring from '@components/agent/ActiveMonitoring.vue'
import PurchaseQueue from '@components/agent/PurchaseQueue.vue'
import TicketHeatmap from '@components/agent/TicketHeatmap.vue'
import PerformanceMetrics from '@components/agent/PerformanceMetrics.vue'
import QuickActions from '@components/agent/QuickActions.vue'
import RecentAlerts from '@components/agent/RecentAlerts.vue'
import RecentActivity from '@components/agent/RecentActivity.vue'
import MonitorDetailsModal from '@components/agent/MonitorDetailsModal.vue'
import NotificationContainer from '@components/ui/NotificationContainer.vue'

// Stores
const userStore = useUserStore()
const { user } = storeToRefs(userStore)

// Composables
const { showNotification } = useNotifications()
const { socket } = useWebSocket()
const queryClient = useQueryClient()

// Agent data composable
const {
  keyMetrics,
  activeMonitors,
  purchaseQueue,
  heatmapData,
  performanceMetrics,
  quickActions,
  recentAlerts,
  recentActivity
} = useAgentData()

// Reactive state
const currentTime = ref('')
const isPaused = ref(false)
const isRefreshing = ref(false)
const isLoading = ref(false)
const heatmapView = ref('platform') // 'platform' or 'category'
const selectedMonitor = ref(null)
const showMonitorModal = ref(false)
const agentStartTime = ref(new Date())

// Dashboard data queries
const { data: agentData, refetch: refetchAgentData } = useQuery({
  queryKey: ['agent-dashboard'],
  queryFn: async () => {
    const response = await fetch('/api/agent/dashboard')
    if (!response.ok) throw new Error('Failed to fetch agent dashboard data')
    return response.json()
  },
  refetchInterval: 5000, // Refetch every 5 seconds for real-time updates
  enabled: computed(() => !isPaused.value)
})

// Methods
const updateCurrentTime = () => {
  currentTime.value = new Date().toLocaleTimeString()
}

const getActiveTime = () => {
  const now = new Date()
  const diff = now - agentStartTime.value
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  return `${hours}h ${minutes}m`
}

const togglePause = () => {
  isPaused.value = !isPaused.value
  showNotification(
    isPaused.value ? 'Monitoring paused' : 'Monitoring resumed',
    isPaused.value ? 'warning' : 'success'
  )
}

const refreshData = async () => {
  isRefreshing.value = true
  try {
    await refetchAgentData()
    showNotification('Dashboard refreshed', 'success')
  } catch (error) {
    showNotification('Failed to refresh dashboard', 'error')
  } finally {
    isRefreshing.value = false
  }
}

const handleToggleMonitor = async (monitorId, enabled) => {
  try {
    await fetch(`/api/agent/monitors/${monitorId}/toggle`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ enabled })
    })
    await refetchAgentData()
    showNotification(
      `Monitor ${enabled ? 'enabled' : 'disabled'}`,
      enabled ? 'success' : 'warning'
    )
  } catch (error) {
    showNotification('Failed to toggle monitor', 'error')
  }
}

const handleViewDetails = (monitor) => {
  selectedMonitor.value = monitor
  showMonitorModal.value = true
}

const handleProcessQueueItem = async (itemId) => {
  try {
    await fetch(`/api/agent/queue/${itemId}/process`, {
      method: 'POST'
    })
    await refetchAgentData()
    showNotification('Queue item processed', 'success')
  } catch (error) {
    showNotification('Failed to process queue item', 'error')
  }
}

const handleRemoveQueueItem = async (itemId) => {
  try {
    await fetch(`/api/agent/queue/${itemId}`, {
      method: 'DELETE'
    })
    await refetchAgentData()
    showNotification('Queue item removed', 'success')
  } catch (error) {
    showNotification('Failed to remove queue item', 'error')
  }
}

const handleReorderQueue = async (newOrder) => {
  try {
    await fetch('/api/agent/queue/reorder', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order: newOrder })
    })
    await refetchAgentData()
    showNotification('Queue reordered', 'success')
  } catch (error) {
    showNotification('Failed to reorder queue', 'error')
  }
}

const toggleHeatmapView = () => {
  heatmapView.value = heatmapView.value === 'platform' ? 'category' : 'platform'
}

const handleHeatmapCellClick = (data) => {
  console.log('Heatmap cell clicked:', data)
  // Navigate to detailed view or show modal
}

const handleQuickAction = (action) => {
  console.log('Quick action:', action)
  // Handle quick action execution
}

const handleDismissAlert = async (alertId) => {
  try {
    await fetch(`/api/agent/alerts/${alertId}/dismiss`, {
      method: 'POST'
    })
    await refetchAgentData()
  } catch (error) {
    showNotification('Failed to dismiss alert', 'error')
  }
}

const handleUpdateMonitor = async (monitorData) => {
  try {
    await fetch(`/api/agent/monitors/${monitorData.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(monitorData)
    })
    await refetchAgentData()
    showMonitorModal.value = false
    showNotification('Monitor updated', 'success')
  } catch (error) {
    showNotification('Failed to update monitor', 'error')
  }
}

const navigateToAlerts = () => {
  // Navigate to alerts page
  console.log('Navigate to alerts')
}

// WebSocket event handlers
const setupWebSocketListeners = () => {
  if (socket.value) {
    socket.value.on('ticket-found', (data) => {
      showNotification(`New ticket found: ${data.event}`, 'success')
      refetchAgentData()
    })

    socket.value.on('monitor-status-changed', (data) => {
      refetchAgentData()
    })

    socket.value.on('queue-updated', () => {
      refetchAgentData()
    })

    socket.value.on('agent-alert', (alert) => {
      showNotification(alert.message, alert.type || 'info')
    })
  }
}

// Lifecycle hooks
onMounted(() => {
  updateCurrentTime()
  const timeInterval = setInterval(updateCurrentTime, 1000)
  
  setupWebSocketListeners()
  
  onUnmounted(() => {
    clearInterval(timeInterval)
  })
})

// Keyboard shortcuts
const handleKeydown = (event) => {
  // Space to pause/resume
  if (event.code === 'Space' && !event.target.matches('input, textarea')) {
    event.preventDefault()
    togglePause()
  }
  // F5 or Ctrl+R to refresh
  if (event.key === 'F5' || ((event.ctrlKey || event.metaKey) && event.key === 'r')) {
    event.preventDefault()
    refreshData()
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

/* Pulse animation for active status */
@keyframes pulse-green {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse-green {
  animation: pulse-green 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Hover effects for interactive elements */
.hover-lift {
  transition: all 0.2s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
