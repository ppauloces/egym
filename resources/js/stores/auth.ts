import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

interface User {
  id: number
  name: string
  email: string
  role: string
}

interface Academia {
  id: number
  nome: string
  logo: string | null
  cor_primaria: string | null
  cor_secundaria: string | null
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const academia = ref<Academia | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))

  const isAuthenticated = computed(() => !!user.value && !!token.value)
  const isGestor = computed(() => user.value?.role === 'gestor')

  // Configurar axios com token
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  async function login(email: string, password: string) {
    const response = await axios.post('/api/login', { email, password })

    user.value = response.data.user
    academia.value = response.data.academia
    token.value = response.data.token

    localStorage.setItem('token', response.data.token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`

    return response.data
  }

  async function fetchUser() {
    if (!token.value) return

    const response = await axios.get('/api/me')
    user.value = response.data.user
    academia.value = response.data.academia
  }

  async function logout() {
    try {
      await axios.post('/api/logout')
    } catch {
      // Ignora erro de logout
    }

    user.value = null
    academia.value = null
    token.value = null

    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }

  return {
    user,
    academia,
    token,
    isAuthenticated,
    isGestor,
    login,
    fetchUser,
    logout
  }
})
