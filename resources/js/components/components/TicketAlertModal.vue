<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h3 class="modal-title">{{ modalTitle }}</h3>
        <button class="close-btn" @click="closeModal">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <div class="modal-body">
        <div v-if="alert" class="alert-details">
          <!-- Alert Type Badge -->
          <div class="alert-type-badge" :class="getAlertTypeClass(alert.type)">
            <span class="alert-icon">{{ getAlertIcon(alert.type) }}</span>
            <span class="alert-type-text">{{ alert.type }}</span>
          </div>
          
          <!-- Event Information -->
          <div class="event-info">
            <h4 class="event-title">{{ alert.event_name }}</h4>
            <p class="event-details">
              <span class="venue">{{ alert.venue_name }}</span> •
              <span class="date">{{ formatDate(alert.event_date) }}</span>
            </p>
          </div>
          
          <!-- Alert Message -->
          <div class="alert-message">
            <p>{{ alert.message }}</p>
          </div>
          
          <!-- Ticket Details (if available) -->
          <div v-if="alert.ticket_details" class="ticket-details">
            <h5 class="section-title">Ticket Details</h5>
            <div class="ticket-grid">
              <div class="ticket-item">
                <span class="label">Section:</span>
                <span class="value">{{ alert.ticket_details.section || 'N/A' }}</span>
              </div>
              <div class="ticket-item">
                <span class="label">Row:</span>
                <span class="value">{{ alert.ticket_details.row || 'N/A' }}</span>
              </div>
              <div class="ticket-item">
                <span class="label">Seats:</span>
                <span class="value">{{ alert.ticket_details.seats || 'N/A' }}</span>
              </div>
              <div class="ticket-item">
                <span class="label">Price:</span>
                <span class="value">${{ alert.ticket_details.price || '0.00' }}</span>
              </div>
              <div class="ticket-item">
                <span class="label">Quantity:</span>
                <span class="value">{{ alert.ticket_details.quantity || 'N/A' }}</span>
              </div>
              <div class="ticket-item">
                <span class="label">Platform:</span>
                <span class="value">{{ alert.ticket_details.platform || 'N/A' }}</span>
              </div>
            </div>
          </div>
          
          <!-- Timestamp -->
          <div class="alert-timestamp">
            <span class="timestamp-label">Alert Time:</span>
            <span class="timestamp-value">{{ formatDateTime(alert.created_at) }}</span>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button v-if="alert && alert.type === 'ticket_found'" class="btn btn-primary" @click="purchaseTicket">
          Purchase Now
        </button>
        <button v-if="alert && alert.type === 'price_drop'" class="btn btn-secondary" @click="viewTickets">
          View Tickets
        </button>
        <button class="btn btn-outline" @click="markAsRead">
          Mark as Read
        </button>
        <button class="btn btn-outline" @click="closeModal">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  alert: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'purchase', 'view-tickets', 'mark-read'])

const modalTitle = computed(() => {
  if (!props.alert) return 'Alert Details'
  
  const titles = {
    ticket_found: 'Tickets Available!',
    price_drop: 'Price Drop Alert',
    error: 'Monitoring Error',
    system: 'System Notification'
  }
  
  return titles[props.alert.type] || 'Alert Details'
})

const closeModal = () => {
  emit('close')
}

const purchaseTicket = () => {
  emit('purchase', props.alert)
}

const viewTickets = () => {
  emit('view-tickets', props.alert)
}

const markAsRead = () => {
  emit('mark-read', props.alert)
}

const getAlertTypeClass = (type) => {
  const classes = {
    ticket_found: 'alert-success',
    price_drop: 'alert-info',
    error: 'alert-error',
    system: 'alert-warning'
  }
  return classes[type] || 'alert-default'
}

const getAlertIcon = (type) => {
  const icons = {
    ticket_found: '🎫',
    price_drop: '📉',
    error: '⚠️',
    system: 'ℹ️'
  }
  return icons[type] || '📢'
}

const formatDate = (dateString) => {
  if (!dateString) return 'Not specified'
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'Unknown'
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 0.75rem;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 0 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.375rem;
  color: #6b7280;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.modal-body {
  padding: 0 1.5rem;
}

.alert-details {
  space-y: 1.5rem;
}

.alert-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
}

.alert-info {
  background: #dbeafe;
  color: #1e40af;
}

.alert-error {
  background: #fee2e2;
  color: #991b1b;
}

.alert-warning {
  background: #fef3c7;
  color: #92400e;
}

.alert-default {
  background: #f3f4f6;
  color: #6b7280;
}

.alert-icon {
  font-size: 1.25rem;
}

.event-info {
  margin-bottom: 1.5rem;
}

.event-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.event-details {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.venue {
  font-weight: 500;
}

.alert-message {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
  border-left: 4px solid #3b82f6;
  margin-bottom: 1.5rem;
}

.alert-message p {
  margin: 0;
  color: #374151;
  line-height: 1.6;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.75rem;
}

.ticket-details {
  margin-bottom: 1.5rem;
}

.ticket-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
}

.ticket-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.alert-timestamp {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
  margin-top: 1.5rem;
}

.timestamp-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.timestamp-value {
  font-size: 0.875rem;
  color: #111827;
  font-weight: 600;
}

.modal-footer {
  display: flex;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  margin-top: 1.5rem;
  justify-content: flex-end;
  flex-wrap: wrap;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
  text-transform: none;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #10b981;
  color: white;
}

.btn-secondary:hover {
  background: #059669;
}

.btn-outline {
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.btn-outline:hover {
  background: #f9fafb;
  color: #111827;
  border-color: #9ca3af;
}

/* Responsive design */
@media (max-width: 640px) {
  .modal-content {
    margin: 0.5rem;
    max-height: 95vh;
  }
  
  .ticket-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
