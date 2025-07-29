<template>
  <div class="metric-card" :class="cardClass">
    <div class="metric-header">
      <div class="metric-icon" :style="{ backgroundColor: iconBgColor, color: iconColor }">
        <span>{{ icon }}</span>
      </div>
      <div class="metric-info">
        <h3 class="metric-title">{{ title }}</h3>
        <p class="metric-description">{{ description }}</p>
      </div>
    </div>
    
    <div class="metric-content">
      <div class="metric-value">
        <span class="value-main">{{ formattedValue }}</span>
        <span v-if="unit" class="value-unit">{{ unit }}</span>
      </div>
      
      <div v-if="change !== null && change !== undefined" class="metric-change" :class="changeClass">
        <span class="change-icon">{{ changeIcon }}</span>
        <span class="change-value">{{ Math.abs(change) }}{{ changeUnit }}</span>
        <span class="change-label">{{ changeLabel }}</span>
      </div>
    </div>
    
    <div v-if="showTrend && trendData && trendData.length > 0" class="metric-trend">
      <div class="trend-chart" :style="{ height: '40px' }">
        <svg viewBox="0 0 100 40" class="trend-svg">
          <polyline
            :points="trendPoints"
            fill="none"
            :stroke="trendColor"
            stroke-width="2"
            class="trend-line"
          />
        </svg>
      </div>
    </div>
    
    <div v-if="footerText" class="metric-footer">
      <span class="footer-text">{{ footerText }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  value: {
    type: [Number, String],
    required: true
  },
  unit: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: '📊'
  },
  iconColor: {
    type: String,
    default: '#ffffff'
  },
  iconBgColor: {
    type: String,
    default: '#3b82f6'
  },
  change: {
    type: Number,
    default: null
  },
  changeUnit: {
    type: String,
    default: '%'
  },
  changeLabel: {
    type: String,
    default: 'vs last period'
  },
  variant: {
    type: String,
    default: 'default', // default, success, warning, error, info
    validator: (value) => ['default', 'success', 'warning', 'error', 'info'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  showTrend: {
    type: Boolean,
    default: false
  },
  trendData: {
    type: Array,
    default: () => []
  },
  footerText: {
    type: String,
    default: ''
  }
})

const cardClass = computed(() => {
  const classes = ['metric-card']
  
  if (props.loading) {
    classes.push('loading')
  }
  
  classes.push(`variant-${props.variant}`)
  
  return classes.join(' ')
})

const formattedValue = computed(() => {
  if (props.loading) return '...'
  
  if (typeof props.value === 'number') {
    // Format large numbers with appropriate suffixes
    if (props.value >= 1000000) {
      return (props.value / 1000000).toFixed(1) + 'M'
    } else if (props.value >= 1000) {
      return (props.value / 1000).toFixed(1) + 'K'
    } else {
      return props.value.toLocaleString()
    }
  }
  
  return props.value
})

const changeClass = computed(() => {
  if (props.change === null || props.change === undefined) return ''
  
  if (props.change > 0) return 'change-positive'
  if (props.change < 0) return 'change-negative'
  return 'change-neutral'
})

const changeIcon = computed(() => {
  if (props.change === null || props.change === undefined) return ''
  
  if (props.change > 0) return '↗'
  if (props.change < 0) return '↘'
  return '→'
})

const trendPoints = computed(() => {
  if (!props.trendData || props.trendData.length === 0) return ''
  
  const data = props.trendData
  const maxValue = Math.max(...data)
  const minValue = Math.min(...data)
  const range = maxValue - minValue || 1
  
  return data
    .map((value, index) => {
      const x = (index / (data.length - 1)) * 100
      const y = 40 - ((value - minValue) / range) * 40
      return `${x},${y}`
    })
    .join(' ')
})

const trendColor = computed(() => {
  const colors = {
    default: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    error: '#ef4444',
    info: '#06b6d4'
  }
  return colors[props.variant] || colors.default
})
</script>

<style scoped>
.metric-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  border-left: 4px solid #e5e7eb;
  position: relative;
  overflow: hidden;
}

.metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.metric-card.loading {
  opacity: 0.7;
}

.metric-card.loading::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

.variant-default {
  border-left-color: #3b82f6;
}

.variant-success {
  border-left-color: #10b981;
}

.variant-warning {
  border-left-color: #f59e0b;
}

.variant-error {
  border-left-color: #ef4444;
}

.variant-info {
  border-left-color: #06b6d4;
}

.metric-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.metric-icon {
  flex-shrink: 0;
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.metric-info {
  flex: 1;
  min-width: 0;
}

.metric-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  margin: 0 0 0.25rem 0;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.metric-description {
  font-size: 0.75rem;
  color: #9ca3af;
  margin: 0;
  line-height: 1.4;
}

.metric-content {
  margin-bottom: 1rem;
}

.metric-value {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
}

.value-main {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  line-height: 1;
}

.value-unit {
  font-size: 1rem;
  font-weight: 500;
  color: #6b7280;
}

.metric-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.change-positive {
  color: #059669;
}

.change-negative {
  color: #dc2626;
}

.change-neutral {
  color: #6b7280;
}

.change-icon {
  font-size: 0.875rem;
}

.change-value {
  font-weight: 600;
}

.change-label {
  color: #9ca3af;
}

.metric-trend {
  margin-bottom: 1rem;
}

.trend-chart {
  width: 100%;
  height: 40px;
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

.metric-footer {
  padding-top: 0.75rem;
  border-top: 1px solid #f3f4f6;
}

.footer-text {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

/* Responsive design */
@media (max-width: 640px) {
  .metric-card {
    padding: 1rem;
  }
  
  .metric-header {
    gap: 0.75rem;
  }
  
  .metric-icon {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 1.25rem;
  }
  
  .value-main {
    font-size: 1.5rem;
  }
}
</style>
