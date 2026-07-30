<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../services/api'
import { useToastsStore } from '../../stores/toasts'
import MapaLeaflet from '../../components/MapaLeaflet.vue'
import FiltrosIncidencias from '../../components/admin/FiltrosIncidencias.vue'

const incidencias = ref([])
const empleados = ref([])
const cargando = ref(false)
const filtros = ref({ estado: '', empleado_id: '', prioridad: '', tipo: '', desde: '', hasta: '' })
const router = useRouter()
const toasts = useToastsStore()
const mapaRef = ref(null)

const marcadores = computed(() =>
  incidencias.value.map((incidencia) => ({
    id: incidencia.id,
    lat: incidencia.lat,
    lng: incidencia.lng,
    estado: incidencia.estado,
    popup: `<strong>${incidencia.tipo}</strong><br>${incidencia.descripcion ?? ''}<br>${incidencia.empleado?.name ?? 'Sin asignar'}`,
  }))
)

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/incidencias', { params: { ...filtros.value, per_page: 500 } })
    incidencias.value = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudieron cargar las incidencias del mapa.' })
  } finally {
    cargando.value = false
  }
}

watch(filtros, cargar, { deep: true })

onMounted(async () => {
  try {
    const { data } = await api.get('/empleados')
    empleados.value = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo cargar la lista de empleados.' })
  }
  await cargar()
})

function irADetalle(id) {
  router.push({ name: 'admin-incidencia-detalle', params: { id } })
}

async function alAlternarFiltros() {
  await nextTick()
  mapaRef.value?.invalidateSize()
}
</script>

<template>
  <div class="dashboard">
    <details class="filtros" open @toggle="alAlternarFiltros">
      <summary>Filtros <span class="contador">({{ incidencias.length }})</span></summary>
      <FiltrosIncidencias v-model="filtros" :empleados="empleados" />
    </details>

    <div class="mapa-contenedor">
      <MapaLeaflet ref="mapaRef" :markers="marcadores" @marker-click="irADetalle" />
    </div>
  </div>
</template>

<style scoped>
.dashboard {
  display: flex;
  flex: 1;
  min-height: 0;
}
.filtros {
  width: 240px;
  flex-shrink: 0;
  padding: 1rem;
  border-right: 1px solid var(--border);
  overflow-y: auto;
}
.filtros summary {
  cursor: pointer;
  font-weight: 600;
  margin-bottom: 0.75rem;
}
.mapa-contenedor {
  flex: 1;
  min-height: 300px;
}
.contador {
  opacity: 0.7;
  font-weight: 400;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .dashboard {
    flex-direction: column;
  }
  .filtros {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid var(--border);
    padding: 0.75rem 1rem;
  }
}
</style>
