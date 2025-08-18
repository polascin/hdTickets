<template>
  <div v-if="show" class="export-modal-overlay" @click="closeModal">
    <div class="export-modal-content" @click.stop>
      <div class="modal-header">
        <h3 class="modal-title">Export Analytics</h3>
        <button class="close-btn" @click="closeModal">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="modal-body">
        <label for="report-type" class="report-label">Select Report Type:</label>
        <select id="report-type" v-model="selectedReportType" class="report-select">
          <option value="overview">Overview Report</option>
          <option value="trends">Trends Report</option>
          <option value="performance">Performance Report</option>
          <option value="success-rates">Success Rates Report</option>
        </select>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" @click="exportReport">
          Export Report
        </button>
        <button class="btn btn-outline" @click="closeModal">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'export'])

const selectedReportType = ref('overview')

const closeModal = () => {
  emit('close')
}

const exportReport = () => {
  emit('export', selectedReportType.value)
  closeModal()
}
</script>

<style scoped>
.export-modal-overlay {
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

.export-modal-content {
  background: white;
  border-radius: 0.75rem;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.modal-title {
  font-size: 1.25rem;
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
  padding: 1rem 1.5rem;
}

.report-label {
  font-size: 0.875rem;
  color: #374151;
  font-weight: 500;
  margin-bottom: 0.5rem;
  display: block;
}

.report-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  background: white;
  color: #374151;
  cursor: pointer;
}

.report-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-footer {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  justify-content: flex-end;
  background: #f9fafb;
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
  .export-modal-content {
    margin: 0.5rem;
    max-height: 95vh;
  }
}
</style>

