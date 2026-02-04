<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import StatCard from '@/components/gestor/StatCard.vue'
import PendingPaymentCard from '@/components/gestor/PendingPaymentCard.vue'

interface DashboardData {
  alunos_ativos: number
  mensalidades_pendentes: number
  receita_mes: number
  a_receber_mes: number
  pendentes: Array<{
    id: number
    aluno: { id: number; nome: string; telefone: string | null }
    valor: number
    data_vencimento: string
    dias_atraso: number
    status: string
    observacao: string | null
  }>
}

const data = ref<DashboardData | null>(null)
const loading = ref(true)
const error = ref('')

const receitaFormatada = computed(() => {
  if (!data.value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(data.value.receita_mes)
})

const aReceberFormatado = computed(() => {
  if (!data.value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(data.value.a_receber_mes)
})

async function fetchDashboard() {
  loading.value = true
  error.value = ''

  try {
    const response = await axios.get('/api/gestor/dashboard')
    data.value = response.data
  } catch (e: any) {
    error.value = 'Erro ao carregar dados do dashboard.'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function handlePagar(id: number) {
  try {
    await axios.post(`/api/gestor/mensalidades/${id}/pagar`)
    // Recarregar dados
    await fetchDashboard()
  } catch (e) {
    console.error('Erro ao registrar pagamento:', e)
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>

<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl">
      {{ error }}
    </div>

    <!-- Content -->
    <div v-else-if="data" class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Visão geral da academia</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500">Alunos Ativos</p>
                <p class="text-2xl font-semibold text-gray-900">{{ data.alunos_ativos }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500">Pendentes</p>
                <p class="text-2xl font-semibold text-gray-900">{{ data.mensalidades_pendentes }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500">Recebido</p>
                <p class="text-xl font-semibold text-gray-900">{{ receitaFormatada }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500">A Receber</p>
                <p class="text-xl font-semibold text-gray-900">{{ aReceberFormatado }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Payments -->
      <div>
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Cobranças próximas</h2>
            <p class="text-xs text-gray-500">Atrasadas e vencendo nos próximos 2 dias</p>
          </div>
        </div>

        <div v-if="data.pendentes.length === 0" class="bg-white rounded-xl p-8 border border-gray-100 text-center">
          <svg class="w-12 h-12 mx-auto text-emerald-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-gray-900 font-medium">Nenhuma cobrança urgente!</p>
          <p class="text-sm text-gray-500 mt-1">Todas as mensalidades urgentes estão em dia</p>
        </div>

        <div v-else class="space-y-2.5">
          <PendingPaymentCard
            v-for="item in data.pendentes"
            :key="item.id"
            :id="item.id"
            :aluno-nome="item.aluno.nome"
            :aluno-telefone="item.aluno.telefone"
            :valor="Number(item.valor)"
            :data-vencimento="item.data_vencimento"
            :dias-atraso="item.dias_atraso"
            :observacao="item.observacao"
            @pagar="handlePagar"
          />
        </div>
      </div>
    </div>
  </div>
</template>
