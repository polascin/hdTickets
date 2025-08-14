<template>
  <div class="activity-feed">
    <div class="feed-header">
      <h3 class="feed-title">Recent Activity</h3>
      <div class="feed-controls">
        <select v-model="selectedFilter" @change="applyFilter" class="filter-select">
          <option value="all">All Activities</option>
          <option value="tickets_found">Tickets Found</option>
          <option value="price_changes">Price Changes</option>
          <option value="errors">Errors</option>
          <option value="system">System Events</option>
        </select>
        <button @click="refreshFeed" class="refresh-btn" :disabled="isRefreshing">
          <svg class="refresh-icon" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>
    </div>
    
    <div class="feed-content">
      <div v-if="filteredActivities.length === 0" class="empty-state">
        <div class="empty-icon">📭</div>
        <p class="empty-message">No activities found</p>
        <p class="empty-description">Activities will appear here as the system monitors tickets</p>
      </div>
      
      <div v-else class="activity-list">
        <div
          v-for="activity in filteredActivities"
          :key="activity.id"
          class="activity-item"
          :class="getActivityClass(activity.type)"
          @click="showActivityDetails(activity)"
        >
          <div class="activity-icon">
            <span>{{ getActivityIcon(activity.type) }}</span>
          </div>
          
          <div class="activity-content">
            <div class="activity-header">
              <span class="activity-title">{{ activity.title }}</span>
              <span class="activity-time">{{ formatRelativeTime(activity.created_at) }}</span>
            </div>
            
            <p class="activity-description">{{ activity.description }}</p>
            
            <div v-if="activity.metadata" class="activity-metadata">
              <span v-if="activity.metadata.event_name" class="metadata-item">
                🎫 {{ activity.metadata.event_name }}
              </span>
              <span v-if="activity.metadata.price" class="metadata-item">
                💰 ${{ activity.metadata.price }}
              </span>
              <span v-if="activity.metadata.platform" class="metadata-item">
                🏷️ {{ activity.metadata.platform }}
              </span>
            </div>
          </div>
          
          <div class="activity-actions">
            <button
              v-if="activity.type === 'ticket_found'"
              @click.stop="viewTickets(activity)"
              class="action-btn primary"
            >
              View
            </button>
            <button
              v-if="activity.type === 'error'"
              @click.stop="retryAction(activity)"
              class="action-btn secondary"
            >
              Retry
            </button>
          </div>
        </div>
      </div>
      
      <div v-if="hasMoreActivities" class="load-more-container">
        <button @click="loadMoreActivities" class="load-more-btn" :disabled="isLoadingMore">
          <span v-if="isLoadingMore">Loading...</span>
          <span v-else>Load More</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  activities: {
    type: Array,
    default: () => []
  },
  autoRefresh: {
    type: Boolean,
    default: true
  },
  refreshInterval: {
    type: Number,
    default: 30000 // 30 seconds
  }
})

const emit = defineEmits(['refresh', 'load-more', 'view-activity', 'view-tickets', 'retry-action'])

// Reactive data
const selectedFilter = ref('all')
const isRefreshing = ref(false)
const isLoadingMore = ref(false)
const hasMoreActivities = ref(true)
let refreshTimer = null

// Computed properties
const filteredActivities = computed(() => {
  if (selectedFilter.value === 'all') {
    return props.activities
  }
  
  return props.activities.filter(activity => {
    switch (selectedFilter.value) {
      case 'tickets_found':
        return activity.type === 'ticket_found'
      case 'price_changes':
        return activity.type === 'price_drop' || activity.type === 'price_increase'
      case 'errors':
        return activity.type === 'error' || activity.type === 'warning'
      case 'system':
        return activity.type === 'system' || activity.type === 'monitor_started' || activity.type === 'monitor_stopped'
      default:
        return true
    }
  })
})

// Methods
const applyFilter = () => {
  // Filter is applied automatically through computed property
}

const refreshFeed = async () => {
  isRefreshing.value = true
  try {
    await emit('refresh')
  } finally {
    isRefreshing.value = false
  }
}

