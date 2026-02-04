<script setup lang="ts">
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps<{
  id: number
  alunoNome: string
  alunoTelefone: string | null
  valor: number
  dataVencimento: string
  diasAtraso: number
  observacao?: string | null
}>()

const dialogObservacaoOpen = ref(false)

const emit = defineEmits<{
  pagar: [id: number]
  whatsapp: [telefone: string]
}>()

const valorFormatado = computed(() => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(props.valor)
})

const dataFormatada = computed(() => {
  // Extrai apenas a data (YYYY-MM-DD) sem considerar timezone
  const [year, month, day] = props.dataVencimento.split('T')[0].split('-')
  return `${day}/${month}/${year}`
})

const statusText = computed(() => {
  if (props.diasAtraso > 0) {
    return `${props.diasAtraso} dia${props.diasAtraso > 1 ? 's' : ''} em atraso`
  }
  if (props.diasAtraso === 0) {
    return 'Vence hoje'
  }
  return `Vence em ${dataFormatada.value}`
})

const isOverdue = computed(() => props.diasAtraso > 0)

function openWhatsApp() {
  if (props.alunoTelefone) {
    const phone = props.alunoTelefone.replace(/\D/g, '')
    const message = encodeURIComponent(
      `Olá ${props.alunoNome}! Sua mensalidade no valor de ${valorFormatado.value} está pendente. Vencimento: ${dataFormatada.value}.`
    )
    window.open(`https://wa.me/55${phone}?text=${message}`, '_blank')
  }
}
</script>

<template>
  <div 
    class="bg-white rounded-xl p-4 border transition-colors hover:border-gray-300"
    :class="isOverdue ? 'border-red-200 bg-red-50/30' : 'border-amber-200 bg-amber-50/30'"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <h3 class="font-semibold text-gray-900 truncate">{{ alunoNome }}</h3>
          <span 
            class="px-2 py-0.5 text-xs font-medium rounded-full flex-shrink-0"
            :class="isOverdue ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-700 border border-amber-200'"
          >
            {{ statusText }}
          </span>
        </div>
        
        <p v-if="alunoTelefone" class="text-sm text-gray-500 mb-2">
          {{ alunoTelefone }}
        </p>
        
        <div class="flex items-baseline gap-1">
          <span class="text-2xl font-semibold text-gray-900">{{ valorFormatado }}</span>
          <span class="text-sm text-gray-500">• Venc. {{ dataFormatada }}</span>
        </div>
      </div>

      <div class="flex flex-col gap-2 flex-shrink-0 min-w-[80px]">
        <!-- Observação -->
        <button
          v-if="observacao"
          @click="dialogObservacaoOpen = true"
          class="w-full h-10 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors border border-blue-200"
          title="Ver observação"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </button>

        <!-- WhatsApp -->
        <button
          v-if="alunoTelefone"
          @click="openWhatsApp"
          class="w-full h-10 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors border border-emerald-200"
          title="Enviar mensagem no WhatsApp"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
        </button>

        <!-- Pagar -->
        <Button
          size="sm"
          @click="emit('pagar', id)"
          class="w-full bg-emerald-600 hover:bg-emerald-700 border-0 shadow-sm"
        >
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Pago
        </Button>
      </div>
    </div>

    <!-- Modal de Observação -->
    <Dialog v-model:open="dialogObservacaoOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Observação</DialogTitle>
          <DialogDescription>
            Mensalidade de {{ alunoNome }}
          </DialogDescription>
        </DialogHeader>
        
        <div class="mt-4">
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ observacao }}</p>
          </div>
        </div>

        <div class="flex justify-end mt-4">
          <Button
            variant="outline"
            @click="dialogObservacaoOpen = false"
          >
            Fechar
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>
