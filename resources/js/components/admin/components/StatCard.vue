<template>
  <div :class="cardClasses">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div :class="iconClasses">
          <component :is="iconComponent" class="w-6 h-6 text-white" />
        </div>
      </div>
      <div class="ml-5 w-0 flex-1">
        <dl>
          <dt class="text-sm font-medium text-gray-500 truncate">
            {{ title }}
          </dt>
          <dd class="flex items-baseline">
            <div class="text-2xl font-semibold text-gray-900">
              {{ formatValue(value) }}
            </div>
            <div v-if="change !== null" :class="changeClasses" class="ml-2 flex items-baseline text-sm font-semibold">
              <component :is="trendIcon" class="self-center flex-shrink-0 h-5 w-5" aria-hidden="true" />
              <span class="sr-only"> {{ trend }} from last period </span>
              {{ Math.abs(change) }}%
            </div>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { 
  DocumentTextIcon, 
  ClockIcon, 
  UsersIcon, 
  HeartIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ChartBarIcon,
  CogIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: String,
  value: [String, Number],
  change: Number,
  icon: String,
  color: String,
  trend: String
})

const iconMap = {
  document: DocumentTextIcon,
  clock: ClockIcon,
  users: UsersIcon,
  heart: HeartIcon,
  chart: ChartBarIcon,
  cog: CogIcon
}

const colorMap = {
  blue: 'bg-blue-500',
  yellow: 'bg-yellow-500',
  green: 'bg-green-500',
  red: 'bg-red-500',
  purple: 'bg-purple-500',
  indigo: 'bg-indigo-500'
}

const iconComponent = computed(() => iconMap[props.icon] || DocumentTextIcon)

const cardClasses = computed(() => [
  'bg-white overflow-hidden shadow-lg rounded-lg p-5',
  'hover:shadow-xl transition-shadow duration-200'
])

const iconClasses = computed(() => [
  'w-10 h-10 rounded-md flex items-center justify-center',
  colorMap[props.color] || 'bg-gray-500'
])

const trendIcon = computed(() => {
  if (props.change > 0) return ArrowUpIcon
  if (props.change < 0) return ArrowDownIcon
  return null
})

const changeClasses = computed(() => {
  if (props.change > 0) return 'text-green-600'
  if (props.change < 0) return 'text-red-600'
  return 'text-gray-500'
})

const formatValue = (value) => {
  if (typeof value === 'number') {
    return value.toLocaleString()
  }
  return value
}
</script>
