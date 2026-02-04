import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { vMaska } from 'maska/vue'
import App from './App.vue'
import router from './router'
import './bootstrap'

// Registrar Service Worker (PWA)
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/build/sw.js', { scope: '/' })
      .then(registration => {
        console.log('SW registrado:', registration)
      })
      .catch(error => {
        console.log('Erro ao registrar SW:', error)
      })
  })
}

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.directive('maska', vMaska)

app.mount('#app')
