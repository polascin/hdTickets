<!--
  HD Tickets User Preferences Panel
  @author Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
  @version 2025.07.v4.0
-->
<template>
  <div class="user-preferences-panel">
    <div class="panel-header">
      <h2 class="panel-title">User Preferences</h2>
      <div class="panel-actions">
        <button @click="resetToDefaults" class="reset-btn" :disabled="saving">
          Reset to Defaults
        </button>
        <button @click="saveAllPreferences" class="save-btn" :disabled="saving || !hasChanges">
          <svg v-if="saving" class="icon spinning">
            <use href="#refresh-icon"></use>
          </svg>
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>

    <div class="preferences-content">
      <!-- Dashboard Preferences -->
      <div class="preference-section">
        <h3 class="section-title">Dashboard Settings</h3>
        <div class="preferences-grid">
          <div class="preference-item">
            <label class="preference-label">
              Theme
              <select v-model="preferences.dashboard.theme" @change="markChanged">
                <option value="light">Light</option>
                <option value="dark">Dark</option>
                <option value="auto">Auto</option>
              </select>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Auto Refresh
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.dashboard.auto_refresh"
                  @change="markChanged"
                  id="auto-refresh"
                >
                <label for="auto-refresh" class="toggle-label"></label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Refresh Interval (seconds)
              <input 
                type="number" 
                v-model.number="preferences.dashboard.refresh_interval"
                @input="markChanged"
                min="10" 
                max="300"
                class="number-input"
              >
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Show Notifications
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.dashboard.show_notifications"
                  @change="markChanged"
                  id="show-notifications"
                >
                <label for="show-notifications" class="toggle-label"></label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Compact Mode
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.dashboard.compact_mode"
                  @change="markChanged"
                  id="compact-mode"
                >
                <label for="compact-mode" class="toggle-label"></label>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Ticket Preferences -->
      <div class="preference-section">
        <h3 class="section-title">Ticket Settings</h3>
        <div class="preferences-grid">
          <div class="preference-item">
            <label class="preference-label">
              Default Sort
              <select v-model="preferences.tickets.default_sort" @change="markChanged">
                <option value="price_asc">Price (Low to High)</option>
                <option value="price_desc">Price (High to Low)</option>
                <option value="date_asc">Date (Earliest First)</option>
                <option value="date_desc">Date (Latest First)</option>
                <option value="venue_asc">Venue (A-Z)</option>
              </select>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Items Per Page
              <select v-model.number="preferences.tickets.items_per_page" @change="markChanged">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Show Unavailable Tickets
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.tickets.show_unavailable"
                  @change="markChanged"
                  id="show-unavailable"
                >
                <label for="show-unavailable" class="toggle-label"></label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Auto-hide Expired
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.tickets.auto_hide_expired"
                  @change="markChanged"
                  id="auto-hide-expired"
                >
                <label for="auto-hide-expired" class="toggle-label"></label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Price Format
              <select v-model="preferences.tickets.price_format" @change="markChanged">
                <option value="USD">USD ($)</option>
                <option value="EUR">EUR (€)</option>
                <option value="GBP">GBP (£)</option>
              </select>
            </label>
          </div>
        </div>
      </div>

      <!-- Alert Preferences -->
      <div class="preference-section">
        <h3 class="section-title">Alert Settings</h3>
        <div class="preferences-grid">
          <div class="preference-item">
            <label class="preference-label">
              Price Drop Threshold (%)
              <input 
                type="number" 
                v-model.number="preferences.alerts.price_drop_threshold"
                @input="markChanged"
                min="1" 
                max="50"
                class="number-input"
              >
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Availability Alerts
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.alerts.availability_alerts"
                  @change="markChanged"
                  id="availability-alerts"
                >
                <label for="availability-alerts" class="toggle-label"></label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Email Notifications
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.alerts.email_notifications"
                  @change="markChanged"
                  id="email-notifications"
                >
                <label for="email-notifications" class="toggle-label"></label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              SMS Notifications
              <div class="toggle-switch">
                <input 
                  type="checkbox" 
                  v-model="preferences.alerts.sms_notifications"
                  @change="markChanged"
                  id="sms-notifications"
                >
                <label for="sms-notifications" class="toggle-label"></label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Alert Frequency
              <select v-model="preferences.alerts.alert_frequency" @change="markChanged">
                <option value="immediate">Immediate</option>
                <option value="hourly">Hourly</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
              </select>
            </label>
          </div>
        </div>
      </div>

      <!-- Monitoring Preferences -->
      <div class="preference-section">
        <h3 class="section-title">Monitoring Settings</h3>
        <div class="preferences-grid">
          <div class="preference-item full-width">
            <label class="preference-label">
              Monitored Platforms
              <div class="platform-checkboxes">
                <label v-for="platform in availablePlatforms" :key="platform.key" class="platform-checkbox">
                  <input 
                    type="checkbox" 
                    :value="platform.key"
                    v-model="preferences.monitoring.platforms"
                    @change="markChanged"
                  >
                  <span>{{ platform.name }}</span>
                </label>
              </div>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Max Price ($)
              <input 
                type="number" 
                v-model.number="preferences.monitoring.max_price"
                @input="markChanged"
                min="0"
                class="number-input"
              >
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Min Price ($)
              <input 
                type="number" 
                v-model.number="preferences.monitoring.min_price"
                @input="markChanged"
                min="0"
                class="number-input"
              >
            </label>
          </div>
          
          <div class="preference-item full-width">
            <label class="preference-label">
              Preferred Sections (comma-separated)
              <input 
                type="text" 
                :value="preferences.monitoring.preferred_sections.join(', ')"
                @input="updatePreferredSections"
                placeholder="e.g., Lower Bowl, Club Level, Upper Deck"
                class="text-input"
              >
            </label>
          </div>
          
          <div class="preference-item full-width">
            <label class="preference-label">
              Exclude Keywords (comma-separated)
              <input 
                type="text" 
                :value="preferences.monitoring.exclude_keywords.join(', ')"
                @input="updateExcludeKeywords"
                placeholder="e.g., parking, merchandise, obstructed"
                class="text-input"
              >
            </label>
          </div>
        </div>
      </div>

      <!-- Display Preferences -->
      <div class="preference-section">
        <h3 class="section-title">Display Settings</h3>
        <div class="preferences-grid">
          <div class="preference-item">
            <label class="preference-label">
              Timezone
              <select v-model="preferences.display.timezone" @change="markChanged">
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Chicago">Central Time</option>
                <option value="America/Denver">Mountain Time</option>
                <option value="America/Los_Angeles">Pacific Time</option>
              </select>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Date Format
              <select v-model="preferences.display.date_format" @change="markChanged">
                <option value="Y-m-d">YYYY-MM-DD</option>
                <option value="m/d/Y">MM/DD/YYYY</option>
                <option value="d/m/Y">DD/MM/YYYY</option>
                <option value="M j, Y">Month DD, YYYY</option>
              </select>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Time Format
              <select v-model="preferences.display.time_format" @change="markChanged">
                <option value="H:i">24-hour (HH:MM)</option>
                <option value="g:i A">12-hour (H:MM AM/PM)</option>
              </select>
            </label>
          </div>
          
          <div class="preference-item">
            <label class="preference-label">
              Currency Symbol
              <select v-model="preferences.display.currency_symbol" @change="markChanged">
                <option value="$">$ (USD)</option>
                <option value="€">€ (EUR)</option>
                <option value="£">£ (GBP)</option>
              </select>
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Save confirmation -->
    <div v-if="showSaveConfirmation" class="save-confirmation">
      <div class="confirmation-content">
        <svg class="success-icon">
          <use href="#check-icon"></use>
        </svg>
        <span>Preferences saved successfully!</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'

