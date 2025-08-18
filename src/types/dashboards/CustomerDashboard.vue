<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Customer Header -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-600 to-red-500 relative overflow-hidden">
      <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:30px_30px]"></div>
      
      <div class="relative z-10 px-6 py-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
          <div class="flex items-center space-x-4">
            <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl border border-white/30 shadow-lg">
              <TicketIcon class="w-10 h-10 text-white" />
            </div>
            <div>
              <h1 class="text-4xl font-bold text-white mb-2">
                Welcome, {{ userStore.user?.name }}! 🎟️
              </h1>
              <p class="text-white/90 text-lg">Your Sports Ticket Hub</p>
              <div class="flex items-center space-x-6 mt-3 text-sm text-white/80">
                <div class="flex items-center">
                  <MapPinIcon class="w-4 h-4 mr-2" />
                  {{ userLocation || 'Location not set' }}
                </div>
                <div class="flex items-center">
                  <HeartIcon class="w-4 h-4 mr-2" />
                  {{ favoriteTeams.length }} Favorite Teams
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <!-- Live Feed Status -->
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
              <div class="flex items-center space-x-3">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                <div>
                  <div class="text-white font-medium">Live Feed</div>
                  <div class="text-white/70 text-sm">{{ liveTicketCount }} new tickets</div>
                </div>
              </div>
            </div>
            
            <!-- Quick Search -->
            <div class="relative">
              <input
                v-model="quickSearchQuery"
                @input="handleQuickSearch"
                type="text"
                placeholder="Quick search events..."
                class="bg-white/20 backdrop-blur-sm border border-white/30 text-white placeholder-white/70 px-4 py-2 rounded-lg w-64 focus:outline-none focus:ring-2 focus:ring-white/50"
              />
              <MagnifyingGlassIcon class="absolute right-3 top-2.5 w-5 h-5 text-white/70" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="px-6 py-8 space-y-8">
      <!-- Personal Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          v-for="stat in personalStats"
          :key="stat.id"
          :title="stat.title"
          :value="stat.value"
          :change="stat.change"
          :trend="stat.trend"
          :icon="stat.icon"
          :color="stat.color"
          :loading="isLoading"
        />
      </div>

      <!-- Personalized Recommendations & Quick Actions -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Personalized Event Recommendations -->
        <div class="xl:col-span-2">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-700 dark:to-gray-600">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recommended For You</h3>
                  <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Based on your preferences and activity</p>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="refreshRecommendations"
                    class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400"
                  >
                    <ArrowPathIcon class="w-4 h-4 inline mr-1" />
                    Refresh
                  </button>
                </div>
              </div>
            </div>
            
            <div class="p-6">
              <EventRecommendations 
                :events="recommendedEvents"
                :loading="isLoading"
                @event-click="handleEventClick"
                @save-event="handleSaveEvent"
                @create-alert="handleCreateAlert"
              />
            </div>
          </div>
        </div>

        <!-- Quick Actions & Shortcuts -->
        <div class="xl:col-span-1 space-y-6">
          <!-- Quick Actions -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-600">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
            </div>
            <div class="p-6">
              <QuickActionGrid 
                :actions="quickActions"
                @action-click="handleQuickAction"
              />
            </div>
          </div>

          <!-- My Alerts Summary -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-gray-700 dark:to-gray-600">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Alerts</h3>
                <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                  {{ activeAlerts.length }} active
                </span>
              </div>
            </div>
            <div class="p-6">
              <AlertsSummary 
                :alerts="activeAlerts"
                @view-all="navigateToAlerts"
                @toggle-alert="handleToggleAlert"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Saved Searches & Price Tracking -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Saved Searches -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-700 dark:to-gray-600">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Saved Searches</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ savedSearches.length }} saved</p>
              </div>
              <button
                @click="showSaveSearchModal = true"
                class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/50 px-3 py-1.5 rounded-md"
              >
                <PlusIcon class="w-4 h-4 inline mr-1" />
                New Search
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <SavedSearches 
              :searches="savedSearches"
              :loading="isLoading"
              @execute-search="handleExecuteSearch"
              @edit-search="handleEditSearch"
              @delete-search="handleDeleteSearch"
            />
          </div>
        </div>

        <!-- Price Tracking Charts -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-gray-700 dark:to-gray-600">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Price Tracking</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Tracking {{ trackedEvents.length }} events</p>
              </div>
              <div class="flex items-center space-x-2">
                <select 
                  v-model="priceTrackingPeriod"
                  class="text-sm border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-700 dark:border-gray-600"
                >
                  <option value="7d">7 days</option>
                  <option value="30d">30 days</option>
                  <option value="90d">90 days</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <PriceTrackingChart 
              :data="priceTrackingData"
              :period="priceTrackingPeriod"
              :loading="isLoading"
              @event-select="handlePriceTrackingEventSelect"
            />
          </div>
        </div>
      </div>

      <!-- Purchase History & Notification Preferences -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Purchase History -->
        <PurchaseHistory 
          :purchases="recentPurchases"
          :loading="isLoading"
          @view-details="handleViewPurchaseDetails"
          @reorder="handleReorder"
        />

        <!-- Notification Preferences Quick Panel -->
        <NotificationPreferences 
          :preferences="notificationPreferences"
          @update="handleUpdateNotificationPreferences"
        />
      </div>

      <!-- Trending & Popular Events -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-teal-50 to-cyan-50 dark:from-gray-700 dark:to-gray-600">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Trending & Popular Events</h3>
              <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">What's hot right now</p>
            </div>
            <div class="flex items-center space-x-2">
              <button
                v-for="category in trendingCategories"
                :key="category"
                @click="selectedTrendingCategory = category"
                :class="[
                  'px-3 py-1.5 rounded-md text-sm font-medium transition-colors',
                  selectedTrendingCategory === category
                    ? 'bg-teal-600 text-white'
                    : 'bg-teal-100 text-teal-700 hover:bg-teal-200 dark:bg-teal-900 dark:text-teal-300'
                ]"
              >
                {{ category }}
              </button>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <TrendingEvents 
            :events="trendingEvents"
            :category="selectedTrendingCategory"
            :loading="isLoading"
            @event-click="handleEventClick"
            @create-alert="handleCreateAlert"
          />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <SaveSearchModal 
      :visible="showSaveSearchModal"
      @close="showSaveSearchModal = false"
      @save="handleSaveSearch"
    />

    <EventDetailsModal 
      v-if="selectedEvent"
      :event="selectedEvent"
      :visible="showEventModal"
      @close="showEventModal = false"
      @create-alert="handleCreateAlert"
      @purchase="handlePurchase"
    />

    <!-- Notifications -->
    <NotificationContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useQuery, useQueryClient } from '@tanstack/vue-query'
