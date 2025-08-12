<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Scraper Header -->
    <div class="bg-gradient-to-r from-orange-500 via-red-500 to-pink-600 relative overflow-hidden">
      <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:28px_28px]"></div>
      
      <div class="relative z-10 px-6 py-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
          <div class="flex items-center space-x-4">
            <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg">
              <CpuChipIcon class="w-10 h-10 text-white" />
            </div>
            <div>
              <h1 class="text-4xl font-bold text-white mb-2">
                Scraper Control Center
              </h1>
              <p class="text-white/90 text-lg">Platform Monitoring & Data Collection Hub</p>
              <div class="flex items-center space-x-6 mt-3 text-sm text-white/80">
                <div class="flex items-center">
                  <GlobeAltIcon class="w-4 h-4 mr-2" />
                  {{ activePlatforms.length }} Platforms Active
                </div>
                <div class="flex items-center">
                  <ClockIcon class="w-4 h-4 mr-2" />
                  Uptime: {{ systemUptime }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <!-- Scraping Status -->
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
              <div class="flex items-center space-x-3">
                <div :class="[
                  'w-3 h-3 rounded-full',
                  scrapingStatus === 'active' ? 'bg-green-400 animate-pulse' : 
                  scrapingStatus === 'paused' ? 'bg-yellow-400' : 'bg-red-400'
                ]"></div>
                <div>
                  <div class="text-white font-medium capitalize">{{ scrapingStatus }}</div>
                  <div class="text-white/70 text-sm">{{ currentJobsCount }} jobs running</div>
                </div>
              </div>
            </div>
            
            <!-- Control Buttons -->
            <div class="flex space-x-2">
              <button 
                @click="toggleScrapingStatus"
                :disabled="isUpdatingStatus"
                :class="[
                  'px-4 py-2 rounded-lg font-medium transition-all duration-300',
                  scrapingStatus === 'active' 
                    ? 'bg-yellow-500 hover:bg-yellow-600 text-white' 
                    : 'bg-green-500 hover:bg-green-600 text-white',
                  { 'opacity-50 cursor-not-allowed': isUpdatingStatus }
                ]"
              >
                <PauseIcon v-if="scrapingStatus === 'active'" class="w-4 h-4 inline mr-1" />
                <PlayIcon v-else class="w-4 h-4 inline mr-1" />
                {{ scrapingStatus === 'active' ? 'Pause All' : 'Resume All' }}
              </button>
              
              <button 
                @click="emergencyStop"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300"
              >
                <StopIcon class="w-4 h-4 inline mr-1" />
                Emergency Stop
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="px-6 py-8 space-y-8">
      <!-- Key Performance Indicators -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <KPICard
          v-for="kpi in performanceKPIs"
          :key="kpi.id"
          :title="kpi.title"
          :value="kpi.value"
          :change="kpi.change"
          :trend="kpi.trend"
          :icon="kpi.icon"
          :color="kpi.color"
          :loading="isLoading"
          :target="kpi.target"
        />
      </div>

      <!-- Scraping Job Status & Platform Health -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Active Scraping Jobs -->
        <div class="xl:col-span-2">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-red-50 dark:from-gray-700 dark:to-gray-600">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Scraping Jobs</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ activeJobs.length }} jobs currently running</p>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="refreshJobs"
                    class="text-sm text-orange-600 hover:text-orange-700 dark:text-orange-400"
                  >
                    <ArrowPathIcon class="w-4 h-4 inline mr-1" />
                    Refresh
                  </button>
                </div>
              </div>
            </div>
            
            <div class="p-6">
              <ScrapingJobsList 
                :jobs="activeJobs"
                :loading="isLoading"
                @pause-job="handlePauseJob"
                @stop-job="handleStopJob"
                @view-logs="handleViewLogs"
                @adjust-frequency="handleAdjustFrequency"
              />
            </div>
          </div>
        </div>

        <!-- Platform Health Indicators -->
        <div class="xl:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden h-full">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-600">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Platform Health</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Real-time status monitoring</p>
                </div>
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <HeartIcon class="w-5 h-5 text-white" />
                </div>
              </div>
            </div>
            
            <div class="p-6">
              <PlatformHealthIndicators 
                :platforms="platformHealth"
                :loading="isLoading"
                @test-connection="handleTestConnection"
                @view-details="handleViewPlatformDetails"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Error Logs & Performance Metrics -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Error Logs & Debugging -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-red-50 to-pink-50 dark:from-gray-700 dark:to-gray-600">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Error Logs & Debugging</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ recentErrors.length }} recent issues</p>
              </div>
              <div class="flex items-center space-x-2">
                <select 
                  v-model="errorLogFilter"
                  class="text-sm border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:border-gray-600"
                >
                  <option value="all">All Errors</option>
                  <option value="critical">Critical</option>
                  <option value="warning">Warning</option>
                  <option value="info">Info</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <ErrorLogsPanel 
              :errors="filteredErrors"
              :loading="isLoading"
              @view-error="handleViewError"
              @resolve-error="handleResolveError"
              @export-logs="handleExportLogs"
            />
          </div>
        </div>

        <!-- Performance Metrics Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance Metrics</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">System performance over time</p>
              </div>
              <div class="flex items-center space-x-2">
                <select 
                  v-model="metricsTimeframe"
                  class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600"
                >
                  <option value="1h">Last Hour</option>
                  <option value="6h">Last 6 Hours</option>
                  <option value="24h">Last 24 Hours</option>
                  <option value="7d">Last 7 Days</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <PerformanceChart 
              :data="performanceData"
              :timeframe="metricsTimeframe"
              :loading="isLoading"
            />
          </div>
        </div>
      </div>

      <!-- Configuration Management & Data Output -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Configuration Management -->
        <ConfigurationPanel 
          :config="scraperConfiguration"
          :loading="isLoading"
          @update-config="handleUpdateConfig"
          @reset-config="handleResetConfig"
          @import-config="handleImportConfig"
          @export-config="handleExportConfig"
        />

        <!-- Data Output & Export -->
        <DataOutputPanel 
          :recentExports="recentExports"
          :dataStats="dataStats"
          :loading="isLoading"
          @create-export="handleCreateExport"
          @schedule-export="handleScheduleExport"
          @download-export="handleDownloadExport"
        />
      </div>

      <!-- System Resource Usage -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-violet-50 dark:from-gray-700 dark:to-gray-600">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Resource Usage</h3>
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Real-time system monitoring</p>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-500">
                Last updated: {{ lastResourceUpdate }}
              </div>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <SystemResourceMonitor 
            :resources="systemResources"
            :loading="isLoading"
            @alert-threshold="handleResourceAlert"
          />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <JobLogsModal 
      v-if="selectedJob"
      :job="selectedJob"
      :visible="showJobLogsModal"
      :logs="jobLogs"
      @close="showJobLogsModal = false"
      @download-logs="handleDownloadJobLogs"
    />

    <ErrorDetailsModal 
      v-if="selectedError"
      :error="selectedError"
      :visible="showErrorModal"
      @close="showErrorModal = false"
      @resolve="handleResolveError"
      @create-issue="handleCreateIssue"
    />

    <PlatformDetailsModal 
      v-if="selectedPlatform"
      :platform="selectedPlatform"
      :visible="showPlatformModal"
      @close="showPlatformModal = false"
      @update-settings="handleUpdatePlatformSettings"
    />

    <!-- Notifications -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useQuery, useQueryClient } from '@tanstack/vue-query'
