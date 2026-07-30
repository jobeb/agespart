<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { liveQuery } from 'dexie'
import { db } from '../../services/db'
import { useIncidenciasStore } from '../../stores/incidencias'
import EstadoBadge from '../../components/EstadoBadge.vue'
import FotoUploader from '../../components/FotoUploader.vue'

const route = useRoute()
const store = useIncidenciasStore()
const incidencia = ref(null)
const nuevasFotos = ref([])
let subscripcion

async function cambiarEstado(estado) {
  await store.actualizarEstado(route.params.uuid, estado)
}

async function subirFotos() {
  for (const archivo of nuevasFotos.value) {
    await store.agregarFoto(route.params.uuid, archivo)
  }
  nuevasFotos.value = []
}

onMounted(() => {
  // Lectura reactiva: si sync.js actualiza esta incidencia en segundo plano
  // (p. ej. al reconectar), la vista se refresca sola sin recargar.
  subscripcion = liveQuery(() => db.incidenciasCache.get(route.params.uuid)).subscribe({
    next: (valor) => (incidencia.value = valor),
  })
})

onBeforeUnmount(() => subscripcion?.unsubscribe())
</script>

<template>
  <div v-if="incidencia" class="detalle">
    <h1>{{ incidencia.data.tipo === 'reparacion' ? 'Reparación' : 'Instalación' }}</h1>
    <EstadoBadge :estado="incidencia.estado" />
    <p v-if="incidencia.estado_sync === 'pendiente'" class="pendiente-sync">⏳ Pendiente de sincronizar</p>

    <p>{{ incidencia.data.descripcion }}</p>
    <p v-if="incidencia.data.direccion"><strong>Dirección:</strong> {{ incidencia.data.direccion }}</p>

    <div class="acciones-estado">
      <button type="button" :disabled="incidencia.estado === 'en_curso'" @click="cambiarEstado('en_curso')">
        Marcar en curso
      </button>
      <button type="button" :disabled="incidencia.estado === 'resuelta'" @click="cambiarEstado('resuelta')">
        Marcar resuelta
      </button>
    </div>

    <FotoUploader v-model="nuevasFotos" />
    <button v-if="nuevasFotos.length" type="button" @click="subirFotos">Guardar fotos</button>
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
.acciones-estado {
  display: flex;
  gap: 0.5rem;
}
.pendiente-sync {
  opacity: 0.7;
  margin: 0;
}
</style>
