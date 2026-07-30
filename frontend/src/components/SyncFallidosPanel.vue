<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { liveQuery } from 'dexie'
import { db } from '../services/db'
import {
  reintentarEntradaOutbox,
  descartarEntradaOutbox,
  reintentarFotoFallida,
  descartarFotoFallida,
} from '../services/sync'
import { useConfirm } from '../composables/useConfirm'
import IconClose from './icons/IconClose.vue'

const emit = defineEmits(['cerrar'])
const { confirmar } = useConfirm()

const entradas = ref([])
const fotos = ref([])
let subEntradas
let subFotos

const etiquetas = {
  crear_incidencia: 'Crear incidencia',
  actualizar_incidencia: 'Actualizar incidencia',
}

onMounted(() => {
  subEntradas = liveQuery(() => db.outbox.where('estado_sync').equals('fallido').toArray()).subscribe({
    next: (valor) => (entradas.value = valor),
  })
  subFotos = liveQuery(() => db.fotosBlobs.where('estado_sync').equals('fallido').toArray()).subscribe({
    next: (valor) => (fotos.value = valor),
  })
})

onBeforeUnmount(() => {
  subEntradas?.unsubscribe()
  subFotos?.unsubscribe()
})

async function descartar(accion, ...args) {
  const ok = await confirmar({
    titulo: 'Descartar cambio pendiente',
    mensaje: 'Este cambio no se ha podido sincronizar y se perderá permanentemente. ¿Descartarlo?',
    textoConfirmar: 'Descartar',
  })
  if (ok) await accion(...args)
}
</script>

<template>
  <div class="overlay" @click.self="emit('cerrar')">
    <div class="panel">
      <div class="cabecera">
        <h2>Cambios con error de sincronización</h2>
        <button type="button" class="cerrar" @click="emit('cerrar')" aria-label="Cerrar"><IconClose /></button>
      </div>

      <p v-if="entradas.length === 0 && fotos.length === 0" class="vacio">No hay cambios pendientes con error.</p>

      <ul class="lista">
        <li v-for="entrada in entradas" :key="`o-${entrada.id}`">
          <div>
            <strong>{{ etiquetas[entrada.tipo_operacion] || entrada.tipo_operacion }}</strong>
            <p>{{ new Date(entrada.created_at).toLocaleString() }} — {{ entrada.intentos }} intentos</p>
          </div>
          <div class="acciones">
            <button type="button" class="btn btn-secondary" @click="reintentarEntradaOutbox(entrada.id)">Reintentar</button>
            <button type="button" class="btn btn-danger" @click="descartar(descartarEntradaOutbox, entrada.id)">Descartar</button>
          </div>
        </li>
        <li v-for="foto in fotos" :key="`f-${foto.uuid_cliente}`">
          <div>
            <strong>Foto: {{ foto.nombre_archivo }}</strong>
            <p>{{ foto.intentos }} intentos</p>
          </div>
          <div class="acciones">
            <button type="button" class="btn btn-secondary" @click="reintentarFotoFallida(foto.uuid_cliente)">Reintentar</button>
            <button type="button" class="btn btn-danger" @click="descartar(descartarFotoFallida, foto.uuid_cliente)">Descartar</button>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 1rem;
  z-index: 1050;
}
.panel {
  background: var(--bg, #fff);
  color: var(--text-h, #000);
  border-radius: 8px;
  padding: 1rem;
  width: 100%;
  max-width: 480px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}
.cabecera {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.cabecera h2 {
  margin: 0;
  font-size: 1.05rem;
}
.cerrar {
  background: none;
  border: none;
  font-size: 1.3rem;
  cursor: pointer;
  min-height: auto;
  padding: 0;
}
.vacio {
  opacity: 0.7;
  margin-top: 0.75rem;
}
.lista {
  list-style: none;
  padding: 0;
  margin: 0.75rem 0 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.lista li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  border: 1px solid rgba(128, 128, 128, 0.2);
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
}
.lista p {
  margin: 0;
  font-size: 0.8rem;
  opacity: 0.7;
}
.acciones {
  display: flex;
  gap: 0.4rem;
  flex-shrink: 0;
}
</style>
