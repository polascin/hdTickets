<template>
  <div class="system-health">
    <div class="health-overview">
      <div class="overall-status" :class="overallStatusClass">
        <div class="status-indicator">
          <span class="status-icon">{{ overallStatusIcon }}</span>
        </div>
        <div class="status-info">
          <h3 class="status-title">System Status</h3>
          <p class="status-description">{{ overallStatusText }}</p>
        </div>
        <div class="status-score">
          <span class="score-value">{{ overallScore }}%</span>
          <span class="score-label">Health Score</span>
        </div>
      </div>
    </div>

    <div class="health-metrics">
      <div
        v-for="metric in healthMetrics"
        :key="metric.key"
        class="metric-item"
        :class="getMetricClass(metric.status)"
      >
        <div class="metric-header">
          <div class="metric-icon">
            <span>{{ metric.icon }}</span>
          </div>
          <div class="metric-info">
            <h4 class="metric-name">{{ metric.name }}</h4>
            <p class="metric-description">{{ metric.description }}</p>
          </div>
          <div class="metric-status">
            <span class="status-badge" :class="getStatusBadgeClass(metric.status)">
              {{ metric.status }}
            </span>
          </div>
        </div>

        <div v-if="metric.details" class="metric-details">
          <div class="detail-grid">
            <div
              v-for="detail in metric.details"
              :key="detail.key"
              class="detail-item"
            >
              <span class="detail-label">{{ detail.label }}:</span>
              <span class="detail-value" :class="getDetailValueClass(detail.status)">
                {{ detail.value }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="metric.trend" class="metric-trend">
          <div class="trend-header">
            <span class="trend-label">24h Trend</span>
            <span class="trend-change" :class="getTrendClass(metric.trend.change)">
              {{ metric.trend.change > 0 ? '+' : '' }}{{ metric.trend.change }}%
            </span>
          </div>
          <div class="trend-chart">
            <svg viewBox="0 0 100 30" class="trend-svg">
              <polyline
                :points="getTrendPoints(metric.trend.data)"
                fill="none"
                :stroke="getTrendColor(metric.trend.change)"
                stroke-width="2"
                class="trend-line"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showActions" class="health-actions">
      <button @click="refreshHealth" class="action-btn primary" :disabled="loading">
        <span v-if="loading" class="loading-spinner">⏳</span>
        <span v-else>🔄</span>
        {{ loading ? 'Refreshing...' : 'Refresh' }}
      </button>
      <button @click="runDiagnostics" class="action-btn secondary">
        <span>🔍</span>
        Run Diagnostics
      </button>
      <button @click="viewLogs" class="action-btn outline">
        <span>📋</span>
        View Logs
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  healthData: {
    type: Object,
    default: () => ({})
  },
  autoRefresh: {
    type: Boolean,
    default: true
  },
  refreshInterval: {
    type: Number,
    default: 30000 // 30 seconds
  },
  showActions: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['refresh', 'diagnostics', 'view-logs'])

const loading = ref(false)
let refreshTimer = null

// Computed properties
const overallScore = computed(() => {
  return props.healthData.overall_score || 0
})

const overallStatusClass = computed(() => {
  const score = overallScore.value
  if (score >= 90) return 'status-excellent'
  if (score >= 75) return 'status-good'
  if (score >= 50) return 'status-warning'
  return 'status-critical'
})

const overallStatusIcon = computed(() => {
  const score = overallScore.value
  if (score >= 90) return '✅'
  if (score >= 75) return '🟢'
  if (score >= 50) return '⚠️'
  return '❌'
})

const overallStatusText = computed(() => {
  const score = overallScore.value
  if (score >= 90) return 'All systems operational'
  if (score >= 75) return 'System running well'
  if (score >= 50) return 'Some issues detected'
  return 'Critical issues present'
})

const healthMetrics = computed(() => {
  return props.healthData.metrics || [
    {
      key: 'cpu',
      name: 'CPU Usage',
      description: 'Server processing load',
      icon: '🖥️',
      status: 'healthy',
      details: [
        { key: 'current', label: 'Current', value: '45%', status: 'normal' },
        { key: 'avg', label: '24h Avg', value: '52%', status: 'normal' }
      ],
      trend: {
        change: -5.2,
        data: [60, 58, 55, 52, 48, 45, 42, 45]
      }
    },
    {
      key: 'memory',
      name: 'Memory Usage',
      description: 'RAM utilization',
      icon: '💾',
      status: 'healthy',
      details: [
        { key: 'used', label: 'Used', value: '6.2 GB', status: 'normal' },
        { key: 'total', label: 'Total', value: '16 GB', status: 'normal' }
      ],
      trend: {
        change: 2.1,
        data: [35, 38, 40, 39, 41, 42, 40, 38]
      }
    },
    {
      key: 'database',
      name: 'Database',
      description: 'Database connectivity and performance',
      icon: '🗄️',
      status: 'healthy',
      details: [
        { key: 'connections', label: 'Connections', value: '24/100', status: 'normal' },
        { key: 'response', label: 'Avg Response', value: '12ms', status: 'good' }
      ],
      trend: {
        change: -1.8,
        data: [15, 14, 13, 12, 11, 10, 12, 12]
      }
    },
    {
      key: 'storage',
      name: 'Storage',
      description: 'Disk space utilization',
      icon: '💿',
      status: 'warning',
      details: [
        { key: 'used', label: 'Used', value: '742 GB', status: 'warning' },
        { key: 'free', label: 'Free', value: '258 GB', status: 'warning' }
      ],
      trend: {
        change: 8.5,
        data: [70, 72, 74, 75, 76, 78, 80, 82]
      }
    }
  ]
})

