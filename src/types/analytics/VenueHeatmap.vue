<template>
  <div class="venue-heatmap">
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
      <div class="text-lg font-medium mb-2">Venue Heatmap</div>
      <div class="text-sm">Heatmap visualization will be displayed here</div>
      <div class="text-xs mt-2">
        Metric: {{ metric }} | Venues: {{ venues.length }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VenueHeatmap',
  
  props: {
    venues: {
      type: Array,
      default: () => []
    },
    metric: {
      type: String,
      default: 'events'
    }
  },
  
  emits: ['venue-selected'],
  
  methods: {
    onVenueClick(venue) {
      this.$emit('venue-selected', venue)
    }
  }
}
</script>

<style scoped>
.venue-heatmap {
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
