<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { toast } from '@/components/ui/toast'

interface Plano {
  id: number
  nome: string
  valor: number
  duracao_dias: number
  descricao: string | null
  ativo: boolean
}

const planos = ref<Plano[]>([])
const loading = ref(true)
const dialogOpen = ref(false)
const salvando = ref(false)
const editando = ref(false)
const planoEditando = ref<number | null>(null)

// Formulário
const form = ref({
  nome: '',
  valor: '',
  duracao_dias: '',
  descricao: '',
  ativo: true
})

const formErrors = ref<Record<string, string>>({})

async function fetchPlanos() {
  loading.value = true
  try {
    const response = await axios.get('/api/gestor/planos')
    planos.value = response.data
  } catch (e) {
    console.error(e)
    toast({
      title: 'Erro ao carregar',
      description: 'Não foi possível carregar os planos.',
      variant: 'destructive',
    })
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.value = {
    nome: '',
    valor: '',
    duracao_dias: '',
    descricao: '',
    ativo: true
  }
  formErrors.value = {}
  editando.value = false
  planoEditando.value = null
}

function abrirModalNovo() {
  resetForm()
  dialogOpen.value = true
}

function abrirModalEditar(plano: Plano) {
  form.value = {
    nome: plano.nome,
    valor: String(plano.valor),
    duracao_dias: String(plano.duracao_dias),
    descricao: plano.descricao || '',
    ativo: plano.ativo
  }
  editando.value = true
  planoEditando.value = plano.id
  dialogOpen.value = true
}

async function handleSubmit() {
  salvando.value = true
  formErrors.value = {}

  try {
    const payload = {
      nome: form.value.nome,
      valor: Number(form.value.valor),
      duracao_dias: Number(form.value.duracao_dias),
      descricao: form.value.descricao || null,
      ativo: form.value.ativo
    }

    if (editando.value && planoEditando.value) {
      await axios.put(`/api/gestor/planos/${planoEditando.value}`, payload)
      toast({
        title: 'Plano atualizado!',
        description: 'O plano foi atualizado com sucesso.',
      })
    } else {
      await axios.post('/api/gestor/planos', payload)
      toast({
        title: 'Plano cadastrado!',
        description: 'O plano foi adicionado com sucesso.',
      })
    }
    
    dialogOpen.value = false
    resetForm()
    await fetchPlanos()
  } catch (error: any) {
    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors
    } else {
      toast({
        title: 'Erro ao salvar',
        description: error.response?.data?.message || 'Ocorreu um erro. Tente novamente.',
        variant: 'destructive',
      })
    }
  } finally {
    salvando.value = false
  }
}

async function handleDelete(id: number) {
  if (!confirm('Tem certeza que deseja excluir este plano?')) {
    return
  }

  try {
    await axios.delete(`/api/gestor/planos/${id}`)
    toast({
      title: 'Plano excluído!',
      description: 'O plano foi removido com sucesso.',
    })
    await fetchPlanos()
  } catch (error: any) {
    toast({
      title: 'Erro ao excluir',
      description: error.response?.data?.message || 'Não foi possível excluir o plano.',
      variant: 'destructive',
    })
  }
}

async function toggleAtivo(plano: Plano) {
  try {
    await axios.put(`/api/gestor/planos/${plano.id}`, {
      ativo: !plano.ativo
    })
    await fetchPlanos()
    toast({
      title: plano.ativo ? 'Plano desativado' : 'Plano ativado',
      description: `O plano foi ${plano.ativo ? 'desativado' : 'ativado'} com sucesso.`,
    })
  } catch (error: any) {
    toast({
      title: 'Erro',
      description: 'Não foi possível alterar o status do plano.',
      variant: 'destructive',
    })
  }
}

onMounted(() => {
  fetchPlanos()
})
</script>

