<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { formatarDataBR, formatarDataParaInput } from '@/lib/dateUtils'
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { toast } from '@/components/ui/toast'

interface Aluno {
  id: number
  nome: string
  cpf: string | null
  telefone: string | null
  email: string | null
  data_nascimento: string | null
  data_matricula: string | null
  matricula_retroativa: boolean
  data_proxima_mensalidade: string | null
  status: string
  plano: { id: number; nome: string; valor: number } | null
}

interface Plano {
  id: number
  nome: string
  valor: number
  ativo: boolean
}

const alunos = ref<Aluno[]>([])
const planos = ref<Plano[]>([])
const loading = ref(true)
const busca = ref('')
const dialogOpen = ref(false)
const salvando = ref(false)
const editando = ref(false)
const alunoEditando = ref<number | null>(null)

// Formulário
const form = ref({
  nome: '',
  cpf: '',
  email: '',
  telefone: '',
  data_nascimento: '',
  data_matricula: '',
  matricula_retroativa: false,
  data_proxima_mensalidade: '',
  plano_id: undefined as string | undefined,
  status: 'ativo'
})

const formErrors = ref<Record<string, string>>({})

async function fetchAlunos() {
  loading.value = true
  try {
    const response = await axios.get('/api/gestor/alunos', {
      params: { busca: busca.value || undefined }
    })
    alunos.value = response.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function fetchPlanos() {
  try {
    const response = await axios.get('/api/gestor/planos')
    // Filtrar apenas planos ativos
    planos.value = response.data.filter((p: Plano) => p.ativo)
  } catch (e) {
    console.error('Erro ao carregar planos:', e)
  }
}

function handleBusca() {
  fetchAlunos()
}

function resetForm() {
  form.value = {
    nome: '',
    cpf: '',
    email: '',
    telefone: '',
    data_nascimento: '',
    data_matricula: '',
    matricula_retroativa: false,
    data_proxima_mensalidade: '',
    plano_id: undefined,
    status: 'ativo'
  }
  formErrors.value = {}
  editando.value = false
  alunoEditando.value = null
}

function abrirModalNovo() {
  resetForm()
  // Define data de matrícula como hoje
  const hoje = new Date()
  const year = hoje.getFullYear()
  const month = String(hoje.getMonth() + 1).padStart(2, '0')
  const day = String(hoje.getDate()).padStart(2, '0')
  form.value.data_matricula = `${year}-${month}-${day}`
  dialogOpen.value = true
}

function abrirModalEditar(aluno: Aluno) {
  form.value = {
    nome: aluno.nome,
    cpf: aluno.cpf || '',
    email: aluno.email || '',
    telefone: aluno.telefone || '',
    data_nascimento: formatarDataParaInput(aluno.data_nascimento),
    data_matricula: formatarDataParaInput(aluno.data_matricula),
    matricula_retroativa: aluno.matricula_retroativa || false,
    data_proxima_mensalidade: formatarDataParaInput(aluno.data_proxima_mensalidade),
    plano_id: aluno.plano ? String(aluno.plano.id) : undefined,
    status: aluno.status
  }
  editando.value = true
  alunoEditando.value = aluno.id
  dialogOpen.value = true
}

async function handleSubmit() {
  salvando.value = true
  formErrors.value = {}

  try {
    const payload = {
      ...form.value,
      plano_id: form.value.plano_id ? Number(form.value.plano_id) : null
    }
    
    if (editando.value && alunoEditando.value) {
      await axios.put(`/api/gestor/alunos/${alunoEditando.value}`, payload)
      toast({
        title: 'Aluno atualizado!',
        description: 'Os dados do aluno foram atualizados com sucesso.',
      })
    } else {
      await axios.post('/api/gestor/alunos', payload)
      toast({
        title: 'Aluno cadastrado!',
        description: 'O aluno foi adicionado com sucesso.',
      })
    }
    
    dialogOpen.value = false
    resetForm()
    await fetchAlunos()
  } catch (error: any) {
    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors
    } else {
      toast({
        title: editando.value ? 'Erro ao atualizar' : 'Erro ao cadastrar',
        description: 'Ocorreu um erro ao cadastrar o aluno. Tente novamente.',
        variant: 'destructive',
      })
    }
  } finally {
    salvando.value = false
  }
}

async function handleDelete(id: number) {
  if (!confirm('Tem certeza que deseja excluir este aluno? Todas as mensalidades serão excluídas também.')) {
    return
  }

  try {
    await axios.delete(`/api/gestor/alunos/${id}`)
    toast({
      title: 'Aluno excluído!',
      description: 'O aluno foi removido com sucesso.',
    })
    await fetchAlunos()
  } catch (error: any) {
    toast({
      title: 'Erro ao excluir',
      description: error.response?.data?.message || 'Não foi possível excluir o aluno.',
      variant: 'destructive',
    })
  }
}

async function toggleStatus(aluno: Aluno) {
  try {
    await axios.post(`/api/gestor/alunos/${aluno.id}/toggle-status`)
    await fetchAlunos()
    toast({
      title: aluno.status === 'ativo' ? 'Aluno inativado' : 'Aluno ativado',
      description: `O aluno foi ${aluno.status === 'ativo' ? 'inativado' : 'ativado'} com sucesso.`,
    })
  } catch (error: any) {
    toast({
      title: 'Erro',
      description: 'Não foi possível alterar o status do aluno.',
      variant: 'destructive',
    })
  }
}

onMounted(() => {
  fetchAlunos()
  fetchPlanos()
})
</script>

<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Alunos</h1>
        <p class="text-sm text-gray-500 mt-1">Gerencie os alunos da academia</p>
      </div>
      
      <Dialog v-model:open="dialogOpen">
        <DialogTrigger as-child>
          <Button class="shadow-sm" @click="abrirModalNovo">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Aluno
          </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>{{ editando ? 'Editar' : 'Cadastrar' }} Aluno</DialogTitle>
            <DialogDescription>
              {{ editando ? 'Atualize as informações do aluno' : 'Preencha os dados do novo aluno' }}
            </DialogDescription>
          </DialogHeader>
          
          <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
            <!-- Nome -->
            <div>
              <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                Nome completo *
              </label>
              <Input
                v-model="form.nome"
                placeholder="João Silva"
                required
                :class="{ 'border-red-500': formErrors.nome }"
              />
              <p v-if="formErrors.nome" class="text-xs text-red-600 mt-1">
                {{ formErrors.nome[0] }}
              </p>
            </div>

            <!-- CPF e Telefone -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                  CPF
                </label>
                <Input
                  v-model="form.cpf"
                  v-maska
                  data-maska="###.###.###-##"
                  placeholder="000.000.000-00"
                  :class="{ 'border-red-500': formErrors.cpf }"
                />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                  Telefone
                </label>
                <Input
                  v-model="form.telefone"
                  v-maska
                  data-maska="['(##) ####-####', '(##) #####-####']"
                  placeholder="(00) 00000-0000"
                  :class="{ 'border-red-500': formErrors.telefone }"
                />
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                E-mail
              </label>
              <Input
                v-model="form.email"
                type="email"
                placeholder="joao@email.com"
                :class="{ 'border-red-500': formErrors.email }"
              />
            </div>

            <!-- Data Nascimento e Data Matrícula -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                  Data de Nascimento
                </label>
                <Input
                  v-model="form.data_nascimento"
                  type="date"
                  :class="{ 'border-red-500': formErrors.data_nascimento }"
                />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                  Data de Matrícula
                </label>
                <Input
                  v-model="form.data_matricula"
                  type="date"
                  :class="{ 'border-red-500': formErrors.data_matricula }"
                />
              </div>
            </div>

            <!-- Matrícula Retroativa -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <div class="flex items-center gap-2">
                <input
                  v-model="form.matricula_retroativa"
                  type="checkbox"
                  id="matricula_retroativa"
                  class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                />
                <label for="matricula_retroativa" class="text-sm font-medium text-gray-700">
                  Matrícula Retroativa
                </label>
              </div>
              <p class="text-xs text-blue-600 mt-1.5">
                ℹ️ Marque se o aluno já está matriculado há algum tempo. Isso evita gerar cobranças antigas. 
                Você informará a data da próxima mensalidade.
              </p>
              
              <!-- Data Próxima Mensalidade (aparece só se marcado) -->
              <div v-if="form.matricula_retroativa" class="mt-3">
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                  Data da Próxima Mensalidade *
                </label>
                <Input
                  v-model="form.data_proxima_mensalidade"
                  type="date"
                  required
                  :class="{ 'border-red-500': formErrors.data_proxima_mensalidade }"
                />
                <p class="text-xs text-gray-500 mt-1">
                  Sistema gerará apenas 1 mensalidade com essa data
                </p>
              </div>
            </div>

            <!-- Plano -->
            <div>
              <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                Plano (opcional)
              </label>
              <Select v-model="form.plano_id">
                <SelectTrigger>
                  <SelectValue placeholder="Selecione um plano" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="plano in planos"
                    :key="plano.id"
                    :value="String(plano.id)"
                  >
                    {{ plano.nome }} - R$ {{ Number(plano.valor).toFixed(2).replace('.', ',') }}
                  </SelectItem>
                  <SelectItem
                    v-if="planos.length === 0"
                    value="0"
                    disabled
                  >
                    Nenhum plano disponível
                  </SelectItem>
                </SelectContent>
              </Select>
              <p class="text-xs text-gray-500 mt-1">Deixe em branco se não tiver plano</p>
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

    <!-- Search -->
    <form @submit.prevent="handleBusca" class="flex gap-2">
      <Input
        v-model="busca"
        placeholder="Buscar por nome ou CPF..."
        class="flex-1"
      />
      <Button type="submit" variant="outline" size="default">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        Buscar
      </Button>
    </form>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- List -->
    <div v-else class="space-y-2.5">
      <div
        v-for="aluno in alunos"
        :key="aluno.id"
        class="bg-white rounded-xl p-4 border border-gray-100 hover:border-gray-200 transition-colors"
      >
        <div class="flex items-start justify-between mb-2">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-semibold text-gray-900">{{ aluno.nome }}</h3>
              <span
                class="px-2 py-0.5 text-xs font-medium rounded-full"
                :class="{
                  'bg-emerald-50 text-emerald-700 border border-emerald-200': aluno.status === 'ativo',
                  'bg-gray-50 text-gray-600 border border-gray-200': aluno.status === 'inativo',
                  'bg-red-50 text-red-700 border border-red-200': aluno.status === 'bloqueado',
                }"
              >
                {{ aluno.status }}
              </span>
            </div>
            <div class="space-y-1 mt-2">
              <p class="text-sm text-gray-600">
                <span class="font-medium">Plano:</span> {{ aluno.plano?.nome || 'Sem plano' }}
                <span v-if="aluno.plano" class="text-gray-400 mx-1">•</span>
                <span v-if="aluno.plano" class="font-medium">
                  R$ {{ Number(aluno.plano.valor).toFixed(2).replace('.', ',') }}
                </span>
              </p>
              <p v-if="aluno.telefone" class="text-sm text-gray-600">
                <span class="font-medium">Telefone:</span> {{ aluno.telefone }}
              </p>
              <p v-if="aluno.data_matricula" class="text-sm text-gray-600">
                <span class="font-medium">Matrícula:</span> {{ formatarDataBR(aluno.data_matricula) }}
              </p>
            </div>
          </div>
        </div>

        <div class="flex gap-2 pt-2 border-t border-gray-100">
          <button
            @click="toggleStatus(aluno)"
            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-lg hover:bg-gray-100 transition-colors flex items-center justify-center gap-1"
            :title="aluno.status === 'ativo' ? 'Inativar' : 'Ativar'"
          >
            <svg v-if="aluno.status === 'ativo'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ aluno.status === 'ativo' ? 'Inativar' : 'Ativar' }}
          </button>
          
          <button
            @click="abrirModalEditar(aluno)"
            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-lg hover:bg-gray-100 transition-colors flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Editar
          </button>
          
          <button
            @click="handleDelete(aluno.id)"
            class="px-3 py-1.5 text-xs font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Excluir
          </button>
        </div>
      </div>

      <div v-if="alunos.length === 0" class="text-center py-12 bg-white rounded-xl border border-gray-100">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="text-gray-500 font-medium">Nenhum aluno encontrado</p>
        <p class="text-sm text-gray-400 mt-1">Comece cadastrando seu primeiro aluno</p>
      </div>
    </div>
  </div>
</template>
