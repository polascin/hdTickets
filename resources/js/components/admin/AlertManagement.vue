<template>
  <div class="alert-management bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Alert Management</h3>
      <button @click="$emit('refresh')" :disabled="loading" 
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
        <span v-if="!loading">Refresh</span>
        <span v-else class="inline-flex items-center">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
          Loading...
        </span>
      </button>
    </div>
    
    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
    
    <div v-else-if="alerts.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      No active alerts
    </div>
    
    <div v-else class="space-y-3">
      <div v-for="alert in alerts" :key="alert.id" 
           class="alert-item flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
        <div class="flex-1">
          <div class="font-medium text-gray-900 dark:text-white">{{ alert.title }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">{{ alert.description }}</div>
        </div>
        <div class="flex space-x-2">
          <button @click="$emit('dismiss', alert.id)" 
                  class="px-3 py-1 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
            Dismiss
          </button>
          <button @click="$emit('escalate', alert.id)" 
                  class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
            Escalate
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AlertManagement',
  props: {
    alerts: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['refresh', 'dismiss', 'escalate']
}
</script>
