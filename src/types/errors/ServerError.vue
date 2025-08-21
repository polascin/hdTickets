<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
      <div class="mb-8">
        <h1 class="text-9xl font-bold text-red-300">500</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mt-4">Server Error</h2>
        <p class="text-gray-600 mt-2">
          Something went wrong on our servers. We're working to fix the issue.
        </p>
      </div>
      
      <div class="space-y-4">
        <button 
          @click="refresh"
          class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Try Again
        </button>
        
        <div>
          <router-link 
            to="/" 
            class="inline-flex items-center px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Go Home
          </router-link>
        </div>
      </div>
      
      <!-- Error Details (development only) -->
      <div v-if="isDevelopment && errorDetails" class="mt-8 p-4 bg-red-50 rounded-lg text-left">
        <h3 class="text-sm font-medium text-red-800 mb-2">Error Details</h3>
        <pre class="text-xs text-red-600 whitespace-pre-wrap">{{ errorDetails }}</pre>
      </div>
      
      <!-- Support Information -->
      <div class="mt-8 pt-8 border-t border-gray-200">
        <h3 class="text-sm font-medium text-gray-800 mb-4">Need Help?</h3>
        <p class="text-sm text-gray-600 mb-4">
          If this problem persists, please contact our support team.
        </p>
        <div class="space-y-2">
          <p class="text-xs text-gray-500">
            Error ID: {{ errorId }}
          </p>
          <p class="text-xs text-gray-500">
            Time: {{ new Date().toLocaleString() }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'ServerError',
  props: {
    error: {
      type: [Error, Object],
      default: null
    }
  },
  setup(props) {
    const errorId = ref(generateErrorId())
    const errorDetails = ref('')
    
    const isDevelopment = computed(() => {
      return import.meta.env.MODE === 'development' || window.Laravel?.env === 'local'
    })
    
    const refresh = () => {
      window.location.reload()
    }
    
    function generateErrorId() {
      return Math.random().toString(36).substr(2, 9).toUpperCase()
    }
    
    onMounted(() => {
      if (props.error && isDevelopment.value) {
        errorDetails.value = props.error.message || props.error.toString()
        
        // Log error details for debugging
        console.error('Server Error:', props.error)
      }
      
      // Report error to monitoring service (if available)
      if (window.Sentry) {
        window.Sentry.captureException(props.error || new Error('Server Error 500'), {
          tags: {
            errorId: errorId.value,
            page: 'ServerError'
          }
        })
      }
    })
    
    return {
      errorId,
      errorDetails,
      isDevelopment,
      refresh
    }
  }
}
</script>
