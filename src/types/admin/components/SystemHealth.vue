<template>
  <div class="system-health">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="metric in healthMetrics" :key="metric.name" class="bg-gray-50 p-4 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">{{ metric.name }}</p>
            <p class="text-2xl font-bold" :class="getStatusColor(metric.status)">
              {{ metric.value }}
            </p>
          </div>
          <div :class="getStatusBadgeColor(metric.status)" class="px-2 py-1 rounded-full text-xs font-medium">
            {{ metric.status }}
          </div>
        </div>
        <div class="mt-2">
          <div class="bg-gray-200 rounded-full h-2">
            <div 
              :class="getProgressColor(metric.percentage)"
              class="h-2 rounded-full transition-all duration-300"
              :style="{ width: metric.percentage + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Services Status -->
    <div class="bg-gray-50 p-4 rounded-lg">
      <h4 class="text-lg font-semibold mb-4">Service Status</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="service in services" :key="service.name" class="flex items-center justify-between p-3 bg-white rounded border">
          <div class="flex items-center">
            <div :class="service.running ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full mr-3"></div>
            <span class="font-medium">{{ service.name }}</span>
          </div>
          <span :class="service.running ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
            {{ service.running ? 'Running' : 'Stopped' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Recent Alerts -->
    <div v-if="alerts.length > 0" class="mt-6">
      <h4 class="text-lg font-semibold mb-4">Recent Alerts</h4>
      <div class="space-y-2">
        <div v-for="alert in alerts" :key="alert.id" :class="getAlertClasses(alert.level)" class="p-3 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium">{{ alert.title }}</p>
              <p class="text-sm opacity-75">{{ alert.message }}</p>
            </div>
            <span class="text-xs">{{ formatTime(alert.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  healthData: {
    type: Object,
    default: () => ({
      cpu: { value: '45%', percentage: 45, status: 'good' },
      memory: { value: '62%', percentage: 62, status: 'warning' },
      disk: { value: '78%', percentage: 78, status: 'critical' },
      database: { value: '12ms', percentage: 88, status: 'good' },
      services: [],
      alerts: []
    })
  }
})

const healthMetrics = computed(() => [
  { name: 'CPU Usage', ...props.healthData.cpu },
  { name: 'Memory Usage', ...props.healthData.memory },
  { name: 'Disk Usage', ...props.healthData.disk },
  { name: 'DB Response', ...props.healthData.database }
])

const services = computed(() => props.healthData.services || [
  { name: 'Web Server', running: true },
  { name: 'Database', running: true },
  { name: 'Queue Worker', running: true },
  { name: 'Scheduler', running: true },
  { name: 'Cache', running: true },
  { name: 'Mail Service', running: false }
])

const alerts = computed(() => props.healthData.alerts || [])

const getStatusColor = (status) => {
  switch (status) {
    case 'good': return 'text-green-600'
    case 'warning': return 'text-yellow-600'
    case 'critical': return 'text-red-600'
    default: return 'text-gray-600'
  }
}

const getStatusBadgeColor = (status) => {
  switch (status) {
    case 'good': return 'bg-green-100 text-green-800'
    case 'warning': return 'bg-yellow-100 text-yellow-800'
    case 'critical': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getProgressColor = (percentage) => {
  if (percentage >= 90) return 'bg-red-500'
  if (percentage >= 70) return 'bg-yellow-500'
  return 'bg-green-500'
}

const getAlertClasses = (level) => {
  switch (level) {
    case 'error': return 'bg-red-50 border border-red-200'
    case 'warning': return 'bg-yellow-50 border border-yellow-200'
    case 'info': return 'bg-blue-50 border border-blue-200'
    default: return 'bg-gray-50 border border-gray-200'
  }
}

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString()
}
</script>
