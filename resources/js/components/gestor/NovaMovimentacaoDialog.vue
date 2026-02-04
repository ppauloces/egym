<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
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
import { toast } from '@/components/ui/toast'

interface Categoria {
  id: number
  nome: string
  cor: string
  icone: string
}

interface Aluno {
  id: number
  nome: string
}

const props = defineProps<{
  open: boolean
  tipo: 'entrada' | 'saida'
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  'created': []
}>()

const categorias = ref<Categoria[]>([])
const alunos = ref<Aluno[]>([])
const salvando = ref(false)

// Form
const form = ref({
  categoria_id: undefined as string | undefined,
  aluno_id: undefined as string | undefined,
  descricao: '',
  valor_total: '',
  data_competencia: new Date().toISOString().split('T')[0],
  observacao: '',
  recorrente: false,
  
  // Pagamento
  tem_entrada: false,
  entrada_valor: '',
  entrada_metodo: 'pix' as string,
  entrada_data: new Date().toISOString().split('T')[0],
  entrada_pago: true,
  
  parcelar: false,
  parcelas_quantidade: 1,
  parcelas_metodo: 'cartao_credito' as string,
  parcelas_primeira_data: '',
  
  // Pagamento à vista
  pagamento_vista_pago: true,
  pagamento_vista_metodo: 'pix' as string,
})

// Computed
const valorTotal = computed(() => parseFloat(form.value.valor_total) || 0)
const valorEntrada = computed(() => parseFloat(form.value.entrada_valor) || 0)
const valorRestante = computed(() => valorTotal.value - valorEntrada.value)
const valorParcela = computed(() => {
  if (form.value.parcelas_quantidade > 0 && valorRestante.value > 0) {
    return valorRestante.value / form.value.parcelas_quantidade
  }
  return 0
})

const tituloModal = computed(() => 
  props.tipo === 'entrada' ? 'Nova Receita' : 'Nova Despesa'
)

// Watch para calcular data da primeira parcela
watch(() => form.value.entrada_data, (novaData) => {
  if (novaData && !form.value.parcelas_primeira_data) {
    const data = new Date(novaData)
    data.setMonth(data.getMonth() + 1)
    form.value.parcelas_primeira_data = data.toISOString().split('T')[0]
  }
})

// Fetch categorias
async function fetchCategorias() {
  try {
    const response = await axios.get(`/api/gestor/categorias-financeiras/${props.tipo}`)
    categorias.value = response.data
  } catch (e) {
    console.error('Erro ao carregar categorias:', e)
  }
}

// Fetch alunos
async function fetchAlunos() {
  try {
    const response = await axios.get('/api/gestor/alunos')
    alunos.value = response.data.data || response.data
  } catch (e) {
    console.error('Erro ao carregar alunos:', e)
  }
}

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    fetchCategorias()
    fetchAlunos()
    resetForm()
  }
})

function resetForm() {
  form.value = {
    categoria_id: undefined,
    aluno_id: undefined,
    descricao: '',
    valor_total: '',
    data_competencia: new Date().toISOString().split('T')[0],
    observacao: '',
    recorrente: false,
    tem_entrada: false,
    entrada_valor: '',
    entrada_metodo: 'pix',
    entrada_data: new Date().toISOString().split('T')[0],
    entrada_pago: true,
    parcelar: false,
    parcelas_quantidade: 1,
    parcelas_metodo: 'cartao_credito',
    parcelas_primeira_data: '',
    pagamento_vista_pago: true,
    pagamento_vista_metodo: 'pix',
  }
}

function incrementarParcelas() {
  if (form.value.parcelas_quantidade < 48) {
    form.value.parcelas_quantidade++
  }
}

function decrementarParcelas() {
  if (form.value.parcelas_quantidade > 1) {
    form.value.parcelas_quantidade--
  }
}

