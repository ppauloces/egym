<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { calcularDiasAtraso } from '@/lib/dateUtils'
import PendingPaymentCard from '@/components/gestor/PendingPaymentCard.vue'
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
import { Button } from '@/components/ui/button'
import { toast } from '@/components/ui/toast'

interface Mensalidade {
  id: number
  aluno: { id: number; nome: string; telefone: string | null }
  plano: { id: number; nome: string }
  valor: number
  data_vencimento: string
  status: string
  observacao: string | null
}

const urgentes = ref<Mensalidade[]>([])
const proximas = ref<Mensalidade[]>([])
const loading = ref(true)
const dialogOpen = ref(false)
const mensalidadeSelecionada = ref<Mensalidade | null>(null)
const metodoPagamento = ref<string | undefined>(undefined)
const salvando = ref(false)

async function fetchMensalidades() {
  loading.value = true
  try {
    const response = await axios.get('/api/gestor/mensalidades/pendentes')
    urgentes.value = response.data.urgentes
    proximas.value = response.data.proximas
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function abrirModalPagamento(id: number) {
  const mensalidade = [...urgentes.value, ...proximas.value].find(m => m.id === id)
  if (mensalidade) {
    mensalidadeSelecionada.value = mensalidade
    metodoPagamento.value = undefined
    dialogOpen.value = true
  }
}

async function confirmarPagamento() {
  if (!mensalidadeSelecionada.value || !metodoPagamento.value) return

  salvando.value = true
  try {
    await axios.post(`/api/gestor/mensalidades/${mensalidadeSelecionada.value.id}/pagar`, {
      metodo_pagamento: metodoPagamento.value
    })
    
    toast({
      title: 'Pagamento registrado!',
      description: 'A mensalidade foi marcada como paga.',
    })
    
    dialogOpen.value = false
    mensalidadeSelecionada.value = null
    metodoPagamento.value = undefined
    await fetchMensalidades()
  } catch (e: any) {
    console.error('Erro ao registrar pagamento:', e)
    toast({
      title: 'Erro ao registrar',
      description: e.response?.data?.message || 'Ocorreu um erro. Tente novamente.',
      variant: 'destructive',
    })
  } finally {
    salvando.value = false
  }
}

// Função movida para @/lib/dateUtils

onMounted(() => {
  fetchMensalidades()
})
</script>

<template>
  <div class="space-y-5">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Mensalidades</h1>
      <p class="text-sm text-gray-500 mt-1">Gerencie os pagamentos pendentes</p>
    </div>

    <!-- Modal Pagamento -->
    <Dialog v-model:open="dialogOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Registrar Pagamento</DialogTitle>
          <DialogDescription>
            Selecione o método de pagamento utilizado
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="mensalidadeSelecionada" class="space-y-4 mt-4">
          <!-- Info do Aluno -->
          <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-sm text-gray-700">
              <span class="font-medium">Aluno:</span> {{ mensalidadeSelecionada.aluno.nome }}
            </p>
            <p class="text-sm text-gray-700 mt-1">
              <span class="font-medium">Valor:</span> 
              R$ {{ Number(mensalidadeSelecionada.valor).toFixed(2).replace('.', ',') }}
            </p>
          </div>

          <!-- Método de Pagamento -->
          <div>
            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
              Método de pagamento *
            </label>
            <Select v-model="metodoPagamento">
              <SelectTrigger>
                <SelectValue placeholder="Selecione o método" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="dinheiro">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Dinheiro
                  </div>
                </SelectItem>
                <SelectItem value="pix">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    PIX
                  </div>
                </SelectItem>
                <SelectItem value="cartao">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Cartão
                  </div>
                </SelectItem>
                <SelectItem value="boleto">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Boleto
                  </div>
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Botões -->
          <div class="flex gap-2 pt-2">
            <Button
              type="button"
              variant="outline"
              class="flex-1"
              @click="dialogOpen = false"
              :disabled="salvando"
            >
              Cancelar
            </Button>
            <Button
              type="button"
              class="flex-1"
              @click="confirmarPagamento"
              :disabled="!metodoPagamento || salvando"
            >
              {{ salvando ? 'Salvando...' : 'Confirmar' }}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- Content -->
    <div v-else class="space-y-8">
      <!-- Cobranças Urgentes -->
      <div>
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Cobranças próximas</h2>
            <p class="text-xs text-gray-500">Atrasadas e vencendo nos próximos 3 dias</p>
          </div>
        </div>

        <div v-if="urgentes.length === 0" class="bg-white rounded-xl p-8 border border-gray-100 text-center">
          <svg class="w-12 h-12 mx-auto text-emerald-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-gray-900 font-medium">Nenhuma cobrança próxima!</p>
          <p class="text-sm text-gray-500 mt-1">Todas as mensalidades estão em dia</p>
        </div>

        <div v-else class="space-y-2.5">
          <PendingPaymentCard
            v-for="item in urgentes"
            :key="item.id"
            :id="item.id"
            :aluno-nome="item.aluno.nome"
            :aluno-telefone="item.aluno.telefone"
            :valor="Number(item.valor)"
            :data-vencimento="item.data_vencimento"
            :dias-atraso="calcularDiasAtraso(item.data_vencimento)"
            :observacao="item.observacao"
            @pagar="abrirModalPagamento"
          />
        </div>
      </div>

      <!-- Próximas Cobranças -->
      <div v-if="proximas.length > 0">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Próximas cobranças</h2>
            <p class="text-xs text-gray-500">Vencimentos futuros</p>
          </div>
        </div>

        <div class="space-y-2.5">
          <PendingPaymentCard
            v-for="item in proximas"
            :key="item.id"
            :id="item.id"
            :aluno-nome="item.aluno.nome"
            :aluno-telefone="item.aluno.telefone"
            :valor="Number(item.valor)"
            :data-vencimento="item.data_vencimento"
            :dias-atraso="calcularDiasAtraso(item.data_vencimento)"
            :observacao="item.observacao"
            @pagar="abrirModalPagamento"
          />
        </div>
      </div>
    </div>
  </div>
</template>
