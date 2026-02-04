<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import NovaMovimentacaoDialog from '@/components/gestor/NovaMovimentacaoDialog.vue'

interface ResumoMensal {
  mes: number
  ano: number
  entradas: {
    recebido: number
    a_receber: number
    total: number
  }
  saidas: {
    pago: number
    a_pagar: number
    total: number
  }
  saldo: {
    realizado: number
    previsto: number
  }
}

interface Transacao {
  id: string
  tipo: string
  descricao: string
  categoria: string
  aluno?: string | null
  valor: number
  data: string
  metodo: string
  parcela?: string
}

const resumo = ref<ResumoMensal | null>(null)
const loading = ref(true)
const dialogEntradaOpen = ref(false)
const dialogSaidaOpen = ref(false)

const mesAtual = new Date().getMonth() + 1
const anoAtual = new Date().getFullYear()

// Extrato
const abaExtrato = ref<'entrada' | 'saida'>('saida')
const transacoes = ref<Transacao[]>([])
const loadingExtrato = ref(false)

const nomesMeses = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
]

const mesNome = computed(() => nomesMeses[mesAtual - 1])

async function fetchResumo() {
  loading.value = true
  try {
    const response = await axios.get('/api/gestor/movimentacoes/resumo', {
      params: { mes: mesAtual, ano: anoAtual }
    })
    resumo.value = response.data
  } catch (e) {
    console.error('Erro ao carregar resumo:', e)
  } finally {
    loading.value = false
  }
}

function formatarMoeda(valor: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor)
}

function formatarData(data: string): string {
  const [year, month, day] = data.split('T')[0].split('-')
  return `${day}/${month}/${year}`
}

async function fetchExtrato() {
  loadingExtrato.value = true
  try {
    const response = await axios.get('/api/gestor/movimentacoes/extrato', {
      params: { 
        tipo: abaExtrato.value,
        mes: mesAtual, 
        ano: anoAtual 
      }
    })
    transacoes.value = response.data
  } catch (e) {
    console.error('Erro ao carregar extrato:', e)
  } finally {
    loadingExtrato.value = false
  }
}

function handleMovimentacaoCriada() {
  fetchResumo()
  fetchExtrato()
}

function trocarAbaExtrato(tipo: 'entrada' | 'saida') {
  abaExtrato.value = tipo
  fetchExtrato()
}

function getMetodoPagamento(metodo: string): string {
  const metodos: Record<string, string> = {
    pix: 'PIX',
    dinheiro: 'Dinheiro',
    cartao: 'Cartão',
    cartao_credito: 'Cartão Crédito',
    cartao_debito: 'Cartão Débito',
    boleto: 'Boleto',
    transferencia: 'Transferência'
  }
  return metodos[metodo] || metodo
}

onMounted(() => {
  fetchResumo()
  fetchExtrato()
})
</script>

