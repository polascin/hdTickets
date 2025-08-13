import { ref, computed } from 'vue'

export function useCustomerData() {
  const customerData = ref({})
  const recentEvents = ref([])
  const savedSearches = ref([])
  const priceAlerts = ref([])
  const purchaseHistory = ref([])
  const recommendations = ref([])
  const notificationPreferences = ref({})
  const isLoading = ref(false)

  const hasActiveAlerts = computed(() => priceAlerts.value.filter(alert => alert.active).length > 0)
  const recentEventsCount = computed(() => recentEvents.value.length)

  const fetchCustomerData = async () => {
    isLoading.value = true
    try {
      // Placeholder implementation
      await new Promise(resolve => setTimeout(resolve, 1000))
      customerData.value = { name: 'Customer', email: 'customer@example.com' }
    } catch (error) {
      console.error('Error fetching customer data:', error)
    } finally {
      isLoading.value = false
    }
  }

  return {
    customerData,
    recentEvents,
    savedSearches,
    priceAlerts,
    purchaseHistory,
    recommendations,
    notificationPreferences,
    isLoading,
    hasActiveAlerts,
    recentEventsCount,
    fetchCustomerData
  }
}
