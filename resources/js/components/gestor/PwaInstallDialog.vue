<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  usePwaInstall,
  isIos,
  isAndroid,
  shouldShowInstallPrompt,
  dismissInstallPrompt,
} from '@/composables/usePwaInstall'

const { canInstall, isInstalled, install } = usePwaInstall()
const open = ref(false)
const installing = ref(false)

const showIosInstructions = computed(() => isIos() && !isInstalled.value)
const showAndroidInstall = computed(() => isAndroid() && canInstall.value && !isInstalled.value)

onMounted(() => {
  // Aguarda 3 segundos antes de mostrar o prompt
  setTimeout(() => {
    if (shouldShowInstallPrompt() && (showIosInstructions.value || showAndroidInstall.value)) {
      open.value = true
    }
  }, 3000)
})

async function handleInstall() {
  installing.value = true
  const success = await install()
  installing.value = false
  
  if (success) {
    open.value = false
  }
}

function handleClose() {
  dismissInstallPrompt()
  open.value = false
}
</script>

<template>
  <Dialog :open="open" @update:open="handleClose">
    <DialogContent class="sm:max-w-md">
      <!-- Android: Instalação automática -->
      <template v-if="showAndroidInstall">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Instalar E-GYM
          </DialogTitle>
          <DialogDescription>
            Instale o aplicativo na sua tela inicial para acesso rápido e experiência completa.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 mt-4">
          <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-100">
            <div class="flex items-start gap-3">
              <div class="bg-blue-100 rounded-full p-2 flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 mb-1">Acesso instantâneo</p>
                <p class="text-xs text-gray-600">Abra direto da tela inicial, sem navegador</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg p-4 border border-emerald-100">
            <div class="flex items-start gap-3">
              <div class="bg-emerald-100 rounded-full p-2 flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 mb-1">Funciona offline</p>
                <p class="text-xs text-gray-600">Consulte dados mesmo sem internet</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-100">
            <div class="flex items-start gap-3">
              <div class="bg-purple-100 rounded-full p-2 flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 mb-1">Notificações</p>
                <p class="text-xs text-gray-600">Receba alertas de vencimentos</p>
              </div>
            </div>
          </div>

          <div class="flex gap-2 pt-2">
            <Button
              variant="outline"
              class="flex-1"
              @click="handleClose"
            >
              Agora não
            </Button>
            <Button
              class="flex-1 bg-blue-600 hover:bg-blue-700"
              @click="handleInstall"
              :disabled="installing"
            >
              <svg v-if="!installing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              <span v-if="installing">Instalando...</span>
              <span v-else>Instalar</span>
            </Button>
          </div>
        </div>
      </template>

      <!-- iOS: Instruções manuais -->
      <template v-else-if="showIosInstructions">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <svg class="w-6 h-6 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.09997 22C7.78997 22.05 6.79997 20.68 5.95997 19.47C4.24997 17 2.93997 12.45 4.69997 9.39C5.56997 7.87 7.12997 6.91 8.81997 6.88C10.1 6.86 11.32 7.75 12.11 7.75C12.89 7.75 14.37 6.68 15.92 6.84C16.57 6.87 18.39 7.1 19.56 8.82C19.47 8.88 17.39 10.1 17.41 12.63C17.44 15.65 20.06 16.66 20.09 16.67C20.06 16.74 19.67 18.11 18.71 19.5M13 3.5C13.73 2.67 14.94 2.04 15.94 2C16.07 3.17 15.6 4.35 14.9 5.19C14.21 6.04 13.07 6.7 11.95 6.61C11.8 5.46 12.36 4.26 13 3.5Z"/>
            </svg>
            Adicionar à Tela de Início
          </DialogTitle>
          <DialogDescription>
            Siga os passos para instalar o E-GYM no seu iPhone/iPad
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 mt-4">
          <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <div class="flex items-start gap-3 mb-3">
              <div class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 text-sm font-bold">
                1
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 mb-2">
                  Toque no botão <strong>Compartilhar</strong>
                </p>
                <div class="flex items-center gap-2 bg-white rounded-lg p-2 border border-blue-200">
                  <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/>
                  </svg>
                  <span class="text-xs text-gray-600">(na barra inferior do Safari)</span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-200">
            <div class="flex items-start gap-3">
              <div class="bg-emerald-600 text-white rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 text-sm font-bold">
                2
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 mb-2">
                  Selecione <strong>"Adicionar à Tela de Início"</strong>
                </p>
                <div class="flex items-center gap-2 bg-white rounded-lg p-2 border border-emerald-200">
                  <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <span class="text-xs text-gray-600">Role para baixo no menu</span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
            <div class="flex items-start gap-3">
              <div class="bg-purple-600 text-white rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 text-sm font-bold">
                3
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">
                  Toque em <strong>"Adicionar"</strong> e pronto! 🎉
                </p>
              </div>
            </div>
          </div>

          <Button
            variant="outline"
            class="w-full mt-4"
            @click="handleClose"
          >
            Entendi
          </Button>
        </div>
      </template>
    </DialogContent>
  </Dialog>
</template>