// Reactive state
const preferences = reactive({
  dashboard: {
    theme: 'light',
    auto_refresh: true,
    refresh_interval: 30,
    show_notifications: true,
    compact_mode: false
  },
  tickets: {
    default_sort: 'price_asc',
    items_per_page: 25,
    show_unavailable: false,
    auto_hide_expired: true,
    price_format: 'USD'
  },
  alerts: {
    price_drop_threshold: 10,
    availability_alerts: true,
    email_notifications: true,
    sms_notifications: false,
    alert_frequency: 'immediate'
  },
  monitoring: {
    platforms: ['ticketmaster', 'stubhub', 'viagogo'],
    max_price: 1000,
    min_price: 50,
    preferred_sections: [],
    exclude_keywords: []
  },
  display: {
    timezone: 'UTC',
    date_format: 'Y-m-d',
    time_format: 'H:i',
    currency_symbol: '$'
  }
})

const saving = ref(false)
const hasChanges = ref(false)
const showSaveConfirmation = ref(false)
const originalPreferences = ref(null)

// Platform ordering maintained consistently across the application
const availablePlatforms = [
  { key: 'ticketmaster', name: 'Ticketmaster' },
  { key: 'stubhub', name: 'StubHub' },
  { key: 'viagogo', name: 'Viagogo' },
  { key: 'seatgeek', name: 'SeatGeek' },
  { key: 'tickpick', name: 'TickPick' },
  { key: 'funzone', name: 'FunZone' },
  { key: 'eventbrite', name: 'Eventbrite' },
  { key: 'bandsintown', name: 'Bandsintown' }
]

// Methods
const loadPreferences = async () => {
  try {
    const response = await axios.get('/api/v1/preferences')
    const data = response.data.data
    
    Object.keys(preferences).forEach(category => {
      if (data[category]) {
        Object.assign(preferences[category], data[category])
      }
    })
    
    // Store original preferences for comparison
    originalPreferences.value = JSON.parse(JSON.stringify(preferences))
    hasChanges.value = false
  } catch (error) {
    console.error('Error loading preferences:', error)
    showNotification('Failed to load preferences', 'error')
  }
}

