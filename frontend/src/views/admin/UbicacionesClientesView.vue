<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../../services/api'
import { useToastsStore } from '../../stores/toasts'
import { useConfirm } from '../../composables/useConfirm'

const ubicaciones = ref([])
const nueva = ref({ nombre: '', direccion: '', contacto: '' })
const error = ref('')
const creando = ref(false)
const toasts = useToastsStore()
const { confirmar } = useConfirm()

async function cargar() {
  try {
    const { data } = await api.get('/ubicaciones-clientes', { params: { todas: 1 } })
    ubicaciones.value = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo cargar el catálogo de ubicaciones.' })
  }
}

async function crear() {
  error.value = ''
  creando.value = true
  try {
    await api.post('/ubicaciones-clientes', nueva.value)
    nueva.value = { nombre: '', direccion: '', contacto: '' }
    await cargar()
    toasts.notificar({ tipo: 'exito', mensaje: 'Ubicación creada.' })
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo crear la ubicación.'
  } finally {
    creando.value = false
  }
}

async function alternarActivo(ubicacion) {
  if (ubicacion.activo) {
    const ok = await confirmar({
      titulo: 'Desactivar ubicación',
      mensaje: `¿Desactivar "${ubicacion.nombre}"? Dejará de aparecer como opción al crear incidencias.`,
      textoConfirmar: 'Desactivar',
    })
    if (!ok) return
  }

  try {
    const { data } = await api.patch(`/ubicaciones-clientes/${ubicacion.id}`, { activo: !ubicacion.activo })
    const index = ubicaciones.value.findIndex((u) => u.id === ubicacion.id)
    ubicaciones.value[index] = data.data
  } catch {
    toasts.notificar({ tipo: 'error', mensaje: 'No se pudo actualizar la ubicación.' })
  }
}

onMounted(cargar)
</script>

<template>
  <div class="ubicaciones">
    <h1>Ubicaciones de clientes</h1>

    <form class="nuevo" @submit.prevent="crear">
      <input v-model="nueva.nombre" placeholder="Nombre" required />
      <input v-model="nueva.direccion" placeholder="Dirección" />
      <input v-model="nueva.contacto" placeholder="Contacto (opcional)" />
      <button type="submit" class="btn btn-primary" :disabled="creando">{{ creando ? 'Creando…' : 'Añadir ubicación' }}</button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>

    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Contacto</th>
            <th>Activa</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ubicacion in ubicaciones" :key="ubicacion.id">
            <td>{{ ubicacion.nombre }}</td>
            <td>{{ ubicacion.direccion }}</td>
            <td>{{ ubicacion.contacto }}</td>
            <td>
              <button type="button" class="btn btn-secondary" @click="alternarActivo(ubicacion)">
                {{ ubicacion.activo ? 'Activa' : 'Inactiva' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.ubicaciones {
  padding: 1rem;
  max-width: 720px;
}
.nuevo {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}
.nuevo input {
  padding: 0.5rem;
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
  white-space: nowrap;
}
.error {
  color: #d33;
}
</style>
