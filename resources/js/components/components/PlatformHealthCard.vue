<template>
  <div class="platform-health-card" :class="statusClass">
    <div class="header">
      <h3>{{ platform.platform }}</h3>
      <button @click.prevent="checkNow" class="check-button" :disabled="loading">
        <svg class="icon">
          <use href="#refresh-icon"></use>
        </svg>
        Check Now
      </button>
    </div>
    <div class="health-details">
      <div v-for="(value, label) in details" :key="label" class="detail">
        <span class="label">{{ label }}</span>
        <span class="value">{{ value }}</span>
      </div>
    </div>
  </div>
</template>
<script setup>
const props = defineProps({
  platform: {
    type: Object,
    required: true
  }
})
const emits = defineEmits(['check-now'])
const loading = ref(false)

const checkNow = async () => {
  loading.value = true
  emits('check-now', platform.platform)
  setTimeout(() => {
    loading.value = false
  }, 2000)
}

const statusClass = computed(() => {
  const baseClass = 'platform-health-card'
  switch (platform.status) {
    case 'healthy':
      return `${baseClass} healthy`
    case 'warning':
      return `${baseClass} warning`
    case 'critical':
      return `${baseClass} critical`
    default:
      return baseClass
  }
})

const details = computed(() => ({
  'Success Rate': `${platform.success_rate}%`,
  'Avg Response Time': `${platform.avg_response_time}ms`,
  'Availability': `${platform.availability}%`,
  'Last Check': platform.last_check || 'N/A',
  'Requests': `${platform.total_requests}`,
  'Failures': `${platform.failed_requests}`
}))
</script>
<style scoped>
.platform-health-card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  transition: all 0.2s ease;
  border-left-width: 5px;
}

.platform-health-card.healthy {
  border-color: #059669;
}
.platform-health-card.warning {
  border-color: #f59e0b;
}
.platform-health-card.critical {
  border-color: #ef4444;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.header h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}
.check-button {
  background: none;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.check-button:hover {
  background: #f9fafb;
}
.check-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.icon {
  width: 16px;
  height: 16px;
  fill: currentColor;
  animation: spin 1s linear infinite;
}

.health-details {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}
.detail {
  background: #f9fafb;
  border-radius: 0.375rem;
  padding: 0.5rem;
  text-align: center;
}
.label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
}
.value {
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
}
</style>
