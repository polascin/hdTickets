<template>
  <div class="platform-metrics">
    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div v-for="platform in platforms" :key="platform.name" 
           class="platform-card p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <h4 class="font-semibold text-gray-900 dark:text-white">{{ platform.name }}</h4>
        <div class="mt-2 space-y-1">
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Status: <span class="text-green-600">{{ platform.status }}</span>
          </div>
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Success Rate: {{ platform.successRate }}%
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlatformMetrics',
  props: {
    platforms: {
      type: Array,
      default: () => [
        { name: 'Ticketmaster', status: 'Active', successRate: 98 },
        { name: 'StubHub', status: 'Active', successRate: 95 },
        { name: 'SeatGeek', status: 'Active', successRate: 97 }
      ]
    },
    loading: {
      type: Boolean,
      default: false
    }
  }
}
</script>
