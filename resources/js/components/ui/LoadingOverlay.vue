<template>
  <div class="loading-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="loading-content bg-white dark:bg-gray-800 rounded-lg p-8 text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
      <div class="text-lg font-medium text-gray-900 dark:text-white">Loading...</div>
      <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">Please wait while we load the dashboard</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoadingOverlay'
}
</script>

<style scoped>
.loading-overlay {
  backdrop-filter: blur(4px);
}
</style>
