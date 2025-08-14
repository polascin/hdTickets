<template>
  <div class="user-profile p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">User Profile</h2>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Information -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Personal Information</h3>
          <form @submit.prevent="updateProfile">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                <input v-model="profile.first_name" type="text" class="w-full border rounded-lg px-3 py-2">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <input v-model="profile.last_name" type="text" class="w-full border rounded-lg px-3 py-2">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input v-model="profile.email" type="email" class="w-full border rounded-lg px-3 py-2">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input v-model="profile.phone" type="tel" class="w-full border rounded-lg px-3 py-2">
              </div>
            </div>
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
              <select v-model="profile.timezone" class="w-full border rounded-lg px-3 py-2">
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Chicago">Central Time</option>
                <option value="America/Denver">Mountain Time</option>
                <option value="America/Los_Angeles">Pacific Time</option>
              </select>
            </div>
            <div class="mt-6">
              <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                Update Profile
              </button>
            </div>
          </form>
        </div>

        <!-- Password Change -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
          <h3 class="text-lg font-semibold mb-4">Change Password</h3>
          <form @submit.prevent="changePassword">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                <input v-model="passwordForm.current_password" type="password" class="w-full border rounded-lg px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <input v-model="passwordForm.new_password" type="password" class="w-full border rounded-lg px-3 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <input v-model="passwordForm.new_password_confirmation" type="password" class="w-full border rounded-lg px-3 py-2" required>
              </div>
            </div>
            <div class="mt-6">
              <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                Change Password
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Profile Picture and Stats -->
      <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 text-center">
          <div class="w-32 h-32 mx-auto rounded-full overflow-hidden bg-gray-200 mb-4">
            <img :src="profile.avatar || '/images/default-avatar.png'" :alt="profile.name" class="w-full h-full object-cover">
          </div>
          <h4 class="text-xl font-semibold">{{ profile.name }}</h4>
          <p class="text-gray-600">{{ profile.email }}</p>
          <button class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            Upload Photo
          </button>
        </div>

        <!-- Account Stats -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Account Statistics</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">Member since</span>
              <span class="font-semibold">{{ formatDate(profile.created_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tickets monitored</span>
              <span class="font-semibold">{{ stats.tickets_monitored || 0 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Alerts received</span>
              <span class="font-semibold">{{ stats.alerts_received || 0 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Last login</span>
              <span class="font-semibold">{{ formatDate(profile.last_login) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'UserProfile',
  setup() {
    const profile = reactive({
      first_name: '',
      last_name: '',
      name: '',
      email: '',
      phone: '',
      timezone: 'UTC',
      avatar: null,
      created_at: null,
      last_login: null
    })

    const passwordForm = reactive({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    })

    const stats = reactive({
      tickets_monitored: 0,
      alerts_received: 0
    })

    const fetchProfile = async () => {
      try {
        const response = await axios.get('/api/user/profile')
        Object.assign(profile, response.data.user)
        Object.assign(stats, response.data.stats)
      } catch (error) {
        console.error('Error fetching profile:', error)
      }
    }

    const updateProfile = async () => {
      try {
        await axios.put('/api/user/profile', profile)
        alert('Profile updated successfully!')
      } catch (error) {
        console.error('Error updating profile:', error)
        alert('Error updating profile')
      }
    }

    const changePassword = async () => {
      if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
        alert('New passwords do not match')
        return
      }

      try {
        await axios.put('/api/user/password', passwordForm)
        alert('Password changed successfully!')
        Object.assign(passwordForm, {
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        })
      } catch (error) {
        console.error('Error changing password:', error)
        if (error.response?.status === 422) {
          alert('Current password is incorrect')
        } else {
          alert('Error changing password')
        }
      }
    }

    const formatDate = (date) => {
      if (!date) return 'Never'
      return new Date(date).toLocaleDateString()
    }

    onMounted(() => {
      fetchProfile()
    })

    return {
      profile,
      passwordForm,
      stats,
      updateProfile,
      changePassword,
      formatDate
    }
  }
}
</script>
