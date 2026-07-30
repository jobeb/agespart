<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { liveQuery } from 'dexie'
import { db } from '../../services/db'
import { useIncidenciasStore } from '../../stores/incidencias'
import EstadoBadge from '../../components/EstadoBadge.vue'

const store = useIncidenciasStore()
const incidencias = ref([])
let subscripcion

onMounted(() => {
  store.cargarMisIncidencias()

  // Lectura reactiva de Dexie: la lista se actualiza sola en cuanto sync.js
  // marca una incidencia como sincronizada, sin esperar a recargar la vista.
  subscripcion = liveQuery(() => db.incidenciasCache.orderBy('updated_at').reverse().toArray()).subscribe({
    next: (valor) => (incidencias.value = valor),
  })
})

onBeforeUnmount(() => subscripcion?.unsubscribe())
</script>

<template>
  <div class="mis-incidencias">
    <h1>Mis incidencias</h1>

    <p v-if="store.cargando && incidencias.length === 0">Cargando…</p>
    <p v-else-if="incidencias.length === 0">No tienes incidencias asignadas todavía.</p>

    <ul v-else class="lista">
      <li v-for="incidencia in incidencias" :key="incidencia.uuid_cliente">
        <RouterLink :to="{ name: 'empleado-incidencia-detalle', params: { uuid: incidencia.uuid_cliente } }">
          <div class="fila">
            <div>
              <strong>{{ incidencia.data.tipo === 'reparacion' ? 'Reparación' : 'Instalación' }}</strong>
              <p>{{ incidencia.data.descripcion }}</p>
            </div>
            <div class="estados">
              <EstadoBadge :estado="incidencia.estado" />
              <span v-if="incidencia.estado_sync === 'pendiente'" class="pendiente-sync">⏳ pendiente de sincronizar</span>
            </div>
          </div>
        </RouterLink>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.mis-incidencias {
  padding: 1rem;
}
.lista {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.lista a {
  text-decoration: none;
  color: inherit;
}
.fila {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border: 1px solid rgba(128, 128, 128, 0.2);
  border-radius: 8px;
}
.estados {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.3rem;
  font-size: 0.8rem;
}
.pendiente-sync {
  opacity: 0.7;
}
</style>