import { 
  TicketIcon,
  MapPinIcon,
  HeartIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

// Composables
import { useUserStore } from '@stores/user'
import { useNotifications } from '@composables/useNotifications'
import { useWebSocket } from '@composables/useWebSocket'
import { useCustomerData } from '@composables/useCustomerData'
import { useGeolocation } from '@vueuse/core'

// Components
import StatCard from '@components/ui/StatCard.vue'
import EventRecommendations from '@components/customer/EventRecommendations.vue'
import QuickActionGrid from '@components/customer/QuickActionGrid.vue'
import AlertsSummary from '@components/customer/AlertsSummary.vue'
import SavedSearches from '@components/customer/SavedSearches.vue'
import PriceTrackingChart from '@components/customer/PriceTrackingChart.vue'
import PurchaseHistory from '@components/customer/PurchaseHistory.vue'
import NotificationPreferences from '@components/customer/NotificationPreferences.vue'
import TrendingEvents from '@components/customer/TrendingEvents.vue'
import SaveSearchModal from '@components/customer/SaveSearchModal.vue'
import EventDetailsModal from '@components/customer/EventDetailsModal.vue'
import NotificationContainer from '@components/ui/NotificationContainer.vue'

// Stores
const userStore = useUserStore()
const { user } = storeToRefs(userStore)

// Composables
const { showNotification } = useNotifications()
const { socket } = useWebSocket()
const queryClient = useQueryClient()
const { coords } = useGeolocation()

// Customer data composable
const {
  personalStats,
  recommendedEvents,
  quickActions,
  activeAlerts,
  savedSearches,
  trackedEvents,
  priceTrackingData,
  recentPurchases,
  notificationPreferences,
  trendingEvents,
  favoriteTeams
} = useCustomerData()

// Reactive state
const quickSearchQuery = ref('')
const liveTicketCount = ref(0)
const isLoading = ref(false)
const priceTrackingPeriod = ref('30d')
const selectedTrendingCategory = ref('All')
const trendingCategories = ref(['All', 'Football', 'Basketball', 'Baseball', 'Hockey', 'Soccer', 'Concert'])
const showSaveSearchModal = ref(false)
const showEventModal = ref(false)
const selectedEvent = ref(null)

// Computed
const userLocation = computed(() => {
  if (coords.value.latitude && coords.value.longitude) {
    return `${coords.value.latitude.toFixed(2)}, ${coords.value.longitude.toFixed(2)}`
  }
  return user.value?.location || null
})

// Dashboard data queries
const { data: customerData, refetch: refetchCustomerData } = useQuery({
  queryKey: ['customer-dashboard'],
  queryFn: async () => {
    const response = await fetch('/api/customer/dashboard')
    if (!response.ok) throw new Error('Failed to fetch customer dashboard data')
    return response.json()
  },
  refetchInterval: 30000, // Refetch every 30 seconds
})

const { data: recommendationsData, refetch: refreshRecommendations } = useQuery({
  queryKey: ['customer-recommendations'],
  queryFn: async () => {
    const response = await fetch('/api/customer/recommendations')
    if (!response.ok) throw new Error('Failed to fetch recommendations')
    return response.json()
  },
  refetchInterval: 300000, // Refetch every 5 minutes
})

// Methods
const handleQuickSearch = async () => {
  if (quickSearchQuery.value.length > 2) {
    // Implement quick search functionality
    console.log('Quick search:', quickSearchQuery.value)
  }
}

const handleEventClick = (event) => {
  selectedEvent.value = event
  showEventModal.value = true
}

const handleSaveEvent = async (eventId) => {
  try {
    await fetch(`/api/customer/events/${eventId}/save`, { method: 'POST' })
    showNotification('Event saved to favorites', 'success')
  } catch (error) {
    showNotification('Failed to save event', 'error')
  }
}

const handleCreateAlert = async (alertData) => {
  try {
    await fetch('/api/customer/alerts', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(alertData)
    })
    showNotification('Alert created successfully', 'success')
    refetchCustomerData()
  } catch (error) {
    showNotification('Failed to create alert', 'error')
  }
}