const saveAllPreferences = async () => {
  saving.value = true
  
  try {
    const savePromises = []
    
    Object.keys(preferences).forEach(category => {
      Object.keys(preferences[category]).forEach(key => {
        savePromises.push(
          axios.post('/api/v1/preferences', {
            category,
            key,
            value: preferences[category][key]
          })
        )
      })
    })
    
    await Promise.all(savePromises)
    
    // Update original preferences
    originalPreferences.value = JSON.parse(JSON.stringify(preferences))
    hasChanges.value = false
    
    // Show success confirmation
    showSaveConfirmation.value = true
    setTimeout(() => {
      showSaveConfirmation.value = false
    }, 3000)
    
    showNotification('Preferences saved successfully', 'success')
    
    // Apply preferences immediately
    applyPreferences()
    
  } catch (error) {
    console.error('Error saving preferences:', error)
    showNotification('Failed to save preferences', 'error')
  } finally {
    saving.value = false
  }
}

const resetToDefaults = async () => {
  if (confirm('Are you sure you want to reset all preferences to defaults? This action cannot be undone.')) {
    try {
      await axios.post('/api/v1/preferences/reset')
      await loadPreferences()
      showNotification('Preferences reset to defaults', 'success')
      applyPreferences()
    } catch (error) {
      console.error('Error resetting preferences:', error)
      showNotification('Failed to reset preferences', 'error')
    }
  }
}

const markChanged = () => {
  hasChanges.value = JSON.stringify(preferences) !== JSON.stringify(originalPreferences.value)
}

const updatePreferredSections = (event) => {
  const value = event.target.value
  preferences.monitoring.preferred_sections = value
    .split(',')
    .map(s => s.trim())
    .filter(s => s.length > 0)
  markChanged()
}

const updateExcludeKeywords = (event) => {
  const value = event.target.value
  preferences.monitoring.exclude_keywords = value
    .split(',')
    .map(s => s.trim())
    .filter(s => s.length > 0)
  markChanged()
}

const applyPreferences = () => {
  // Apply theme
  if (preferences.dashboard.theme === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
  
  // Update global preferences in utilities
  if (window.hdTicketsPrefs) {
    Object.keys(preferences).forEach(category => {
      Object.keys(preferences[category]).forEach(key => {
        window.hdTicketsPrefs.set(`${category}.${key}`, preferences[category][key])
      })
    })
  }
  
  // Emit preferences changed event
  window.dispatchEvent(new CustomEvent('preferences-changed', {
    detail: preferences
  }))
}

const showNotification = (message, type = 'info') => {
  if (window.hdTicketsUtils && window.hdTicketsUtils.notify) {
    window.hdTicketsUtils.notify(message, type)
  }
}

// Lifecycle
onMounted(() => {
  loadPreferences()
})

// Watch for changes in any preference
watch(preferences, markChanged, { deep: true })
</script>

<style scoped>
.user-preferences-panel {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.panel-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.panel-actions {
  display: flex;
  gap: 0.75rem;
}

.reset-btn, .save-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.reset-btn {
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.reset-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.save-btn {
  background: #3b82f6;
  color: white;
  border: 1px solid #3b82f6;
}

.save-btn:hover:not(:disabled) {
  background: #2563eb;
}

.save-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.icon {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.icon.spinning {
  animation: spin 1s linear infinite;
}

.preferences-content {
  padding: 1.5rem;
}

.preference-section {
  margin-bottom: 2rem;
}

.preference-section:last-child {
  margin-bottom: 0;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.preferences-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.preference-item {
  display: flex;
  flex-direction: column;
}

.preference-item.full-width {
  grid-column: 1 / -1;
}

.preference-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.preference-label select,
.number-input,
.text-input {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  transition: border-color 0.2s ease;
}

.preference-label select:focus,
.number-input:focus,
.text-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-label {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #d1d5db;
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-label:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

input:checked + .toggle-label {
  background-color: #3b82f6;
}

input:checked + .toggle-label:before {
  transform: translateX(20px);
}

.platform-checkboxes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.platform-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
}

.platform-checkbox input {
  width: 16px;
  height: 16px;
}

.save-confirmation {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: #10b981;
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideIn 0.3s ease;
  z-index: 1000;
}

.confirmation-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.success-icon {
  width: 20px;
  height: 20px;
  fill: currentColor;
}

/* Responsive design */
@media (max-width: 768px) {
  .panel-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .panel-actions {
    justify-content: center;
  }
  
  .preferences-grid {
    grid-template-columns: 1fr;
  }
  
  .platform-checkboxes {
    grid-template-columns: 1fr;
  }
}

/* Animations */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
