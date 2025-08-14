<template>
  <div class="stat-card" :class="[`stat-card--${color}`, { 'stat-card--loading': loading }]">
    <div class="stat-card__header">
      <div class="stat-card__icon">
        <component :is="iconComponent" class="icon" />
      </div>
      <div v-if="isRealtime" class="stat-card__realtime-indicator">
        <div class="realtime-dot"></div>
        <span>Live</span>
      </div>
    </div>
    
    <div class="stat-card__content">
      <div class="stat-card__value" :class="{ skeleton: loading }">
        <transition name="value" mode="out-in">
          <span :key="value">{{ loading ? '—' : formattedValue }}</span>
        </transition>
      </div>
      
      <div class="stat-card__title">{{ title }}</div>
      
      <div v-if="change && !loading" class="stat-card__change" :class="[`trend--${trend}`]">
        <component :is="trendIcon" class="trend-icon" />
        <span>{{ change }}</span>
        <span v-if="timeframe" class="timeframe">{{ timeframe }}</span>
      </div>
    </div>
    
    <div v-if="loading" class="stat-card__loading">
      <div class="loading-spinner"></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CogIcon,
  TicketIcon,
  UsersIcon,
  ServerIcon,
  ChartBarIcon,
  ClockIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  ArrowTrendingUpIcon as TrendingUpIcon,
  ArrowTrendingDownIcon as TrendingDownIcon,
  MinusIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  change: {
    type: String,
    default: null
  },
  trend: {
    type: String,
    default: 'stable',
    validator: value => ['up', 'down', 'stable'].includes(value)
  },
  icon: {
    type: String,
    default: 'chart',
    validator: value => ['cog', 'ticket', 'users', 'server', 'chart', 'clock', 'currency', 'warning'].includes(value)
  },
  color: {
    type: String,
    default: 'blue',
    validator: value => ['blue', 'green', 'purple', 'red', 'yellow', 'gray'].includes(value)
  },
  isRealtime: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  timeframe: {
    type: String,
    default: null
  }
})

// Icon mapping
const iconComponents = {
  cog: CogIcon,
  ticket: TicketIcon,
  users: UsersIcon,
  server: ServerIcon,
  chart: ChartBarIcon,
  clock: ClockIcon,
  currency: CurrencyDollarIcon,
  warning: ExclamationTriangleIcon
}

const trendIcons = {
  up: TrendingUpIcon,
  down: TrendingDownIcon,
  stable: MinusIcon
}

// Computed properties
const iconComponent = computed(() => iconComponents[props.icon] || ChartBarIcon)
const trendIcon = computed(() => trendIcons[props.trend] || MinusIcon)

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    // Format large numbers
    if (props.value >= 1000000) {
      return `${(props.value / 1000000).toFixed(1)}M`
    } else if (props.value >= 1000) {
      return `${(props.value / 1000).toFixed(1)}K`
    }
  }
  return props.value
})
</script>

<style scoped>
.stat-card {
  position: relative;
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-card--loading {
  pointer-events: none;
}

.stat-card__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.stat-card__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}

.stat-card--blue .stat-card__icon {
  background: #dbeafe;
  color: #1e40af;
}

.stat-card--green .stat-card__icon {
  background: #d1fae5;
  color: #065f46;
}

.stat-card--purple .stat-card__icon {
  background: #e9d5ff;
  color: #6b21a8;
}

.stat-card--red .stat-card__icon {
  background: #fee2e2;
  color: #991b1b;
}

.stat-card--yellow .stat-card__icon {
  background: #fef3c7;
  color: #92400e;
}

.stat-card--gray .stat-card__icon {
  background: #f3f4f6;
  color: #374151;
}

.icon {
  width: 1.5rem;
  height: 1.5rem;
}

.stat-card__realtime-indicator {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #059669;
  background: #d1fae5;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
}

.realtime-dot {
  width: 6px;
  height: 6px;
  background: #059669;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

.stat-card__content {
  flex: 1;
}

.stat-card__value {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
  margin-bottom: 0.25rem;
  min-height: 2.4rem;
}

.stat-card__value.skeleton {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: skeleton 1.5s infinite;
  border-radius: 0.25rem;
}

.stat-card__title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.stat-card__change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.stat-card__change.trend--up {
  color: #059669;
}

.stat-card__change.trend--down {
  color: #dc2626;
}

.stat-card__change.trend--stable {
  color: #6b7280;
}

.trend-icon {
  width: 1rem;
  height: 1rem;
}

.timeframe {
  color: #9ca3af;
  font-weight: 400;
  margin-left: 0.25rem;
}

.stat-card__loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid #e5e7eb;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Responsive design */
@media (max-width: 640px) {
  .stat-card {
    padding: 1rem;
  }
  
  .stat-card__icon {
    width: 2.5rem;
    height: 2.5rem;
  }
  
  .icon {
    width: 1.25rem;
    height: 1.25rem;
  }
  
  .stat-card__value {
    font-size: 1.75rem;
  }
  
  .stat-card__realtime-indicator {
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
  }
}

/* Animations */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes skeleton {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

/* Transitions */
.value-enter-active,
.value-leave-active {
  transition: all 0.3s ease;
}

.value-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.value-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