<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Financeiro</h1>
          <p class="text-sm text-gray-500 mt-1">{{ mesNome }} de {{ anoAtual }}</p>
        </div>
        <div class="relative group">
          <button 
            class="w-5 h-5 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
            type="button"
          >
            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
          <div class="absolute left-0 top-8 w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10">
            <p class="font-medium mb-1">💡 Informação</p>
            <p>As mensalidades pagas são automaticamente incluídas como entradas no resumo financeiro.</p>
            <div class="absolute -top-1 left-3 w-2 h-2 bg-gray-900 transform rotate-45"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- Resumo -->
    <div v-else-if="resumo" class="space-y-4">
      <!-- Saldo -->
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-blue-100 text-sm">Saldo do Mês</p>
            <p class="text-3xl font-bold mt-1">
              {{ formatarMoeda(resumo.saldo.realizado) }}
            </p>
          </div>
          <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="flex items-center gap-4 text-sm">
          <div>
            <span class="text-blue-100">Recebido - Pago</span>
          </div>
        </div>
      </div>

      <!-- Entradas e Saídas -->
      <div class="grid grid-cols-2 gap-3">
        <!-- Entradas -->
        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <div class="flex items-center gap-2 mb-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Recebido</p>
              <p class="text-xl font-semibold text-emerald-600">
                {{ formatarMoeda(resumo.entradas.recebido) }}
              </p>
            </div>
          </div>
          <div class="space-y-1 text-xs">
            <div class="flex justify-between text-gray-600">
              <span>A receber:</span>
              <span class="font-medium">{{ formatarMoeda(resumo.entradas.a_receber) }}</span>
            </div>
            <div class="flex justify-between text-gray-400 border-t border-gray-100 pt-1 mt-1">
              <span>Total previsto:</span>
              <span class="font-medium">{{ formatarMoeda(resumo.entradas.total) }}</span>
            </div>
          </div>
        </div>

        <!-- Saídas -->
        <div class="bg-white rounded-xl p-4 border border-gray-100">
          <div class="flex items-center gap-2 mb-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Pago</p>
              <p class="text-xl font-semibold text-red-600">
                {{ formatarMoeda(resumo.saidas.pago) }}
              </p>
            </div>
          </div>
          <div class="space-y-1 text-xs">
            <div class="flex justify-between text-gray-600">
              <span>A pagar:</span>
              <span class="font-medium">{{ formatarMoeda(resumo.saidas.a_pagar) }}</span>
            </div>
            <div class="flex justify-between text-gray-400 border-t border-gray-100 pt-1 mt-1">
              <span>Total previsto:</span>
              <span class="font-medium">{{ formatarMoeda(resumo.saidas.total) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Ações Rápidas -->
      <div class="space-y-2">
        <p class="text-sm font-medium text-gray-700">Ações Rápidas</p>
        <div class="grid grid-cols-2 gap-2">
          <Button
            @click="dialogEntradaOpen = true"
            class="bg-emerald-600 hover:bg-emerald-700 h-12"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Receita
          </Button>
          <Button
            @click="dialogSaidaOpen = true"
            variant="outline"
            class="h-12 border-red-200 text-red-600 hover:bg-red-50"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
            Nova Despesa
          </Button>
        </div>
      </div>

      <!-- Links Rápidos -->
      <div class="grid grid-cols-3 gap-2">
        <router-link
          to="/gestor/receitas"
          class="flex flex-col items-center justify-center gap-1 p-3 bg-white border border-emerald-200 rounded-lg hover:bg-emerald-50 transition-colors"
        >
          <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
          </svg>
          <span class="text-xs font-medium text-emerald-700">Receitas</span>
        </router-link>
        <router-link
          to="/gestor/despesas"
          class="flex flex-col items-center justify-center gap-1 p-3 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
        >
          <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
          </svg>
          <span class="text-xs font-medium text-red-700">Despesas</span>
        </router-link>
        <router-link
          to="/gestor/mensalidades"
          class="flex flex-col items-center justify-center gap-1 p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
        >
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span class="text-xs font-medium text-gray-700">Mensalidades</span>
        </router-link>
      </div>

      <!-- Extrato Bancário -->
      <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <!-- Header do Extrato -->
        <div class="p-4 border-b border-gray-100">
          <h3 class="font-semibold text-gray-900 mb-3">Extrato do Mês</h3>
          <div class="flex gap-2">
            <button
              @click="trocarAbaExtrato('entrada')"
              class="flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-all"
              :class="abaExtrato === 'entrada' 
                ? 'bg-emerald-500 text-white shadow-sm' 
                : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
            >
              <div class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                </svg>
                Entradas
              </div>
            </button>
            <button
              @click="trocarAbaExtrato('saida')"
              class="flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-all"
              :class="abaExtrato === 'saida' 
                ? 'bg-red-500 text-white shadow-sm' 
                : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
            >
              <div class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                </svg>
                Saídas
              </div>
            </button>
          </div>
        </div>

        <!-- Lista de Transações -->
        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
          <!-- Loading -->
          <div v-if="loadingExtrato" class="p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto"></div>
          </div>

          <!-- Transações -->
          <div
            v-else-if="transacoes.length > 0"
            v-for="transacao in transacoes"
            :key="transacao.id"
            class="p-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-medium text-gray-500 uppercase">
                    {{ transacao.categoria }}
                  </span>
                  <span v-if="transacao.parcela" class="text-xs text-gray-400">
                    • {{ transacao.parcela }}
                  </span>
                </div>
                <p class="font-medium text-gray-900 truncate">
                  {{ transacao.descricao }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs text-gray-500">
                    {{ formatarData(transacao.data) }}
                  </span>
                  <span class="text-xs text-gray-400">•</span>
                  <span class="text-xs text-gray-500">
                    {{ getMetodoPagamento(transacao.metodo) }}
                  </span>
                  <span v-if="transacao.aluno" class="flex items-center gap-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ transacao.aluno }}
                  </span>
                </div>
              </div>
              <div class="text-right flex-shrink-0">
                <p 
                  class="font-semibold text-lg"
                  :class="abaExtrato === 'entrada' ? 'text-emerald-600' : 'text-red-600'"
                >
                  {{ abaExtrato === 'entrada' ? '+' : '-' }}{{ formatarMoeda(transacao.valor) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-gray-500 font-medium">Nenhuma transação</p>
            <p class="text-sm text-gray-400 mt-1">
              Não há {{ abaExtrato === 'entrada' ? 'entradas' : 'saídas' }} registradas neste mês
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <NovaMovimentacaoDialog
      v-model:open="dialogEntradaOpen"
      tipo="entrada"
      @created="handleMovimentacaoCriada"
    />
    <NovaMovimentacaoDialog
      v-model:open="dialogSaidaOpen"
      tipo="saida"
      @created="handleMovimentacaoCriada"
    />
  </div>
</template>
