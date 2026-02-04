<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import NovaMovimentacaoDialog from '@/components/gestor/NovaMovimentacaoDialog.vue'

interface Categoria {
  id: number
  nome: string
  cor: string
}

interface Parcela {
  id: number
  numero: number
  valor: number
  data_vencimento: string
  data_pagamento: string | null
  status: string
  metodo_pagamento: string | null
}

interface Movimentacao {
  id: number
  descricao: string
  valor_total: number
  data_competencia: string
  categoria: Categoria
  aluno?: { id: number; nome: string } | null
  parcelas: Parcela[]
  status_geral: string
}

const movimentacoes = ref<Movimentacao[]>([])
const loading = ref(true)
const dialogOpen = ref(false)
const dialogPagamentoOpen = ref(false)
const parcelaSelecionada = ref<Parcela | null>(null)
const metodoPagamento = ref('pix')
const pagando = ref(false)

async function fetchMovimentacoes() {
  loading.value = true
  try {
    const response = await axios.get('/api/gestor/movimentacoes', {
      params: { tipo: 'saida' }
    })
    movimentacoes.value = response.data.data
  } catch (e) {
    console.error('Erro ao carregar despesas:', e)
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

function getStatusColor(status: string): string {
  switch (status) {
    case 'pago':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'atrasado':
      return 'bg-red-50 text-red-700 border-red-200'
    default:
      return 'bg-amber-50 text-amber-700 border-amber-200'
  }
}

function getStatusLabel(status: string): string {
  switch (status) {
    case 'pago':
      return 'Pago'
    case 'atrasado':
      return 'Atrasado'
    default:
      return 'Pendente'
  }
}

function handleMovimentacaoCriada() {
  fetchMovimentacoes()
}

function abrirDialogPagamento(parcela: Parcela) {
  parcelaSelecionada.value = parcela
  metodoPagamento.value = 'pix'
  dialogPagamentoOpen.value = true
}

async function registrarPagamento() {
  if (!parcelaSelecionada.value) return

  pagando.value = true
  try {
    await axios.post(`/api/gestor/parcelas/${parcelaSelecionada.value.id}/pagar`, {
      metodo_pagamento: metodoPagamento.value,
      data_pagamento: new Date().toISOString().split('T')[0],
    })

    dialogPagamentoOpen.value = false
    fetchMovimentacoes()
  } catch (error) {
    console.error('Erro ao registrar pagamento:', error)
    alert('Erro ao registrar pagamento. Tente novamente.')
  } finally {
    pagando.value = false
  }
}

onMounted(() => {
  fetchMovimentacoes()
})
</script>

<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Despesas</h1>
        <p class="text-sm text-gray-500 mt-1">Gerencie suas saídas</p>
      </div>
      
      <Button class="shadow-sm" @click="dialogOpen = true">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nova Despesa
      </Button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- List -->
    <div v-else class="space-y-3">
      <div
        v-for="mov in movimentacoes"
        :key="mov.id"
        class="bg-white rounded-xl p-4 border border-gray-100"
      >
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <div 
                class="w-3 h-3 rounded-full flex-shrink-0" 
                :style="{ backgroundColor: mov.categoria.cor }"
              />
              <span class="text-xs font-medium text-gray-500">{{ mov.categoria.nome }}</span>
              <span 
                class="px-2 py-0.5 text-xs font-medium rounded-full border"
                :class="getStatusColor(mov.status_geral)"
              >
                {{ getStatusLabel(mov.status_geral) }}
              </span>
            </div>
            <h3 class="font-semibold text-gray-900">{{ mov.descricao }}</h3>
            <div class="flex items-center gap-2 mt-1">
              <p class="text-sm text-gray-500">
                {{ formatarData(mov.data_competencia) }}
              </p>
              <span v-if="mov.aluno" class="flex items-center gap-1 text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ mov.aluno.nome }}
              </span>
            </div>
          </div>
          <div class="text-right">
            <p class="text-xl font-semibold text-red-600">
              {{ formatarMoeda(mov.valor_total) }}
            </p>
          </div>
        </div>

        <!-- Parcelas -->
        <div v-if="mov.parcelas.length >= 1" class="border-t border-gray-100 pt-3 mt-3">
          <p class="text-xs font-medium text-gray-500 mb-2">
            Parcelas ({{ mov.parcelas.length }})
          </p>
          <div class="space-y-2">
            <div 
              v-for="parcela in mov.parcelas.slice(0, 5)"
              :key="parcela.id"
              class="flex items-center justify-between text-xs p-2 rounded-lg hover:bg-gray-50"
            >
              <div class="flex items-center gap-2">
                <span 
                  class="w-1.5 h-1.5 rounded-full"
                  :class="parcela.status === 'pago' ? 'bg-emerald-500' : 'bg-gray-300'"
                />
                <span class="text-gray-600">
                  {{ parcela.numero === 0 ? 'Entrada' : `${parcela.numero}/${mov.parcelas.length}` }}
                </span>
                <span class="text-gray-400">•</span>
                <span class="text-gray-600">{{ formatarData(parcela.data_vencimento) }}</span>
                <span 
                  v-if="parcela.status === 'pago'"
                  class="px-1.5 py-0.5 text-xs font-medium rounded bg-emerald-50 text-emerald-700"
                >
                  Pago
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="font-medium text-gray-700">
                  {{ formatarMoeda(parcela.valor) }}
                </span>
                <button
                  v-if="parcela.status !== 'pago'"
                  @click="abrirDialogPagamento(parcela)"
                  class="px-2 py-1 text-xs font-medium rounded bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors"
                >
                  Pagar
                </button>
              </div>
            </div>
            <p v-if="mov.parcelas.length > 5" class="text-xs text-gray-400 mt-1 ml-2">
              + {{ mov.parcelas.length - 5 }} parcelas
            </p>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="movimentacoes.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="text-gray-500 font-medium">Nenhuma despesa cadastrada</p>
        <p class="text-sm text-gray-400 mt-1">Comece registrando sua primeira despesa</p>
      </div>
    </div>

    <!-- Modal Nova Despesa -->
    <NovaMovimentacaoDialog
      v-model:open="dialogOpen"
      tipo="saida"
      @created="handleMovimentacaoCriada"
    />

    <!-- Modal Pagamento -->
    <Dialog v-model:open="dialogPagamentoOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Registrar Pagamento</DialogTitle>
          <DialogDescription>
            Confirme o pagamento da parcela
          </DialogDescription>
        </DialogHeader>

        <div v-if="parcelaSelecionada" class="space-y-4 mt-4">
          <!-- Informações da Parcela -->
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Valor:</span>
                <span class="font-semibold text-gray-900">
                  {{ formatarMoeda(parcelaSelecionada.valor) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Vencimento:</span>
                <span class="text-gray-900">
                  {{ formatarData(parcelaSelecionada.data_vencimento) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Método de Pagamento -->
          <div>
            <label class="text-sm font-medium text-gray-700 mb-2 block">
              Método de Pagamento
            </label>
            <Select v-model="metodoPagamento">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="pix">PIX</SelectItem>
                <SelectItem value="dinheiro">Dinheiro</SelectItem>
                <SelectItem value="cartao_credito">Cartão de Crédito</SelectItem>
                <SelectItem value="cartao_debito">Cartão de Débito</SelectItem>
                <SelectItem value="boleto">Boleto</SelectItem>
                <SelectItem value="transferencia">Transferência</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Botões -->
          <div class="flex gap-2 pt-2">
            <Button
              variant="outline"
              class="flex-1"
              @click="dialogPagamentoOpen = false"
              :disabled="pagando"
            >
              Cancelar
            </Button>
            <Button
              class="flex-1 bg-emerald-600 hover:bg-emerald-700"
              @click="registrarPagamento"
              :disabled="pagando"
            >
              {{ pagando ? 'Processando...' : 'Confirmar Pagamento' }}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>
