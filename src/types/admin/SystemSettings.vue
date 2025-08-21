<template>
  <div class="system-settings p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">System Settings</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- General Settings -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">General Settings</h3>
        <form @submit.prevent="saveGeneralSettings">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Application Name</label>
            <input v-model="settings.app_name" type="text" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
            <input v-model="settings.contact_email" type="email" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Mode</label>
            <select v-model="settings.maintenance_mode" class="w-full border rounded-lg px-3 py-2">
              <option value="false">Disabled</option>
              <option value="true">Enabled</option>
            </select>
          </div>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            Save General Settings
          </button>
        </form>
      </div>

      <!-- Scraping Settings -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Scraping Settings</h3>
        <form @submit.prevent="saveScrapingSettings">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Scraping Interval (minutes)</label>
            <input v-model="settings.scraping_interval" type="number" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Concurrent Threads</label>
            <input v-model="settings.max_threads" type="number" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Request Timeout (seconds)</label>
            <input v-model="settings.request_timeout" type="number" class="w-full border rounded-lg px-3 py-2">
          </div>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            Save Scraping Settings
          </button>
        </form>
      </div>

      <!-- API Settings -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">API Settings</h3>
        <form @submit.prevent="saveApiSettings">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rate Limit (requests/minute)</label>
            <input v-model="settings.api_rate_limit" type="number" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">API Version</label>
            <select v-model="settings.api_version" class="w-full border rounded-lg px-3 py-2">
              <option value="v1">v1</option>
              <option value="v2">v2</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Enable API Documentation</label>
            <select v-model="settings.api_docs_enabled" class="w-full border rounded-lg px-3 py-2">
              <option value="false">Disabled</option>
              <option value="true">Enabled</option>
            </select>
          </div>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            Save API Settings
          </button>
        </form>
      </div>

      <!-- Security Settings -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Security Settings</h3>
        <form @submit.prevent="saveSecuritySettings">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Session Lifetime (minutes)</label>
            <input v-model="settings.session_lifetime" type="number" class="w-full border rounded-lg px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Enable Two-Factor Authentication</label>
            <select v-model="settings.two_factor_enabled" class="w-full border rounded-lg px-3 py-2">
              <option value="false">Disabled</option>
              <option value="true">Enabled</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Password Complexity</label>
            <select v-model="settings.password_complexity" class="w-full border rounded-lg px-3 py-2">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            Save Security Settings
          </button>
        </form>
      </div>
    </div>

    <!-- System Information -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold mb-4">System Information</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="text-sm text-gray-600">Laravel Version</div>
          <div class="text-lg font-semibold">{{ systemInfo.laravel_version }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="text-sm text-gray-600">PHP Version</div>
          <div class="text-lg font-semibold">{{ systemInfo.php_version }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="text-sm text-gray-600">Database Version</div>
          <div class="text-lg font-semibold">{{ systemInfo.database_version }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="text-sm text-gray-600">Redis Status</div>
          <div class="text-lg font-semibold" :class="systemInfo.redis_status === 'connected' ? 'text-green-600' : 'text-red-600'">
            {{ systemInfo.redis_status }}
          </div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="text-sm text-gray-600">Queue Status</div>
          <div class="text-lg font-semibold" :class="systemInfo.queue_status === 'running' ? 'text-green-600' : 'text-red-600'">
            {{ systemInfo.queue_status }}
          </div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="text-sm text-gray-600">Storage Used</div>
          <div class="text-lg font-semibold">{{ systemInfo.storage_used }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'SystemSettings',
  setup() {
    const settings = reactive({
      app_name: 'HD Tickets',
      contact_email: '',
      maintenance_mode: 'false',
      scraping_interval: 30,
      max_threads: 5,
      request_timeout: 60,
      api_rate_limit: 1000,
      api_version: 'v1',
      api_docs_enabled: 'true',
      session_lifetime: 120,
      two_factor_enabled: 'false',
      password_complexity: 'medium'
    })

    const systemInfo = reactive({
      laravel_version: '12.x',
      php_version: '8.4',
      database_version: 'MariaDB 10.4',
      redis_status: 'connected',
      queue_status: 'running',
      storage_used: '2.5 GB'
    })

    const fetchSettings = async () => {
      try {
        const response = await axios.get('/api/admin/settings')
        Object.assign(settings, response.data.settings)
      } catch (error) {
        console.error('Error fetching settings:', error)
      }
    }

    const fetchSystemInfo = async () => {
      try {
        const response = await axios.get('/api/admin/system-info')
        Object.assign(systemInfo, response.data.info)
      } catch (error) {
        console.error('Error fetching system info:', error)
      }
    }

    const saveGeneralSettings = async () => {
      try {
        await axios.put('/api/admin/settings/general', {
          app_name: settings.app_name,
          contact_email: settings.contact_email,
          maintenance_mode: settings.maintenance_mode
        })
        alert('General settings saved successfully!')
      } catch (error) {
        console.error('Error saving general settings:', error)
        alert('Error saving general settings')
      }
    }

    const saveScrapingSettings = async () => {
      try {
        await axios.put('/api/admin/settings/scraping', {
          scraping_interval: settings.scraping_interval,
          max_threads: settings.max_threads,
          request_timeout: settings.request_timeout
        })
        alert('Scraping settings saved successfully!')
      } catch (error) {
        console.error('Error saving scraping settings:', error)
        alert('Error saving scraping settings')
      }
    }

    const saveApiSettings = async () => {
      try {
        await axios.put('/api/admin/settings/api', {
          api_rate_limit: settings.api_rate_limit,
          api_version: settings.api_version,
          api_docs_enabled: settings.api_docs_enabled
        })
        alert('API settings saved successfully!')
      } catch (error) {
        console.error('Error saving API settings:', error)
        alert('Error saving API settings')
      }
    }

    const saveSecuritySettings = async () => {
      try {
        await axios.put('/api/admin/settings/security', {
          session_lifetime: settings.session_lifetime,
          two_factor_enabled: settings.two_factor_enabled,
          password_complexity: settings.password_complexity
        })
        alert('Security settings saved successfully!')
      } catch (error) {
        console.error('Error saving security settings:', error)
        alert('Error saving security settings')
      }
    }

    onMounted(() => {
      fetchSettings()
      fetchSystemInfo()
    })

    return {
      settings,
      systemInfo,
      saveGeneralSettings,
      saveScrapingSettings,
      saveApiSettings,
      saveSecuritySettings
    }
  }
}
</script>
