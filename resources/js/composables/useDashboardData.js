import { ref, computed } from 'vue'
import axios from 'axios'

export function useDashboardData() {
  const loading = ref(false)
  const error = ref(null)
  const dashboardData = ref({
    stats: {
      totalUsers: 0,
      totalTickets: 0,
      totalRevenue: 0,
      activeAlerts: 0
    },
    charts: {
      userGrowth: [],
      ticketSales: [],
      revenueChart: [],
      platformPerformance: []
    },
    recentActivity: [],
    systemHealth: {
      database: 'healthy',
      redis: 'healthy',
      queue: 'healthy',
      scraping: 'healthy'
    }
  })

  // Computed properties
  const totalUsers = computed(() => dashboardData.value.stats.totalUsers)
  const totalTickets = computed(() => dashboardData.value.stats.totalTickets)
  const totalRevenue = computed(() => dashboardData.value.stats.totalRevenue)
  const activeAlerts = computed(() => dashboardData.value.stats.activeAlerts)
  
  const systemHealthStatus = computed(() => {
    const health = dashboardData.value.systemHealth
    const statuses = Object.values(health)
    
    if (statuses.every(status => status === 'healthy')) return 'healthy'
    if (statuses.some(status => status === 'critical')) return 'critical'
    return 'warning'
  })

  const recentActivityItems = computed(() => {
    return dashboardData.value.recentActivity.slice(0, 10)
  })

  const chartData = computed(() => dashboardData.value.charts)

  // Methods
  const fetchDashboardData = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/admin/dashboard')
      
      if (response.data.success) {
        dashboardData.value = { ...dashboardData.value, ...response.data.data }
      }
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard data'
      console.error('Dashboard data fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchStats = async (timeRange = '30d') => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`/api/admin/stats?range=${timeRange}`)
      
      if (response.data.success) {
        dashboardData.value.stats = { ...dashboardData.value.stats, ...response.data.stats }
      }
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch statistics'
      console.error('Stats fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchChartData = async (chartType, timeRange = '30d') => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`/api/admin/charts/${chartType}?range=${timeRange}`)
      
      if (response.data.success) {
        dashboardData.value.charts[chartType] = response.data.data
      }
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || `Failed to fetch ${chartType} chart data`
      console.error('Chart data fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchSystemHealth = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/admin/system-health')
      
      if (response.data.success) {
        dashboardData.value.systemHealth = { ...dashboardData.value.systemHealth, ...response.data.health }
      }
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch system health'
      console.error('System health fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchRecentActivity = async (limit = 10) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`/api/admin/activity?limit=${limit}`)
      
      if (response.data.success) {
        dashboardData.value.recentActivity = response.data.activities
      }
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch recent activity'
      console.error('Recent activity fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const generateReport = async (reportType, options = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post('/api/admin/reports/generate', {
        type: reportType,
        ...options
      })
      
      if (response.data.success) {
        return { 
          success: true, 
          report: response.data.report,
          downloadUrl: response.data.downloadUrl 
        }
      }
      
      return { success: false, message: 'Failed to generate report' }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to generate report'
      console.error('Report generation error:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const exportDashboardData = async (format = 'xlsx') => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`/api/admin/export/dashboard?format=${format}`, {
        responseType: 'blob'
      })
      
      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `dashboard_export_${new Date().toISOString().split('T')[0]}.${format}`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to export dashboard data'
      console.error('Dashboard export error:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const refreshAllData = async () => {
    loading.value = true
    
    try {
      await Promise.all([
        fetchDashboardData(),
        fetchSystemHealth(),
        fetchRecentActivity()
      ])
    } catch (err) {
      console.error('Error refreshing dashboard data:', err)
    }
  }

  const updateStatsPeriodically = (intervalMs = 30000) => {
    const interval = setInterval(async () => {
      await fetchStats()
      await fetchSystemHealth()
    }, intervalMs)
    
    return () => clearInterval(interval)
  }

  // Utility functions
  const clearError = () => {
    error.value = null
  }

  const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value)
  }

  const formatNumber = (value) => {
    return new Intl.NumberFormat('en-US').format(value)
  }

  const formatPercentage = (value, decimals = 1) => {
    return `${Number(value).toFixed(decimals)}%`
  }

  const getHealthColor = (status) => {
    switch (status) {
      case 'healthy': return 'text-green-500'
      case 'warning': return 'text-yellow-500'
      case 'critical': return 'text-red-500'
      default: return 'text-gray-500'
    }
  }

  const getHealthIcon = (status) => {
    switch (status) {
      case 'healthy': return '✅'
      case 'warning': return '⚠️'
      case 'critical': return '🚨'
      default: return '❓'
    }
  }

  return {
    // State
    loading,
    error,
    dashboardData,
    
    // Computed
    totalUsers,
    totalTickets,
    totalRevenue,
    activeAlerts,
    systemHealthStatus,
    recentActivityItems,
    chartData,
    
    // Methods
    fetchDashboardData,
    fetchStats,
    fetchChartData,
    fetchSystemHealth,
    fetchRecentActivity,
    generateReport,
    exportDashboardData,
    refreshAllData,
    updateStatsPeriodically,
    clearError,
    
    // Utilities
    formatCurrency,
    formatNumber,
    formatPercentage,
    getHealthColor,
    getHealthIcon
  }
}
