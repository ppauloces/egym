<script setup lang="ts">
defineProps<{
  label: string
  value: string | number
  icon: 'users' | 'alert' | 'money-in' | 'money-out'
  color?: 'blue' | 'yellow' | 'green' | 'red'
}>()
</script>

<template>
  <div class="bg-white rounded-xl p-4 shadow-sm">
    <div class="flex items-center gap-3">
      <!-- Icon -->
      <div
        class="w-12 h-12 rounded-full flex items-center justify-center"
        :class="{
          'bg-blue-100 text-blue-600': color === 'blue',
          'bg-yellow-100 text-yellow-600': color === 'yellow',
          'bg-green-100 text-green-600': color === 'green',
          'bg-red-100 text-red-600': color === 'red',
        }"
      >
        <svg v-if="icon === 'users'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg v-else-if="icon === 'alert'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <svg v-else-if="icon === 'money-in'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg v-else-if="icon === 'money-out'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
      </div>

      <!-- Content -->
      <div>
        <p class="text-2xl font-bold text-gray-900">{{ value }}</p>
        <p class="text-sm text-gray-500">{{ label }}</p>
      </div>
    </div>
  </div>
</template>
