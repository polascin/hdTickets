<template>
  <div class="notification-container fixed top-4 right-4 z-50 space-y-2">
    <!-- Notifications will be rendered here -->
  </div>
</template>

<script>
export default {
  name: 'NotificationContainer'
}
</script>

<style scoped>
.notification-container {
  pointer-events: none;
}

.notification-container > * {
  pointer-events: all;
}
</style>
