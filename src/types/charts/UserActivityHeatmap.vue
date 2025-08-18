<template>
  <div class="user-activity-heatmap bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Activity Heatmap</h3>
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
    </div>
    <div v-else class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">
      User activity heatmap will be displayed here
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserActivityHeatmap',
  props: {
    data: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  }
}
</script>
