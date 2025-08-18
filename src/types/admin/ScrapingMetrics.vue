<template>
  <div class="scraping-metrics bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
      Scraping Metrics
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="metric-card text-center p-4 bg-gray-50 dark:bg-gray-700 rounded">
        <div class="text-2xl font-bold text-blue-600">{{ totalScrapes || 0 }}</div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Total Scrapes</div>
      </div>
      <div class="metric-card text-center p-4 bg-gray-50 dark:bg-gray-700 rounded">
        <div class="text-2xl font-bold text-green-600">{{ successRate || 0 }}%</div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Success Rate</div>
      </div>
      <div class="metric-card text-center p-4 bg-gray-50 dark:bg-gray-700 rounded">
        <div class="text-2xl font-bold text-red-600">{{ errorCount || 0 }}</div>
        <div class="text-sm text-gray-600 dark:text-gray-400">Errors</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ScrapingMetrics',
  
  props: {
    totalScrapes: {
      type: Number,
      default: 0
    },
    successRate: {
      type: Number,
      default: 0
    },
    errorCount: {
      type: Number,
      default: 0
    }
  }
}
</script>

<style scoped>
.metric-card {
  transition: transform 0.2s;
}

.metric-card:hover {
  transform: translateY(-2px);
}
</style>
