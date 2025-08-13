#!/bin/bash

# Array of missing components
declare -a missing_components=(
  "resources/js/components/agent/MonitorDetailsModal.vue"
  "resources/js/components/agent/PerformanceMetrics.vue"
  "resources/js/components/agent/PurchaseQueue.vue"
  "resources/js/components/agent/QuickActions.vue"
  "resources/js/components/agent/RecentActivity.vue"
  "resources/js/components/agent/RecentAlerts.vue"
  "resources/js/components/agent/TicketHeatmap.vue"
  "resources/js/components/ui/MetricCard.vue"
  "resources/js/components/ui/KPICard.vue"
)

# Create directories and components
for component in "${missing_components[@]}"; do
  dir=$(dirname "$component")
  mkdir -p "$dir"
  filename=$(basename "$component" .vue)
  
  cat > "$component" << COMP_EOF
<template>
  <div class="placeholder-component">
    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
      $filename Component Placeholder
    </div>
  </div>
</template>

<script>
export default {
  name: '$filename'
}
</script>
COMP_EOF
done

echo "Created placeholder components"
