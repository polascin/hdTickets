<template>
  <div class="availability-indicator">
    <span :class="{ 'available': available, 'unavailable': !available }">
      {{ available ? 'Available' : 'Sold Out' }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'AvailabilityIndicator',
  props: {
    available: {
      type: Boolean,
      required: true
    }
  }
}
</script>

<style scoped>
.availability-indicator {
  font-size: 0.875rem;
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  text-align: center;
}

.available {
  color: #ffffff;
  background-color: #2a9d8f;
}

.unavailable {
  color: #ffffff;
  background-color: #e63946;
}
</style>

