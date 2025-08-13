import { ref, computed } from 'vue'
import axios from 'axios'

export function useAgentData() {
  const loading = ref(false)
  const error = ref(null)
  const tickets = ref([])
  const stats = ref({
    totalTickets: 0,
    successfulPurchases: 0,
    pendingAlerts: 0,
    todayEarnings: 0
  })
  const alerts = ref([])
  const purchaseQueue = ref([])

  // Computed properties
  const successRate = computed(() => {
    if (stats.value.totalTickets === 0) return 0
    return Math.round((stats.value.successfulPurchases / stats.value.totalTickets) * 100)
  })

  const pendingAlertsCount = computed(() => {
    return alerts.value.filter(alert => alert.status === 'pending').length
  })

  const criticalAlerts = computed(() => {
    return alerts.value.filter(alert => alert.priority === 'high' && alert.status === 'pending')
  })

  const todaysTickets = computed(() => {
    const today = new Date().toDateString()
    return tickets.value.filter(ticket => {
      return new Date(ticket.created_at).toDateString() === today
    })
  })

  // Methods
  const fetchAgentDashboard = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/agent/dashboard')
      
      if (response.data.success) {
        tickets.value = response.data.tickets || []
        stats.value = { ...stats.value, ...response.data.stats }
        alerts.value = response.data.alerts || []
        purchaseQueue.value = response.data.purchaseQueue || []
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard data'
      console.error('Agent dashboard fetch error:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchTickets = async (filters = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/agent/tickets', { params: filters })
      tickets.value = response.data.tickets || []
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch tickets'
      console.error('Tickets fetch error:', err)
      return { tickets: [], meta: {} }
    } finally {
      loading.value = false
    }
  }

  const updateTicketStatus = async (ticketId, status, notes = '') => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.put(`/api/agent/tickets/${ticketId}/status`, {
        status,
        notes
      })
      
      if (response.data.success) {
        // Update local ticket
        const ticketIndex = tickets.value.findIndex(t => t.id === ticketId)
        if (ticketIndex !== -1) {
          tickets.value[ticketIndex] = { ...tickets.value[ticketIndex], ...response.data.ticket }
        }
        return { success: true }
      }
      
      return { success: false, message: 'Failed to update ticket status' }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update ticket status'
      console.error('Ticket status update error:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const fetchAlerts = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/agent/alerts')
      alerts.value = response.data.alerts || []
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch alerts'
      console.error('Alerts fetch error:', err)
      return { alerts: [] }
    } finally {
      loading.value = false
    }
  }

  const handleAlert = async (alertId, action, notes = '') => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post(`/api/agent/alerts/${alertId}/handle`, {
        action,
        notes
      })
      
      if (response.data.success) {
        // Update local alert
        const alertIndex = alerts.value.findIndex(a => a.id === alertId)
        if (alertIndex !== -1) {
          if (action === 'dismiss') {
            alerts.value.splice(alertIndex, 1)
          } else {
            alerts.value[alertIndex] = { ...alerts.value[alertIndex], ...response.data.alert }
          }
        }
        return { success: true }
      }
      
      return { success: false, message: 'Failed to handle alert' }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to handle alert'
      console.error('Alert handling error:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const fetchPurchaseQueue = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get('/api/agent/purchase-queue')
      purchaseQueue.value = response.data.queue || []
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch purchase queue'
      console.error('Purchase queue fetch error:', err)
      return { queue: [] }
    } finally {
      loading.value = false
    }
  }

  const processPurchaseQueue = async (queueItemId, action) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post(`/api/agent/purchase-queue/${queueItemId}/process`, {
        action
      })
      
      if (response.data.success) {
        // Update local queue item
        const queueIndex = purchaseQueue.value.findIndex(q => q.id === queueItemId)
        if (queueIndex !== -1) {
          if (action === 'complete' || action === 'cancel') {
            purchaseQueue.value.splice(queueIndex, 1)
          } else {
            purchaseQueue.value[queueIndex] = { ...purchaseQueue.value[queueIndex], ...response.data.queueItem }
          }
        }
        return { success: true, data: response.data }
      }
      
      return { success: false, message: 'Failed to process purchase queue item' }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to process purchase queue item'
      console.error('Purchase queue processing error:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const generateReport = async (type, dateRange) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post('/api/agent/reports/generate', {
        type,
        date_range: dateRange
      })
      
      if (response.data.success) {
        return { success: true, report: response.data.report, downloadUrl: response.data.downloadUrl }
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

  const exportData = async (type, filters = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post('/api/agent/export', {
        type,
        filters
      }, {
        responseType: 'blob'
      })
      
      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `${type}_export_${new Date().toISOString().split('T')[0]}.xlsx`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to export data'
      console.error('Data export error:', err)
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  // Utility functions
  const refreshData = async () => {
    await Promise.all([
      fetchAgentDashboard(),
      fetchAlerts(),
      fetchPurchaseQueue()
    ])
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    loading,
    error,
    tickets,
    stats,
    alerts,
    purchaseQueue,
    
    // Computed
    successRate,
    pendingAlertsCount,
    criticalAlerts,
    todaysTickets,
    
    // Methods
    fetchAgentDashboard,
    fetchTickets,
    updateTicketStatus,
    fetchAlerts,
    handleAlert,
    fetchPurchaseQueue,
    processPurchaseQueue,
    generateReport,
    exportData,
    refreshData,
    clearError
  }
}
