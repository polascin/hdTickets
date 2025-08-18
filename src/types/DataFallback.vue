<template>
  <div class="data-fallback" :class="fallbackClass">
    <!-- No Data State -->
    <div v-if="type === 'no-data'" class="fallback-content">
      <div class="fallback-icon">
        <i class="fas fa-inbox text-gray-400"></i>
      </div>
      <h3 class="fallback-title">{{ title || 'No Data Available' }}</h3>
      <p class="fallback-message">{{ message || 'There is currently no data to display.' }}</p>
      <div v-if="showActions" class="fallback-actions">
        <button v-if="canRefresh" @click="handleRefresh" class="action-btn primary" :disabled="loading">
          <i class="fas fa-refresh" :class="{ 'animate-spin': loading }"></i>
          {{ loading ? 'Refreshing...' : 'Refresh' }}
        </button>
        <button v-if="showCreate" @click="handleCreate" class="action-btn secondary">
          <i class="fas fa-plus"></i>
          {{ createLabel || 'Create First Item' }}
        </button>
      </div>
    </div>
    
    <!-- Loading Error State -->
    <div v-else-if="type === 'error'" class="fallback-content">
      <div class="fallback-icon">
        <i class="fas fa-exclamation-triangle text-red-500"></i>
      </div>
      <h3 class="fallback-title">{{ title || 'Failed to Load Data' }}</h3>
      <p class="fallback-message">{{ message || 'We encountered an issue while loading the data. Please try again.' }}</p>
      <div v-if="errorDetails" class="error-details">
        <details>
          <summary>Error Details</summary>
          <pre>{{ errorDetails }}</pre>
        </details>
      </div>
      <div v-if="showActions" class="fallback-actions">
        <button v-if="canRetry" @click="handleRetry" class="action-btn primary" :disabled="loading">
          <i class="fas fa-redo" :class="{ 'animate-spin': loading }"></i>
          {{ loading ? 'Retrying...' : 'Try Again' }}
        </button>
        <button v-if="canReport" @click="handleReport" class="action-btn secondary">
          <i class="fas fa-bug"></i>
          Report Issue
        </button>
      </div>
    </div>
    
    <!-- Network Error State -->
    <div v-else-if="type === 'network-error'" class="fallback-content">
      <div class="fallback-icon">
        <i class="fas fa-wifi text-gray-400"></i>
      </div>
      <h3 class="fallback-title">{{ title || 'Connection Problem' }}</h3>
      <p class="fallback-message">{{ message || 'Please check your internet connection and try again.' }}</p>
      <div v-if="showActions" class="fallback-actions">
        <button v-if="canRetry" @click="handleRetry" class="action-btn primary" :disabled="loading">
          <i class="fas fa-redo" :class="{ 'animate-spin': loading }"></i>
          {{ loading ? 'Retrying...' : 'Try Again' }}
        </button>
        <button @click="checkConnection" class="action-btn secondary">
          <i class="fas fa-network-wired"></i>
          Check Connection
        </button>
      </div>
    </div>
    
    <!-- Timeout State -->
    <div v-else-if="type === 'timeout'" class="fallback-content">
      <div class="fallback-icon">
        <i class="fas fa-clock text-yellow-500"></i>
      </div>
      <h3 class="fallback-title">{{ title || 'Request Timed Out' }}</h3>
      <p class="fallback-message">{{ message || 'The request took too long to complete. This might be due to a slow connection or server issues.' }}</p>
      <div v-if="showActions" class="fallback-actions">
        <button v-if="canRetry" @click="handleRetry" class="action-btn primary" :disabled="loading">
          <i class="fas fa-redo" :class="{ 'animate-spin': loading }"></i>
          {{ loading ? 'Retrying...' : 'Try Again' }}
        </button>
      </div>
    </div>
    
    <!-- Maintenance State -->
    <div v-else-if="type === 'maintenance'" class="fallback-content">
      <div class="fallback-icon">
        <i class="fas fa-tools text-blue-500"></i>
      </div>
      <h3 class="fallback-title">{{ title || 'Under Maintenance' }}</h3>
      <p class="fallback-message">{{ message || 'This feature is temporarily unavailable due to maintenance. Please try again later.' }}</p>
      <div v-if="estimatedTime" class="maintenance-info">
        <p class="estimated-time">
          <i class="fas fa-clock"></i>
          Estimated completion: {{ estimatedTime }}
        </p>
      </div>
      <div v-if="showActions" class="fallback-actions">
        <button @click="handleRefresh" class="action-btn secondary" :disabled="loading">
          <i class="fas fa-refresh" :class="{ 'animate-spin': loading }"></i>
          Check Again
        </button>
      </div>
    </div>
    
    <!-- Loading Skeleton -->
    <div v-else-if="type === 'loading'" class="fallback-content loading-skeleton">
      <div class="skeleton-items">
        <div v-for="i in skeletonCount" :key="i" class="skeleton-item">
          <div class="skeleton-avatar"></div>
          <div class="skeleton-text">
            <div class="skeleton-line long"></div>
            <div class="skeleton-line medium"></div>
            <div class="skeleton-line short"></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Custom fallback -->
    <div v-else-if="type === 'custom'" class="fallback-content">
      <slot name="custom">
        <div class="fallback-icon">
          <i :class="customIcon || 'fas fa-question-circle text-gray-400'"></i>
        </div>
        <h3 class="fallback-title">{{ title }}</h3>
        <p class="fallback-message">{{ message }}</p>
      </slot>
      <div v-if="showActions" class="fallback-actions">
        <slot name="actions"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => [
      'no-data', 'error', 'network-error', 'timeout', 
      'maintenance', 'loading', 'custom'
    ].includes(value)
  },
  title: {
    type: String,
    default: null
  },
  message: {
    type: String,
    default: null
  },
  errorDetails: {
    type: String,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: true
  },
  canRetry: {
    type: Boolean,
    default: true
  },
  canRefresh: {
    type: Boolean,
    default: true
  },
  canReport: {
    type: Boolean,
    default: true
  },
  showCreate: {
    type: Boolean,
    default: false
  },
  createLabel: {
    type: String,
    default: null
  },
  estimatedTime: {
    type: String,
    default: null
  },
  skeletonCount: {
    type: Number,
    default: 3
  },
  customIcon: {
    type: String,
    default: null
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  }
})