<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Planos</h1>
        <p class="text-sm text-gray-500 mt-1">Gerencie os planos da academia</p>
      </div>
      
      <Dialog v-model:open="dialogOpen">
        <DialogTrigger as-child>
          <Button class="shadow-sm" @click="abrirModalNovo">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Plano
          </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>{{ editando ? 'Editar' : 'Cadastrar' }} Plano</DialogTitle>
            <DialogDescription>
              {{ editando ? 'Atualize as informações do plano' : 'Preencha os dados do novo plano' }}
            </DialogDescription>
          </DialogHeader>
          
          <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
            <!-- Nome -->
            <div>
              <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                Nome do plano *
              </label>
              <Input
                v-model="form.nome"
                placeholder="Plano Mensal"
                required
                :class="{ 'border-red-500': formErrors.nome }"
              />
              <p v-if="formErrors.nome" class="text-xs text-red-600 mt-1">
                {{ formErrors.nome[0] }}
              </p>
            </div>

            <!-- Valor e Duração -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                  Valor (R$) *
                </label>
                <Input
                  v-model="form.valor"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="99.90"
                  required
                  :class="{ 'border-red-500': formErrors.valor }"
                />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                  Duração (dias) *
                </label>
                <Input
                  v-model="form.duracao_dias"
                  type="number"
                  min="1"
                  placeholder="30"
                  required
                  :class="{ 'border-red-500': formErrors.duracao_dias }"
                />
              </div>
            </div>

            <!-- Descrição -->
            <div>
              <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                Descrição (opcional)
              </label>
              <textarea
                v-model="form.descricao"
                rows="3"
                placeholder="Descreva os benefícios do plano..."
                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                :class="{ 'border-red-500': formErrors.descricao }"
              />
            </div>

            <!-- Ativo -->
            <div class="flex items-center gap-2">
              <input
                v-model="form.ativo"
                type="checkbox"
                id="ativo"
                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
              />
              <label for="ativo" class="text-sm font-medium text-gray-700">
                Plano ativo
              </label>
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
                type="submit"
                class="flex-1"
                :disabled="salvando"
              >
                {{ salvando ? 'Salvando...' : (editando ? 'Atualizar' : 'Cadastrar') }}
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- List -->
    <div v-else class="space-y-2.5">
      <div
        v-for="plano in planos"
        :key="plano.id"
        class="bg-white rounded-xl p-4 border border-gray-100 hover:border-gray-200 transition-colors"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-semibold text-gray-900">{{ plano.nome }}</h3>
              <span
                class="px-2 py-0.5 text-xs font-medium rounded-full"
                :class="plano.ativo 
                  ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' 
                  : 'bg-gray-50 text-gray-600 border border-gray-200'"
              >
                {{ plano.ativo ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
            
            <div class="space-y-1 mt-2">
              <p class="text-sm text-gray-600">
                <span class="font-medium">Valor:</span> 
                R$ {{ Number(plano.valor).toFixed(2).replace('.', ',') }}
              </p>
              <p class="text-sm text-gray-600">
                <span class="font-medium">Duração:</span> 
                {{ plano.duracao_dias }} dia{{ plano.duracao_dias > 1 ? 's' : '' }}
              </p>
              <p v-if="plano.descricao" class="text-sm text-gray-500 mt-1">
                {{ plano.descricao }}
              </p>
            </div>
          </div>

          <div class="flex gap-2 ml-4">
            <button
              @click="toggleAtivo(plano)"
              class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
              :title="plano.ativo ? 'Desativar' : 'Ativar'"
            >
              <svg v-if="plano.ativo" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
            
            <button
              @click="abrirModalEditar(plano)"
              class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
              title="Editar"
            >
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            
            <button
              @click="handleDelete(plano.id)"
              class="p-2 rounded-lg hover:bg-red-50 transition-colors"
              title="Excluir"
            >
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="planos.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <p class="text-gray-500 font-medium">Nenhum plano cadastrado</p>
        <p class="text-sm text-gray-400 mt-1">Crie seu primeiro plano</p>
      </div>
    </div>
  </div>
</template>
