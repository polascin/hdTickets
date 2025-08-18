<template>
  <div class="error-boundary">
    <div v-if="hasError" class="error-fallback">
      <div class="error-card modern-card">
        <div class="error-icon">
          <i class="fas fa-exclamation-triangle text-red-500"></i>
        </div>
        <div class="error-content">
          <h2 class="error-title">Something went wrong</h2>
          <p class="error-message">{{ errorMessage }}</p>
          <div class="error-actions">
            <button @click="retry" class="retry-btn modern-button">
              <i class="fas fa-redo"></i>
              Try Again
            </button>
            <button v-if="!hideReportButton" @click="reportError" class="report-btn">
              <i class="fas fa-bug"></i>
              Report Issue
            </button>
          </div>
          <details v-if="showErrorDetails && errorDetails" class="error-details">
            <summary>Technical Details</summary>
            <pre>{{ errorDetails }}</pre>
          </details>
        </div>
      </div>
    </div>
    <slot v-else></slot>
  </div>
</template>

<script setup>
import { ref, onErrorCaptured, provide } from 'vue'

const props = defineProps({
  fallbackComponent: {
    type: Object,
    default: null
  },
  hideReportButton: {
    type: Boolean,
    default: false
  },
  showErrorDetails: {
    type: Boolean,
    default: false
  },
  onError: {
    type: Function,
    default: null
  },
  maxRetries: {
    type: Number,
    default: 3
  }
})

const emit = defineEmits(['error', 'retry'])

const hasError = ref(false)
const errorMessage = ref('')
const errorDetails = ref('')
const retryCount = ref(0)
const errorInstance = ref(null)

// Provide error reporting method to child components
provide('reportError', (error, context) => {
  captureError(error, context)
})

onErrorCaptured((error, instance, info) => {
  captureError(error, { instance, info })
  return false // Prevent error from propagating
})

const captureError = (error, context = {}) => {
  console.error('Error Boundary caught error:', error, context)
  
  hasError.value = true
  errorInstance.value = error
  errorMessage.value = error.message || 'An unexpected error occurred'
  
  // Format error details for technical view
  errorDetails.value = JSON.stringify({
    message: error.message,
    stack: error.stack,
    context: context,
    timestamp: new Date().toISOString(),
    userAgent: navigator.userAgent,
    url: window.location.href
  }, null, 2)
  
  // Call custom error handler if provided
  if (props.onError) {
    props.onError(error, context)
  }
  
  // Emit error event
  emit('error', { error, context })
  
  // Report to error tracking service if available
  if (window.AppCore?.getModule('errorTracking')) {
    window.AppCore.getModule('errorTracking').captureError(error, {
      ...context,
      componentName: 'ErrorBoundary',
      retryCount: retryCount.value
    })
  }
}

const retry = () => {
  if (retryCount.value >= props.maxRetries) {
    if (window.hdTicketsUtils?.notify) {
      window.hdTicketsUtils.notify(
        `Maximum retry attempts (${props.maxRetries}) reached. Please refresh the page.`,
        'error'
      )
    }
    return
  }
  
  retryCount.value++
  hasError.value = false
  errorMessage.value = ''
  errorDetails.value = ''
  errorInstance.value = null
  
  emit('retry', { retryCount: retryCount.value })
  
  console.log(`Error Boundary retry attempt ${retryCount.value}/${props.maxRetries}`)
}

const reportError = () => {
  // Prepare error report
  const errorReport = {
    message: errorMessage.value,
    details: errorDetails.value,
    timestamp: new Date().toISOString(),
    url: window.location.href,
    userAgent: navigator.userAgent,
    retryCount: retryCount.value
  }
  
  // Send to error reporting endpoint
  if (window.AppCore?.apiRequest) {
    window.AppCore.apiRequest('/api/errors/report', {
      method: 'POST',
      body: JSON.stringify(errorReport)
    }).catch(err => {
      console.error('Failed to report error:', err)
    })
  }
  
  // Show confirmation
  if (window.hdTicketsUtils?.notify) {
    window.hdTicketsUtils.notify('Error report sent. Thank you!', 'success')
  }
}

// Reset error state when component is reused
const resetError = () => {
  hasError.value = false
  errorMessage.value = ''
  errorDetails.value = ''
  errorInstance.value = null
  retryCount.value = 0
}

// Expose methods for parent components
defineExpose({
  captureError,
  resetError,
  hasError: () => hasError.value,
  getErrorDetails: () => errorDetails.value
})
</script>

<style scoped>
.error-boundary {
  width: 100%;
  height: 100%;
}

.error-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  padding: 2rem;
}

.error-card {
  max-width: 500px;
  width: 100%;
  padding: 2rem;
  text-align: center;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border: 2px solid #fee2e2;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #ef4444;
}

.error-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.error-message {
  color: #6b7280;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 1rem;
}

.retry-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.retry-btn:hover {
  background: #2563eb;
  transform: translateY(-2px);
}

.report-btn {
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.report-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.error-details {
  text-align: left;
  margin-top: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
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
  max-height: 200px;
  overflow-y: auto;
}

/* Responsive design */
@media (max-width: 640px) {
  .error-fallback {
    padding: 1rem;
  }
  
  .error-card {
    padding: 1.5rem;
  }
  
  .error-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .retry-btn,
  .report-btn {
    width: 100%;
    justify-content: center;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .error-card {
    background: #1f2937;
    border-color: #fca5a5;
  }
  
  .error-title {
    color: #f9fafb;
  }
  
  .error-message {
    color: #d1d5db;
  }
  
  .error-details {
    background: #374151;
    border-color: #4b5563;
  }
  
  .error-details summary {
    color: #e5e7eb;
  }
  
  .error-details pre {
    color: #d1d5db;
  }
  
  .report-btn {
    color: #d1d5db;
    border-color: #4b5563;
  }
  
  .report-btn:hover {
    background: #374151;
    border-color: #6b7280;
  }
}
</style>