const emit = defineEmits([
  'retry', 'refresh', 'create', 'report'
])

const fallbackClass = computed(() => {
  return {
    [`fallback-${props.type}`]: true,
    [`fallback-${props.size}`]: true,
    'loading': props.loading
  }
})

const handleRetry = () => {
  emit('retry')
}

const handleRefresh = () => {
  emit('refresh')
}

const handleCreate = () => {
  emit('create')
}

const handleReport = () => {
  emit('report')
  
  // Default error reporting if no custom handler
  if (window.hdTicketsUtils?.notify) {
    window.hdTicketsUtils.notify('Error report sent. Thank you for your feedback!', 'success')
  }
}

const checkConnection = () => {
  // Check network connection
  if (navigator.onLine) {
    if (window.hdTicketsUtils?.notify) {
      window.hdTicketsUtils.notify('Your connection appears to be working. Trying to refresh...', 'info')
    }
    handleRetry()
  } else {
    if (window.hdTicketsUtils?.notify) {
      window.hdTicketsUtils.notify('No internet connection detected. Please check your network settings.', 'warning')
    }
  }
}
</script>

<style scoped>
.data-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  padding: 2rem;
}

.fallback-small {
  min-height: 120px;
  padding: 1rem;
}

.fallback-large {
  min-height: 300px;
  padding: 3rem;
}

.fallback-content {
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.fallback-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.fallback-small .fallback-icon {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.fallback-large .fallback-icon {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
}

.fallback-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.fallback-small .fallback-title {
  font-size: 1rem;
}

.fallback-large .fallback-title {
  font-size: 1.5rem;
}

.fallback-message {
  color: #6b7280;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.error-details {
  margin-bottom: 1.5rem;
  text-align: left;
}

.error-details details {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 0.75rem;
}

.error-details summary {
  cursor: pointer;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.error-details pre {
  font-size: 0.75rem;
  color: #6b7280;
  white-space: pre-wrap;
  word-break: break-all;
  max-height: 150px;
  overflow-y: auto;
  margin: 0;
}

.maintenance-info {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 0.5rem;
  padding: 0.75rem;
  margin-bottom: 1.5rem;
}

.estimated-time {
  color: #1e40af;
  font-size: 0.875rem;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.fallback-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
}

.action-btn.primary:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-1px);
}

.action-btn.secondary {
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.action-btn.secondary:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

/* Loading Skeleton Styles */
.loading-skeleton {
  text-align: left;
}

.skeleton-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.skeleton-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.skeleton-avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-line {
  height: 0.75rem;
  border-radius: 0.375rem;
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-line.long {
  width: 100%;
}

.skeleton-line.medium {
  width: 75%;
}

.skeleton-line.short {
  width: 50%;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Responsive Design */
@media (max-width: 640px) {
  .data-fallback {
    padding: 1rem;
  }
  
  .fallback-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .action-btn {
    justify-content: center;
    width: 100%;
  }
  
  .skeleton-item {
    gap: 0.75rem;
  }
  
  .skeleton-avatar {
    width: 2.5rem;
    height: 2.5rem;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .fallback-title {
    color: #f9fafb;
  }
  
  .fallback-message {
    color: #d1d5db;
  }
  
  .error-details details {
    background: #374151;
    border-color: #4b5563;
  }
  
  .error-details summary {
    color: #e5e7eb;
  }
  
  .error-details pre {
    color: #d1d5db;
  }
  
  .maintenance-info {
    background: #1e3a8a;
    border-color: #3b82f6;
  }
  
  .estimated-time {
    color: #93c5fd;
  }
  
  .action-btn.secondary {
    color: #d1d5db;
    border-color: #4b5563;
  }
  
  .action-btn.secondary:hover:not(:disabled) {
    background: #374151;
    border-color: #6b7280;
  }
  
  .skeleton-avatar,
  .skeleton-line {
    background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    background-size: 200% 100%;
  }
}
</style>