import { 
  CpuChipIcon,
  GlobeAltIcon,
  ClockIcon,
  PlayIcon,
  PauseIcon,
  StopIcon,
  ArrowPathIcon,
  HeartIcon
} from '@heroicons/vue/24/outline'

// Composables
import { useNotifications } from '@composables/useNotifications'
import { useWebSocket } from '@composables/useWebSocket'
import { useScraperData } from '@composables/useScraperData'

// Components
import KPICard from '@components/ui/KPICard.vue'
import ScrapingJobsList from '@components/scraper/ScrapingJobsList.vue'
import PlatformHealthIndicators from '@components/scraper/PlatformHealthIndicators.vue'
import ErrorLogsPanel from '@components/scraper/ErrorLogsPanel.vue'
import PerformanceChart from '@components/scraper/PerformanceChart.vue'
import ConfigurationPanel from '@components/scraper/ConfigurationPanel.vue'
import DataOutputPanel from '@components/scraper/DataOutputPanel.vue'
import SystemResourceMonitor from '@components/scraper/SystemResourceMonitor.vue'
import JobLogsModal from '@components/scraper/JobLogsModal.vue'
import ErrorDetailsModal from '@components/scraper/ErrorDetailsModal.vue'
import PlatformDetailsModal from '@components/scraper/PlatformDetailsModal.vue'
import NotificationContainer from '@components/ui/NotificationContainer.vue'

