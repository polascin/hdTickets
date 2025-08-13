import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAnalyticsStore = defineStore('analytics', () => {
  // State
  const loading = ref(false)
  const error = ref(null)
  const dateRange = ref({ start: null, end: null })
  const selectedMetrics = ref(['sales', 'revenue', 'users'])
  const dashboardData = ref({})
  const chartData = ref({})
  const reports = ref([])
  const filters = ref({
    platform: 'all',
    sport: 'all',
    region: 'all',
    dateRange: '30d'
  })

  // Analytics data
  const metrics = ref({
    totalSales: 0,
    totalRevenue: 0,
    totalUsers: 0,
    conversionRate: 0,
    avgOrderValue: 0,
    returnUsers: 0
  })

  const salesData = ref([])
  const revenueData = ref([])
  const userGrowthData = ref([])
  const platformPerformance = ref([])
  const topEvents = ref([])

  // Computed properties
  const isLoading = computed(() => loading.value)
  const hasError = computed(() => error.value !== null)
  
  const totalSales = computed(() => metrics.value.totalSales)
  const totalRevenue = computed(() => metrics.value.totalRevenue)
  const totalUsers = computed(() => metrics.value.totalUsers)
  const conversionRate = computed(() => metrics.value.conversionRate)

  const salesTrend = computed(() => {
    if (salesData.value.length < 2) return 0
    const current = salesData.value[salesData.value.length - 1]?.value || 0
    const previous = salesData.value[salesData.value.length - 2]?.value || 0
    return previous === 0 ? 0 : ((current - previous) / previous) * 100
  })

  const revenueTrend = computed(() => {
    if (revenueData.value.length < 2) return 0
    const current = revenueData.value[revenueData.value.length - 1]?.value || 0
    const previous = revenueData.value[revenueData.value.length - 2]?.value || 0
    return previous === 0 ? 0 : ((current - previous) / previous) * 100
  })

  const bestPerformingPlatform = computed(() => {
    if (!platformPerformance.value.length) return null
    return platformPerformance.value.reduce((best, current) => 
      current.revenue > (best?.revenue || 0) ? current : best
    )
  })

  const topPerformingEvents = computed(() => {
    return topEvents.value.slice(0, 5)
  })

  // Actions
  const fetchAnalyticsData = async (options = {}) => {
    loading.value = true
    error.value = null

    try {
      const params = {
        ...filters.value,
        ...options
      }

      const response = await axios.get('/api/analytics/dashboard', { params })

      if (response.data.success) {
        const data = response.data.data
        
        metrics.value = { ...metrics.value, ...data.metrics }
        salesData.value = data.sales || []
        revenueData.value = data.revenue || []
        userGrowthData.value = data.userGrowth || []
        platformPerformance.value = data.platformPerformance || []
        topEvents.value = data.topEvents || []
        dashboardData.value = data.dashboard || {}
        chartData.value = data.charts || {}
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch analytics data'
      console.error('Analytics data fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchMetrics = async (metricType, timeRange = '30d') => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/analytics/metrics/${metricType}`, {
        params: { range: timeRange, ...filters.value }
      })

      if (response.data.success) {
        const data = response.data.data
        
        switch (metricType) {
          case 'sales':
            salesData.value = data
            break
          case 'revenue':
            revenueData.value = data
            break
          case 'users':
            userGrowthData.value = data
            break
          case 'platforms':
            platformPerformance.value = data
            break
        }
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || `Failed to fetch ${metricType} metrics`
      console.error('Metrics fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchEventAnalytics = async (eventId, options = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/analytics/events/${eventId}`, {
        params: options
      })

      if (response.data.success) {
        return response.data.data
      }

      return null
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch event analytics'
      console.error('Event analytics fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const fetchPlatformAnalytics = async (platform, timeRange = '30d') => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/analytics/platforms/${platform}`, {
        params: { range: timeRange }
      })

      if (response.data.success) {
        return response.data.data
      }

      return null
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch platform analytics'
      console.error('Platform analytics fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const generateReport = async (reportType, options = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post('/api/analytics/reports/generate', {
        type: reportType,
        filters: filters.value,
        ...options
      })

      if (response.data.success) {
        const report = response.data.report
        reports.value.unshift(report)
        return { success: true, report }
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

  const exportData = async (format = 'xlsx', dataType = 'all') => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post('/api/analytics/export', {
        format,
        type: dataType,
        filters: filters.value
      }, {
        responseType: 'blob'
      })

      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `analytics_${dataType}_${new Date().toISOString().split('T')[0]}.${format}`)
      document.body.appendChild(link)
      link.click()
      link.remove()

      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to export data'
      console.error('Analytics export error:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const fetchReports = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get('/api/analytics/reports')

      if (response.data.success) {
        reports.value = response.data.reports
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch reports'
      console.error('Reports fetch error:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
  }

  const setDateRange = (start, end) => {
    dateRange.value = { start, end }
    filters.value.dateRange = 'custom'
  }

  const setSelectedMetrics = (metrics) => {
    selectedMetrics.value = metrics
  }

  const refreshData = async () => {
    await Promise.all([
      fetchAnalyticsData(),
      fetchReports()
    ])
  }

  const clearError = () => {
    error.value = null
  }

  const resetFilters = () => {
    filters.value = {
      platform: 'all',
      sport: 'all',
      region: 'all',
      dateRange: '30d'
    }
    dateRange.value = { start: null, end: null }
  }

  // Utility functions
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value)
  }

  const formatNumber = (value, decimals = 0) => {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }).format(value)
  }

  const formatPercentage = (value, decimals = 1) => {
    return `${Number(value).toFixed(decimals)}%`
  }

  const getTrendColor = (trend) => {
    if (trend > 0) return 'text-green-500'
    if (trend < 0) return 'text-red-500'
    return 'text-gray-500'
  }

  const getTrendIcon = (trend) => {
    if (trend > 0) return '↗️'
    if (trend < 0) return '↘️'
    return '➡️'
  }

  return {
    // State
    loading,
    error,
    dateRange,
    selectedMetrics,
    dashboardData,
    chartData,
    reports,
    filters,
    metrics,
    salesData,
    revenueData,
    userGrowthData,
    platformPerformance,
    topEvents,

    // Computed
    isLoading,
    hasError,
    totalSales,
    totalRevenue,
    totalUsers,
    conversionRate,
    salesTrend,
    revenueTrend,
    bestPerformingPlatform,
    topPerformingEvents,

    // Actions
    fetchAnalyticsData,
    fetchMetrics,
    fetchEventAnalytics,
    fetchPlatformAnalytics,
    generateReport,
    exportData,
    fetchReports,
    setFilters,
    setDateRange,
    setSelectedMetrics,
    refreshData,
    clearError,
    resetFilters,

    // Utilities
    formatCurrency,
    formatNumber,
    formatPercentage,
    getTrendColor,
    getTrendIcon
  }
})
