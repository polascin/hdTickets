<template>
  <div 
    :class="[
      'skeleton-screen animate-pulse',
      sizeClass,
      roundedClass,
      { 'dark:bg-gray-700': darkMode }
    ]"
    :style="customStyle"
    :aria-label="ariaLabel"
    role="progressbar"
    aria-busy="true"
  >
    <!-- Text lines skeleton -->
    <template v-if="variant === 'text'">
      <div 
        v-for="(line, index) in lines" 
        :key="index"
        :class="[
          'skeleton-line bg-gray-200 dark:bg-gray-700',
          { 'mb-2': index < lines - 1 }
        ]"
        :style="{ 
          width: getLineWidth(index, line),
          height: `${lineHeight}px`
        }"
      ></div>
    </template>

    <!-- Card skeleton -->
    <template v-else-if="variant === 'card'">
      <div class="skeleton-card-header flex items-center space-x-4 mb-4">
        <div class="skeleton-avatar w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <div class="flex-1">
          <div class="skeleton-title h-4 bg-gray-200 dark:bg-gray-700 rounded mb-2" style="width: 60%"></div>
          <div class="skeleton-subtitle h-3 bg-gray-200 dark:bg-gray-700 rounded" style="width: 40%"></div>
        </div>
      </div>
      <div class="skeleton-card-content space-y-3">
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded" style="width: 100%"></div>
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded" style="width: 85%"></div>
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded" style="width: 70%"></div>
      </div>
    </template>

    <!-- Table skeleton -->
    <template v-else-if="variant === 'table'">
      <div class="skeleton-table space-y-2">
        <!-- Table header -->
        <div class="skeleton-table-header flex space-x-4 pb-2 border-b border-gray-200 dark:border-gray-700">
          <div 
            v-for="col in columns" 
            :key="`header-${col}`"
            class="h-4 bg-gray-200 dark:bg-gray-700 rounded"
            :style="{ width: getColumnWidth(col) }"
          ></div>
        </div>
        <!-- Table rows -->
        <div 
          v-for="row in rows" 
          :key="`row-${row}`"
          class="skeleton-table-row flex space-x-4 py-2"
        >
          <div 
            v-for="col in columns" 
            :key="`${row}-${col}`"
            class="h-3 bg-gray-200 dark:bg-gray-700 rounded"
            :style="{ width: getColumnWidth(col) }"
          ></div>
        </div>
      </div>
    </template>

    <!-- Chart skeleton -->
    <template v-else-if="variant === 'chart'">
      <div class="skeleton-chart">
        <div class="skeleton-chart-header flex justify-between items-center mb-4">
          <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded" style="width: 30%"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded" style="width: 15%"></div>
        </div>
        <div class="skeleton-chart-body relative h-64 bg-gray-100 dark:bg-gray-800 rounded">
          <!-- Chart bars -->
          <div class="absolute bottom-0 left-0 right-0 flex items-end justify-between px-4 pb-4">
            <div 
              v-for="bar in 8" 
              :key="`bar-${bar}`"
              class="bg-gray-200 dark:bg-gray-700 rounded-t"
              :style="{ 
                width: '10%', 
                height: `${Math.random() * 60 + 20}%` 
              }"
            ></div>
          </div>
        </div>
      </div>
    </template>

    <!-- List skeleton -->
    <template v-else-if="variant === 'list'">
      <div class="skeleton-list space-y-4">
        <div 
          v-for="item in items" 
          :key="`item-${item}`"
          class="skeleton-list-item flex items-center space-x-4"
        >
          <div class="skeleton-icon w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded" style="width: 60%"></div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded" style="width: 40%"></div>
          </div>
          <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded" style="width: 80px"></div>
        </div>
      </div>
    </template>

    <!-- Profile skeleton -->
    <template v-else-if="variant === 'profile'">
      <div class="skeleton-profile text-center">
        <div class="skeleton-avatar w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-full mx-auto mb-4"></div>
        <div class="skeleton-name h-6 bg-gray-200 dark:bg-gray-700 rounded mx-auto mb-2" style="width: 200px"></div>
        <div class="skeleton-title h-4 bg-gray-200 dark:bg-gray-700 rounded mx-auto mb-4" style="width: 150px"></div>
        <div class="skeleton-stats flex justify-center space-x-8 mb-4">
          <div 
            v-for="stat in 3" 
            :key="`stat-${stat}`"
            class="text-center"
          >
            <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded mb-1" style="width: 60px"></div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded" style="width: 80px"></div>
          </div>
        </div>
      </div>
    </template>

    <!-- Default rectangle skeleton -->
    <template v-else>
      <div 
        :class="[
          'skeleton-default bg-gray-200 dark:bg-gray-700',
          roundedClass
        ]"
        :style="{ 
          width: '100%', 
          height: typeof height === 'number' ? `${height}px` : height 
        }"
      ></div>
    </template>

    <!-- Loading indicator overlay -->
    <div 
      v-if="showOverlay"
      class="skeleton-overlay absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-gray-900/80"
    >
      <div class="skeleton-spinner">
        <svg 
          class="animate-spin h-8 w-8 text-blue-600" 
          xmlns="http://www.w3.org/2000/svg" 
          fill="none" 
          viewBox="0 0 24 24"
        >
          <circle 
            class="opacity-25" 
            cx="12" 
            cy="12" 
            r="10" 
            stroke="currentColor" 
            stroke-width="4"
          ></circle>
          <path 
            class="opacity-75" 
            fill="currentColor" 
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SkeletonScreen',
  props: {
    variant: {
      type: String,
      default: 'default',
      validator: (value) => [
        'default', 'text', 'card', 'table', 'chart', 
        'list', 'profile', 'image', 'button'
      ].includes(value)
    },
    lines: {
      type: Number,
      default: 3
    },
    lineHeight: {
      type: Number,
      default: 16
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: 200
    },
    size: {
      type: String,
      default: 'medium',
      validator: (value) => ['small', 'medium', 'large'].includes(value)
    },
    rounded: {
      type: String,
      default: 'medium',
      validator: (value) => ['none', 'small', 'medium', 'large', 'full'].includes(value)
    },
    rows: {
      type: Number,
      default: 5
    },
    columns: {
      type: Number,
      default: 4
    },
    items: {
      type: Number,
      default: 3
    },
    showOverlay: {
      type: Boolean,
      default: false
    },
    darkMode: {
      type: Boolean,
      default: false
    },
    ariaLabel: {
      type: String,
      default: 'Loading content'
    },
    customClass: {
      type: String,
      default: ''
    }
  },
  computed: {
    sizeClass() {
      const sizeMap = {
        small: 'p-2',
        medium: 'p-4',
        large: 'p-6'
      };
      return sizeMap[this.size] || sizeMap.medium;
    },
    roundedClass() {
      const roundedMap = {
        none: 'rounded-none',
        small: 'rounded-sm',
        medium: 'rounded-md',
        large: 'rounded-lg',
        full: 'rounded-full'
      };
      return roundedMap[this.rounded] || roundedMap.medium;
    },
    customStyle() {
      const style = {};
      
      if (this.width !== '100%') {
        style.width = typeof this.width === 'number' ? `${this.width}px` : this.width;
      }
      
      return style;
    }
  },
  methods: {
    getLineWidth(index, total) {
      // Make last line shorter for more realistic text appearance
      if (index === total - 1 && total > 1) {
        return '75%';
      }
      
      // Vary line widths slightly
      const variations = ['100%', '95%', '90%', '85%'];
      return variations[index % variations.length];
    },
    getColumnWidth(columnIndex) {
      // Vary column widths for table
      const widths = ['25%', '20%', '30%', '25%'];
      return widths[columnIndex % widths.length];
    }
  },
  mounted() {
    // Performance tracking
    if (window.performanceMonitoring) {
      window.performanceMonitoring.mark(`skeleton-${this.variant}-mounted`);
    }
  }
};
</script>