const handleQuickAction = (action) => {
  console.log('Quick action:', action)
  // Handle quick action execution based on action type
  switch (action.type) {
    case 'browse_tickets':
      // Navigate to ticket browser
      break
    case 'my_alerts':
      navigateToAlerts()
      break
    case 'price_drops':
      // Show price drops
      break
    default:
      console.log('Unknown quick action:', action)
  }
}

const handleToggleAlert = async (alertId, enabled) => {
  try {
    await fetch(`/api/customer/alerts/${alertId}/toggle`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ enabled })
    })
    refetchCustomerData()
  } catch (error) {
    showNotification('Failed to toggle alert', 'error')
  }
}

const handleExecuteSearch = (search) => {
  console.log('Execute search:', search)
  // Navigate to search results with saved search parameters
}

const handleEditSearch = (search) => {
  console.log('Edit search:', search)
  // Show edit search modal
}

const handleDeleteSearch = async (searchId) => {
  try {
    await fetch(`/api/customer/searches/${searchId}`, { method: 'DELETE' })
    refetchCustomerData()
    showNotification('Search deleted', 'success')
  } catch (error) {
    showNotification('Failed to delete search', 'error')
  }
}

const handleSaveSearch = async (searchData) => {
  try {
    await fetch('/api/customer/searches', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(searchData)
    })
    refetchCustomerData()
    showSaveSearchModal.value = false
    showNotification('Search saved successfully', 'success')
  } catch (error) {
    showNotification('Failed to save search', 'error')
  }
}

const handlePriceTrackingEventSelect = (event) => {
  console.log('Price tracking event selected:', event)
  // Show detailed price tracking for specific event
}

const handleViewPurchaseDetails = (purchase) => {
  console.log('View purchase details:', purchase)
  // Navigate to purchase details page
}

const handleReorder = async (purchaseId) => {
  try {
    await fetch(`/api/customer/purchases/${purchaseId}/reorder`, { method: 'POST' })
    showNotification('Order placed successfully', 'success')
  } catch (error) {
    showNotification('Failed to reorder', 'error')
  }
}

const handleUpdateNotificationPreferences = async (preferences) => {
  try {
    await fetch('/api/customer/notification-preferences', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(preferences)
    })
    showNotification('Preferences updated', 'success')
  } catch (error) {
    showNotification('Failed to update preferences', 'error')
  }
}

const handlePurchase = (event) => {
  console.log('Purchase event:', event)
  // Navigate to purchase flow
}

const navigateToAlerts = () => {
  console.log('Navigate to alerts')
  // Navigate to alerts management page
}

// WebSocket event handlers
const setupWebSocketListeners = () => {
  if (socket.value) {
    socket.value.on('new-ticket-found', (data) => {
      liveTicketCount.value++
      if (data.matchesPreferences) {
        showNotification(`New ticket found: ${data.event}`, 'info')
      }
    })

    socket.value.on('price-drop-alert', (data) => {
      showNotification(`Price drop: ${data.event} - Now $${data.newPrice}`, 'success')
    })

    socket.value.on('ticket-alert-triggered', (data) => {
      showNotification(data.message, 'success')
    })

    socket.value.on('recommendation-update', () => {
      refreshRecommendations()
    })
  }
}

// Lifecycle hooks
onMounted(() => {
  setupWebSocketListeners()
})
</script>

<style scoped>
.bg-grid-white\/\[0\.05\] {
  background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
}

/* Custom scrollbar for recommendation sections */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Animate new ticket notifications */
@keyframes ticketAlert {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.ticket-alert {
  animation: ticketAlert 0.3s ease-in-out;
}

/* Recommendation card hover effects */
.recommendation-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.recommendation-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
