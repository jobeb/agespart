<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { liveQuery } from 'dexie'
import { db } from '../services/db'
import { useOnlineStatus } from '../composables/useOnlineStatus'
import SyncFallidosPanel from './SyncFallidosPanel.vue'

const { isOnline } = useOnlineStatus()
const pendientes = ref(0)
const fallidos = ref(0)
const panelAbierto = ref(false)
let subPendientes
let subFallidosOutbox
let subFallidosFotos
let fallidosOutbox = 0
let fallidosFotos = 0

function actualizarFallidos() {
  fallidos.value = fallidosOutbox + fallidosFotos
}

onMounted(() => {
  subPendientes = liveQuery(() => db.outbox.where('estado_sync').notEqual('fallido').count()).subscribe({
    next: (count) => (pendientes.value = count),
  })
  subFallidosOutbox = liveQuery(() => db.outbox.where('estado_sync').equals('fallido').count()).subscribe({
    next: (count) => {
      fallidosOutbox = count
      actualizarFallidos()
    },
  })
  subFallidosFotos = liveQuery(() => db.fotosBlobs.where('estado_sync').equals('fallido').count()).subscribe({
    next: (count) => {
      fallidosFotos = count
      actualizarFallidos()
    },
  })
})

onBeforeUnmount(() => {
  subPendientes?.unsubscribe()
  subFallidosOutbox?.unsubscribe()
  subFallidosFotos?.unsubscribe()
})
</script>

<template>
  <div class="sync-wrapper">
    <button
      type="button"
      class="sync-indicator"
      :class="{ offline: !isOnline, 'con-fallos': fallidos > 0 }"
      @click="panelAbierto = !panelAbierto"
    >
      <span class="punto" />
      <span v-if="fallidos > 0">{{ fallidos }} con error</span>
      <span v-else-if="!isOnline">Sin conexión</span>
      <span v-else-if="pendientes > 0">Sincronizando ({{ pendientes }})</span>
      <span v-else>Al día</span>
    </button>

    <SyncFallidosPanel v-if="panelAbierto" @cerrar="panelAbierto = false" />
  </div>
</template>

<style scoped>
.sync-wrapper {
  position: relative;
}
.sync-indicator {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  opacity: 0.8;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  color: inherit;
  min-height: auto;
}
.punto {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #2e9e4f;
}
.offline .punto {
  background: #d33;
}
.con-fallos .punto {
  background: #d97706;
}
</style>
