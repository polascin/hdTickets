<template>
  <div class="filter-panel" :class="{ 'mobile': isMobile }">
    <div class="filter-header">
      <h3 class="filter-title">Filters</h3>
      <button @click="clearAllFilters" class="clear-btn">Clear All</button>
    </div>

    <!-- Price Range Filter -->
    <div class="filter-group">
      <h4 class="filter-group-title">Price Range</h4>
      <div class="price-range">
        <input 
          type="range" 
          v-model="filters.priceMin" 
          :min="0" 
          :max="1000" 
          class="price-slider"
          @input="updateFilters"
        />
        <input 
          type="range" 
          v-model="filters.priceMax" 
          :min="0" 
          :max="1000" 
          class="price-slider"
          @input="updateFilters"
        />
        <div class="price-labels">
          <span>${{ filters.priceMin }}</span>
          <span>${{ filters.priceMax }}</span>
        </div>
      </div>
    </div>

    <!-- Sport Type Filter -->
    <div class="filter-group">
      <h4 class="filter-group-title">Sport Type</h4>
      <div class="sport-options">
        <label v-for="sport in sportTypes" :key="sport" class="sport-label">
          <input 
            type="checkbox" 
            :value="sport" 
            v-model="filters.sports"
            @change="updateFilters"
          />
          {{ sport }}
        </label>
      </div>
    </div>

    <!-- Date Range Filter -->
    <div class="filter-group">
      <h4 class="filter-group-title">Date Range</h4>
      <div class="date-range">
        <input 
          type="date" 
          v-model="filters.startDate" 
          class="date-input"
          @change="updateFilters"
        />
        <input 
          type="date" 
          v-model="filters.endDate" 
          class="date-input"
          @change="updateFilters"
        />
      </div>
    </div>

    <!-- Location Filter -->
    <div class="filter-group">
      <h4 class="filter-group-title">Location</h4>
      <select v-model="filters.location" @change="updateFilters" class="location-select">
        <option value="">All Locations</option>
        <option v-for="location in locations" :key="location" :value="location">
          {{ location }}
        </option>
      </select>
    </div>

    <!-- Availability Filter -->
    <div class="filter-group">
      <h4 class="filter-group-title">Availability</h4>
      <label class="availability-label">
        <input 
          type="checkbox" 
          v-model="filters.availableOnly"
          @change="updateFilters"
        />
        Available Only
      </label>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useWindowSize } from '@vueuse/core';
import cssTimestamp from '../utils/cssTimestamp.js';

export default {
  name: 'FilterPanel',
  emits: ['filter-change'],
  setup(props, { emit }) {
    const { width } = useWindowSize();
    const isMobile = computed(() => width.value < 768);
    
    const filters = ref({
      priceMin: 0,
      priceMax: 1000,
      sports: [],
      startDate: '',
      endDate: '',
      location: '',
      availableOnly: false
    });
    
    const sportTypes = ref([
      'Football',
      'Basketball',
      'Baseball',
      'Hockey',
      'Soccer',
      'Tennis',
      'Golf',
      'Boxing',
      'MMA',
      'Other'
    ]);
    
    const locations = ref([
      'New York, NY',
      'Los Angeles, CA',
      'Chicago, IL',
      'Houston, TX',
      'Phoenix, AZ',
      'Philadelphia, PA',
      'San Antonio, TX',
      'San Diego, CA',
      'Dallas, TX',
      'San Jose, CA'
    ]);
    
    const updateFilters = () => {
      emit('filter-change', { ...filters.value });
    };
    
    const clearAllFilters = () => {
      filters.value = {
        priceMin: 0,
        priceMax: 1000,
        sports: [],
        startDate: '',
        endDate: '',
        location: '',
        availableOnly: false
      };
      updateFilters();
    };
    
    onMounted(() => {
      // Load component-specific CSS with timestamp
      cssTimestamp.loadCSS('/assets/css/filter-panel.css', {
        id: 'filter-panel-styles'
      });
    });
    
    return {
      isMobile,
      filters,
      sportTypes,
      locations,
      updateFilters,
      clearAllFilters
    };
  }
};
</script>

<style scoped>
.filter-panel {
  @apply bg-white dark:bg-gray-800 rounded-lg shadow-md p-6;
  max-width: 300px;
  width: 100%;
}

.filter-panel.mobile {
  max-width: 100%;
  @apply rounded-none shadow-none border-t border-gray-200 dark:border-gray-700;
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  @apply mb-6 pb-4 border-b border-gray-200 dark:border-gray-700;
}

.filter-title {
  @apply text-lg font-semibold text-gray-900 dark:text-white;
  margin: 0;
}

.clear-btn {
  @apply text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300;
  background: none;
  border: none;
  cursor: pointer;
}

.filter-group {
  @apply mb-6;
}

.filter-group-title {
  @apply text-sm font-medium text-gray-700 dark:text-gray-300 mb-3;
  margin: 0;
}

/* Price Range Styles */
.price-range {
  position: relative;
}

.price-slider {
  @apply w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer;
  @apply dark:bg-gray-700;
  margin-bottom: 8px;
}

.price-slider::-webkit-slider-thumb {
  @apply appearance-none w-5 h-5 bg-blue-600 rounded-full cursor-pointer;
}

.price-slider::-moz-range-thumb {
  @apply w-5 h-5 bg-blue-600 rounded-full cursor-pointer border-0;
}

.price-labels {
  display: flex;
  justify-content: space-between;
  @apply text-sm text-gray-600 dark:text-gray-400;
}

/* Sport Options Styles */
.sport-options {
  @apply space-y-2;
}

.sport-label {
  display: flex;
  align-items: center;
  @apply text-sm text-gray-700 dark:text-gray-300 cursor-pointer;
}

.sport-label input[type="checkbox"] {
  @apply mr-2 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded;
  @apply focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800;
  @apply dark:bg-gray-700 dark:border-gray-600;
}

/* Date Range Styles */
.date-range {
  @apply space-y-3;
}

.date-input {
  @apply w-full px-3 py-2 text-sm border border-gray-300 rounded-md;
  @apply focus:ring-2 focus:ring-blue-500 focus:border-transparent;
  @apply dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}

/* Location Styles */
.location-select {
  @apply w-full px-3 py-2 text-sm border border-gray-300 rounded-md;
  @apply focus:ring-2 focus:ring-blue-500 focus:border-transparent;
  @apply dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}

/* Availability Styles */
.availability-label {
  display: flex;
  align-items: center;
  @apply text-sm text-gray-700 dark:text-gray-300 cursor-pointer;
}

.availability-label input[type="checkbox"] {
  @apply mr-2 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded;
  @apply focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800;
  @apply dark:bg-gray-700 dark:border-gray-600;
}

/* Mobile Responsive */
@media (max-width: 767px) {
  .filter-panel {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 40;
    max-height: 60vh;
    overflow-y: auto;
  }
  
  .filter-group {
    @apply mb-4;
  }
  
  .sport-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }
}
</style>