async function handleSubmit() {
  salvando.value = true

  try {
    const payload: any = {
      categoria_id: Number(form.value.categoria_id),
      aluno_id: form.value.aluno_id ? Number(form.value.aluno_id) : null,
      tipo: props.tipo,
      descricao: form.value.descricao,
      valor_total: valorTotal.value,
      data_competencia: form.value.data_competencia,
      observacao: form.value.observacao || null,
      recorrente: form.value.recorrente,
      pagamento: {},
    }

    // Entrada
    if (form.value.tem_entrada && valorEntrada.value > 0) {
      payload.pagamento.entrada = {
        valor: valorEntrada.value,
        metodo: form.value.entrada_metodo,
        data: form.value.entrada_data,
        pago: form.value.entrada_pago,
      }
    }

    // Parcelas
    if (form.value.parcelar && form.value.parcelas_quantidade > 0) {
      payload.pagamento.parcelas = {
        quantidade: form.value.parcelas_quantidade,
        metodo: form.value.parcelas_metodo,
        primeira_data: form.value.parcelas_primeira_data,
      }
    }

    // Se não tem entrada nem parcelas, é pagamento à vista
    if (!form.value.tem_entrada && !form.value.parcelar) {
      payload.pagamento.metodo = form.value.pagamento_vista_metodo
      payload.pagamento.pago = form.value.pagamento_vista_pago
    }

    await axios.post('/api/gestor/movimentacoes', payload)
    
    toast({
      title: props.tipo === 'entrada' ? 'Receita cadastrada!' : 'Despesa cadastrada!',
      description: 'Movimentação registrada com sucesso.',
    })

    emit('update:open', false)
    emit('created')
  } catch (error: any) {
    console.error('Erro ao salvar:', error)
    toast({
      title: 'Erro ao salvar',
      description: error.response?.data?.message || 'Ocorreu um erro. Tente novamente.',
      variant: 'destructive',
    })
  } finally {
    salvando.value = false
  }
}

