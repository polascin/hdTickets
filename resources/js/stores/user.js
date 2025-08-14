import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useUserStore = defineStore('user', () => {
  // State
  const user = ref(null)
  const permissions = ref([])
  const preferences = ref({})
  const loading = ref(false)
  const authenticated = ref(false)

  // Getters
  const isAuthenticated = computed(() => authenticated.value && user.value !== null)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isAgent = computed(() => user.value?.role === 'agent')
  const userName = computed(() => user.value?.name || 'Guest')
  const userEmail = computed(() => user.value?.email || '')
  const userAvatar = computed(() => user.value?.avatar || '/images/default-avatar.png')

  // Check if user has specific permission
  const hasPermission = computed(() => (permission) => {
    if (!permissions.value) return false
    if (Array.isArray(permission)) {
      return permission.some(p => permissions.value.includes(p))
    }
    return permissions.value.includes(permission)
  })

  // Check if user has all specified permissions
  const hasAllPermissions = computed(() => (requiredPermissions) => {
    if (!permissions.value) return false
    if (!Array.isArray(requiredPermissions)) {
      requiredPermissions = [requiredPermissions]
    }
    return requiredPermissions.every(p => permissions.value.includes(p))
  })

  // Actions
  const fetchUser = async () => {
    loading.value = true
    try {
      const response = await axios.get('/api/user')
      user.value = response.data.user
      permissions.value = response.data.permissions || []
      preferences.value = response.data.preferences || {}
      authenticated.value = true
    } catch (error) {
      console.error('Failed to fetch user:', error)
      await logout()
    } finally {
      loading.value = false
    }
  }

  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await axios.post('/api/auth/login', credentials)
      if (response.data.success) {
        user.value = response.data.user
        permissions.value = response.data.permissions || []
        preferences.value = response.data.preferences || {}
        authenticated.value = true
        
        // Store in localStorage for persistence
        localStorage.setItem('user', JSON.stringify(response.data.user))
        return { success: true }
      }
      return { success: false, message: 'Login failed' }
    } catch (error) {
      console.error('Login error:', error)
      return { 
        success: false, 
        message: error.response?.data?.message || 'Login failed' 
      }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    try {
      await axios.post('/api/auth/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      permissions.value = []
      preferences.value = {}
      authenticated.value = false
      
      // Clear localStorage
      localStorage.removeItem('user')
      localStorage.removeItem('permissions')
      
      loading.value = false
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    try {
      const response = await axios.put('/api/user/profile', profileData)
      user.value = { ...user.value, ...response.data.user }
      return { success: true }
    } catch (error) {
      console.error('Profile update error:', error)
      return { 
        success: false, 
        message: error.response?.data?.message || 'Profile update failed' 
      }
    } finally {
      loading.value = false
    }
  }

  const updatePreferences = async (newPreferences) => {
    loading.value = true
    try {
      const response = await axios.put('/api/user/preferences', newPreferences)
      preferences.value = { ...preferences.value, ...response.data.preferences }
      return { success: true }
    } catch (error) {
      console.error('Preferences update error:', error)
      return { 
        success: false, 
        message: error.response?.data?.message || 'Preferences update failed' 
      }
    } finally {
      loading.value = false
    }
  }

  const changePassword = async (passwordData) => {
    loading.value = true
    try {
      await axios.put('/api/user/password', passwordData)
      return { success: true }
    } catch (error) {
      console.error('Password change error:', error)
      return { 
        success: false, 
        message: error.response?.data?.message || 'Password change failed' 
      }
    } finally {
      loading.value = false
    }
  }

  // Initialize from localStorage if available
  const initializeFromStorage = () => {
    const storedUser = localStorage.getItem('user')
    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
        authenticated.value = true
        // Fetch fresh data from server
        fetchUser()
      } catch (error) {
        console.error('Failed to parse stored user data:', error)
        localStorage.removeItem('user')
      }
    }
  }

  return {
    // State
    user,
    permissions,
    preferences,
    loading,
    authenticated,
    
    // Getters
    isAuthenticated,
    isAdmin,
    isAgent,
    userName,
    userEmail,
    userAvatar,
    hasPermission,
    hasAllPermissions,
    
    // Actions
    fetchUser,
    login,
    logout,
    updateProfile,
    updatePreferences,
    changePassword,
    initializeFromStorage
  }
})