<style scoped>
.skeleton-screen {
  position: relative;
  overflow: hidden;
}

.skeleton-line,
.skeleton-default,
.skeleton-avatar,
.skeleton-title,
.skeleton-subtitle,
.skeleton-icon {
  background: linear-gradient(90deg, 
    rgba(243, 244, 246, 1) 25%, 
    rgba(229, 231, 235, 1) 50%, 
    rgba(243, 244, 246, 1) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.dark .skeleton-line,
.dark .skeleton-default,
.dark .skeleton-avatar,
.dark .skeleton-title,
.dark .skeleton-subtitle,
.dark .skeleton-icon {
  background: linear-gradient(90deg, 
    rgba(55, 65, 81, 1) 25%, 
    rgba(75, 85, 99, 1) 50%, 
    rgba(55, 65, 81, 1) 75%
  );
  background-size: 200% 100%;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

.skeleton-overlay {
  backdrop-filter: blur(2px);
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  .skeleton-line,
  .skeleton-default,
  .skeleton-avatar,
  .skeleton-title,
  .skeleton-subtitle,
  .skeleton-icon {
    animation: none;
  }
  
  .animate-pulse,
  .animate-spin {
    animation: none;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .skeleton-line,
  .skeleton-default,
  .skeleton-avatar,
  .skeleton-title,
  .skeleton-subtitle,
  .skeleton-icon {
    background: #000;
    opacity: 0.1;
  }
  
  .dark .skeleton-line,
  .dark .skeleton-default,
  .dark .skeleton-avatar,
  .dark .skeleton-title,
  .dark .skeleton-subtitle,
  .dark .skeleton-icon {
    background: #fff;
    opacity: 0.1;
  }
}
</style>