// Composables
const { showNotification } = useNotifications()
const { socket } = useWebSocket()
const queryClient = useQueryClient()

// Scraper data composable
const {
  performanceKPIs,
  activeJobs,
  activePlatforms,
  platformHealth,
  recentErrors,
  performanceData,
  scraperConfiguration,
  recentExports,
  dataStats,
  systemResources
} = useScraperData()

// Reactive state
const scrapingStatus = ref('active')
const currentJobsCount = ref(0)
const systemUptime = ref('2d 14h 32m')
const isLoading = ref(false)
const isUpdatingStatus = ref(false)
const errorLogFilter = ref('all')
const metricsTimeframe = ref('6h')
const lastResourceUpdate = ref('')

// Modal state
const showJobLogsModal = ref(false)
const showErrorModal = ref(false)
const showPlatformModal = ref(false)
const selectedJob = ref(null)
const selectedError = ref(null)
const selectedPlatform = ref(null)
const jobLogs = ref([])

// Dashboard data queries
const { data: scraperData, refetch: refetchScraperData } = useQuery({
  queryKey: ['scraper-dashboard'],
  queryFn: async () => {
    const response = await fetch('/api/scraper/dashboard')
    if (!response.ok) throw new Error('Failed to fetch scraper dashboard data')
    return response.json()
  },
  refetchInterval: 10000, // Refetch every 10 seconds for real-time updates
})

const { data: jobsData, refetch: refreshJobs } = useQuery({
  queryKey: ['scraper-jobs'],
  queryFn: async () => {
    const response = await fetch('/api/scraper/jobs')
    if (!response.ok) throw new Error('Failed to fetch scraping jobs')
    return response.json()
  },
  refetchInterval: 5000, // Refetch every 5 seconds
})

// Computed properties
const filteredErrors = computed(() => {
  if (errorLogFilter.value === 'all') return recentErrors.value
  return recentErrors.value.filter(error => error.level === errorLogFilter.value)
})

// Methods
const updateSystemUptime = () => {
  const startTime = new Date('2025-01-28T10:00:00Z') // Example start time
  const now = new Date()
  const diff = now - startTime
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  
  systemUptime.value = `${days}d ${hours}h ${minutes}m`
}

const updateLastResourceUpdate = () => {
  lastResourceUpdate.value = new Date().toLocaleTimeString()
}

const toggleScrapingStatus = async () => {
  isUpdatingStatus.value = true
  try {
    const newStatus = scrapingStatus.value === 'active' ? 'paused' : 'active'
    
    await fetch('/api/scraper/status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: newStatus })
    })
    
    scrapingStatus.value = newStatus
    showNotification(
      `Scraping ${newStatus === 'active' ? 'resumed' : 'paused'}`,
      newStatus === 'active' ? 'success' : 'warning'
    )
    
    await refetchScraperData()
  } catch (error) {
    showNotification('Failed to update scraping status', 'error')
  } finally {
    isUpdatingStatus.value = false
  }
}

const emergencyStop = async () => {
  if (confirm('Are you sure you want to perform an emergency stop? This will halt all scraping operations immediately.')) {
    try {
      await fetch('/api/scraper/emergency-stop', { method: 'POST' })
      scrapingStatus.value = 'stopped'
      currentJobsCount.value = 0
      showNotification('Emergency stop executed', 'warning')
      await refetchScraperData()
    } catch (error) {
      showNotification('Failed to execute emergency stop', 'error')
    }
  }
}

