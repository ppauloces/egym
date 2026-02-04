<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineProps<{
  primaryColor: string
}>()

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const navItems = [
  { name: 'gestor.dashboard', label: 'Inicio', icon: 'home' },
  { name: 'gestor.alunos', label: 'Alunos', icon: 'users' },
  { name: 'gestor.planos', label: 'Planos', icon: 'list' },
  { name: 'gestor.financeiro', label: 'Financeiro', icon: 'chart' },
  { name: 'gestor.mensalidades', label: 'Cobranças', icon: 'wallet' },
]

const isActive = (name: string) => route.name === name

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-2 py-1.5 z-20 backdrop-blur-sm bg-white/95">
    <div class="flex items-center justify-around max-w-2xl mx-auto">
      <button
        v-for="item in navItems"
        :key="item.name"
        @click="router.push({ name: item.name })"
        class="flex flex-col items-center py-2.5 px-4 rounded-xl transition-all relative"
        :class="isActive(item.name) 
          ? 'text-gray-900 bg-gray-100' 
          : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
      >
        <!-- Active indicator -->
        <div 
          v-if="isActive(item.name)"
          class="absolute -top-[1px] left-1/2 -translate-x-1/2 w-12 h-1 rounded-full"
          :style="{ backgroundColor: primaryColor }"
        />
        
        <!-- Icons -->
        <svg 
          v-if="item.icon === 'home'" 
          class="w-6 h-6" 
          :fill="isActive(item.name) ? 'currentColor' : 'none'"
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <svg 
          v-else-if="item.icon === 'users'" 
          class="w-6 h-6" 
          :fill="isActive(item.name) ? 'currentColor' : 'none'"
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg 
          v-else-if="item.icon === 'list'" 
          class="w-6 h-6" 
          :fill="isActive(item.name) ? 'currentColor' : 'none'"
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <svg 
          v-else-if="item.icon === 'chart'" 
          class="w-6 h-6" 
          :fill="isActive(item.name) ? 'currentColor' : 'none'"
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <svg 
          v-else-if="item.icon === 'wallet'" 
          class="w-6 h-6" 
          :fill="isActive(item.name) ? 'currentColor' : 'none'"
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>

        <span 
          class="text-xs mt-1.5 font-medium"
          :class="isActive(item.name) ? 'font-semibold' : ''"
        >
          {{ item.label }}
        </span>
      </button>
    </div>
  </nav>
</template>
