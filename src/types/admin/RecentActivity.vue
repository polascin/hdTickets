<template>
  <div class="recent-activity bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
    
    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
    
    <div v-else-if="activities.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      No recent activity
    </div>
    
    <div v-else class="space-y-4">
      <div v-for="activity in activities.slice(0, 10)" :key="activity.id" 
           class="activity-item flex items-start space-x-3 pb-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
        <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
        <div class="flex-1">
          <div class="text-sm font-medium text-gray-900 dark:text-white">
            {{ activity.description }}
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ formatTime(activity.created_at) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RecentActivity',
  props: {
    activities: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    formatTime(timestamp) {
      return new Date(timestamp).toLocaleTimeString()
    }
  }
}
</script>
