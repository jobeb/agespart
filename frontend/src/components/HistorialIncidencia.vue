<script setup>
import { ref } from 'vue'

const props = defineProps({
  eventos: { type: Array, default: () => [] },
  enviando: { type: Boolean, default: false },
})
const emit = defineEmits(['comentar'])

const texto = ref('')

const etiquetas = {
  creacion: 'Incidencia creada',
  cambio_estado: 'Cambio de estado',
  cambio_asignacion: 'Reasignación',
  cambio_prioridad: 'Cambio de prioridad',
  comentario: 'Comentario',
}

function descripcion(evento) {
  if (evento.tipo === 'comentario' || evento.tipo === 'creacion') return null
  const campo = Object.keys(evento.datos_nuevos || {})[0]
  if (!campo) return null
  return `${evento.datos_previos?.[campo] ?? '—'} → ${evento.datos_nuevos?.[campo] ?? '—'}`
}

function enviar() {
  if (!texto.value.trim()) return
  emit('comentar', texto.value.trim())
  texto.value = ''
}
</script>

<template>
  <div class="historial">
    <h2>Historial</h2>

    <ul v-if="eventos.length" class="lista">
      <li v-for="evento in eventos" :key="evento.id ?? evento.uuid_cliente">
        <div class="cabecera">
          <strong>{{ etiquetas[evento.tipo] || evento.tipo }}</strong>
          <span class="meta">{{ evento.actor_nombre ?? 'Sistema' }} · {{ new Date(evento.created_at).toLocaleString() }}</span>
        </div>
        <p v-if="evento.tipo === 'comentario'">{{ evento.comentario }}</p>
        <p v-else-if="descripcion(evento)" class="cambio">{{ descripcion(evento) }}</p>
      </li>
    </ul>
    <p v-else class="vacio">Sin actividad todavía.</p>

    <form class="nuevo-comentario" @submit.prevent="enviar">
      <textarea v-model="texto" rows="2" placeholder="Añadir un comentario…" />
      <button type="submit" class="btn btn-secondary" :disabled="enviando || !texto.trim()">Comentar</button>
    </form>
  </div>
</template>

<style scoped>
.historial {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.lista {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 320px;
  overflow-y: auto;
}
.lista li {
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 0.5rem 0.75rem;
}
.cabecera {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.meta {
  font-size: 0.8rem;
  opacity: 0.7;
}
.cambio {
  margin-top: 0.25rem;
  font-size: 0.9rem;
  opacity: 0.85;
}
.vacio {
  opacity: 0.7;
}
.nuevo-comentario {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
</style>
