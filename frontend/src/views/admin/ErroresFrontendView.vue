<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { useToastsStore } from '../../stores/toasts'

const errores = ref([])
const cargando = ref(true)
const expandido = ref(null)
const toasts = useToastsStore()

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/frontend-errors')
    errores.value = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudieron cargar los errores.' })
  } finally {
    cargando.value = false
  }
}

function alternar(id) {
  expandido.value = expandido.value === id ? null : id
}

onMounted(cargar)
</script>

<template>
  <div class="errores">
    <h1>Errores de frontend</h1>

    <p v-if="cargando">Cargando…</p>
    <p v-else-if="errores.length === 0">No se han registrado errores. Buena señal.</p>

    <ul v-else class="lista">
      <li v-for="error in errores" :key="error.id" class="item">
        <button type="button" class="cabecera" @click="alternar(error.id)">
          <span class="mensaje">{{ error.mensaje }}</span>
          <span class="meta">{{ new Date(error.created_at).toLocaleString() }} · {{ error.usuario ?? 'anónimo' }}</span>
        </button>
        <div v-if="expandido === error.id" class="detalle">
          <p v-if="error.url"><strong>URL:</strong> {{ error.url }}</p>
          <p v-if="error.user_agent"><strong>Navegador:</strong> {{ error.user_agent }}</p>
          <pre v-if="error.stack">{{ error.stack }}</pre>
        </div>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.errores {
  padding: 1rem;
}
.lista {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.item {
  border: 1px solid var(--border);
  border-radius: var(--radius);
}
.cabecera {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.15rem;
  background: none;
  border: none;
  text-align: left;
  padding: 0.6rem 0.8rem;
}
.mensaje {
  font-weight: 600;
  color: var(--danger);
}
.meta {
  font-size: 0.8rem;
  opacity: 0.7;
}
.detalle {
  padding: 0 0.8rem 0.8rem;
  font-size: 0.85rem;
}
.detalle pre {
  white-space: pre-wrap;
  word-break: break-word;
  background: var(--bg-soft);
  padding: 0.5rem;
  border-radius: var(--radius);
  max-height: 300px;
  overflow-y: auto;
}
</style>
