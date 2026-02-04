<script setup lang="ts">
import { computed } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BottomNav from '@/components/gestor/BottomNav.vue'

const authStore = useAuthStore()

const academia = computed(() => authStore.academia)
const user = computed(() => authStore.user)

// Aplicar cores da academia
const primaryColor = computed(() => academia.value?.cor_primaria || '#f59e0b')
</script>

<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-white border-b border-gray-200 backdrop-blur-sm bg-white/95">
      <div class="px-4 py-3.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <!-- Logo -->
            <div class="relative">
              <img
                v-if="academia?.logo"
                :src="academia.logo"
                :alt="academia.nome"
                class="h-11 w-11 rounded-xl object-cover border border-gray-200 shadow-sm"
              />
              <div
                v-else
                class="h-11 w-11 rounded-xl flex items-center justify-center text-white font-semibold text-lg shadow-sm"
                :style="{ backgroundColor: primaryColor }"
              >
                {{ academia?.nome?.charAt(0) || 'E' }}
              </div>
            </div>

            <div>
              <h1 class="font-semibold text-gray-900">{{ academia?.nome || 'E-GYM' }}</h1>
              <p class="text-xs text-gray-500 mt-0.5">{{ user?.name }}</p>
            </div>
          </div>

          <!-- User Menu -->
          <button 
            @click="authStore.logout"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            title="Sair"
          >
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="px-4 py-5 max-w-2xl mx-auto">
      <RouterView />
    </main>

    <!-- Bottom Navigation -->
    <BottomNav :primary-color="primaryColor" />
  </div>
</template>
