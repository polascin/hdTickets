<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
      <div class="mb-8">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2v6m0 8v6m10-12H16m-8 0H2"/>
        </svg>
        <h2 class="text-2xl font-semibold text-gray-800 mt-4">You're Offline</h2>
        <p class="text-gray-600 mt-2">
          Check your internet connection and try again.
        </p>
      </div>
      
      <div class="mb-8">
        <div class="flex items-center justify-center space-x-2 mb-4">
          <div class="w-3 h-3 rounded-full" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></div>
          <span class="text-sm" :class="isOnline ? 'text-green-600' : 'text-red-600'">
            {{ isOnline ? 'Back online!' : 'Offline' }}
          </span>
        </div>
        
        <button 
          @click="checkConnection"
          :disabled="checking"
          class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50"
        >
          <svg v-if="!checking" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <svg v-else class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ checking ? 'Checking...' : 'Try Again' }}
        </button>
      </div>
      
      <!-- Offline Features -->
      <div class="bg-blue-50 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">Available Offline</h3>
        <ul class="text-sm text-blue-800 space-y-2">
          <li class="flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            View cached ticket data
          </li>
          <li class="flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            Browse saved events
          </li>
          <li class="flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            View user preferences
          </li>
        </ul>
        
        <div class="mt-4 pt-4 border-t border-blue-200">
          <button 
            @click="goToOfflineMode"
            class="inline-flex items-center text-sm text-blue-700 hover:text-blue-900"
          >
            Continue in offline mode
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Tips -->
      <div class="text-left">
        <h3 class="text-sm font-medium text-gray-800 mb-3">Connection Tips</h3>
        <ul class="text-sm text-gray-600 space-y-1">
          <li>• Check your WiFi or cellular connection</li>
          <li>• Try moving to a different location</li>
          <li>• Restart your router or modem</li>
          <li>• Contact your internet service provider</li>
        </ul>
      </div>
      
      <!-- Last sync info -->
      <div v-if="lastSync" class="mt-6 pt-6 border-t border-gray-200 text-xs text-gray-500">
        Last synced: {{ formatDate(lastSync) }}
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'OfflinePage',
  setup() {
    const router = useRouter()
    const isOnline = ref(navigator.onLine)
    const checking = ref(false)
    const lastSync = ref(localStorage.getItem('lastSync'))
    
    const updateOnlineStatus = () => {
      isOnline.value = navigator.onLine
      
      if (isOnline.value) {
        // Automatically redirect when back online
        setTimeout(() => {
          router.push('/')
        }, 1500)
      }
    }
    
    const checkConnection = async () => {
      checking.value = true
      
      try {
        // Try to fetch from the server
        const response = await fetch('/api/health', {
          method: 'HEAD',
          cache: 'no-cache'
        })
        
        if (response.ok) {
          isOnline.value = true
          router.push('/')
        }
      } catch (error) {
        isOnline.value = false
      } finally {
        checking.value = false
      }
    }
    
    const goToOfflineMode = () => {
      // Store offline mode flag
      localStorage.setItem('offlineMode', 'true')
      router.push('/')
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return 'Never'
      return new Date(dateString).toLocaleString()
    }
    
    onMounted(() => {
      window.addEventListener('online', updateOnlineStatus)
      window.addEventListener('offline', updateOnlineStatus)
      
      // Check connection periodically
      const interval = setInterval(checkConnection, 30000)
      
      onUnmounted(() => {
        window.removeEventListener('online', updateOnlineStatus)
        window.removeEventListener('offline', updateOnlineStatus)
        clearInterval(interval)
      })
    })
    
    return {
      isOnline,
      checking,
      lastSync,
      checkConnection,
      goToOfflineMode,
      formatDate
    }
  }
}
</script>
