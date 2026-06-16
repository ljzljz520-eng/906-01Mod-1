<template>
  <button
    type="button"
    @click="$emit('click')"
    class="group relative px-4 py-2 rounded-xl border-2 text-sm font-medium transition-all duration-200 text-left flex items-center gap-2"
    :class="[
      active
        ? 'border-purple-500 bg-purple-50 text-purple-700 shadow-md ring-2 ring-purple-200'
        : 'border-gray-200 bg-white text-gray-700 hover:border-purple-300 hover:bg-purple-50/50 hover:shadow-sm'
    ]"
    :title="description"
  >
    <span>{{ label }}</span>
    <span
      v-if="badge"
      class="px-1.5 py-0.5 rounded-md text-[10px] font-semibold"
      :class="active ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'"
    >
      {{ badge }}
    </span>
    <svg
      v-if="active"
      class="w-4 h-4 text-purple-500"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
    </svg>
  </button>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  },
  description: {
    type: String,
    default: ''
  },
  badge: {
    type: String,
    default: ''
  }
})

defineEmits(['click'])
</script>
