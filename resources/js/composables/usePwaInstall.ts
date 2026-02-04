import { ref, onMounted, onUnmounted } from 'vue'

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>
}

const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null)
const canInstall = ref(false)
const isInstalled = ref(false)

export function usePwaInstall() {
  const handleBeforeInstall = (e: Event) => {
    e.preventDefault()
    deferredPrompt.value = e as BeforeInstallPromptEvent
    canInstall.value = true
  }

  const handleAppInstalled = () => {
    deferredPrompt.value = null
    canInstall.value = false
    isInstalled.value = true
  }

  onMounted(() => {
    // Verifica se já está instalado
    if (isInStandaloneMode()) {
      isInstalled.value = true
      return
    }

    window.addEventListener('beforeinstallprompt', handleBeforeInstall)
    window.addEventListener('appinstalled', handleAppInstalled)
  })

  onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstall)
    window.removeEventListener('appinstalled', handleAppInstalled)
  })

  async function install() {
    if (!deferredPrompt.value) {
      return false
    }

    try {
      await deferredPrompt.value.prompt()
      const { outcome } = await deferredPrompt.value.userChoice

      if (outcome === 'accepted') {
        deferredPrompt.value = null
        canInstall.value = false
        return true
      }

      return false
    } catch (error) {
      console.error('Erro ao instalar PWA:', error)
      return false
    }
  }

  return {
    canInstall,
    isInstalled,
    install,
  }
}

// Detecta se é iOS
export function isIos(): boolean {
  return /iphone|ipad|ipod/i.test(window.navigator.userAgent)
}

// Detecta se está em modo standalone (já instalado)
export function isInStandaloneMode(): boolean {
  if ('standalone' in window.navigator) {
    return (window.navigator as any).standalone === true
  }
  return window.matchMedia('(display-mode: standalone)').matches
}

// Detecta se é Android
export function isAndroid(): boolean {
  return /android/i.test(window.navigator.userAgent)
}

// Verifica se deve mostrar prompt de instalação
export function shouldShowInstallPrompt(): boolean {
  // Não mostrar se já está instalado
  if (isInStandaloneMode()) {
    return false
  }

  // Verifica se já foi fechado recentemente (localStorage)
  const lastDismissed = localStorage.getItem('pwa-install-dismissed')
  if (lastDismissed) {
    const daysSinceDismissed = (Date.now() - parseInt(lastDismissed)) / (1000 * 60 * 60 * 24)
    if (daysSinceDismissed < 7) {
      return false // Não mostrar se foi fechado há menos de 7 dias
    }
  }

  return true
}

// Marca como fechado
export function dismissInstallPrompt(): void {
  localStorage.setItem('pwa-install-dismissed', Date.now().toString())
}
