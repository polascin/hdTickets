<template>
  <div class="activity-feed">
    <div v-if="activities.length === 0" class="text-center text-gray-600">
      No recent activities
    </div>
    <ul v-else class="space-y-4">
      <li v-for="activity in activities" :key="activity.id" class="bg-gray-50 p-4 rounded-lg shadow">
        <div class="flex justify-between items-center">
          <div class="flex items-center">
            <div :class="['text-gray-400', 'mr-4', iconClasses(activity.type)]">
              <component :is="activityIcon(activity.type)" class="w-5 h-5" />
            </div>
            <div>
              <p class="text-sm text-gray-900">{{ activity.message }}</p>
              <p class="text-sm text-gray-500">{{ formatDate(activity.timestamp) }}</p>
            </div>
          </div>
          <div :class="['text-sm', labelClasses(activity.status)]">
            {{ activity.status }}
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CheckCircleIcon,
  ExclamationIcon,
  XCircleIcon,
  UserCircleIcon,
  DocumentTextIcon,
  TicketIcon,
  CogIcon
} from '@heroicons/vue/24/solid'

const props = defineProps({
  activities: {
    type: Array,
    default: () => []
  }
})

const activityIcon = type => {
  switch (type) {
    case 'success':
      return CheckCircleIcon
    case 'error':
      return XCircleIcon
    case 'warning':
      return ExclamationIcon
    case 'user':
      return UserCircleIcon
    case 'document':
      return DocumentTextIcon
    case 'ticket':
      return TicketIcon
    case 'config':
      return CogIcon
    default:
      return DocumentTextIcon
  }
}

const iconClasses = type => {
  switch (type) {
    case 'success':
      return 'text-green-400'
    case 'error':
      return 'text-red-400'
    case 'warning':
      return 'text-yellow-400'
    case 'user':
      return 'text-blue-400'
    case 'document':
      return 'text-gray-400'
    case 'ticket':
      return 'text-indigo-400'
    case 'config':
      return 'text-purple-400'
    default:
      return 'text-gray-400'
  }
}

const labelClasses = status => {
  switch (status) {
    case 'completed':
      return 'text-green-600'
    case 'failed':
      return 'text-red-600'
    case 'pending':
      return 'text-yellow-600'
    default:
      return 'text-gray-600'
  }
}

const formatDate = timestamp => {
  const date = new Date(timestamp)
  return `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`
}
</script>

<style scoped>
.activity-feed {
  @apply max-h-96 overflow-y-auto;
}
</style>