const loadMoreActivities = async () => {
  isLoadingMore.value = true
  try {
    const result = await emit('load-more')
    if (!result || result.length === 0) {
      hasMoreActivities.value = false
    }
  } finally {
    isLoadingMore.value = false
  }
}

const showActivityDetails = (activity) => {
  emit('view-activity', activity)
}

const viewTickets = (activity) => {
  emit('view-tickets', activity)
}

const retryAction = (activity) => {
  emit('retry-action', activity)
}

const getActivityClass = (type) => {
  const classes = {
    ticket_found: 'activity-success',
    price_drop: 'activity-info',
    price_increase: 'activity-warning',
    error: 'activity-error',
    warning: 'activity-warning',
    system: 'activity-neutral',
    monitor_started: 'activity-success',
    monitor_stopped: 'activity-neutral'
  }
  return classes[type] || 'activity-default'
}

const getActivityIcon = (type) => {
  const icons = {
    ticket_found: '🎫',
    price_drop: '📉',
    price_increase: '📈',
    error: '❌',
    warning: '⚠️',
    system: 'ℹ️',
    monitor_started: '▶️',
    monitor_stopped: '⏸️'
  }
  return icons[type] || '📢'
}

const formatRelativeTime = (dateString) => {
  if (!dateString) return 'Unknown'
  
  const now = new Date()
  const date = new Date(dateString)
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}

const startAutoRefresh = () => {
  if (props.autoRefresh && props.refreshInterval > 0) {
    refreshTimer = setInterval(() => {
      if (!isRefreshing.value) {
        refreshFeed()
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
.activity-feed {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.feed-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.feed-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.feed-controls {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.filter-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  background: white;
  color: #374151;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.refresh-btn {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background: white;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
}

.refresh-btn:hover:not(:disabled) {
  background: #f3f4f6;
  color: #111827;
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.refresh-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.feed-content {
  max-height: 600px;
  overflow-y: auto;
}

.empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: #6b7280;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-message {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #374151;
}

.empty-description {
  font-size: 0.875rem;
  margin: 0;
}

.activity-list {
  padding: 0;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-left: 4px solid transparent;
}

.activity-item:hover {
  background: #f9fafb;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-success {
  border-left-color: #10b981;
}

.activity-info {
  border-left-color: #3b82f6;
}

.activity-warning {
  border-left-color: #f59e0b;
}

.activity-error {
  border-left-color: #ef4444;
}

.activity-neutral {
  border-left-color: #6b7280;
}

.activity-default {
  border-left-color: #e5e7eb;
}

.activity-icon {
  flex-shrink: 0;
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 50%;
  font-size: 1.25rem;
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.25rem;
}

.activity-title {
  font-weight: 600;
  color: #111827;
  font-size: 0.875rem;
}

.activity-time {
  font-size: 0.75rem;
  color: #6b7280;
  white-space: nowrap;
  margin-left: 0.5rem;
}

.activity-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 0.5rem 0;
  line-height: 1.4;
}

.activity-metadata {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.metadata-item {
  font-size: 0.75rem;
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}

.activity-actions {
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
}

.action-btn {
  padding: 0.25rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
}

.action-btn.primary:hover {
  background: #2563eb;
}

.action-btn.secondary {
  background: #6b7280;
  color: white;
}

.action-btn.secondary:hover {
  background: #4b5563;
}

.load-more-container {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  text-align: center;
}

.load-more-btn {
  padding: 0.75rem 2rem;
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.load-more-btn:hover:not(:disabled) {
  background: #e5e7eb;
  color: #111827;
}

.load-more-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Responsive design */
@media (max-width: 640px) {
  .feed-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .feed-controls {
    justify-content: space-between;
  }
  
  .activity-item {
    padding: 0.75rem 1rem;
  }
  
  .activity-header {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .activity-time {
    margin-left: 0;
  }
  
  .activity-actions {
    margin-top: 0.5rem;
  }
}
</style>