// Methods
const getMetricClass = (status) => {
  const classes = {
    healthy: 'metric-healthy',
    warning: 'metric-warning',
    critical: 'metric-critical',
    unknown: 'metric-unknown'
  }
  return classes[status] || classes.unknown
}

const getStatusBadgeClass = (status) => {
  const classes = {
    healthy: 'badge-success',
    warning: 'badge-warning',
    critical: 'badge-error',
    unknown: 'badge-default'
  }
  return classes[status] || classes.unknown
}

const getDetailValueClass = (status) => {
  const classes = {
    good: 'value-good',
    normal: 'value-normal',
    warning: 'value-warning',
    critical: 'value-critical'
  }
  return classes[status] || classes.normal
}

const getTrendClass = (change) => {
  if (change > 0) return 'trend-up'
  if (change < 0) return 'trend-down'
  return 'trend-neutral'
}

const getTrendColor = (change) => {
  if (change > 0) return '#ef4444' // red for increasing (bad)
  if (change < 0) return '#10b981' // green for decreasing (good)
  return '#6b7280' // gray for neutral
}

const getTrendPoints = (data) => {
  if (!data || data.length === 0) return ''
  
  const maxValue = Math.max(...data)
  const minValue = Math.min(...data)
  const range = maxValue - minValue || 1
  
  return data
    .map((value, index) => {
      const x = (index / (data.length - 1)) * 100
      const y = 30 - ((value - minValue) / range) * 30
      return `${x},${y}`
    })
    .join(' ')
}

const refreshHealth = async () => {
  loading.value = true
  try {
    await emit('refresh')
  } finally {
    loading.value = false
  }
}

const runDiagnostics = () => {
  emit('diagnostics')
}

const viewLogs = () => {
  emit('view-logs')
}

const startAutoRefresh = () => {
  if (props.autoRefresh && props.refreshInterval > 0) {
    refreshTimer = setInterval(() => {
      if (!loading.value) {
        refreshHealth()
      }
    }, props.refreshInterval)
  }
}

const stopAutoRefresh = () => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
    refreshTimer = null
  }
}

// Lifecycle hooks
onMounted(() => {
  startAutoRefresh()
})

onUnmounted(() => {
  stopAutoRefresh()
})
</script>

<style scoped>
.system-health {
  space-y: 1.5rem;
}

.health-overview {
  margin-bottom: 1.5rem;
}

.overall-status {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: white;
  border-radius: 0.75rem;
  border-left: 4px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.status-excellent {
  border-left-color: #10b981;
  background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
}

.status-good {
  border-left-color: #3b82f6;
  background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
}

.status-warning {
  border-left-color: #f59e0b;
  background: linear-gradient(135deg, #fffbeb 0%, #fefce8 100%);
}

.status-critical {
  border-left-color: #ef4444;
  background: linear-gradient(135deg, #fef2f2 0%, #fef1f1 100%);
}

.status-indicator {
  flex-shrink: 0;
  width: 4rem;
  height: 4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  font-size: 2rem;
}

.status-info {
  flex: 1;
  min-width: 0;
}

.status-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 0.25rem 0;
}

.status-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.status-score {
  text-align: center;
}

.score-value {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  line-height: 1;
}

.score-label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.health-metrics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.metric-item {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  border-left: 4px solid #e5e7eb;
}

.metric-healthy {
  border-left-color: #10b981;
}

.metric-warning {
  border-left-color: #f59e0b;
}

.metric-critical {
  border-left-color: #ef4444;
}

.metric-unknown {
  border-left-color: #6b7280;
}

.metric-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.5rem;
}

.metric-icon {
  flex-shrink: 0;
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 0.5rem;
  font-size: 1.25rem;
}

.metric-info {
  flex: 1;
  min-width: 0;
}

.metric-name {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 0.25rem 0;
}

.metric-description {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0;
}

.metric-status {
  flex-shrink: 0;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

.badge-error {
  background: #fee2e2;
  color: #991b1b;
}

.badge-default {
  background: #f3f4f6;
  color: #6b7280;
}

.metric-details {
  padding: 0 1.5rem 1rem 1.5rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.detail-value {
  font-size: 0.875rem;
  font-weight: 600;
}

.value-good {
  color: #059669;
}

.value-normal {
  color: #111827;
}

.value-warning {
  color: #d97706;
}

.value-critical {
  color: #dc2626;
}

.metric-trend {
  padding: 0 1.5rem 1rem 1.5rem;
}

.trend-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.trend-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.trend-change {
  font-size: 0.75rem;
  font-weight: 600;
}

.trend-up {
  color: #dc2626;
}

.trend-down {
  color: #059669;
}

.trend-neutral {
  color: #6b7280;
}

.trend-chart {
  height: 30px;
  background: #f9fafb;
  border-radius: 0.375rem;
  padding: 0.25rem;
}

.trend-svg {
  width: 100%;
  height: 100%;
}

.trend-line {
  stroke-linecap: round;
  stroke-linejoin: round;
}

.health-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
}

.action-btn.primary:hover:not(:disabled) {
  background: #2563eb;
}

.action-btn.secondary {
  background: #10b981;
  color: white;
}

.action-btn.secondary:hover {
  background: #059669;
}

.action-btn.outline {
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.action-btn.outline:hover {
  background: #f9fafb;
  color: #111827;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive design */
@media (max-width: 640px) {
  .overall-status {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .health-metrics {
    grid-template-columns: 1fr;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .health-actions {
    flex-direction: column;
  }
}
</style>
