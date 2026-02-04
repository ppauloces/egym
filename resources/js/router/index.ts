import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Login.vue'),
      meta: { guest: true }
    },
    {
      path: '/gestor',
      component: () => import('@/layouts/GestorLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/gestor/dashboard'
        },
        {
          path: 'dashboard',
          name: 'gestor.dashboard',
          component: () => import('@/views/gestor/Dashboard.vue')
        },
        {
          path: 'alunos',
          name: 'gestor.alunos',
          component: () => import('@/views/gestor/Alunos.vue')
        },
        {
          path: 'mensalidades',
          name: 'gestor.mensalidades',
          component: () => import('@/views/gestor/Mensalidades.vue')
        },
        {
          path: 'planos',
          name: 'gestor.planos',
          component: () => import('@/views/gestor/Planos.vue')
        },
        {
          path: 'financeiro',
          name: 'gestor.financeiro',
          component: () => import('@/views/gestor/Financeiro.vue')
        },
        {
          path: 'despesas',
          name: 'gestor.despesas',
          component: () => import('@/views/gestor/Despesas.vue')
        },
        {
          path: 'receitas',
          name: 'gestor.receitas',
          component: () => import('@/views/gestor/Receitas.vue')
        },
      ]
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Tenta recuperar sessão se tiver token
  if (!authStore.user && authStore.token) {
    try {
      await authStore.fetchUser()
    } catch {
      authStore.logout()
    }
  }

  // Rota requer autenticação
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
  }

  // Rota é para visitantes (login) e usuário está logado
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'gestor.dashboard' })
  }

  next()
})

export default router
