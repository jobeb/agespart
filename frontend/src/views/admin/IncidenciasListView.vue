<script setup>
import { onMounted, ref, watch } from 'vue'
import { api } from '../../services/api'
import { useToastsStore } from '../../stores/toasts'
import EstadoBadge from '../../components/EstadoBadge.vue'
import FiltrosIncidencias from '../../components/admin/FiltrosIncidencias.vue'

const incidencias = ref([])
const empleados = ref([])
const cargando = ref(true)
const pagina = ref(1)
const meta = ref(null)
const filtros = ref({ estado: '', empleado_id: '', tipo: '', desde: '', hasta: '' })
const toasts = useToastsStore()

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/incidencias', { params: { ...filtros.value, page: pagina.value } })
    incidencias.value = data.data
    meta.value = data.meta
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudieron cargar las incidencias.' })
  } finally {
    cargando.value = false
  }
}

watch(filtros, () => {
  pagina.value = 1
  cargar()
}, { deep: true })

function irAPagina(nueva) {
  pagina.value = nueva
  cargar()
}

onMounted(async () => {
  try {
    const { data } = await api.get('/empleados')
    empleados.value = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo cargar la lista de empleados.' })
  }
  await cargar()
})
</script>

<template>
  <div class="lista">
    <h1>Incidencias</h1>

    <FiltrosIncidencias v-model="filtros" :empleados="empleados" />

    <p v-if="cargando">Cargando…</p>

    <template v-else>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Descripción</th>
              <th>Estado</th>
              <th>Empleado</th>
              <th>Fecha</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="incidencia in incidencias" :key="incidencia.id">
              <td data-label="Tipo">{{ incidencia.tipo }}</td>
              <td data-label="Descripción">{{ incidencia.descripcion }}</td>
              <td data-label="Estado"><EstadoBadge :estado="incidencia.estado" /></td>
              <td data-label="Empleado">{{ incidencia.empleado?.name ?? 'Sin asignar' }}</td>
              <td data-label="Fecha">{{ new Date(incidencia.created_at).toLocaleString() }}</td>
              <td data-label="">
                <RouterLink :to="{ name: 'admin-incidencia-detalle', params: { id: incidencia.id } }">Ver</RouterLink>
              </td>
            </tr>
            <tr v-if="incidencias.length === 0">
              <td colspan="6">No hay incidencias con estos filtros.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta" class="paginacion">
        <button type="button" :disabled="meta.current_page <= 1" @click="irAPagina(meta.current_page - 1)">Anterior</button>
        <span>Página {{ meta.current_page }} de {{ meta.last_page }} ({{ meta.total }} en total)</span>
        <button type="button" :disabled="meta.current_page >= meta.last_page" @click="irAPagina(meta.current_page + 1)">Siguiente</button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.lista {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.table-scroll {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th,
td {
  text-align: left;
  padding: 0.5rem;
  border-bottom: 1px solid rgba(128, 128, 128, 0.2);
}
.paginacion {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.9rem;
}

@media (max-width: 640px) {
  thead {
    display: none;
  }
  table,
  tbody,
  tr,
  td {
    display: block;
    width: 100%;
  }
  tr {
    margin-bottom: 0.75rem;
    border: 1px solid rgba(128, 128, 128, 0.2);
    border-radius: 8px;
    padding: 0.5rem;
  }
  td {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    border-bottom: none;
    padding: 0.25rem 0.25rem;
  }
  td[data-label]:not([data-label=''])::before {
    content: attr(data-label);
    font-weight: 600;
    opacity: 0.7;
  }
}
</style>