const handlePauseJob = async (jobId) => {
  try {
    await fetch(`/api/scraper/jobs/${jobId}/pause`, { method: 'POST' })
    showNotification('Job paused', 'success')
    await refreshJobs()
  } catch (error) {
    showNotification('Failed to pause job', 'error')
  }
}

const handleStopJob = async (jobId) => {
  try {
    await fetch(`/api/scraper/jobs/${jobId}/stop`, { method: 'POST' })
    showNotification('Job stopped', 'success')
    await refreshJobs()
  } catch (error) {
    showNotification('Failed to stop job', 'error')
  }
}

const handleViewLogs = async (job) => {
  selectedJob.value = job
  try {
    const response = await fetch(`/api/scraper/jobs/${job.id}/logs`)
    jobLogs.value = await response.json()
    showJobLogsModal.value = true
  } catch (error) {
    showNotification('Failed to fetch job logs', 'error')
  }
}

const handleAdjustFrequency = async (jobId, frequency) => {
  try {
    await fetch(`/api/scraper/jobs/${jobId}/frequency`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ frequency })
    })
    showNotification('Job frequency updated', 'success')
    await refreshJobs()
  } catch (error) {
    showNotification('Failed to update job frequency', 'error')
  }
}

const handleTestConnection = async (platformId) => {
  try {
    const response = await fetch(`/api/scraper/platforms/${platformId}/test`)
    const result = await response.json()
    showNotification(
      `Connection test ${result.success ? 'passed' : 'failed'}`,
      result.success ? 'success' : 'error'
    )
  } catch (error) {
    showNotification('Failed to test connection', 'error')
  }
}

const handleViewPlatformDetails = (platform) => {
  selectedPlatform.value = platform
  showPlatformModal.value = true
}

const handleViewError = (error) => {
  selectedError.value = error
  showErrorModal.value = true
}

const handleResolveError = async (errorId) => {
  try {
    await fetch(`/api/scraper/errors/${errorId}/resolve`, { method: 'POST' })
    showNotification('Error marked as resolved', 'success')
    await refetchScraperData()
  } catch (error) {
    showNotification('Failed to resolve error', 'error')
  }
}

const handleExportLogs = async () => {
  try {
    const response = await fetch('/api/scraper/logs/export')
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `scraper_logs_${new Date().toISOString().slice(0, 10)}.json`
    a.click()
    window.URL.revokeObjectURL(url)
    showNotification('Logs exported successfully', 'success')
  } catch (error) {
    showNotification('Failed to export logs', 'error')
  }
}

const handleUpdateConfig = async (config) => {
  try {
    await fetch('/api/scraper/config', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(config)
    })
    showNotification('Configuration updated', 'success')
    await refetchScraperData()
  } catch (error) {
    showNotification('Failed to update configuration', 'error')
  }
}

const handleResetConfig = async () => {
  if (confirm('Are you sure you want to reset the configuration to defaults?')) {
    try {
      await fetch('/api/scraper/config/reset', { method: 'POST' })
      showNotification('Configuration reset to defaults', 'success')
      await refetchScraperData()
    } catch (error) {
      showNotification('Failed to reset configuration', 'error')
    }
  }
}

const handleImportConfig = async (configFile) => {
  try {
    const formData = new FormData()
    formData.append('config', configFile)
    
    await fetch('/api/scraper/config/import', {
      method: 'POST',
      body: formData
    })
    showNotification('Configuration imported successfully', 'success')
    await refetchScraperData()
  } catch (error) {
    showNotification('Failed to import configuration', 'error')
  }
}

const handleExportConfig = async () => {
  try {
    const response = await fetch('/api/scraper/config/export')
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `scraper_config_${new Date().toISOString().slice(0, 10)}.json`
    a.click()
    window.URL.revokeObjectURL(url)
    showNotification('Configuration exported successfully', 'success')
  } catch (error) {
    showNotification('Failed to export configuration', 'error')
  }
}

