import { createApp } from 'vue'
import { createPinia } from 'pinia'
import 'leaflet/dist/leaflet.css'
import './style.css'
import App from './App.vue'
import { router } from './router'
import { initSync } from './services/sync'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.mount('#app')

initSync()
