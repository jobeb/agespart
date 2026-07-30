import { createApp } from 'vue'
import { createPinia } from 'pinia'
import 'leaflet/dist/leaflet.css'
import './style.css'
import App from './App.vue'
import { router } from './router'
import { initSync } from './services/sync'
import { reportarError } from './services/errorLogger'

const app = createApp(App)

app.config.errorHandler = (err, instance, info) => {
  console.error(err)
  reportarError({ mensaje: err?.message ?? String(err), stack: err?.stack, contexto: { info } })
}

window.addEventListener('error', (event) => {
  reportarError({ mensaje: event.message, stack: event.error?.stack, url: event.filename })
})

window.addEventListener('unhandledrejection', (event) => {
  const razon = event.reason
  reportarError({
    mensaje: razon?.message ?? String(razon),
    stack: razon?.stack,
    contexto: { tipo: 'unhandledrejection' },
  })
})

app.use(createPinia())
app.use(router)
app.mount('#app')

initSync()
