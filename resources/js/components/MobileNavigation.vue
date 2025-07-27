<template>
  <nav class="mobile-navigation">
    <ul class="navigation-list">
      <li v-for="item in navigationItems" :key="item.name">
        <router-link :to="item.path" class="navigation-link">
          <item.icon class="navigation-icon" />
          <span class="navigation-text">{{ item.name }}</span>
        </router-link>
      </li>
    </ul>
  </nav>
</template>

<script>
import { ref } from 'vue';
import { HomeIcon, UserIcon, EventIcon } from '@heroicons/vue/outline'; // Example icons

export default {
  name: 'MobileNavigation',
  setup() {
    const navigationItems = ref([
      { name: 'Home', path: '/', icon: HomeIcon },
      { name: 'Profile', path: '/profile', icon: UserIcon },
      { name: 'Events', path: '/events', icon: EventIcon },
      // Add more items as needed
    ]);

    return {
      navigationItems,
    };
  },
};
</script>

<style scoped>
.mobile-navigation {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: #fff;
  border-top: 1px solid #e5e5e5;
  display: flex;
  justify-content: space-around;
  @apply z-50;
}

.navigation-list {
  display: flex;
  flex: 1;
  justify-content: space-around;
}

.navigation-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  color: #4a4a4a;
  padding: 10px 0;
}

.navigation-link:hover {
  background-color: #f5f5f5;
}

.navigation-icon {
  @apply w-6 h-6 mb-1;
  color: inherit;
}

.navigation-text {
  @apply text-xs;
}
</style>

