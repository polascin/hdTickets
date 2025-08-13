<template>
  <div class="management-card" :class="cardVariant">
    <div class="card-header">
      <div class="card-icon" :style="{ backgroundColor: iconBgColor, color: iconColor }">
        <span>{{ icon }}</span>
      </div>
      <div class="card-info">
        <h3 class="card-title">{{ title }}</h3>
        <p class="card-description">{{ description }}</p>
      </div>
      <div v-if="showBadge && badgeText" class="card-badge" :class="badgeVariant">
        {{ badgeText }}
      </div>
    </div>
    
    <div v-if="stats && stats.length > 0" class="card-stats">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="stat-item"
      >
        <span class="stat-value">{{ stat.value }}</span>
        <span class="stat-label">{{ stat.label }}</span>
      </div>
    </div>
    
    <div v-if="showContent" class="card-content">
      <slot name="content">
        <p class="default-content">{{ content || 'No additional content' }}</p>
      </slot>
    </div>
    
    <div v-if="actions && actions.length > 0" class="card-actions">
      <button
        v-for="action in actions"
        :key="action.key"
        @click="handleAction(action)"
        :class="getActionClass(action)"
        :disabled="action.disabled || loading"
      >
        <span v-if="action.icon" class="action-icon">{{ action.icon }}</span>
        <span>{{ action.label }}</span>
        <span v-if="loading && currentAction === action.key" class="loading-spinner">⏳</span>
      </button>
    </div>
    
    <div v-if="showFooter" class="card-footer">
      <slot name="footer">
        <span class="footer-text">{{ footerText }}</span>
        <span v-if="lastUpdated" class="last-updated">
          Updated {{ formatRelativeTime(lastUpdated) }}
        </span>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  content: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: '⚙️'
  },
  iconColor: {
    type: String,
    default: '#ffffff'
  },
  iconBgColor: {
    type: String,
    default: '#3b82f6'
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'success', 'warning', 'error'].includes(value)
  },
  showBadge: {
    type: Boolean,
    default: false
  },
  badgeText: {
    type: String,
    default: ''
  },
  badgeVariant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'success', 'warning', 'error', 'info'].includes(value)
  },
  stats: {
    type: Array,
    default: () => []
  },
  actions: {
    type: Array,
    default: () => []
  },
  showContent: {
    type: Boolean,
    default: false
  },
  showFooter: {
    type: Boolean,
    default: false
  },
  footerText: {
    type: String,
    default: ''
  },
  lastUpdated: {
    type: [String, Date],
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['action'])

const currentAction = ref(null)

const cardVariant = computed(() => {
  return `management-card-${props.variant}`
})

const handleAction = (action) => {
  if (action.disabled || props.loading) return
  
  currentAction.value = action.key
  emit('action', action)
  
  // Reset current action after a delay to stop loading spinner
  setTimeout(() => {
    currentAction.value = null
  }, 1000)
}

const getActionClass = (action) => {
  const baseClass = 'action-btn'
  const variantClass = action.variant ? `btn-${action.variant}` : 'btn-default'
  const disabledClass = action.disabled || props.loading ? 'btn-disabled' : ''
  
  return [baseClass, variantClass, disabledClass].filter(Boolean).join(' ')
}

const formatRelativeTime = (dateString) => {
  if (!dateString) return 'Unknown'
  
  const now = new Date()
  const date = new Date(dateString)
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)
  
  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}
</script>

<style scoped>
.management-card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  overflow: hidden;
  border-left: 4px solid #e5e7eb;
}

.management-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.management-card-default {
  border-left-color: #6b7280;
}

.management-card-primary {
  border-left-color: #3b82f6;
}

.management-card-success {
  border-left-color: #10b981;
}

.management-card-warning {
  border-left-color: #f59e0b;
}

.management-card-error {
  border-left-color: #ef4444;
}

.card-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem 1.5rem 1rem 1.5rem;
  position: relative;
}

.card-icon {
  flex-shrink: 0;
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.card-info {
  flex: 1;
  min-width: 0;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 0.25rem 0;
}

.card-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.4;
}

.card-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.card-badge.badge-default {
  background: #f3f4f6;
  color: #6b7280;
}

.card-badge.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.card-badge.badge-warning {
  background: #fef3c7;
  color: #92400e;
}

.card-badge.badge-error {
  background: #fee2e2;
  color: #991b1b;
}

.card-badge.badge-info {
  background: #dbeafe;
  color: #1e40af;
}

.card-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
  gap: 1rem;
  padding: 0 1.5rem 1rem 1.5rem;
  border-bottom: 1px solid #f3f4f6;
}

.stat-item {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  line-height: 1;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.card-content {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f3f4f6;
}

.default-content {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.5;
}

.card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
  text-decoration: none;
}

.btn-default {
  background: #f3f4f6;
  color: #374151;
}

.btn-default:hover:not(.btn-disabled) {
  background: #e5e7eb;
  color: #111827;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(.btn-disabled) {
  background: #2563eb;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover:not(.btn-disabled) {
  background: #059669;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover:not(.btn-disabled) {
  background: #d97706;
}

.btn-error {
  background: #ef4444;
  color: white;
}

.btn-error:hover:not(.btn-disabled) {
  background: #dc2626;
}

.btn-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-icon {
  font-size: 1rem;
}

.loading-spinner {
  font-size: 0.875rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.footer-text {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.last-updated {
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Responsive design */
@media (max-width: 640px) {
  .card-header {
    padding: 1rem;
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .card-badge {
    position: static;
    align-self: flex-start;
    margin-top: 0.5rem;
  }
  
  .card-icon {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 1.25rem;
  }
  
  .card-stats {
    padding: 0 1rem 1rem 1rem;
  }
  
  .card-content {
    padding: 1rem;
  }
  
  .card-actions {
    padding: 1rem;
    flex-direction: column;
  }
  
  .action-btn {
    justify-content: center;
  }
  
  .card-footer {
    padding: 0.75rem 1rem;
    flex-direction: column;
    gap: 0.25rem;
    align-items: flex-start;
  }
}
</style>