function formatarMoeda(valor: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor)
}
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-lg max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>{{ tituloModal }}</DialogTitle>
        <DialogDescription>
          Preencha os dados da {{ tipo === 'entrada' ? 'receita' : 'despesa' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
        <!-- Categoria -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">
            Categoria *
          </label>
          <Select v-model="form.categoria_id">
            <SelectTrigger>
              <SelectValue placeholder="Selecione uma categoria" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="cat in categorias"
                :key="cat.id"
                :value="String(cat.id)"
              >
                <div class="flex items-center gap-2">
                  <div 
                    class="w-3 h-3 rounded-full" 
                    :style="{ backgroundColor: cat.cor }"
                  />
                  {{ cat.nome }}
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Aluno (Opcional) -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">
            Aluno (opcional)
          </label>
          <Select v-model="form.aluno_id">
            <SelectTrigger>
              <SelectValue placeholder="Nenhum aluno vinculado" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="aluno in alunos"
                :key="aluno.id"
                :value="String(aluno.id)"
              >
                {{ aluno.nome }}
              </SelectItem>
            </SelectContent>
          </Select>
          <button
            v-if="form.aluno_id"
            type="button"
            @click="form.aluno_id = undefined"
            class="text-xs text-gray-500 hover:text-gray-700 mt-1"
          >
            Remover vínculo
          </button>
          <p class="text-xs text-gray-500 mt-1">
            {{ tipo === 'entrada' ? 'Ex: Quem comprou a creatina' : 'Ex: Aluno relacionado à despesa' }}
          </p>
        </div>

        <!-- Descrição -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">
            Descrição *
          </label>
          <Input
            v-model="form.descricao"
            placeholder="Ex: Conta de energia - Janeiro"
            required
          />
        </div>

        <!-- Valor Total e Data -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
              Valor Total *
            </label>
            <Input
              v-model="form.valor_total"
              type="number"
              step="0.01"
              min="0.01"
              placeholder="0,00"
              required
            />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
              Data de Referência
            </label>
            <Input
              v-model="form.data_competencia"
              type="date"
              required
            />
          </div>
        </div>

        <!-- Recorrente -->
        <div class="flex items-center gap-2 bg-gray-50 p-3 rounded-lg">
          <input
            v-model="form.recorrente"
            type="checkbox"
            id="recorrente"
            class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
          />
          <label for="recorrente" class="text-sm font-medium text-gray-700">
            Despesa recorrente (repete todo mês)
          </label>
        </div>

        <!-- Forma de Pagamento -->
        <div class="border border-gray-200 rounded-lg p-4 space-y-4">
          <h3 class="font-medium text-gray-900">Forma de Pagamento</h3>

          <!-- Pagamento à Vista (quando não tem entrada nem parcelamento) -->
          <div v-if="!form.tem_entrada && !form.parcelar" class="space-y-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-sm font-medium text-blue-900">Pagamento à Vista</span>
            </div>
            
            <div>
              <label class="text-xs text-blue-900 mb-1 block font-medium">Método</label>
              <Select v-model="form.pagamento_vista_metodo">
                <SelectTrigger class="h-9 bg-white">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pix">PIX</SelectItem>
                  <SelectItem value="dinheiro">Dinheiro</SelectItem>
                  <SelectItem value="cartao_debito">Débito</SelectItem>
                  <SelectItem value="cartao_credito">Crédito</SelectItem>
                  <SelectItem value="transferencia">Transferência</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="flex items-center gap-2">
              <input
                v-model="form.pagamento_vista_pago"
                type="checkbox"
                id="pagamento_vista_pago"
                class="w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500"
              />
              <label for="pagamento_vista_pago" class="text-sm font-medium text-blue-900">
                Já foi {{ tipo === 'entrada' ? 'recebido' : 'pago' }}
              </label>
            </div>
          </div>

          <!-- Entrada -->
          <div class="space-y-3">
            <div class="flex items-center gap-2">
              <input
                v-model="form.tem_entrada"
                type="checkbox"
                id="tem_entrada"
                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
              />
              <label for="tem_entrada" class="text-sm font-medium text-gray-700">
                Tem entrada
              </label>
            </div>

            <div v-if="form.tem_entrada" class="pl-6 space-y-3 border-l-2 border-gray-200">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Valor da entrada</label>
                  <Input
                    v-model="form.entrada_valor"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0,00"
                  />
                </div>
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Método</label>
                  <Select v-model="form.entrada_metodo">
                    <SelectTrigger class="h-9">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="pix">PIX</SelectItem>
                      <SelectItem value="dinheiro">Dinheiro</SelectItem>
                      <SelectItem value="cartao_debito">Débito</SelectItem>
                      <SelectItem value="transferencia">Transferência</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Data</label>
                  <Input
                    v-model="form.entrada_data"
                    type="date"
                  />
                </div>
                <div class="flex items-end pb-1">
                  <div class="flex items-center gap-2">
                    <input
                      v-model="form.entrada_pago"
                      type="checkbox"
                      id="entrada_pago"
                      class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                    />
                    <label for="entrada_pago" class="text-sm text-gray-700">
                      Já foi pago
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Parcelamento -->
          <div class="space-y-3">
            <div class="flex items-center gap-2">
              <input
                v-model="form.parcelar"
                type="checkbox"
                id="parcelar"
                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
              />
              <label for="parcelar" class="text-sm font-medium text-gray-700">
                Parcelar {{ form.tem_entrada ? 'restante' : '' }}
              </label>
            </div>

            <div v-if="form.parcelar" class="pl-6 space-y-3 border-l-2 border-gray-200">
              <!-- Quantidade de parcelas -->
              <div>
                <label class="text-xs text-gray-600 mb-1 block">Quantidade de parcelas</label>
                <div class="flex items-center gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="decrementarParcelas"
                    :disabled="form.parcelas_quantidade <= 1"
                    class="h-9 w-9 p-0"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </Button>
                  <span class="w-12 text-center font-semibold text-lg">
                    {{ form.parcelas_quantidade }}
                  </span>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="incrementarParcelas"
                    :disabled="form.parcelas_quantidade >= 48"
                    class="h-9 w-9 p-0"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Método</label>
                  <Select v-model="form.parcelas_metodo">
                    <SelectTrigger class="h-9">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="cartao_credito">Cartão de Crédito</SelectItem>
                      <SelectItem value="boleto">Boleto</SelectItem>
                      <SelectItem value="pix">PIX</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">1ª Parcela</label>
                  <Input
                    v-model="form.parcelas_primeira_data"
                    type="date"
                  />
                </div>
              </div>

              <!-- Preview das parcelas -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-800">
                  <span class="font-semibold">{{ form.parcelas_quantidade }}x</span> de 
                  <span class="font-semibold">{{ formatarMoeda(valorParcela) }}</span>
                </p>
                <p v-if="form.tem_entrada && valorEntrada > 0" class="text-xs text-blue-600 mt-1">
                  + Entrada de {{ formatarMoeda(valorEntrada) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Observação -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">
            Observação (opcional)
          </label>
          <textarea
            v-model="form.observacao"
            rows="2"
            placeholder="Anotações adicionais..."
            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
          />
        </div>

        <!-- Botões -->
        <div class="flex gap-2 pt-2">
          <Button
            type="button"
            variant="outline"
            class="flex-1"
            @click="$emit('update:open', false)"
            :disabled="salvando"
          >
            Cancelar
          </Button>
          <Button
            type="submit"
            class="flex-1"
            :disabled="salvando || !form.categoria_id || !form.descricao || valorTotal <= 0"
          >
            {{ salvando ? 'Salvando...' : 'Salvar' }}
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>
