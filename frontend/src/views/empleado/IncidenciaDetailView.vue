<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { liveQuery } from 'dexie'
import { db } from '../../services/db'
import { api } from '../../services/api'
import { useIncidenciasStore } from '../../stores/incidencias'
import EstadoBadge from '../../components/EstadoBadge.vue'
import PrioridadBadge from '../../components/PrioridadBadge.vue'
import FotoUploader from '../../components/FotoUploader.vue'
import FirmaCanvas from '../../components/FirmaCanvas.vue'
import HistorialIncidencia from '../../components/HistorialIncidencia.vue'

const route = useRoute()
const store = useIncidenciasStore()
const incidencia = ref(null)
const nuevasFotos = ref([])
const eventos = ref([])
const enviandoComentario = ref(false)
const mostrarFirma = ref(false)
const firma = ref(null)
const nombreReceptor = ref('')
let subscripcion

async function cambiarEstado(estado) {
  await store.actualizarEstado(route.params.uuid, estado)
}

async function resolverSinFirma() {
  await store.resolverConConformidad(route.params.uuid)
  mostrarFirma.value = false
}

async function resolverConFirma() {
  await store.resolverConConformidad(route.params.uuid, {
    firma_base64: firma.value,
    firma_nombre_receptor: nombreReceptor.value || null,
  })
  mostrarFirma.value = false
}

async function subirFotos() {
  for (const archivo of nuevasFotos.value) {
    await store.agregarFoto(route.params.uuid, archivo)
  }
  nuevasFotos.value = []
}

async function cargarEventos() {
  if (!navigator.onLine || !incidencia.value?.server_id) return
  try {
    const { data } = await api.get(`/incidencias/${incidencia.value.server_id}/eventos`)
    eventos.value = data.data
  } catch {
    // sin red real o error: el historial simplemente no se muestra esta vez
  }
}

async function comentar(texto) {
  enviandoComentario.value = true
  try {
    await store.agregarComentario(route.params.uuid, texto)
    if (navigator.onLine) await cargarEventos()
  } finally {
    enviandoComentario.value = false
  }
}

onMounted(() => {
  // Lectura reactiva: si sync.js actualiza esta incidencia en segundo plano
  // (p. ej. al reconectar), la vista se refresca sola sin recargar.
  subscripcion = liveQuery(() => db.incidenciasCache.get(route.params.uuid)).subscribe({
    next: (valor) => {
      incidencia.value = valor
      cargarEventos()
    },
  })
})

onBeforeUnmount(() => subscripcion?.unsubscribe())
</script>

<template>
  <div v-if="incidencia" class="detalle">
    <h1>{{ incidencia.data.tipo === 'reparacion' ? 'Reparación' : 'Instalación' }}</h1>
    <div class="badges">
      <EstadoBadge :estado="incidencia.estado" />
      <PrioridadBadge v-if="incidencia.data.prioridad" :prioridad="incidencia.data.prioridad" />
    </div>
    <p v-if="incidencia.estado_sync === 'pendiente'" class="pendiente-sync">⏳ Pendiente de sincronizar</p>

    <p>{{ incidencia.data.descripcion }}</p>
    <p v-if="incidencia.data.direccion"><strong>Dirección:</strong> {{ incidencia.data.direccion }}</p>

    <div class="acciones-estado">
      <button type="button" :disabled="incidencia.estado === 'en_curso'" @click="cambiarEstado('en_curso')">
        Marcar en curso
      </button>
      <button
        v-if="incidencia.estado !== 'resuelta'"
        type="button"
        class="btn btn-primary"
        @click="mostrarFirma = !mostrarFirma"
      >
        Marcar resuelta
      </button>
    </div>

    <div v-if="mostrarFirma" class="card conformidad">
      <h2>Conformidad de cierre (opcional)</h2>
      <label>
        Nombre de quien recibe
        <input v-model="nombreReceptor" type="text" placeholder="Opcional" />
      </label>
      <FirmaCanvas v-model="firma" />
      <div class="acciones-firma">
        <button type="button" class="btn btn-primary" :disabled="!firma" @click="resolverConFirma">Confirmar con firma</button>
        <button type="button" class="btn btn-secondary" @click="resolverSinFirma">Marcar resuelta sin firma</button>
      </div>
    </div>

    <RouterLink v-if="incidencia.server_id" :to="{ name: 'parte-trabajo', params: { id: incidencia.server_id } }">
      Ver parte de trabajo
    </RouterLink>

    <FotoUploader v-model="nuevasFotos" />
    <button v-if="nuevasFotos.length" type="button" @click="subirFotos">Guardar fotos</button>

    <HistorialIncidencia :eventos="eventos" :enviando="enviandoComentario" @comentar="comentar" />
  </div>
</template>

<style scoped>
.detalle {
  padding: 1rem;
  max-width: 480px;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.badges {
  display: flex;
  gap: 0.5rem;
}
.acciones-estado {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.pendiente-sync {
  opacity: 0.7;
  margin: 0;
}
.conformidad {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.conformidad label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
}
.acciones-firma {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
</style>