const handleCreateExport = async (exportConfig) => {
  try {
    await fetch('/api/scraper/exports', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(exportConfig)
    })
    showNotification('Export created successfully', 'success')
    await refetchScraperData()
  } catch (error) {
    showNotification('Failed to create export', 'error')
  }
}

const handleScheduleExport = async (scheduleConfig) => {
  try {
    await fetch('/api/scraper/exports/schedule', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(scheduleConfig)
    })
    showNotification('Export scheduled successfully', 'success')
  } catch (error) {
    showNotification('Failed to schedule export', 'error')
  }
}

const handleDownloadExport = async (exportId) => {
  try {
    const response = await fetch(`/api/scraper/exports/${exportId}/download`)
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `export_${exportId}.csv`
    a.click()
    window.URL.revokeObjectURL(url)
    showNotification('Export downloaded successfully', 'success')
  } catch (error) {
    showNotification('Failed to download export', 'error')
  }
}

const handleResourceAlert = (resource) => {
  showNotification(
    `Resource alert: ${resource.name} is at ${resource.usage}%`,
    'warning'
  )
}

const handleDownloadJobLogs = async (jobId) => {
  try {
    const response = await fetch(`/api/scraper/jobs/${jobId}/logs/download`)
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `job_${jobId}_logs.txt`
    a.click()
    window.URL.revokeObjectURL(url)
    showNotification('Job logs downloaded', 'success')
  } catch (error) {
    showNotification('Failed to download logs', 'error')
  }
}

const handleCreateIssue = async (errorId, issueData) => {
  try {
    await fetch(`/api/scraper/errors/${errorId}/create-issue`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(issueData)
    })
    showNotification('Issue created successfully', 'success')
  } catch (error) {
    showNotification('Failed to create issue', 'error')
  }
}

const handleUpdatePlatformSettings = async (platformId, settings) => {
  try {
    await fetch(`/api/scraper/platforms/${platformId}/settings`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(settings)
    })
    showNotification('Platform settings updated', 'success')
    showPlatformModal.value = false
    await refetchScraperData()
  } catch (error) {
    showNotification('Failed to update platform settings', 'error')
  }
}

// WebSocket event handlers
const setupWebSocketListeners = () => {
  if (socket.value) {
    socket.value.on('scraper-job-started', (data) => {
      showNotification(`Scraping job started: ${data.jobName}`, 'info')
      refreshJobs()
    })

    socket.value.on('scraper-job-completed', (data) => {
      showNotification(`Scraping job completed: ${data.jobName}`, 'success')
      refreshJobs()
    })

    socket.value.on('scraper-job-failed', (data) => {
      showNotification(`Scraping job failed: ${data.jobName}`, 'error')
      refreshJobs()
    })

    socket.value.on('platform-status-changed', (data) => {
      refetchScraperData()
    })

    socket.value.on('resource-alert', (data) => {
      handleResourceAlert(data)
    })

    socket.value.on('error-detected', (error) => {
      showNotification(`New error detected: ${error.message}`, 'error')
      refetchScraperData()
    })
  }
}

// Lifecycle hooks
onMounted(() => {
  setupWebSocketListeners()
  updateSystemUptime()
  updateLastResourceUpdate()
  
  // Update uptime every minute
  const uptimeInterval = setInterval(updateSystemUptime, 60000)
  
  // Update resource timestamp every 30 seconds
  const resourceInterval = setInterval(updateLastResourceUpdate, 30000)
  
  onUnmounted(() => {
    clearInterval(uptimeInterval)
    clearInterval(resourceInterval)
  })
})
</script>

<style scoped>
.bg-grid-white\/\[0\.05\] {
  background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
}

/* Status indicator animations */
.status-active {
  animation: pulse-green 2s infinite;
}

.status-paused {
  animation: pulse-yellow 2s infinite;
}

.status-stopped {
  animation: pulse-red 2s infinite;
}

@keyframes pulse-green {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes pulse-yellow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes pulse-red {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Loading states for job cards */
.job-card-loading {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

/* Emergency button pulse */
.emergency-button {
  animation: emergency-pulse 2s infinite;
}

@keyframes emergency-pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
  50% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
}
</style>
