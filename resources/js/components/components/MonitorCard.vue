<template>
  <div class="monitor-card" :class="statusClass">
    <div class="monitor-header">
      <span class="monitor-id">#{{ monitor.id }}</span>
      <span class="status-badge" :class="getStatusClass(monitor.status)">
        {{ monitor.status }}
      </span>
    </div>
    
    <h4 class="monitor-title">{{ monitor.event_name }}</h4>
    
    <p class="monitor-meta">
      <span>{{ monitor.venue_name }}</span> • 
      <span>{{ formatDate(monitor.event_date) }}</span>
    </p>
    
    <div class="monitor-details">
      <div class="detail-row">
        <span class="label">Price Range:</span>
        <span class="value">${{ monitor.min_price }} - ${{ monitor.max_price }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Tickets Needed:</span>
        <span class="value">{{ monitor.quantity_needed }}</span>
      </div>
      <div class="detail-row">
        <span class="label">Last Check:</span>
        <span class="value">{{ formatDateTime(monitor.last_checked_at) }}</span>
      </div>
    </div>
    
    <div class="monitor-actions">
      <button @click="$emit('view', monitor.id)" class="action-btn view-btn">
        View Details
      </button>
      <button @click="$emit('check-now', monitor.id)" class="action-btn check-btn">
        Check Now
      </button>
      <button 
        @click="$emit('toggle', monitor.id)" 
        :class="monitor.is_active ? 'action-btn pause-btn' : 'action-btn resume-btn'"
      >
        {{ monitor.is_active ? 'Pause' : 'Resume' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  monitor: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['view', 'check-now', 'toggle', 'edit'])

const statusClass = computed(() => {
  const baseClass = 'monitor-card'
  if (!props.monitor.is_active) return `${baseClass} inactive`
  
  switch (props.monitor.status) {
    case 'active':
      return `${baseClass} active`
    case 'checking':
      return `${baseClass} checking`
    case 'error':
      return `${baseClass} error`
    default:
      return baseClass
  }
})

const getStatusClass = (status) => {
  const classes = {
    active: 'status-active',
    paused: 'status-paused',
    checking: 'status-checking',
    error: 'status-error'
  }
  return classes[status] || 'status-default'
}

const formatDate = (dateString) => {
  if (!dateString) return 'Not set'
  return new Date(dateString).toLocaleDateString()
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'Never'
  return new Date(dateString).toLocaleString()
}
</script>

<style scoped>
.monitor-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  border-left: 4px solid #e5e7eb;
}

.monitor-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.monitor-card.active {
  border-left-color: #10b981;
}

.monitor-card.checking {
  border-left-color: #3b82f6;
}

.monitor-card.error {
  border-left-color: #ef4444;
}

.monitor-card.inactive {
  border-left-color: #9ca3af;
  opacity: 0.7;
}

.monitor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.monitor-id {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.875rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-active {
  background: #d1fae5;
  color: #065f46;
}

.status-paused {
  background: #fef3c7;
  color: #92400e;
}

.status-checking {
  background: #dbeafe;
  color: #1e40af;
}

.status-error {
  background: #fee2e2;
  color: #991b1b;
}

.status-default {
  background: #f3f4f6;
  color: #6b7280;
}

.monitor-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #111827;
  line-height: 1.3;
}

.monitor-meta {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 1rem;
  font-weight: 500;
}

.monitor-details {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  border: 1px solid #f3f4f6;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.value {
  font-size: 0.875rem;
  color: #111827;
  font-weight: 600;
}

.monitor-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.action-btn {
  flex: 1;
  min-width: fit-content;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.view-btn {
  background: #3b82f6;
  color: white;
}

.view-btn:hover {
  background: #2563eb;
}

.check-btn {
  background: #6b7280;
  color: white;
}

.check-btn:hover {
  background: #4b5563;
}

.pause-btn {
  background: #f59e0b;
  color: white;
}

.pause-btn:hover {
  background: #d97706;
}

.resume-btn {
  background: #10b981;
  color: white;
}

.resume-btn:hover {
  background: #059669;
}

/* Responsive design */
@media (max-width: 640px) {
  .monitor-actions {
    flex-direction: column;
  }
  
  .action-btn {
    flex: none;
  }
}
</style>
