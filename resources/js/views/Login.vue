<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import logoImg from '@/assets/images/logo.png'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    await authStore.login(email.value, password.value)
    router.push('/gestor/dashboard')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao fazer login. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[#EAF8FF] px-4">
    <div class="w-full max-w-sm">
      <!-- Logo -->
      <div class="text-center mb-8">
        <img :src="logoImg" alt="E-Gym" class="w-64 h-16 mx-auto">
        <p class="text-gray-500 mt-2">Acesse sua conta</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Error -->
        <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
          {{ error }}
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            E-mail
          </label>
          <Input id="email" v-model="email" type="email" placeholder="seu@email.com" required class="w-full" />
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Senha
          </label>
          <Input id="password" v-model="password" type="password" placeholder="••••••••" required class="w-full" />
        </div>

        <!-- Submit -->
        <Button type="submit" class="w-full bg-[#1EB4F0] hover:bg-[#21C1FF]/90" :disabled="loading">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </Button>
      </form>
    </div>
  </div>
</template>
