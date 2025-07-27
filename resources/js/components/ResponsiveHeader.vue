<template>
  <header class="responsive-header" :class="{ 'mobile-view': isMobile }">
    <div class="header-container">
      <!-- Logo and Title -->
      <div class="header-brand">
        <div class="logo">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/>
          </svg>
        </div>
        <h1 class="brand-title" v-if="!isMobile">Sports Tickets Monitor</h1>
      </div>

      <!-- Search Bar (Hidden on mobile) -->
      <div class="header-search" v-if="!isMobile">
        <input 
          type="text" 
          placeholder="Search events, teams, venues..."
          v-model="searchQuery"
          @input="handleSearch"
          class="search-input"
        />
        <button class="search-btn" @click="performSearch">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
          </svg>
        </button>
      </div>

      <!-- User Menu and Actions -->
      <div class="header-actions">
        <!-- Notifications -->
        <button 
          class="action-btn notification-btn" 
          @click="toggleNotifications"
          :class="{ 'has-notifications': hasUnreadNotifications }"
        >
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
          </svg>
          <span class="notification-badge" v-if="notificationCount > 0">{{ notificationCount }}</span>
        </button>

        <!-- Settings -->
        <button class="action-btn settings-btn" @click="toggleSettings" v-if="!isMobile">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
          </svg>
        </button>

        <!-- Mobile Menu Toggle -->
        <button class="action-btn menu-toggle" @click="toggleMobileMenu" v-if="isMobile">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
          </svg>
        </button>

        <!-- User Profile -->
        <div class="user-profile" @click="toggleUserMenu" ref="userProfile">
          <img 
            :src="user.avatar || defaultAvatar" 
            :alt="user.name || 'User'"
            class="user-avatar"
          />
          <span class="user-name" v-if="!isMobile">{{ user.name || 'User' }}</span>
          <svg class="dropdown-icon" viewBox="0 0 24 24" fill="currentColor" v-if="!isMobile">
            <path d="M7 10l5 5 5-5z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Mobile Search Bar (Visible when toggled) -->
    <div class="mobile-search" v-if="isMobile && showMobileSearch">
      <input 
        type="text" 
        placeholder="Search events, teams, venues..."
        v-model="searchQuery"
        @input="handleSearch"
        class="search-input mobile"
      />
      <button class="search-btn" @click="performSearch">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
        </svg>
      </button>
    </div>

    <!-- User Dropdown Menu -->
    <div class="user-dropdown" v-if="showUserMenu" ref="dropdown">
      <a href="/profile" class="dropdown-item">Profile</a>
      <a href="/preferences" class="dropdown-item">Preferences</a>
      <a href="/watchlist" class="dropdown-item">Watchlist</a>
      <div class="dropdown-divider"></div>
      <button @click="logout" class="dropdown-item logout">Logout</button>
    </div>
  </header>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useWindowSize } from '@vueuse/core';
import cssTimestamp from '../utils/cssTimestamp.js';

export default {
  name: 'ResponsiveHeader',
  setup() {
    const { width } = useWindowSize();
    const isMobile = computed(() => width.value < 768);
    
    const searchQuery = ref('');
    const showMobileSearch = ref(false);
    const showUserMenu = ref(false);
    const hasUnreadNotifications = ref(false);
    const notificationCount = ref(0);
    
    const user = ref({
      name: 'Sports Fan',
      avatar: null
    });
    
    const defaultAvatar = '/assets/images/default-avatar.png';
    
    // Methods
    const handleSearch = () => {
      // Debounced search implementation
      // Implementation would go here
    };
    
    const performSearch = () => {
      if (searchQuery.value.trim()) {
        // Emit search event or navigate
        console.log('Searching for:', searchQuery.value);
      }
    };
    
    const toggleNotifications = () => {
      // Toggle notification center
      console.log('Toggle notifications');
    };
    
    const toggleSettings = () => {
      // Toggle settings panel
      console.log('Toggle settings');
    };
    
    const toggleMobileMenu = () => {
      showMobileSearch.value = !showMobileSearch.value;
    };
    
    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value;
    };
    
    const logout = () => {
      // Handle logout
      console.log('Logout');
    };
    
    // Click outside handler
    const handleClickOutside = (event) => {
      if (showUserMenu.value && !event.target.closest('.user-profile') && !event.target.closest('.user-dropdown')) {
        showUserMenu.value = false;
      }
    };
    
    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
      
      // Load component-specific CSS with timestamp
      cssTimestamp.loadCSS('/assets/css/responsive-header.css', {
        id: 'responsive-header-styles'
      });
    });
    
    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });
    
    return {
      isMobile,
      searchQuery,
      showMobileSearch,
      showUserMenu,
      hasUnreadNotifications,
      notificationCount,
      user,
      defaultAvatar,
      handleSearch,
      performSearch,
      toggleNotifications,
      toggleSettings,
      toggleMobileMenu,
      toggleUserMenu,
      logout
    };
  }
};
</script>

<style scoped>
.responsive-header {
  @apply bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm;
  position: sticky;
  top: 0;
  z-index: 50;
}

.header-container {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 64px;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  @apply w-8 h-8 text-blue-600;
}

.brand-title {
  @apply text-xl font-semibold text-gray-900 dark:text-white;
  margin: 0;
}

.header-search {
  flex: 1;
  max-width: 400px;
  margin: 0 24px;
  position: relative;
}

.search-input {
  @apply w-full px-4 py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg;
  @apply focus:ring-2 focus:ring-blue-500 focus:border-transparent;
  @apply dark:bg-gray-800 dark:border-gray-600 dark:text-white;
}

.search-input.mobile {
  @apply w-full px-4 py-3;
}

.search-btn {
  position: absolute;
  left: 8px;
  top: 50%;
  transform: translateY(-50%);
  @apply w-5 h-5 text-gray-400;
  background: none;
  border: none;
  cursor: pointer;
}

.mobile-search {
  @apply px-4 py-3 border-b border-gray-200 dark:border-gray-700;
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.action-btn {
  @apply w-10 h-10 flex items-center justify-center rounded-lg;
  @apply text-gray-500 hover:text-gray-700 hover:bg-gray-100;
  @apply dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800;
  border: none;
  cursor: pointer;
  position: relative;
}

.action-btn svg {
  @apply w-5 h-5;
}

.notification-btn.has-notifications {
  @apply text-blue-600;
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  @apply bg-red-500 text-white text-xs rounded-full;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 600;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 8px;
  @apply px-3 py-2 rounded-lg cursor-pointer;
  @apply hover:bg-gray-100 dark:hover:bg-gray-800;
}

.user-avatar {
  @apply w-8 h-8 rounded-full object-cover;
}

.user-name {
  @apply text-sm font-medium text-gray-700 dark:text-gray-300;
}

.dropdown-icon {
  @apply w-4 h-4 text-gray-400;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  @apply mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700;
  z-index: 60;
}

.dropdown-item {
  @apply block w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300;
  @apply hover:bg-gray-100 dark:hover:bg-gray-700;
  text-decoration: none;
  border: none;
  background: none;
  cursor: pointer;
  text-align: left;
}

.dropdown-item.logout {
  @apply text-red-600 dark:text-red-400;
}

.dropdown-divider {
  @apply h-px bg-gray-200 dark:bg-gray-700 my-1;
}

/* Mobile specific styles */
.mobile-view .header-container {
  @apply px-4;
}

.mobile-view .brand-title {
  display: none;
}

@media (max-width: 767px) {
  .header-search {
    display: none;
  }
  
  .user-name {
    display: none;
  }
  
  .dropdown-icon {
    display: none;
  }
}
</style>
